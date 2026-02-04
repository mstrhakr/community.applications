# Community Applications

[![Tests](https://github.com/Squidly271/community.applications/actions/workflows/tests.yml/badge.svg)](https://github.com/Squidly271/community.applications/actions/workflows/tests.yml)

The One Stop Shop for all applications for Unraid®.

> **Trademark Notice:** Unraid® is a registered trademark of Lime Technology, Inc. This project is not affiliated with, endorsed by, or sponsored by Lime Technology, Inc.

## Development

### Running Tests

This project uses [PHPUnit](https://phpunit.de/) for testing with the [plugin-tests](https://github.com/mstrhakr/plugin-tests) framework.

```bash
# Install dependencies
composer install

# Run tests
composer test
# or
vendor/bin/phpunit --testdox
```

### Test Framework

Tests are located in `tests/unit/` and use the plugin-tests framework (included as a git submodule in `tests/framework/`).

The framework provides:
- Mock functions for Unraid® platform APIs (`parse_plugin_cfg()`, `autov()`, etc.)
- Mock globals (`$var`, `$disks`, `$shares`)
- File path virtualization for testing without the actual Unraid® environment

See `tests/bootstrap.php` for the test setup configuration.
