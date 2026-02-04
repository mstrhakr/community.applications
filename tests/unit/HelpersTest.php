<?php

declare(strict_types=1);

namespace CommunityApplications\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Community Applications helper functions
 * 
 * These tests validate the framework bootstrapping works by testing
 * pure functions from helpers.php
 */
class HelpersTest extends TestCase
{
    private static bool $helpersLoaded = false;

    public static function setUpBeforeClass(): void
    {
        if (!self::$helpersLoaded) {
            // Include helpers.php through the stream wrapper
            require_once '/usr/local/emhttp/plugins/community.applications/include/helpers.php';
            self::$helpersLoaded = true;
        }
    }

    // =====================================================
    // Tests for arrayEntriesToObject()
    // =====================================================

    public function testArrayEntriesToObjectWithDefaultFlag(): void
    {
        $input = ['one', 'two', 'three'];
        $result = \arrayEntriesToObject($input);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('one', $result);
        $this->assertArrayHasKey('two', $result);
        $this->assertArrayHasKey('three', $result);
        $this->assertTrue($result['one']);
        $this->assertTrue($result['two']);
        $this->assertTrue($result['three']);
    }

    public function testArrayEntriesToObjectWithCustomFlag(): void
    {
        $input = ['a', 'b'];
        $result = \arrayEntriesToObject($input, 'custom');
        
        $this->assertEquals('custom', $result['a']);
        $this->assertEquals('custom', $result['b']);
    }

    public function testArrayEntriesToObjectWithEmptyArray(): void
    {
        $result = \arrayEntriesToObject([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testArrayEntriesToObjectWithNonArray(): void
    {
        $result = \arrayEntriesToObject('not an array');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // =====================================================
    // Tests for startsWith()
    // =====================================================

    public function testStartsWithMatchingPrefix(): void
    {
        $this->assertTrue(\startsWith('hello world', 'hello'));
    }

    public function testStartsWithNonMatchingPrefix(): void
    {
        $this->assertFalse(\startsWith('hello world', 'world'));
    }

    public function testStartsWithEmptyNeedle(): void
    {
        $this->assertTrue(\startsWith('hello', ''));
    }

    public function testStartsWithCaseInsensitive(): void
    {
        // startsWith uses strripos which is case-insensitive
        $this->assertTrue(\startsWith('Hello World', 'hello'));
    }

    public function testStartsWithNonStringInputs(): void
    {
        $this->assertFalse(\startsWith(123, 'test'));
        $this->assertFalse(\startsWith('test', 123));
    }

    // =====================================================
    // Tests for endsWith()
    // =====================================================

    public function testEndsWithMatchingSuffix(): void
    {
        $this->assertTrue(\endsWith('hello world', 'world'));
    }

    public function testEndsWithNonMatchingSuffix(): void
    {
        $this->assertFalse(\endsWith('hello world', 'hello'));
    }

    public function testEndsWithEmptyString(): void
    {
        $this->assertTrue(\endsWith('hello', ''));
    }

    public function testEndsWithExactMatch(): void
    {
        $this->assertTrue(\endsWith('test', 'test'));
    }

    // =====================================================
    // Tests for first_str_replace()
    // =====================================================

    public function testFirstStrReplaceReplacesFirst(): void
    {
        $result = \first_str_replace('foo bar foo', 'foo', 'baz');
        $this->assertEquals('baz bar foo', $result);
    }

    public function testFirstStrReplaceNoMatch(): void
    {
        $result = \first_str_replace('hello world', 'xyz', 'abc');
        $this->assertEquals('hello world', $result);
    }

    public function testFirstStrReplaceEmptyNeedle(): void
    {
        $result = \first_str_replace('hello', '', 'x');
        $this->assertEquals('xhello', $result);
    }

    // =====================================================
    // Tests for last_str_replace()
    // =====================================================

    public function testLastStrReplaceReplacesLast(): void
    {
        $result = \last_str_replace('foo bar foo', 'foo', 'baz');
        $this->assertEquals('foo bar baz', $result);
    }

    public function testLastStrReplaceNoMatch(): void
    {
        $result = \last_str_replace('hello world', 'xyz', 'abc');
        $this->assertEquals('hello world', $result);
    }

    // =====================================================
    // Tests for searchArray()
    // =====================================================

    public function testSearchArrayFindsValue(): void
    {
        $array = [
            ['name' => 'apple', 'color' => 'red'],
            ['name' => 'banana', 'color' => 'yellow'],
            ['name' => 'grape', 'color' => 'purple'],
        ];
        
        $result = \searchArray($array, 'name', 'banana');
        $this->assertEquals(1, $result);
    }

    public function testSearchArrayNotFound(): void
    {
        $array = [
            ['name' => 'apple'],
            ['name' => 'banana'],
        ];
        
        $result = \searchArray($array, 'name', 'orange');
        $this->assertFalse($result);
    }

    public function testSearchArrayWithStartingIndex(): void
    {
        $array = [
            ['name' => 'apple'],
            ['name' => 'banana'],
            ['name' => 'apple'],  // Second apple at index 2
        ];
        
        $result = \searchArray($array, 'name', 'apple', 1);
        $this->assertEquals(2, $result);
    }

    public function testSearchArrayEmptyArray(): void
    {
        $result = \searchArray([], 'key', 'value');
        $this->assertFalse($result);
    }

    // =====================================================
    // Tests for getPost()
    // =====================================================

    public function testGetPostReturnsPostedValue(): void
    {
        $_POST['testKey'] = urlencode('test value');
        $result = \getPost('testKey', 'default');
        $this->assertEquals('test value', $result);
        unset($_POST['testKey']);
    }

    public function testGetPostReturnsDefault(): void
    {
        $result = \getPost('nonexistent', 'default value');
        $this->assertEquals('default value', $result);
    }

    // =====================================================
    // Tests for var_dump_ret()
    // =====================================================

    public function testVarDumpRetReturnsString(): void
    {
        $result = \var_dump_ret('test');
        $this->assertIsString($result);
        $this->assertStringContainsString('string', $result);
        $this->assertStringContainsString('test', $result);
    }

    public function testVarDumpRetWithArray(): void
    {
        $result = \var_dump_ret(['a' => 1, 'b' => 2]);
        $this->assertIsString($result);
        $this->assertStringContainsString('array', $result);
    }

    public function testVarDumpRetWithNull(): void
    {
        $result = \var_dump_ret(null);
        $this->assertIsString($result);
        $this->assertStringContainsString('NULL', $result);
    }
}
