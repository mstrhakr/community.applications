<?php

declare(strict_types=1);

namespace CommunityApplications\Tests;

use PHPUnit\Framework\TestCase;

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
}
