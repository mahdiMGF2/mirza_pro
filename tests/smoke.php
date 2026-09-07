<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$httpContract = $root . '/src/Contracts/HttpClientInterface.php';
$panelContract = $root . '/src/Contracts/PanelAdapterInterface.php';
$assert(is_file($httpContract), 'HTTP client contract is missing');
$assert(is_file($panelContract), 'Panel adapter contract is missing');

require_once $httpContract;
require_once $panelContract;

$httpMethods = ['setHeaders', 'setBearerToken', 'setCookie', 'get', 'post', 'put', 'delete', 'patch'];
$httpReflection = new ReflectionClass('HttpClientInterface');
foreach ($httpMethods as $method) {
    $assert($httpReflection->hasMethod($method), "HTTP contract method is missing: {$method}");
}

$panelMethods = ['createUser', 'getUser', 'deleteUser'];
$panelReflection = new ReflectionClass('PanelAdapterInterface');
foreach ($panelMethods as $method) {
    $assert($panelReflection->hasMethod($method), "Panel contract method is missing: {$method}");
}

$rootHtaccess = (string) file_get_contents($root . '/.htaccess');
$apiHtaccess = (string) file_get_contents($root . '/api/.htaccess');
$assert(str_contains($rootHtaccess, 'FilesMatch "^(config|composer)\\.php$"'), 'Root config protection is missing');
$assert(str_contains($apiHtaccess, 'utils.php'), 'API utility protection is missing');
$assert(str_contains($apiHtaccess, 'error_log'), 'API log protection is missing');

$installer = (string) file_get_contents($root . '/install.sh');
$assert(str_contains($installer, 'umask 077'), 'Installer private umask is missing');
$assert(str_contains($installer, 'install -m 0755 "$TEMP_FILE" "${MASTER_PATH}.new"'), 'Atomic self-update staging is missing');

$config = (string) file_get_contents($root . '/config.php');
$assert(str_contains($config, "MIRZA_BRAND_NAME"), 'Configurable display brand is missing');
$assert(str_contains($config, "\$brandname = 'Root Bot';"), 'Default display brand is missing');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Mirza smoke tests passed (" . (count($httpMethods) + count($panelMethods) + 7) . " checks)." . PHP_EOL);
