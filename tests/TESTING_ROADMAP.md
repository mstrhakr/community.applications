# Testing Roadmap for Community Applications

## Current State (Updated)
- **157 tests** covering **~45 functions** (~44% function coverage)
- **199 assertions** verifying behavior
- Line coverage reports 97% but is misleading (see below)
- Tests organized by dependency tier

## Why Line Coverage is Misleading
PHPUnit counts lines executed during `require_once` as "covered". Since CA's code is procedural (not OOP), loading files executes most lines. Real function coverage is now ~44%.

## Test File Summary

| File | Tests | Focus |
|------|-------|-------|
| `ExecTest.php` | 10 | `checkRandomApp()` validation |
| `GlobalsTest.php` | 21 | Sort functions, version checking, global state |
| `HelpersTest.php` | 27 | String utilities, array helpers |
| `PureFunctionsTest.php` | 80 | Pure functions, TypeConverter |
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

### Tier 3: File I/O Functions ✅ COMPLETE
| Function | Status | Tests |
|----------|--------|-------|
| `readJsonFile()` | ✅ | 3 tests |
| `writeJsonFile()` | ✅ | 2 tests |
| `ca_file_put_contents()` | ✅ | 3 tests |
| `readXmlFile()` | ✅ | 6 tests |
| `write_ini_file()` | ✅ | 3 tests |

### Tier 4: Network Functions - Not Yet Tested
Need cURL mocking in plugin-tests framework:

| Function | File | Priority |
|----------|------|----------|
| `download_url()` | helpers.php | Medium |
| `download_json()` | helpers.php | Medium |
| `DownloadApplicationFeed()` | exec.php | Low |

### Tier 5: Complex Action Handlers - Not Yet Tested
Integration test candidates (require significant setup):

| Function | File | Complexity |
|----------|------|------------|
| `get_content()` | exec.php | High |
| `display_content()` | exec.php | High |
| `createXML()` | exec.php | Medium |
| `search_dockerhub()` | exec.php | High |
| `pinApp()` | exec.php | Medium |
| `toggleFavourite()` | exec.php | Medium |

## Remaining Untested Functions

### helpers.php
- `getGlobals()` - Populates $GLOBALS['templates']
- `ca_plugin()` - Plugin attribute caching
- `dropAttributeCache()` - Cache management
- `checkPluginUpdate()` - Plugin update checking
- `makeXML()` - XML generation from template
- `moderateTemplates()` - Template moderation
- `pluginDupe()` - Duplicate plugin detection
- `checkInstalledPlugin()` - Plugin installation check
- `isMobile()` - Mobile browser detection
- `formatTags()` - Tag display formatting
- `postReturn()` - POST response handler
- `languageCheck()` - Language update checking
- `getAllInfo()` - Docker info aggregation
- `debug()` - Debug logging
- `checkServerDate()` - Server date validation
- `isTailScaleInstalled()` - Tailscale detection

### exec.php (Action Handlers)
Most functions in exec.php are POST action handlers that require:
- Full HTTP context
- Database/file state
- Docker daemon connection

These are better suited for integration testing.

## Running Tests

```bash
# Unix/Linux/macOS
./bin/phpunit

# Windows
.\bin\phpunit.cmd

# With coverage report
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
