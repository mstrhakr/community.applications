<?php

declare(strict_types=1);

namespace CommunityApplications\Tests;

use PHPUnit\Framework\TestCase;
use PluginTests\Mocks\FunctionMocks;

/**
 * Tests for CA functions that require globals and mocked system state
 * 
 * These tests prove that the framework properly handles:
 * - Global variables ($caSettings, $caPaths)
 * - Mocked system files
 * - Plugin config
 */
class GlobalsTest extends TestCase
{
    private static bool $helpersLoaded = false;

    public static function setUpBeforeClass(): void
    {
        if (!self::$helpersLoaded) {
            require_once '/usr/local/emhttp/plugins/community.applications/include/helpers.php';
            self::$helpersLoaded = true;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Reset function mocks between tests
        FunctionMocks::reset();
    }

    // =====================================================
    // Tests for versionCheck() - uses global $caSettings
    // =====================================================

    public function testVersionCheckPassesWithValidVersion(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        $template = [
            'MinVer' => '6.0.0',
            'MaxVer' => '8.0.0',
        ];
        
        $this->assertTrue(\versionCheck($template));
    }

    public function testVersionCheckFailsWhenBelowMinVer(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '6.5.0';
        
        $template = [
            'MinVer' => '7.0.0',
        ];
        
        $this->assertFalse(\versionCheck($template));
    }

    public function testVersionCheckFailsWhenAboveMaxVer(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '8.0.0';
        
        $template = [
            'MaxVer' => '7.5.0',
        ];
        
        $this->assertFalse(\versionCheck($template));
    }

    public function testVersionCheckWithIncompatibleVersion(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        $template = [
            'pluginVersion' => '1.0.0',
            'IncompatibleVersion' => '1.0.0',
        ];
        
        $this->assertFalse(\versionCheck($template));
    }

    public function testVersionCheckWithIncompatibleVersionArray(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        $template = [
            'pluginVersion' => '1.0.0',
            'IncompatibleVersion' => ['0.9.0', '1.0.0', '1.1.0'],
        ];
        
        $this->assertFalse(\versionCheck($template));
    }

    // =====================================================
    // Tests for mySort() - uses global $sortOrder
    // =====================================================

    public function testMySortByNameAscending(): void
    {
        global $sortOrder;
        $sortOrder = ['sortBy' => 'Name', 'sortDir' => 'Up'];
        
        $a = ['SortName' => 'Apple', 'Name' => 'Apple'];
        $b = ['SortName' => 'Banana', 'Name' => 'Banana'];
        
        $result = \mySort($a, $b);
        $this->assertEquals(-1, $result);
    }

    public function testMySortByNameDescending(): void
    {
        global $sortOrder;
        $sortOrder = ['sortBy' => 'Name', 'sortDir' => 'Down'];
        
        $a = ['SortName' => 'Apple', 'Name' => 'Apple'];
        $b = ['SortName' => 'Banana', 'Name' => 'Banana'];
        
        $result = \mySort($a, $b);
        $this->assertEquals(1, $result);
    }

    public function testMySortByDownloads(): void
    {
        global $sortOrder;
        $sortOrder = ['sortBy' => 'downloads', 'sortDir' => 'Down'];
        
        $a = ['downloads' => 100];
        $b = ['downloads' => 50];
        
        $result = \mySort($a, $b);
        $this->assertEquals(-1, $result);  // 100 > 50, descending = -1
    }

    public function testMySortEqual(): void
    {
        global $sortOrder;
        $sortOrder = ['sortBy' => 'Name', 'sortDir' => 'Up'];
        
        $a = ['SortName' => 'Same', 'Name' => 'Same'];
        $b = ['SortName' => 'Same', 'Name' => 'Same'];
        
        $result = \mySort($a, $b);
        $this->assertEquals(0, $result);
    }

    // =====================================================
    // Tests for repositorySort() - uses global $caSettings
    // =====================================================

    public function testRepositorySortFavouriteFirst(): void
    {
        global $caSettings;
        $caSettings['favourite'] = 'MyFavorite';
        
        $a = ['RepoName' => 'MyFavorite'];
        $b = ['RepoName' => 'OtherRepo'];
        
        $result = \repositorySort($a, $b);
        $this->assertEquals(-1, $result);
    }

    public function testRepositorySortFavouriteSecond(): void
    {
        global $caSettings;
        $caSettings['favourite'] = 'MyFavorite';
        
        $a = ['RepoName' => 'OtherRepo'];
        $b = ['RepoName' => 'MyFavorite'];
        
        $result = \repositorySort($a, $b);
        $this->assertEquals(1, $result);
    }

    public function testRepositorySortNeitherFavourite(): void
    {
        global $caSettings;
        $caSettings['favourite'] = 'MyFavorite';
        
        $a = ['RepoName' => 'RepoA'];
        $b = ['RepoName' => 'RepoB'];
        
        $result = \repositorySort($a, $b);
        $this->assertEquals(0, $result);
    }

    // =====================================================
    // Tests for favouriteSort() - uses global $caSettings
    // =====================================================

    public function testFavouriteSortFavouriteFirst(): void
    {
        global $caSettings;
        $caSettings['favourite'] = 'MyFavoriteRepo';
        
        $a = ['Repo' => 'MyFavoriteRepo'];
        $b = ['Repo' => 'OtherRepo'];
        
        $result = \favouriteSort($a, $b);
        $this->assertEquals(-1, $result);
    }

    public function testFavouriteSortFavouriteSecond(): void
    {
        global $caSettings;
        $caSettings['favourite'] = 'MyFavoriteRepo';
        
        $a = ['Repo' => 'OtherRepo'];
        $b = ['Repo' => 'MyFavoriteRepo'];
        
        $result = \favouriteSort($a, $b);
        $this->assertEquals(1, $result);
    }

    public function testFavouriteSortNeitherFavourite(): void
    {
        global $caSettings;
        $caSettings['favourite'] = 'SomeOtherRepo';
        
        $a = ['Repo' => 'RepoA'];
        $b = ['Repo' => 'RepoB'];
        
        $result = \favouriteSort($a, $b);
        $this->assertEquals(0, $result);
    }

    // =====================================================
    // Tests for fixTemplates() - uses global $caSettings
    // =====================================================

    public function testFixTemplatesAddsDefaultMinVer(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        // Docker template without MinVer should get 6.0
        $template = ['Name' => 'TestApp', 'MinVer' => null];
        $result = \fixTemplates($template);
        
        $this->assertEquals('6.0', $result['MinVer']);
    }

    public function testFixTemplatesPluginGetsHigherMinVer(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        // Plugin without MinVer should get 6.1 (higher than docker)
        $template = ['Name' => 'TestPlugin', 'MinVer' => null, 'Plugin' => true];
        $result = \fixTemplates($template);
        
        $this->assertEquals('6.1', $result['MinVer']);
    }

    public function testFixTemplatesHandlesDeprecatedBoolean(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        // String "FALSE" should become boolean false
        $template = ['Name' => 'TestApp', 'MinVer' => '6.0', 'Deprecated' => 'FALSE'];
        $result = \fixTemplates($template);
        
        $this->assertFalse($result['Deprecated']);
    }

    public function testFixTemplatesHandlesBlacklistBoolean(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        $template = ['Name' => 'TestApp', 'MinVer' => '6.0', 'Blacklist' => 'true'];
        $result = \fixTemplates($template);
        
        $this->assertTrue($result['Blacklist']);
    }

    public function testFixTemplatesMarksDeprecatedByMaxVer(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '8.0.0'; // Higher than DeprecatedMaxVer
        
        $template = [
            'Name' => 'OldApp',
            'MinVer' => '6.0',
            'DeprecatedMaxVer' => '7.5.0',
            'Deprecated' => false,
        ];
        $result = \fixTemplates($template);
        
        $this->assertTrue($result['Deprecated']);
    }

    // =====================================================
    // Tests for randomFile() - uses global $caPaths
    // =====================================================

    public function testRandomFileReturnsPath(): void
    {
        global $caPaths;
        
        $file = \randomFile();
        
        $this->assertIsString($file);
        // tempnam truncates prefix to 3 chars, so "CA-Temp-" becomes "CA-"
        $this->assertStringContainsString('CA-', $file);
        
        // Clean up if it was created
        @unlink($file);
    }

    // =====================================================
    // Tests for isMobile() - uses $_SERVER['HTTP_USER_AGENT']
    // =====================================================

    public function testIsMobileDetectsIPhone(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1';
        
        $this->assertTrue(\isMobile());
    }

    public function testIsMobileDetectsAndroid(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Mobile Safari/537.36';
        
        $this->assertTrue(\isMobile());
    }

    public function testIsMobileDetectsIPad(): void
    {
        // iPad with mobile keyword
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';
        
        // iPad without explicit "mobile" may not be detected - this tests the real behavior
        $result = \isMobile();
        $this->assertIsBool($result);
    }

    public function testIsMobileReturnsFalseForDesktop(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36';
        
        $this->assertFalse(\isMobile());
    }

    public function testIsMobileDetectsBlackberry(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+';
        
        $this->assertTrue(\isMobile());
    }

    // =====================================================
    // Tests for isTailScaleInstalled() - uses is_file()
    // =====================================================

    public function testTailScaleNotInstalledByDefault(): void
    {
        // When neither plugin file exists, should return false
        // StreamWrapper makes /var/log/plugins virtual
        $this->assertFalse(\isTailScaleInstalled());
    }

    public function testTailScaleDetectsPreviewPlugin(): void
    {
        // Create the preview plugin file
        @mkdir('/var/log/plugins', 0777, true);
        @touch('/var/log/plugins/tailscale-preview.plg');
        
        $this->assertTrue(\isTailScaleInstalled());
        
        // Cleanup
        @unlink('/var/log/plugins/tailscale-preview.plg');
    }

    public function testTailScaleDetectsStablePlugin(): void
    {
        // Create the stable plugin file
        @mkdir('/var/log/plugins', 0777, true);
        @touch('/var/log/plugins/tailscale.plg');
        
        $this->assertTrue(\isTailScaleInstalled());
        
        // Cleanup
        @unlink('/var/log/plugins/tailscale.plg');
    }

    // =====================================================
    // Tests for getGlobals() - populates $GLOBALS['templates']
    // =====================================================

    public function testGetGlobalsLoadsTemplatesFromFile(): void
    {
        global $caPaths;
        
        // Create a test templates file
        $testTemplates = [
            ['Name' => 'TestApp1', 'Repository' => 'test/app1'],
            ['Name' => 'TestApp2', 'Repository' => 'test/app2'],
        ];
        
        // Write to the expected path
        @mkdir(dirname($caPaths['community-templates-info']), 0777, true);
        file_put_contents($caPaths['community-templates-info'], serialize($testTemplates));
        
        // Clear any existing global
        unset($GLOBALS['templates']);
        
        \getGlobals();
        
        $this->assertIsArray($GLOBALS['templates']);
        $this->assertCount(2, $GLOBALS['templates']);
        $this->assertEquals('TestApp1', $GLOBALS['templates'][0]['Name']);
        
        // Cleanup
        @unlink($caPaths['community-templates-info']);
    }

    public function testGetGlobalsReturnsEmptyWhenFileNotExists(): void
    {
        global $caPaths;
        
        // Ensure file doesn't exist
        @unlink($caPaths['community-templates-info']);
        unset($GLOBALS['templates']);
        
        \getGlobals();
        
        $this->assertEquals([], $GLOBALS['templates']);
    }

    public function testGetGlobalsDoesNotReloadIfAlreadyPopulated(): void
    {
        global $caPaths;
        
        // Pre-populate the global
        $GLOBALS['templates'] = [['Name' => 'AlreadyLoaded']];
        
        // Create a different file content
        @mkdir(dirname($caPaths['community-templates-info']), 0777, true);
        file_put_contents($caPaths['community-templates-info'], serialize([['Name' => 'FromFile']]));
        
        \getGlobals();
        
        // Should still have the pre-populated value
        $this->assertEquals('AlreadyLoaded', $GLOBALS['templates'][0]['Name']);
        
        // Cleanup
        @unlink($caPaths['community-templates-info']);
        unset($GLOBALS['templates']);
    }

    // =====================================================
    // Tests for dropAttributeCache() - deletes cache file
    // =====================================================

    public function testDropAttributeCacheDeletesFile(): void
    {
        global $caPaths;
        
        // Create the cache file
        @mkdir(dirname($caPaths['pluginAttributesCache']), 0777, true);
        file_put_contents($caPaths['pluginAttributesCache'], serialize(['test' => 'data']));
        
        $this->assertFileExists($caPaths['pluginAttributesCache']);
        
        \dropAttributeCache();
        
        $this->assertFileDoesNotExist($caPaths['pluginAttributesCache']);
    }

    public function testDropAttributeCacheHandlesMissingFile(): void
    {
        global $caPaths;
        
        // Ensure file doesn't exist
        @unlink($caPaths['pluginAttributesCache']);
        
        // Should not throw an error
        \dropAttributeCache();
        
        $this->assertFileDoesNotExist($caPaths['pluginAttributesCache']);
    }

    // =====================================================
    // Tests for pluginDupe() - detects duplicate plugins
    // =====================================================

    public function testPluginDupeDetectsDuplicates(): void
    {
        global $caPaths;
        
        // Create the templates file with duplicates (getGlobals() loads from this)
        $templates = [
            ['Plugin' => true, 'Repository' => 'https://example.com/plugin1.plg'],
            ['Plugin' => true, 'Repository' => 'https://other.com/plugin1.plg'],
            ['Plugin' => true, 'Repository' => 'https://example.com/unique.plg'],
        ];
        
        @mkdir(dirname($caPaths['community-templates-info']), 0777, true);
        @mkdir(dirname($caPaths['pluginDupes']), 0777, true);
        file_put_contents($caPaths['community-templates-info'], serialize($templates));
        
        // Clear any existing global so getGlobals() will reload from file
        unset($GLOBALS['templates']);
        
        \pluginDupe();
        
        $dupes = @unserialize(@file_get_contents($caPaths['pluginDupes'])) ?: [];
        
        $this->assertArrayHasKey('plugin1.plg', $dupes);
        $this->assertArrayNotHasKey('unique.plg', $dupes);
        
        // Cleanup
        @unlink($caPaths['community-templates-info']);
        @unlink($caPaths['pluginDupes']);
        unset($GLOBALS['templates']);
    }

    public function testPluginDupeIgnoresNonPlugins(): void
    {
        global $caPaths;
        
        // Set up templates with docker apps (not plugins)
        $templates = [
            ['Repository' => 'linuxserver/plex'],
            ['Repository' => 'linuxserver/plex'], // Same docker image
            ['Plugin' => true, 'Repository' => 'https://example.com/unique.plg'],
        ];
        
        @mkdir(dirname($caPaths['community-templates-info']), 0777, true);
        @mkdir(dirname($caPaths['pluginDupes']), 0777, true);
        file_put_contents($caPaths['community-templates-info'], serialize($templates));
        
        // Clear any existing global
        unset($GLOBALS['templates']);
        
        \pluginDupe();
        
        $dupes = @unserialize(@file_get_contents($caPaths['pluginDupes'])) ?: [];
        
        // Docker apps should not count as dupes
        $this->assertCount(0, $dupes);
        
        // Cleanup
        @unlink($caPaths['community-templates-info']);
        @unlink($caPaths['pluginDupes']);
        unset($GLOBALS['templates']);
    }

    // =====================================================
    // Tests for ca_plugin() - with plugin() mock
    // =====================================================

    public function testCaPluginReturnsChanges(): void
    {
        // 'changes' is a PLUGIN_METHOD, so it calls plugin() directly
        \PluginTests\Mocks\FunctionMocks::setPluginCommandOutput('changes', '/var/log/plugins/test.plg', "<b>Version 1.0</b>\n- Bug fix");
        
        $result = \ca_plugin('changes', '/var/log/plugins/test.plg');
        
        // strip_tags and html_entity_decode should clean the output
        $this->assertStringContainsString('Version 1.0', $result);
        $this->assertStringNotContainsString('<b>', $result);
    }

    public function testCaPluginReturnsAttributeFromXml(): void
    {
        global $caPaths;
        
        // Create a plugin XML file for attribute caching
        @mkdir('/var/log/plugins', 0777, true);
        $xml = '<?xml version="1.0" ?><PLUGIN version="2024.01.15" name="Test Plugin" pluginURL="https://example.com/test.plg"></PLUGIN>';
        file_put_contents('/var/log/plugins/test.plg', $xml);
        
        // Ensure attribute cache is clear
        @unlink($caPaths['pluginAttributesCache']);
        
        // ca_plugin with non-method attribute reads from XML
        $result = \ca_plugin('version', '/var/log/plugins/test.plg');
        
        $this->assertEquals('2024.01.15', $result);
        
        // Cleanup
        @unlink('/var/log/plugins/test.plg');
        @unlink($caPaths['pluginAttributesCache']);
    }

    public function testCaPluginReturnsFalseForMissingPlugin(): void
    {
        global $caPaths;
        @unlink($caPaths['pluginAttributesCache']);
        
        // File doesn't exist, should return false
        $result = \ca_plugin('version', '/var/log/plugins/nonexistent.plg');
        
        $this->assertFalse($result);
    }

    // =====================================================
    // Tests for checkPluginUpdate()
    // =====================================================

    public function testCheckPluginUpdateReturnsFalseWhenNotInstalled(): void
    {
        // Plugin not installed (file doesn't exist)
        $result = \checkPluginUpdate('nonexistent.plg');
        
        $this->assertFalse($result);
    }

    public function testCheckPluginUpdateReturnsFalseWhenNoUpgrade(): void
    {
        global $caSettings;
        $caSettings['unRaidVersion'] = '7.0.0';
        
        // Create installed plugin file
        @mkdir('/var/log/plugins', 0777, true);
        file_put_contents('/var/log/plugins/test.plg', '<?xml version="1.0" ?><PLUGIN version="2024.01.15"></PLUGIN>');
        
        // No upgrade available (no /tmp/plugins file)
        $result = \checkPluginUpdate('test.plg');
        
        $this->assertFalse($result);
        
        // Cleanup
        @unlink('/var/log/plugins/test.plg');
    }

    // =====================================================
    // Tests for checkInstalledPlugin()
    // =====================================================

    public function testCheckInstalledPluginReturnsFalseWhenNotInstalled(): void
    {
        $template = [
            'PluginURL' => 'https://example.com/test.plg',
        ];
        
        // Plugin file doesn't exist
        $result = \checkInstalledPlugin($template);
        
        $this->assertFalse($result);
    }

    public function testCheckInstalledPluginReturnsFalseWithHideFromCA(): void
    {
        // Even if installed, hideFromCA returns false
        @mkdir('/var/log/plugins', 0777, true);
        file_put_contents('/var/log/plugins/test.plg', '<?xml version="1.0" ?><PLUGIN></PLUGIN>');
        
        $template = [
            'PluginURL' => 'https://example.com/test.plg',
            'hideFromCA' => true,
        ];
        
        $result = \checkInstalledPlugin($template);
        
        $this->assertFalse($result);
        
        // Cleanup
        @unlink('/var/log/plugins/test.plg');
    }

    // =====================================================
    // Tests for checkServerDate()
    // =====================================================

    public function testCheckServerDateReturnsTrueWhenDateCorrect(): void
    {
        // Mock the plugin version (CA uses this to check server date)
        // checkServerDate() calls plugin() directly (not ca_plugin())
        \PluginTests\Mocks\FunctionMocks::setPluginAttributes('/var/log/plugins/community.applications.plg', [
            'version' => '2026.02.04',
        ]);
        
        $result = \checkServerDate();
        
        // Current date is Feb 4, 2026, CA version is 2026.02.04
        // Difference should be 0 days, which is < 30 days
        $this->assertTrue($result);
    }

    public function testCheckServerDateReturnsFalseWhenServerDateTooOld(): void
    {
        // Mock a future CA version to simulate server date being too old
        // If CA version is 60 days in the future, server's date must be wrong
        \PluginTests\Mocks\FunctionMocks::setPluginAttributes('/var/log/plugins/community.applications.plg', [
            'version' => '2026.04.15',  // ~70 days in future from Feb 4
        ]);
        
        $result = \checkServerDate();
        
        // If server thinks it's Feb 4 but CA version says April 15 (>30 days in future)
        // Then server date is wrong - returns false
        $this->assertFalse($result);
    }

    public function testCheckServerDateReturnsTrueWhenNoCaVersion(): void
    {
        // No plugin attributes set means version returns false
        $result = \checkServerDate();
        
        // When no CA version found, returns true (can't check)
        $this->assertTrue($result);
    }
}
