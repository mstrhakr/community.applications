<?php

declare(strict_types=1);

namespace CommunityApplications\Tests;

use PluginTests\TestCase;

/**
 * Tests for file I/O functions
 * 
 * Uses the plugin-tests framework's createTempDir() for temporary files.
 * These are integration-style tests that verify actual file operations.
 */
class FileIOTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = $this->createTempDir();
        
        // Update caPaths to use temp directory for test files
        $GLOBALS['caPaths']['tempFiles'] = $this->tempDir;
    }

    // =====================================================
    // Tests for ca_file_put_contents()
    // =====================================================

    public function testCaFilePutContentsWritesFile(): void
    {
        $file = $this->tempDir . '/test.txt';
        $content = 'Hello World';
        
        $result = \ca_file_put_contents($file, $content);
        
        $this->assertEquals(strlen($content), $result);
        $this->assertFileExists($file);
        $this->assertEquals($content, file_get_contents($file));
    }

    public function testCaFilePutContentsUsesAtomicWrite(): void
    {
        $file = $this->tempDir . '/atomic.txt';
        $content = 'Atomic content';
        
        // The function writes to file~ then renames
        \ca_file_put_contents($file, $content);
        
        // Verify the final file exists and temp file doesn't
        $this->assertFileExists($file);
        $this->assertFileDoesNotExist($file . '~');
    }

    public function testCaFilePutContentsReturnsFalseOnFailure(): void
    {
        // Try to write to a non-existent directory
        $file = '/nonexistent/path/file.txt';
        
        $result = @\ca_file_put_contents($file, 'test');
        
        $this->assertFalse($result);
    }

    // =====================================================
    // Tests for writeJsonFile() and readJsonFile()
    // =====================================================

    public function testWriteJsonFileCreatesFile(): void
    {
        $file = $this->tempDir . '/data.json';
        $data = ['name' => 'Test', 'value' => 123];
        
        \writeJsonFile($file, $data);
        
        $this->assertFileExists($file);
    }

    public function testReadJsonFileReadsSerializedData(): void
    {
        $file = $this->tempDir . '/serialized.dat';
        $data = ['key' => 'value', 'nested' => ['a' => 1]];
        
        // Write serialized format (CA's default)
        file_put_contents($file, serialize($data));
        
        $result = \readJsonFile($file);
        
        $this->assertEquals($data, $result);
    }

    public function testReadJsonFileReadsJsonData(): void
    {
        $file = $this->tempDir . '/json.json';
        $data = ['key' => 'value', 'number' => 42];
        
        // Write JSON format
        file_put_contents($file, json_encode($data));
        
        $result = \readJsonFile($file);
        
        $this->assertEquals($data, $result);
    }

    public function testReadJsonFileReturnsEmptyArrayForMissingFile(): void
    {
        $result = \readJsonFile($this->tempDir . '/nonexistent.json');
        
        $this->assertEquals([], $result);
    }

    public function testWriteAndReadJsonFileRoundTrip(): void
    {
        $file = $this->tempDir . '/roundtrip.dat';
        $data = [
            'apps' => [
                ['name' => 'App1', 'downloads' => 1000],
                ['name' => 'App2', 'downloads' => 2000],
            ],
            'version' => '1.0',
        ];
        
        \writeJsonFile($file, $data);
        $result = \readJsonFile($file);
        
        $this->assertEquals($data, $result);
    }

    // =====================================================
    // Tests for randomFile()
    // =====================================================

    public function testRandomFileReturnsPath(): void
    {
        $result = \randomFile();
        
        $this->assertIsString($result);
        // Normalize path separators for cross-platform comparison
        $normalizedResult = str_replace('\\', '/', $result);
        $normalizedTempDir = str_replace('\\', '/', $this->tempDir);
        $this->assertStringContainsString($normalizedTempDir, $normalizedResult);
    }

    public function testRandomFileReturnsUniquePaths(): void
    {
        $file1 = \randomFile();
        $file2 = \randomFile();
        
        $this->assertNotEquals($file1, $file2);
    }

    // =====================================================
    // Tests for readXmlFile()
    // =====================================================

    public function testReadXmlFileReturnsArray(): void
    {
        $file = $this->tempDir . '/template.xml';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Container>
    <Name>TestApp</Name>
    <Repository>user/repo</Repository>
    <Description>A test application</Description>
</Container>
XML;
        
        file_put_contents($file, $xml);
        
        $result = \readXmlFile($file);
        
        $this->assertIsArray($result);
        $this->assertEquals('TestApp', $result['Name']);
        $this->assertEquals('user/repo', $result['Repository']);
    }

    public function testReadXmlFileReturnsFalseForMissingFile(): void
    {
        $result = \readXmlFile($this->tempDir . '/nonexistent.xml');
        
        $this->assertFalse($result);
    }

    public function testReadXmlFileReturnsFalseForInvalidXml(): void
    {
        $file = $this->tempDir . '/invalid.xml';
        file_put_contents($file, 'not xml content');
        
        $result = \readXmlFile($file);
        
        $this->assertFalse($result);
    }

    public function testReadXmlFileWithConfigAttributes(): void
    {
        $file = $this->tempDir . '/template_with_config.xml';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Container>
    <Name>TestApp</Name>
    <Repository>user/repo</Repository>
    <Config Type="Port" Default="8080">8080</Config>
</Container>
XML;
        
        file_put_contents($file, $xml);
        
        $result = \readXmlFile($file);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('Config', $result);
    }

    public function testReadXmlFileAddsPathToResult(): void
    {
        $file = $this->tempDir . '/template.xml';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Container>
    <Name>TestApp</Name>
    <Repository>user/repo</Repository>
</Container>
XML;
        
        file_put_contents($file, $xml);
        
        $result = \readXmlFile($file);
        
        $this->assertEquals($file, $result['Path']);
    }

    public function testReadXmlFileGenericMode(): void
    {
        $file = $this->tempDir . '/template.xml';
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Container>
    <Name>TestApp</Name>
    <Repository>user/repo</Repository>
</Container>
XML;
        
        file_put_contents($file, $xml);
        
        // Generic mode returns raw parsed XML without CA enhancements
        $result = \readXmlFile($file, true);
        
        $this->assertIsArray($result);
        // In generic mode, Path is NOT added
        $this->assertArrayNotHasKey('Path', $result);
    }

    // =====================================================
    // Tests for write_ini_file()
    // =====================================================

    public function testWriteIniFileCreatesFile(): void
    {
        $file = $this->tempDir . '/config.ini';
        $data = [
            'setting1' => 'value1',
            'setting2' => 'value2',
        ];
        
        \write_ini_file($file, $data);
        
        $this->assertFileExists($file);
    }

    public function testWriteIniFileFormat(): void
    {
        $file = $this->tempDir . '/config.ini';
        $data = [
            'name' => 'TestPlugin',
            'enabled' => 'true',
        ];
        
        \write_ini_file($file, $data);
        
        $content = file_get_contents($file);
        $this->assertStringContainsString('name="TestPlugin"', $content);
        $this->assertStringContainsString('enabled="true"', $content);
    }

    public function testWriteIniFileWithSections(): void
    {
        $file = $this->tempDir . '/config.ini';
        $data = [
            'section1' => [
                'key1' => 'val1',
                'key2' => 'val2',
            ],
        ];
        
        \write_ini_file($file, $data);
        
        $content = file_get_contents($file);
        $this->assertStringContainsString('[section1]', $content);
        $this->assertStringContainsString('key1="val1"', $content);
    }
}
