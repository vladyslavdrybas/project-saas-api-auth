<?php

declare(strict_types=1);

const HTTP_UNAUTHORIZED = 401;
const HTTP_OK = 200;

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(array $data, int $code = 200): string
{
    http_response_code($code);

    return json_encode($data);
}

$proxyNetwork = $_SERVER['PROXY_NETWORK'];
$env = $_SERVER['APP_ENV'];
$requestUri = $_SERVER['REQUEST_URI'];

$isAuthUri = str_starts_with($requestUri, '/auth/check?');
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

$isApiKey = isset($_GET['apiKey']);
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

$apiKey = htmlspecialchars($_GET['apiKey']);
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

$query = 'SELECT api_key as apiKey, enddate FROM smfn_api_key WHERE api_key = :APIKEY';

try {
    $statement = $pdo->prepare($query, [PDO::FETCH_ASSOC]);
    $statement->execute(['APIKEY' => $apiKey]);
    $dbApiKey = $statement->fetch();
    if (false === $dbApiKey) {
        throw new \Exception('Not found.');
    }
    $now = new \DateTime();
    $endDate = new \DateTime($dbApiKey['enddate']);

    if ($endDate < $now) {
        throw new \Exception('Api key expired.');
    }

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
