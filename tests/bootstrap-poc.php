<?php
/**
 * Community Applications Test Bootstrap - PROOF OF CONCEPT
 * 
 * Demonstrates how to use the plugin-tests framework with community.applications
 */

// Load the framework
require_once __DIR__ . '/framework/src/php/bootstrap.php';

use PluginTests\PluginBootstrap;
use PluginTests\StreamWrapper\UnraidStreamWrapper;
use PluginTests\Mocks\FunctionMocks;

// Source root - community.applications has a deeper path structure
$sourceRoot = realpath(__DIR__ . '/../source/community.applications/usr/local/emhttp/plugins/community.applications');

// Initialize with plugin name and a base path
// Since CA has multiple subdirs (include/, scripts/, skins/), we'll map from the plugin root
PluginBootstrap::init(
    'community.applications',
    $sourceRoot . '/include',  // Primary PHP location
    [
        'config' => [
            // Default settings from default.cfg
            'maxPerPage' => '24',
            'dockerSearch' => 'yes',
            'iconSize' => '96',
        ],
        'subPath' => 'include',  // Maps to /plugins/community.applications/include/
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

// Skins directory
$skinsDir = $sourceRoot . '/skins';
$skinFiles = glob("$skinsDir/**/*.php");
foreach ($skinFiles as $file) {
    $relativePath = str_replace($sourceRoot, '', $file);
    $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
    UnraidStreamWrapper::addMapping(
        "/usr/local/emhttp/plugins/community.applications" . $relativePath,
        $file
    );
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

echo "Bootstrap loaded successfully!\n";
echo "Source root: $sourceRoot\n";
echo "Mapped paths:\n";

// Verify mappings
$mappings = UnraidStreamWrapper::getMappings();
foreach ($mappings as $unraidPath => $localPath) {
    if (strpos($unraidPath, 'community.applications') !== false) {
        echo "  $unraidPath\n";
    }
}
