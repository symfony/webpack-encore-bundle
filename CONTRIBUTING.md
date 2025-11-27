Contributing
============

When contributing, you can fix some things that will be detected by CI anyway *before* sending your pull request.

The following tools will be installed in the `tools` directory, so they don't share the bundle requirements.

PHPStan
-------

```bash
composer install --working-dir=tools/phpstan
tools/phpstan/vendor/bin/phpstan analyze
# Based on the results, you may want to update the baseline
tools/phpstan/vendor/bin/phpstan analyze --generate-baseline
```

Psalm
-----

```bash
composer install --working-dir=tools/psalm
tools/psalm/vendor/bin/psalm
```

PHPUnit
-------

```bash
./vendor/bin/simple-phpunit
```
