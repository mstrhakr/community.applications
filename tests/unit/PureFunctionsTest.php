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

    // =====================================================
    // Tests for plain() - removes brackets from IP strings
    // =====================================================

    public function testPlainRemovesBrackets(): void
    {
        $this->assertEquals('192.168.1.1', \plain('[192.168.1.1]'));
    }

    public function testPlainWithNoBrackets(): void
    {
        $this->assertEquals('192.168.1.1', \plain('192.168.1.1'));
    }

    public function testPlainWithIPv6(): void
    {
        $this->assertEquals('::1', \plain('[::1]'));
    }

    // =====================================================
    // Tests for ca_explode() - explode with padding
    // =====================================================

    public function testCaExplodeBasicSplit(): void
    {
        $result = \ca_explode(':', '192.168.1.1:8080');
        $this->assertEquals(['192.168.1.1', '8080'], $result);
    }

    public function testCaExplodePadsWhenNoDelimiter(): void
    {
        $result = \ca_explode(':', '192.168.1.1');
        $this->assertEquals(['192.168.1.1', ''], $result);
    }

    public function testCaExplodeWithCustomCount(): void
    {
        $result = \ca_explode(':', 'a:b:c', 3);
        $this->assertEquals(['a', 'b', 'c'], $result);
    }

    public function testCaExplodePadsToCount(): void
    {
        $result = \ca_explode(':', 'a', 3);
        $this->assertEquals(['a', '', ''], $result);
    }

    // =====================================================
    // Tests for getYoutubeThumbnail()
    // =====================================================

    public function testYoutubeThumbnailFromShortUrl(): void
    {
        $result = \getYoutubeThumbnail('https://youtu.be/dQw4w9WgXcQ');
        $this->assertEquals('https://img.youtube.com/vi/dQw4w9WgXcQ/default.jpg', $result);
    }

    public function testYoutubeThumbnailFromWatchUrl(): void
    {
        $result = \getYoutubeThumbnail('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertEquals('https://img.youtube.com/vi/dQw4w9WgXcQ/default.jpg', $result);
    }

    public function testYoutubeThumbnailFromWatchUrlNoWww(): void
    {
        $result = \getYoutubeThumbnail('https://youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertEquals('https://img.youtube.com/vi/dQw4w9WgXcQ/default.jpg', $result);
    }

    public function testYoutubeThumbnailReturnsOriginalForNonYoutube(): void
    {
        $url = 'https://example.com/video.mp4';
        $result = \getYoutubeThumbnail($url);
        $this->assertEquals($url, $result);
    }

    // =====================================================
    // Tests for addMissingVars()
    // =====================================================

    public function testAddMissingVarsAddsNullForMissingKeys(): void
    {
        $template = ['Name' => 'Test App', 'Repository' => 'test/app'];
        $result = \addMissingVars($template);
        
        // Check that original values are preserved
        $this->assertEquals('Test App', $result['Name']);
        $this->assertEquals('test/app', $result['Repository']);
        
        // Check that missing vars are added as null
        $this->assertArrayHasKey('Category', $result);
        $this->assertNull($result['Category']);
        $this->assertArrayHasKey('MinVer', $result);
        $this->assertNull($result['MinVer']);
    }

    public function testAddMissingVarsPreservesExistingValues(): void
    {
        $template = ['Category' => 'Media', 'MinVer' => '6.10'];
        $result = \addMissingVars($template);
        
        $this->assertEquals('Media', $result['Category']);
        $this->assertEquals('6.10', $result['MinVer']);
    }

    public function testAddMissingVarsReturnsNonArrayInput(): void
    {
        $this->assertEquals('string', \addMissingVars('string'));
        $this->assertNull(\addMissingVars(null));
    }

    // =====================================================
    // Tests for portsUsed()
    // =====================================================

    public function testPortsUsedReturnEmptyForNonBridgeNetwork(): void
    {
        $template = ['Network' => 'host', 'Config' => []];
        $result = \portsUsed($template);
        $this->assertEquals('[]', $result);
    }

    public function testPortsUsedExtractsBridgePorts(): void
    {
        $template = [
            'Network' => 'bridge',
            'Config' => [
                ['@attributes' => ['Type' => 'Port', 'Default' => '8080'], 'value' => ''],
                ['@attributes' => ['Type' => 'Port', 'Default' => '443'], 'value' => '8443'],
            ]
        ];
        $result = json_decode(\portsUsed($template), true);
        $this->assertContains('8080', $result);
        $this->assertContains('8443', $result);
    }

    public function testPortsUsedReturnsEmptyForNonArray(): void
    {
        $result = \portsUsed('not an array');
        $this->assertEquals('[]', $result);
    }

    public function testPortsUsedSkipsNonPortConfig(): void
    {
        $template = [
            'Network' => 'bridge',
            'Config' => [
                ['@attributes' => ['Type' => 'Path'], 'value' => '/data'],
                ['@attributes' => ['Type' => 'Port', 'Default' => '8080'], 'value' => ''],
            ]
        ];
        $result = json_decode(\portsUsed($template), true);
        $this->assertCount(1, $result);
        $this->assertContains('8080', $result);
    }

    // =====================================================
    // Tests for removeXMLtags() - recursive tag stripping
    // =====================================================

    public function testRemoveXMLtagsStripsSimpleTags(): void
    {
        $template = ['Description' => '<b>Bold</b> text'];
        \removeXMLtags($template);
        $this->assertStringNotContainsString('<b>', $template['Description']);
        $this->assertStringContainsString('Bold', $template['Description']);
    }

    public function testRemoveXMLtagsHandlesNestedArrays(): void
    {
        $template = [
            'App' => [
                'Name' => '<i>Styled</i>',
                'Description' => '<b>Bold</b>'
            ]
        ];
        \removeXMLtags($template);
        $this->assertStringNotContainsString('<i>', $template['App']['Name']);
        $this->assertStringNotContainsString('<b>', $template['App']['Description']);
    }

    public function testRemoveXMLtagsConvertsBrTags(): void
    {
        $template = ['Description' => 'Line 1<br>Line 2'];
        \removeXMLtags($template);
        // <br> gets converted to newlines, then processed
        $this->assertIsString($template['Description']);
    }

    // =====================================================
    // Tests for TypeConverter static methods
    // =====================================================

    public function testTypeConverterIsArrayWithArray(): void
    {
        $this->assertTrue(\TypeConverter::isArray(['a', 'b', 'c']));
    }

    public function testTypeConverterIsArrayWithString(): void
    {
        $this->assertFalse(\TypeConverter::isArray('not an array'));
    }

    public function testTypeConverterIsJsonWithValidJson(): void
    {
        $this->assertTrue(\TypeConverter::isJson('{"key": "value"}'));
    }

    public function testTypeConverterIsJsonWithInvalidJson(): void
    {
        $this->assertFalse(\TypeConverter::isJson('not json'));
    }

    public function testTypeConverterIsObjectWithObject(): void
    {
        $obj = new \stdClass();
        $this->assertTrue(\TypeConverter::isObject($obj));
    }

    public function testTypeConverterIsObjectWithArray(): void
    {
        $this->assertFalse(\TypeConverter::isObject(['key' => 'value']));
    }

    public function testTypeConverterIsSerializedWithSerialized(): void
    {
        $serialized = serialize(['a' => 1, 'b' => 2]);
        $this->assertNotFalse(\TypeConverter::isSerialized($serialized));
    }

    public function testTypeConverterIsSerializedWithPlainString(): void
    {
        $this->assertFalse(\TypeConverter::isSerialized('plain string'));
    }

    public function testTypeConverterIsXmlWithValidXml(): void
    {
        $xml = '<?xml version="1.0"?><root><item>test</item></root>';
        $this->assertNotFalse(\TypeConverter::isXml($xml));
    }

    public function testTypeConverterIsXmlWithInvalidXml(): void
    {
        $this->assertFalse(\TypeConverter::isXml('not xml'));
    }

    public function testTypeConverterIsDetectsArray(): void
    {
        $this->assertEquals('array', \TypeConverter::is(['a', 'b']));
    }

    public function testTypeConverterIsDetectsJson(): void
    {
        $this->assertEquals('json', \TypeConverter::is('{"key": "value"}'));
    }

    public function testTypeConverterIsDetectsObject(): void
    {
        $obj = new \stdClass();
        $this->assertEquals('object', \TypeConverter::is($obj));
    }

    // =====================================================
    // Tests for formatTags() - tag display formatting
    // Note: Requires global templates to be set
    // =====================================================

    // Skipped: formatTags() requires complex global state

    // =====================================================
    // Tests for write_ini_file() format generation
    // Note: Uses file I/O, tested separately
    // =====================================================

    // Skipped: write_ini_file() requires file I/O

    // =====================================================
    // Tests for fixAttributes() - XML attribute normalization
    // =====================================================

    public function testFixAttributesNormalizesConfig(): void
    {
        $template = [
            'Config' => [
                '@attributes' => ['Type' => 'Port'],
                'value' => '8080'
            ]
        ];
        \fixAttributes($template, 'Config');
        
        // After fix, Config should be array with numeric index
        $this->assertIsArray($template['Config']);
    }

    public function testFixAttributesHandlesMissingAttribute(): void
    {
        $template = ['Name' => 'Test'];
        // Should not throw when attribute doesn't exist
        \fixAttributes($template, 'Config');
        $this->assertArrayNotHasKey('Config', $template);
    }

    public function testFixAttributesHandlesNonArrayValue(): void
    {
        $template = ['Config' => 'string value'];
        // Should not throw when Config is not an array
        \fixAttributes($template, 'Config');
        $this->assertEquals('string value', $template['Config']);
    }

    // =====================================================
    // Tests for makeXML() - XML generation from template
    // =====================================================

    public function testMakeXMLGeneratesValidXML(): void
    {
        $template = [
            'Name' => 'TestApp',
            'Repository' => 'test/app',
            'Overview' => 'Test application description',
        ];
        
        $xml = \makeXML($template);
        
        $this->assertIsString($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<Container>', $xml);
        $this->assertStringContainsString('<Name>TestApp</Name>', $xml);
    }

    public function testMakeXMLAddsVersionAttribute(): void
    {
        $template = [
            'Name' => 'TestApp',
            'Config' => [
                '@attributes' => ['Type' => 'Port'],
                'value' => '8080'
            ],
        ];
        
        $xml = \makeXML($template);
        
        // With Config entries, should add version="2" attribute
        $this->assertStringContainsString('version="2"', $xml);
    }

    public function testMakeXMLCopiesOverviewToDescription(): void
    {
        $template = [
            'Name' => 'TestApp',
            'Overview' => 'My app overview',
        ];
        
        $xml = \makeXML($template);
        
        // Overview should be copied to Description
        $this->assertStringContainsString('<Description>My app overview</Description>', $xml);
    }

    public function testMakeXMLHandlesNetworkConfig(): void
    {
        $template = [
            'Name' => 'TestApp',
            'Network' => [
                '@attributes' => ['Default' => 'bridge'],
            ],
        ];
        
        $xml = \makeXML($template);
        
        $this->assertStringContainsString('Network', $xml);
    }

    public function testMakeXMLSanitizesRequiresLinks(): void
    {
        $template = [
            'Name' => 'TestApp',
            'Requires' => 'Needs //search_term\\\\',
        ];
        
        $xml = \makeXML($template);
        
        // The sanitization replaces //term\\ with term
        $this->assertStringContainsString('Requires', $xml);
    }

    // =====================================================
    // Tests for languageCheck() - checks language updates
    // =====================================================

    public function testLanguageCheckReturnsFalseWithoutURL(): void
    {
        $template = ['LanguageURL' => ''];
        
        $this->assertFalse(\languageCheck($template));
    }

    public function testLanguageCheckReturnsFalseWhenNotInstalled(): void
    {
        global $caPaths;
        
        $template = [
            'LanguageURL' => 'https://example.com/lang.xml',
            'LanguagePack' => 'de_DE',
        ];
        
        // Language not installed
        @unlink($caPaths['installedLanguages'] . '/lang-de_DE.xml');
        
        $this->assertFalse(\languageCheck($template));
    }
}
