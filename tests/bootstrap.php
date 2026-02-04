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

// Mock /etc/unraid-version - required by exec.php
UnraidStreamWrapper::addMockContent(
    '/etc/unraid-version',
    "version=\"7.0.0\"\n"
);

// Mock /var/run/dockerd.pid for Docker running check
UnraidStreamWrapper::addMockContent(
    '/var/run/dockerd.pid',
    "12345\n"
);

// Mock dynamix config
FunctionMocks::setPluginConfig('dynamix', [
    'theme' => 'black',
    'display' => 'array',
]);

// Set $docroot which CA uses extensively
$GLOBALS['docroot'] = '/usr/local/emhttp';
$_SERVER['DOCUMENT_ROOT'] = '/usr/local/emhttp';
$_SERVER['REQUEST_URI'] = 'docker/apps';

// Set up temp directory for CA
$caTempDir = sys_get_temp_dir() . '/community.applications/tempFiles';
if (!is_dir($caTempDir)) {
    mkdir($caTempDir, 0777, true);
}

// Define caPaths global that helpers.php expects
$GLOBALS['caPaths'] = [
    'tempFiles' => $caTempDir,
    'flashDrive' => sys_get_temp_dir() . '/ca-flash',
];

// Define caSettings global
$GLOBALS['caSettings'] = [
    'maxPerPage' => 24,
    'dockerSearch' => 'yes',
    'unRaidVersion' => '7.0.0',
    'dev' => 'no',
];

// Note: debug() is defined in helpers.php, so we don't define it here
