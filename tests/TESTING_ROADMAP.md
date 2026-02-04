# Testing Roadmap for Community Applications

## Current State
- 50 tests covering 14 of ~103 functions (~13% function coverage)
- Line coverage reports 97% but this is misleading (see below)
- Tests focus on pure utility functions from helpers.php

## Why Line Coverage is Misleading
PHPUnit counts lines executed during `require_once` as "covered". Since CA's code is procedural (not OOP), loading files executes most lines. Real function coverage is ~13%.

## Test Priority Tiers

### Tier 1: Pure Functions (No Dependencies) - HIGH VALUE
Easy to test, no mocking needed:

| Function | File | Status | Notes |
|----------|------|--------|-------|
| `alphaNumeric()` | helpers.php | TODO | Simple regex |
| `validURL()` | helpers.php | TODO | URL validation |
| `filterMatch()` | helpers.php | TODO | Search matching logic |
| `getDownloads()` | helpers.php | TODO | Number formatting |
| `fixDescription()` | helpers.php | TODO | String sanitization |
| `getAuthor()` | helpers.php | TODO | Author extraction |
| `categoryList()` | helpers.php | TODO | Category formatting |
| `languageAuthorList()` | helpers.php | TODO | Author list formatting |
| `fixTemplates()` | helpers.php | TODO | Template validation |

### Tier 2: Functions with Global Dependencies
Need global state setup but no I/O:

| Function | File | Dependencies | Status |
|----------|------|--------------|--------|
| `versionCheck()` | helpers.php | `$caSettings` | DONE |
| `mySort()` | helpers.php | `$sortOrder` | DONE |
| `repositorySort()` | helpers.php | `$caSettings` | DONE |
| `checkRandomApp()` | exec.php | `$caSettings` | DONE |
| `favouriteSort()` | helpers.php | `$caSettings` | TODO |

### Tier 3: Functions with File I/O
Need mock filesystem:

| Function | File | I/O Type | Status |
|----------|------|----------|--------|
| `readJsonFile()` | helpers.php | Read | TODO |
| `writeJsonFile()` | helpers.php | Write | TODO |
| `ca_file_put_contents()` | helpers.php | Write | TODO |
| `readXmlFile()` | helpers.php | Read/Parse | TODO |
| `checkInstalledPlugin()` | helpers.php | Read | TODO |

### Tier 4: Functions with Network/External Dependencies
Need comprehensive mocking:

| Function | File | Dependencies | Status |
|----------|------|--------------|--------|
| `download_url()` | helpers.php | cURL | TODO |
| `download_json()` | helpers.php | cURL | TODO |
| `DownloadApplicationFeed()` | exec.php | Network | TODO |

### Tier 5: Complex Action Handlers
Integration test candidates:

| Function | File | Complexity | Status |
|----------|------|------------|--------|
| `get_content()` | exec.php | High | TODO |
| `display_content()` | exec.php | High | TODO |
| `createXML()` | exec.php | Medium | TODO |
| `search_dockerhub()` | exec.php | High | TODO |

## Running Tests

```bash
# Unix/Linux/macOS
./bin/phpunit

# Windows
.\bin\phpunit.cmd

# With coverage
./bin/phpunit --coverage-text
```

## Requirements
- PHP 8.1+ with `short_open_tag=On`
- Xdebug for coverage
- plugin-tests framework (tests/framework submodule)
