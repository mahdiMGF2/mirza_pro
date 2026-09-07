<?php
// This variable added for high load panels which their response time is long and bot can't communicate with online panel!
// null for default settings
$request_exec_timeout = null;

$dbhost = '{database_url}';
$dbname = '{database_name}';
$usernamedb = '{username_db}';
$passworddb = '{password_db}';
$APIKEY = '{API_KEY}';
$adminnumber = '{admin_number}';
$domainhosts = '{domain_name}';
$usernamebot = '{username_bot}';
$brandname = 'Root Bot';

// Environment values take precedence when provided; installer-generated values remain supported.
$configValue = static function (string $environment, string $fallback): string {
    $value = getenv($environment);
    return $value === false ? $fallback : (string) $value;
};

$dbhost = $configValue('MIRZA_DB_HOST', $dbhost);
$dbname = $configValue('MIRZA_DB_NAME', $dbname);
$usernamedb = $configValue('MIRZA_DB_USER', $usernamedb);
$passworddb = $configValue('MIRZA_DB_PASSWORD', $passworddb);
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];
$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("error: database connection failed");
}
$APIKEY = $configValue('MIRZA_TELEGRAM_BOT_TOKEN', $APIKEY);
$adminnumber = $configValue('MIRZA_ADMIN_CHAT_ID', $adminnumber);
$domainhosts = $configValue('MIRZA_DOMAIN', $domainhosts);
$usernamebot = $configValue('MIRZA_BOT_USERNAME', $usernamebot);
$brandname = $configValue('MIRZA_BRAND_NAME', $brandname);

?>
