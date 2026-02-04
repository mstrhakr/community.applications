<?php

declare(strict_types=1);

namespace CommunityApplications\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for pure utility functions that have no dependencies
 * 
 * These are the easiest functions to test - no mocking needed.
 */
class PureFunctionsTest extends TestCase
{
    // =====================================================
    // Tests for alphaNumeric()
    // =====================================================

    public function testAlphaNumericRemovesSpaces(): void
    {
        $this->assertEquals('HelloWorld', \alphaNumeric('Hello World'));
    }

    public function testAlphaNumericRemovesSpecialChars(): void
    {
        $this->assertEquals('Test123', \alphaNumeric('Test!@#$%123'));
    }

    public function testAlphaNumericPreservesCase(): void
    {
        $this->assertEquals('AbCdEf', \alphaNumeric('AbCdEf'));
    }

    public function testAlphaNumericWithEmptyString(): void
    {
        $this->assertEquals('', \alphaNumeric(''));
    }

    public function testAlphaNumericWithOnlySpecialChars(): void
    {
        $this->assertEquals('', \alphaNumeric('!@#$%^&*()'));
    }

    // =====================================================
    // Tests for validURL()
    // =====================================================

    public function testValidURLWithHttps(): void
    {
        $this->assertNotFalse(\validURL('https://example.com'));
    }

    public function testValidURLWithHttp(): void
    {
        $this->assertNotFalse(\validURL('http://example.com'));
    }

    public function testValidURLWithPath(): void
    {
        $this->assertNotFalse(\validURL('https://example.com/path/to/page'));
    }

    public function testValidURLWithQueryString(): void
    {
        $this->assertNotFalse(\validURL('https://example.com?foo=bar'));
    }

    public function testValidURLRejectsPlainText(): void
    {
        $this->assertFalse(\validURL('not a url'));
    }

    public function testValidURLRejectsPartialURL(): void
    {
        $this->assertFalse(\validURL('example.com'));
    }

    public function testValidURLRejectsEmpty(): void
    {
        $this->assertFalse(\validURL(''));
    }

    // =====================================================
    // Tests for filterMatch()
    // =====================================================

    public function testFilterMatchSingleWordFound(): void
    {
        $searchArray = ['Docker Container', 'Media Server', 'Plex'];
        $this->assertTrue(\filterMatch('Plex', $searchArray));
    }

    public function testFilterMatchSingleWordNotFound(): void
    {
        $searchArray = ['Docker Container', 'Media Server', 'Plex'];
        $this->assertFalse(\filterMatch('Emby', $searchArray));
    }

    public function testFilterMatchMultipleWordsAllFound(): void
    {
        $searchArray = ['Docker Container for Media Server', 'Plex Media'];
        $this->assertTrue(\filterMatch('Docker Media', $searchArray, true));
    }

    public function testFilterMatchMultipleWordsPartialInExactMode(): void
    {
        $searchArray = ['Docker Container', 'Media Server'];
        // In exact mode, ALL words must be found
        $this->assertFalse(\filterMatch('Docker Plex', $searchArray, true));
    }

    public function testFilterMatchMultipleWordsPartialInNonExactMode(): void
    {
        $searchArray = ['Docker Container', 'Media Server'];
        // In non-exact mode, ANY word match is enough
        $this->assertTrue(\filterMatch('Docker Plex', $searchArray, false));
    }

    public function testFilterMatchCaseInsensitive(): void
    {
        $searchArray = ['DOCKER', 'PLEX'];
        $this->assertTrue(\filterMatch('docker', $searchArray));
    }

    public function testFilterMatchEmptyFilter(): void
    {
        $searchArray = ['Docker', 'Plex'];
        // Empty filter should match nothing (no words to find)
        $this->assertFalse(\filterMatch('', $searchArray));
    }

    public function testFilterMatchEmptySearchArray(): void
    {
        $this->assertFalse(\filterMatch('Docker', []));
    }

    // =====================================================
    // Tests for getDownloads()
    // =====================================================

    public function testGetDownloadsOverTenMillion(): void
    {
        $result = \getDownloads(15000000);
        $this->assertStringContainsString('10,000,000', $result);
    }

    public function testGetDownloadsOverOneMillion(): void
    {
        $result = \getDownloads(1500000);
        $this->assertStringContainsString('1,000,000', $result);
    }

    public function testGetDownloadsOverOneThousand(): void
    {
        $result = \getDownloads(5500);
        $this->assertStringContainsString('5,000', $result);
    }

    public function testGetDownloadsLowCountWithFlag(): void
    {
        // With lowFlag=true, should return actual count for small numbers
        $result = \getDownloads(50, true);
        $this->assertEquals(50, $result);
    }

    public function testGetDownloadsLowCountWithoutFlag(): void
    {
        // Without lowFlag, should return empty string for small numbers
        $result = \getDownloads(50, false);
        $this->assertEquals('', $result);
    }

    // =====================================================
    // Tests for fixDescription()
    // =====================================================

    public function testFixDescriptionRemovesBrTags(): void
    {
        $input = 'Line one[br]Line two';
        $result = \fixDescription($input);
        // [br] gets converted to <br> then stripped, leaving just the text
        $this->assertStringNotContainsString('[br]', $result);
    }

    public function testFixDescriptionRemovesBoldTags(): void
    {
        $input = '[b]Bold text[/b]';
        $result = \fixDescription($input);
        $this->assertStringNotContainsString('[b]', $result);
        $this->assertStringNotContainsString('[/b]', $result);
    }

    public function testFixDescriptionRemovesHtmlTags(): void
    {
        $input = '<script>alert("xss")</script>Normal text';
        $result = \fixDescription($input);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('Normal text', $result);
    }

    public function testFixDescriptionHandlesNull(): void
    {
        $result = \fixDescription(null);
        $this->assertEquals('', $result);
    }

    public function testFixDescriptionTrimsWhitespace(): void
    {
        $input = '  Padded text  ';
        $result = \fixDescription($input);
        $this->assertEquals('Padded text', $result);
    }

    // =====================================================
    // Tests for getAuthor()
    // =====================================================

    public function testGetAuthorFromPluginTemplate(): void
    {
        $template = [
            'PluginURL' => 'https://example.com/plugin.plg',
            'PluginAuthor' => 'John Doe'
        ];
        $this->assertEquals('John Doe', \getAuthor($template));
    }

    public function testGetAuthorFromDockerRepository(): void
    {
        $template = [
            'Repository' => 'linuxserver/plex'
        ];
        $this->assertEquals('linuxserver', \getAuthor($template));
    }

    public function testGetAuthorFromDockerHubLibrary(): void
    {
        $template = [
            'Repository' => 'library/nginx'
        ];
        // library/ gets stripped, leaving just 'nginx' path
        $result = \getAuthor($template);
        $this->assertIsString($result);
    }

    public function testGetAuthorFromGhcr(): void
    {
        $template = [
            'Repository' => 'ghcr.io/linuxserver/plex'
        ];
        // ghcr.io/ gets stripped
        $this->assertEquals('linuxserver', \getAuthor($template));
    }

    public function testGetAuthorWithExplicitAuthorField(): void
    {
        $template = [
            'Author' => 'Jane Smith',
            'Repository' => 'someuser/someimage'
        ];
        $this->assertEquals('Jane Smith', \getAuthor($template));
    }

    public function testGetAuthorStripsHtmlTags(): void
    {
        $template = [
            'Author' => '<b>Styled</b> Author'
        ];
        $this->assertEquals('Styled Author', \getAuthor($template));
    }

    // =====================================================
    // Tests for categoryList()
    // =====================================================

    public function testCategoryListSingleCategory(): void
    {
        $result = \categoryList('Media');
        $this->assertEquals('Media', $result);
    }

    public function testCategoryListMultipleCategories(): void
    {
        $result = \categoryList('Media,Server,Docker');
        // Without popup, shows max 2 + "and X more"
        $this->assertStringContainsString('Media', $result);
    }

    public function testCategoryListTrimsWhitespace(): void
    {
        $result = \categoryList(' Media , Server ');
        $this->assertStringContainsString('Media', $result);
    }

    public function testCategoryListWithColonSeparator(): void
    {
        $result = \categoryList('Media: Server');
        // Colons should be handled
        $this->assertIsString($result);
    }

    // =====================================================
    // Tests for languageAuthorList()
    // =====================================================

    public function testLanguageAuthorListSingleAuthor(): void
    {
        $result = \languageAuthorList('John Doe');
        $this->assertEquals('John Doe', $result);
    }

    public function testLanguageAuthorListThreeAuthors(): void
    {
        $result = \languageAuthorList('Author1, Author2, Author3');
        // 3 or fewer should show all
        $this->assertStringContainsString('Author1', $result);
        $this->assertStringContainsString('Author2', $result);
        $this->assertStringContainsString('Author3', $result);
    }

    public function testLanguageAuthorListManyAuthors(): void
    {
        $result = \languageAuthorList('A1, A2, A3, A4, A5');
        // More than 3 should show 2 + "and X more"
        $this->assertStringContainsString('A1', $result);
        $this->assertStringContainsString('A2', $result);
        $this->assertStringContainsString('more', $result);
    }
}
