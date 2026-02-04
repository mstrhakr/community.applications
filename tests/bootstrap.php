<?php
/**
 * Community Applications Test Bootstrap
 * 
 * Uses the plugin-tests framework to enable testing of CA code
 */

// Load the framework
require_once __DIR__ . '/framework/src/php/bootstrap.php';

use PluginTests\PluginBootstrap;
use PluginTests\StreamWrapper\UnraidStreamWrapper;
use PluginTests\Mocks\FunctionMocks;

// Source root - community.applications has a deeper path structure
$sourceRoot = realpath(__DIR__ . '/../source/community.applications/usr/local/emhttp/plugins/community.applications');

if ($sourceRoot === false) {
    throw new \RuntimeException("Source root not found. Expected at: " . __DIR__ . '/../source/community.applications/usr/local/emhttp/plugins/community.applications');
}

// Initialize with plugin name - we map from the 'include' subdirectory  
PluginBootstrap::init(
    'community.applications',
    $sourceRoot . '/include',
    [
        'config' => [
            // Default settings from default.cfg
            'maxPerPage' => '24',
            'dockerSearch' => 'yes',
            'iconSize' => '96',
            'dev' => 'no',
        ],
        'subPath' => 'include',
    ]
);

// Map additional directories that community.applications uses
// Scripts directory
$scriptsDir = $sourceRoot . '/scripts';
foreach (glob("$scriptsDir/*.php") as $file) {
    $basename = basename($file);
    UnraidStreamWrapper::addMapping(
        "/usr/local/emhttp/plugins/community.applications/scripts/$basename",
        $file
    );
}

// Skins directory (recursive)
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceRoot . '/skins', FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $relativePath = str_replace($sourceRoot, '', $file->getPathname());
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        UnraidStreamWrapper::addMapping(
            "/usr/local/emhttp/plugins/community.applications" . $relativePath,
            $file->getPathname()
        );
    }
}

// Mock dynamix config - required BEFORE paths.php is loaded
FunctionMocks::setPluginConfig('dynamix', [
    'theme' => 'black',
    'display' => 'array',
]);

// Set up dynamixSettings global which paths.php uses
$GLOBALS['dynamixSettings'] = [
    'theme' => 'black',
    'display' => 'array',
];

// Set $docroot which CA uses extensively
// Must be set as both GLOBALS and local variable because exec.php uses: $docroot = $docroot ?? ...
$docroot = '/usr/local/emhttp';
$GLOBALS['docroot'] = $docroot;
$_SERVER['DOCUMENT_ROOT'] = $docroot;
$_SERVER['REQUEST_URI'] = 'docker/apps';

// Pre-create the CA logs directory and log file
// This prevents the debug() infinite loop (debug() calls ca_plugin() on first init)
$caLogsDir = '/tmp/CA_logs';
$logFile = "$caLogsDir/ca_log.txt";
if (!is_dir($caLogsDir)) {
    mkdir($caLogsDir, 0777, true);
}
if (!file_exists($logFile)) {
    file_put_contents($logFile, "Test log initialized\n");
}

// Create required temp directories that exec.php expects
$tempDirs = [
    '/tmp/CA_logs',
    '/tmp/community.applications/tempFiles',
    '/tmp/community.applications/templates-community',
];
foreach ($tempDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Include paths.php first to set up $caPaths - this is required before helpers.php
require_once '/usr/local/emhttp/plugins/community.applications/include/paths.php';

// Debug: verify $caPaths was set
if (!isset($caPaths)) {
    throw new \RuntimeException('paths.php did not set $caPaths');
}
// Ensure it's in GLOBALS
$GLOBALS['caPaths'] = $caPaths;

// Define caSettings global - matches what parse_plugin_cfg returns merged with defaults
// This must be set because helpers.php functions use it
$GLOBALS['caSettings'] = [
    'maxPerPage' => 24,
    'dockerSearch' => 'yes',
    'unRaidVersion' => '7.0.0',
    'dockerRunning' => true,
    'favourite' => '',
    'hideIncompatible' => 'false',
    'hideDeprecated' => 'false',
    'startup' => 'random',
    'iconSize' => '96',
    'dev' => 'no',
    'dynamixTheme' => 'black',
];

// Define sortOrder global - default sort state
$GLOBALS['sortOrder'] = [
    'sortBy' => 'Name',
    'sortDir' => 'Up',
];

// Now include helpers.php which uses $caPaths
require_once '/usr/local/emhttp/plugins/community.applications/include/helpers.php';

// Note: All CA functions from helpers.php are now available for testing
// exec.php is NOT included by default because it has too many side effects
// Tests that need exec.php functions should include it themselves with proper setup
