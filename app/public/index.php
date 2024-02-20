<?php

declare(strict_types=1);

const HTTP_UNAUTHORIZED = 401;
const HTTP_OK = 200;

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(array $data, int $code = HTTP_OK): string
{
    http_response_code($code);

    return json_encode($data);
}

$proxyNetwork = $_SERVER['PROXY_NETWORK'];
$env = $_SERVER['APP_ENV'];
$requestUri = $_SERVER['REQUEST_URI'];


$isAuthUri = $requestUri === '/auth/check';
if (!$isAuthUri) {
    die(
        jsonResponse(
            [
                'error' => 'Api auth path not found.',
            ],
            HTTP_UNAUTHORIZED
        )
    );
}

$isApiKey = isset($_SERVER['HTTP_X_AUTH_API_KEY']);
if (!$isApiKey) {
    die(
        jsonResponse(
            [
                'error' => 'Api key not found.',
            ],
            HTTP_UNAUTHORIZED
        )
    );
}

$apiKey = htmlspecialchars($_SERVER['HTTP_X_AUTH_API_KEY']);
$apiKey = htmlentities($apiKey);
if (false === preg_match('/^[a-zA-Z0-9]{64}$/', $apiKey) || strlen($apiKey) !== 64) {
    die(
        jsonResponse(
            [
                'error' => 'Api key validation fail.',
            ],
            HTTP_UNAUTHORIZED
        )
    );
}

$pdo = null;
try {
    $dsn = $_SERVER['DATABASE_URL'];
    // make a database connection
    $pdo = new \PDO($dsn);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(
        jsonResponse(
            [
                'error' => $e->getMessage(),
            ],
            HTTP_UNAUTHORIZED
        )
    );
}

$query = 'SELECT l.owner_id as sender, ak.end_date as end_date, ak.cost_per_call as cost_per_call, l.balance as owner_balance FROM smfn_api_key as ak INNER JOIN smfn_ledger as l ON l.owner_id = ak.owner_id WHERE api_key = :APIKEY';

try {
    $statement = $pdo->prepare($query, [PDO::FETCH_ASSOC]);
    $statement->execute(['APIKEY' => $apiKey]);
    $dbApiKey = $statement->fetch(PDO::FETCH_ASSOC);

    if (false === $dbApiKey) {
        throw new \Exception('Not found.');
    }

    if ($dbApiKey['owner_balance'] < $dbApiKey['cost_per_call']) {
        throw new \Exception('Not enough credits.');
    }

    if ($dbApiKey['end_date'] instanceof DateTimeInterface) {
        $now = new DateTime();
        if ($dbApiKey['end_date'] < $now) {
            throw new \Exception('Api key expired.');
        }
    }

    $query = 'UPDATE smfn_ledger SET balance = :NEW_BALANCE WHERE owner_id = :SENDER';
    $statement = $pdo->prepare($query, [PDO::FETCH_ASSOC]);
    $statement->execute([
        'NEW_BALANCE' => $dbApiKey['owner_balance'] - $dbApiKey['cost_per_call'],
        'SENDER' => $dbApiKey['sender']
    ]);

} catch (\Exception $e) {
    die(
        jsonResponse(
            [
                'error' => $e->getMessage(),
            ],
            HTTP_UNAUTHORIZED
        )
    );
}

http_response_code(HTTP_OK);

echo 'OK';
