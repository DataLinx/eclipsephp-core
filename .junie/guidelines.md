# Eclipse Core Development Guidelines

This document provides essential information for developers working on the Eclipse Core package.

## Build/Configuration Instructions

### Requirements
- PHP 8.3
- Composer
- Node.js and npm (for asset compilation)

### Local Development Setup

1. **Clone the repository**

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup using Lando (recommended)**
   
   The project includes a `.lando.dist.yml` configuration file for local development using Lando.
   
   1. Copy `.lando.dist.yml` to `.lando.yml` and customize as needed
   2. Start the Lando environment:
      ```bash
      lando start
      ```
   3. Run setup commands:
      ```bash
      lando composer install
      lando npm install
      ```

4. **Manual Setup (without Lando)**
   
   If you're not using Lando, you'll need to:
   
   1. Run the setup script:
      ```bash
      composer setup
      ```
   2. This will:
      - Install npm dependencies
      - Publish Eclipse configuration
      - Sync the package skeleton

## Testing Information

### Test Configuration

The project uses Pest PHP (built on PHPUnit) for testing with Orchestra Testbench for Laravel package testing.

Configuration files:
- `phpunit.xml.dist`: PHPUnit configuration
- `testbench.yaml`: Orchestra Testbench configuration

### Running Tests

When running inside the container (after `lando ssh`):
```bash
# Run all tests
composer test

# Run specific tests
composer test -- --filter=TestName

```

When running outside the container:
```bash
lando test

# Run specific tests
lando test -- --filter=TestName

```

### Adding New Tests

1. Create a new test file in the `tests/Feature` or `tests/Unit` directory
2. Use the Pest PHP syntax for writing tests:

```php
<?php

test('example test', function () {
    // This is a simple example test
    $this->assertTrue(true);
});

test('example assertion', function () {
    // This test demonstrates basic assertions
    $value = 'Eclipse';
    $this->assertEquals('Eclipse', $value);
    $this->assertNotEquals('Other', $value);
    $this->assertStringContains('clip', $value);
});
```

3. Run the tests to verify they work

### Testing Environment

Tests use:
- SQLite in-memory database
- Array cache driver
- Sync queue connection

## Code Style & Development Guidelines

### Code Style

- The project follows PSR-4 autoloading standards
- Uses Laravel Pint for code formatting
- 4-space indentation (2-space for YAML files)
- UTF-8 encoding and LF line endings

### Formatting Code

```bash
# Format code using Laravel Pint
composer format

# Using Lando
lando format
```

### Debugging Tools

The project includes several debugging tools:
- Laravel Telescope for request/query debugging
- Laravel Horizon for queue monitoring
- Log Viewer for log file inspection

Access to these tools is restricted based on user roles and permissions, as shown in the access tests.
