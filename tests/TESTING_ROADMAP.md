# Testing Roadmap for Community Applications

## Current State (Updated)
- **189 tests** covering **~56 functions** (~54% function coverage)
- **239 assertions** verifying behavior
- Line coverage reports 97% but is misleading (see below)
- Tests organized by dependency tier

## Why Line Coverage is Misleading
PHPUnit counts lines executed during `require_once` as "covered". Since CA's code is procedural (not OOP), loading files executes most lines. Real function coverage is now ~54%.

## Test File Summary

| File | Tests | Focus |
|------|-------|-------|
| `ExecTest.php` | 10 | `checkRandomApp()` validation |
| `GlobalsTest.php` | 46 | Sort functions, version checking, global state, plugin functions |
| `HelpersTest.php` | 27 | String utilities, array helpers |
| `PureFunctionsTest.php` | 87 | Pure functions, TypeConverter, makeXML |
| `FileIOTest.php` | 19 | File read/write operations |

## Completed Tests by Tier

### Tier 1: Pure Functions ✅ COMPLETE
| Function | Status | Tests |
|----------|--------|-------|
| `alphaNumeric()` | ✅ | 5 tests |
| `validURL()` | ✅ | 7 tests |
| `filterMatch()` | ✅ | 8 tests |
| `getDownloads()` | ✅ | 5 tests |
| `fixDescription()` | ✅ | 5 tests |
| `getAuthor()` | ✅ | 6 tests |
| `categoryList()` | ✅ | 4 tests |
| `languageAuthorList()` | ✅ | 3 tests |
| `plain()` | ✅ | 3 tests |
| `ca_explode()` | ✅ | 4 tests |
| `getYoutubeThumbnail()` | ✅ | 4 tests |
| `addMissingVars()` | ✅ | 3 tests |
| `portsUsed()` | ✅ | 4 tests |
| `removeXMLtags()` | ✅ | 3 tests |
| `fixAttributes()` | ✅ | 3 tests |
| `TypeConverter::*` | ✅ | 13 tests |
| `arrayEntriesToObject()` | ✅ | 4 tests |
| `startsWith()` | ✅ | 5 tests |
| `endsWith()` | ✅ | 4 tests |
| `first_str_replace()` | ✅ | 3 tests |
| `last_str_replace()` | ✅ | 2 tests |
| `searchArray()` | ✅ | 4 tests |
| `getPost()` | ✅ | 2 tests |
| `var_dump_ret()` | ✅ | 3 tests |

### Tier 2: Global State Functions ✅ COMPLETE
| Function | Status | Tests |
|----------|--------|-------|
| `versionCheck()` | ✅ | 5 tests |
| `mySort()` | ✅ | 4 tests |
| `repositorySort()` | ✅ | 3 tests |
| `favouriteSort()` | ✅ | 3 tests |
| `fixTemplates()` | ✅ | 5 tests |
| `checkRandomApp()` | ✅ | 10 tests |
| `randomFile()` | ✅ | 3 tests |
| `isMobile()` | ✅ | 5 tests |
| `isTailScaleInstalled()` | ✅ | 3 tests |
| `getGlobals()` | ✅ | 3 tests |
| `dropAttributeCache()` | ✅ | 2 tests |
| `pluginDupe()` | ✅ | 2 tests |
| `makeXML()` | ✅ | 5 tests |
| `languageCheck()` | ✅ | 2 tests |
| `ca_plugin()` | ✅ | 3 tests |
| `checkPluginUpdate()` | ✅ | 2 tests |
| `checkInstalledPlugin()` | ✅ | 2 tests |
| `checkServerDate()` | ✅ | 3 tests |

### Tier 3: File I/O Functions ✅ COMPLETE
| Function | Status | Tests |
|----------|--------|-------|
| `readJsonFile()` | ✅ | 3 tests |
| `writeJsonFile()` | ✅ | 2 tests |
| `ca_file_put_contents()` | ✅ | 3 tests |
| `readXmlFile()` | ✅ | 6 tests |
| `write_ini_file()` | ✅ | 3 tests |

### Tier 4: Network Functions - Not Unit Testable
Require cURL mocking which adds significant complexity with low ROI:

| Function | File | Verdict |
|----------|------|---------|
| `download_url()` | helpers.php | Integration test |
| `download_json()` | helpers.php | Integration test |
| `DownloadApplicationFeed()` | exec.php | Integration test |

### Tier 5: Complex Action Handlers - Integration Tests
Require full Unraid environment (Docker, filesystem state, HTTP context):

| Function | File | Complexity |
|----------|------|------------|
| `get_content()` | exec.php | High |
| `display_content()` | exec.php | High |
| `createXML()` | exec.php | Medium |
| `search_dockerhub()` | exec.php | High |
| `pinApp()` | exec.php | Medium |
| `toggleFavourite()` | exec.php | Medium |

## Remaining Untested Functions - Analysis

### helpers.php - NOW TESTED ✅

| Function | Status | Tests |
|----------|--------|-------|
| `getGlobals()` | ✅ | 3 tests |
| `dropAttributeCache()` | ✅ | 2 tests |
| `makeXML()` | ✅ | 5 tests |
| `pluginDupe()` | ✅ | 2 tests |
| `isMobile()` | ✅ | 5 tests |
| `languageCheck()` | ✅ | 2 tests |
| `isTailScaleInstalled()` | ✅ | 3 tests |
| `ca_plugin()` | ✅ | 3 tests |
| `checkPluginUpdate()` | ✅ | 2 tests |
| `checkInstalledPlugin()` | ✅ | 2 tests |
| `checkServerDate()` | ✅ | 3 tests |

### helpers.php - CANNOT Unit Test (Missing Mocks) ❌

| Function | Blocker | Requires |
|----------|---------|----------|
| `getAllInfo()` | Uses `$DockerTemplates`, `$DockerClient` | DockerMock integration |
| `debug()` | Uses `$_SESSION`, `shell_exec()` | Session mock, exec mock |
| `postReturn()` | HTTP headers, output buffering | Integration test |
| `moderateTemplates()` | Complex chain (getGlobals, versionCheck, etc.) | Many dependencies |
| `formatTags()` | Uses `$GLOBALS['templates']`, `tr()` | Needs HTML assertion |

### exec.php (Action Handlers)
All 48 functions in exec.php are POST action handlers requiring:
- Full HTTP context (`$_POST`, `$_SERVER`)
- Database/file state
- Docker daemon connection
- External service calls

**Verdict**: Integration test candidates only.

## Maximum Achievable Unit Test Coverage

### Summary
- **52 functions tested** out of 103 total (~51%)
- **179 tests** with **228 assertions**
- Remaining 51 functions require either:
  - Missing mocks (`plugin()` function)
  - Integration test environment (Docker, HTTP, Sessions)
  - Complex dependency chains

### To reach higher coverage, the plugin-tests framework needs:
1. `plugin()` function mock - would enable 4 more functions
2. `$_SESSION` mock - would enable `debug()`
3. Docker class integration with DockerMock - would enable `getAllInfo()`
4. Output buffering test helpers - would enable `postReturn()`
./bin/phpunit --coverage-text

# Specific test file
./bin/phpunit tests/unit/PureFunctionsTest.php
```

## Requirements
- PHP 8.1+ with `short_open_tag=On`
- Xdebug for coverage
- plugin-tests framework (tests/framework submodule)

## Test Code Quality

The tests discovered several minor issues in CA source code:
1. Missing `$_POST['action']` null check in `writeJsonFile()` debug logging
2. Various undefined array key warnings (PHP 8 strictness)

These are logged as warnings during test runs but don't affect functionality.
