<?php

declare(strict_types=1);

namespace CommunityApplications\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for functions in exec.php
 * 
 * These tests load the REAL exec.php file via the stream wrapper
 * to ensure we're testing actual production code.
 */
class ExecTest extends TestCase
{
    private static bool $execLoaded = false;

    public static function setUpBeforeClass(): void
    {
        // exec.php has heavy initialization code - only load once
        if (self::$execLoaded) {
            return;
        }
        
        // Set up POST action to avoid exec.php trying to run an action
        if (!isset($_POST['action'])) {
            $_POST['action'] = '';
        }
        
        // exec.php uses $docroot variable (not $GLOBALS['docroot'])
        // We need to set it in the local scope
        $docroot = $GLOBALS['docroot'] ?? '/usr/local/emhttp';
        
        // exec.php also needs $caPaths in local scope for its top-level code
        $caPaths = $GLOBALS['caPaths'] ?? null;
        
        // Use output buffering to capture any output from exec.php init
        \ob_start();
        try {
            require_once '/usr/local/emhttp/plugins/community.applications/include/exec.php';
            self::$execLoaded = true;
        } catch (\Throwable $e) {
            \ob_end_clean();
            self::markTestSkipped('Could not load exec.php: ' . $e->getMessage());
        }
        \ob_end_clean();
        
        // Verify the function exists
        if (!\function_exists('checkRandomApp')) {
            self::markTestSkipped('checkRandomApp function not available after loading exec.php');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset caSettings for each test
        $GLOBALS['caSettings'] = [
            'hideIncompatible' => 'false',
            'hideDeprecated' => 'false',
        ];
    }

    /**
     * Test that "Community Applications" app is always filtered out
     */
    public function testRejectsCommunityApplicationsApp(): void
    {
        $app = [
            'Name' => 'Community Applications',
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertFalse(\checkRandomApp($app));
    }

    /**
     * Test that apps with BranchName are filtered out
     */
    public function testRejectsAppsWithBranchName(): void
    {
        $app = [
            'Name' => 'Test App',
            'BranchName' => 'beta',
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertFalse(\checkRandomApp($app));
    }

    /**
     * Test that non-displayable apps are filtered out
     */
    public function testRejectsNonDisplayableApps(): void
    {
        $app = [
            'Name' => 'Test App',
            'Displayable' => false,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertFalse(\checkRandomApp($app));
    }

    /**
     * Test that incompatible apps are filtered when hideIncompatible is enabled
     */
    public function testRejectsIncompatibleAppsWhenSettingEnabled(): void
    {
        $GLOBALS['caSettings']['hideIncompatible'] = 'true';

        $app = [
            'Name' => 'Test App',
            'Displayable' => true,
            'Compatible' => false,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertFalse(\checkRandomApp($app));
    }

    /**
     * Test that incompatible apps are shown when hideIncompatible is disabled
     */
    public function testAllowsIncompatibleAppsWhenSettingDisabled(): void
    {
        $GLOBALS['caSettings']['hideIncompatible'] = 'false';

        $app = [
            'Name' => 'Test App',
            'Displayable' => true,
            'Compatible' => false,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertTrue(\checkRandomApp($app));
    }

    /**
     * Test that blacklisted apps are always filtered out
     */
    public function testRejectsBlacklistedApps(): void
    {
        $app = [
            'Name' => 'Test App',
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => true,
            'Deprecated' => false,
        ];

        $this->assertFalse(\checkRandomApp($app));
    }

    /**
     * Test that deprecated apps are filtered when hideDeprecated is enabled
     */
    public function testRejectsDeprecatedAppsWhenSettingEnabled(): void
    {
        $GLOBALS['caSettings']['hideDeprecated'] = 'true';

        $app = [
            'Name' => 'Test App',
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => true,
        ];

        $this->assertFalse(\checkRandomApp($app));
    }

    /**
     * Test that deprecated apps are shown when hideDeprecated is disabled
     */
    public function testAllowsDeprecatedAppsWhenSettingDisabled(): void
    {
        $GLOBALS['caSettings']['hideDeprecated'] = 'false';

        $app = [
            'Name' => 'Test App',
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => true,
        ];

        $this->assertTrue(\checkRandomApp($app));
    }

    /**
     * Test that a valid, compatible, displayable app passes all checks
     */
    public function testAllowsValidApp(): void
    {
        $app = [
            'Name' => 'Awesome Docker App',
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertTrue(\checkRandomApp($app));
    }

    /**
     * Test that missing BranchName key doesn't cause error (null coalescing)
     */
    public function testHandlesMissingBranchNameKey(): void
    {
        $app = [
            'Name' => 'Test App',
            // BranchName intentionally missing
            'Displayable' => true,
            'Compatible' => true,
            'Blacklist' => false,
            'Deprecated' => false,
        ];

        $this->assertTrue(\checkRandomApp($app));
    }
}
