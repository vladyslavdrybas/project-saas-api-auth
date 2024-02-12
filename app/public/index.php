<?php

declare(strict_types=1);

$proxyNetwork = $_SERVER['PROXY_NETWORK'];
$env = $_SERVER['APP_ENV'];
$requestUri = $_SERVER['REQUEST_URI'];

$isAuthUri = str_starts_with($requestUri, '/auth/check?');
if (!$isAuthUri) {
    die('Api auth path not found.');
}

$isApiKey = isset($_GET['apiKey']);
if (!$isApiKey) {
    die('Api key not found.');
}

$apiKey = htmlspecialchars($_GET['apiKey']);
$apiKey = htmlentities($apiKey);
if (false === preg_match('/^[a-zA-Z0-9]{64}$/', $apiKey) || strlen($apiKey) !== 64) {
    die('Api key validation fail.');
}

$pdo = null;
try {
    $dsn = $_SERVER['DATABASE_URL'];
    // make a database connection
    $pdo = new \PDO($dsn);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die($e->getMessage());
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
    die($e->getMessage());
}

echo 'OK';
