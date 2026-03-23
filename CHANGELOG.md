# Changelog

All notable changes to `philiprehberger/php-money` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

## [1.2.0] - 2026-03-22

### Added
- `Money::min(Money ...$amounts)` — returns the smallest value among the given Money instances
- `Money::max(Money ...$amounts)` — returns the largest value among the given Money instances
- `Money->allocateEqual(int $parts)` — convenience shorthand for equal-ratio allocation

## [1.1.1] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

---

## [1.1.0] - 2026-03-13

### Fixed
- `divide()` now uses strict comparison for zero-check, preventing type coercion bugs with loose `==`

### Removed
- Unused `config/money.php` file and config publishing in `MoneyServiceProvider` (config was never read by any code path)

### Added
- Tests for all 25 currency factory methods
- Tests for `divide()` edge cases (zero integer, zero float)
- Test for `percentage()` with negative values

---

## [1.0.0] - 2026-03-09

### Added

- `Money` immutable value object with integer-based amount storage (smallest currency unit).
- Static factories: `Money::USD()`, `Money::EUR()`, `Money::GBP()`, `Money::of()`, `Money::zero()`.
- `Money::parse()` for parsing formatted money strings (e.g. `"$19.99"`).
- Arithmetic operations: `add()`, `subtract()`, `multiply()`, `divide()`, `percentage()` — all return new instances.
- `allocate()` for proportional splitting without rounding loss (largest-remainder method).
- Comparison methods: `equals()`, `greaterThan()`, `lessThan()`, `greaterThanOrEqual()`, `lessThanOrEqual()`, `isZero()`, `isPositive()`, `isNegative()`.
- `format()` with locale-aware output via `ext-intl` `NumberFormatter`.
- `toArray()` and `jsonSerialize()` for serialisation.
- `Currency` value object with a registry of 25 common ISO 4217 currencies.
- `Currency::fromCode()` for registry lookups.
- Static factory methods for all registered currencies (`Currency::USD()`, `Currency::EUR()`, etc.).
- `CurrencyMismatchException` thrown on cross-currency arithmetic.
- `InvalidAmountException` for parse errors, division by zero, and invalid allocation ratios.
- `MoneyCast` Laravel Eloquent cast — stores as JSON, optional default currency parameter.
- `MoneyServiceProvider` for Laravel auto-discovery and config publishing.
- `config/money.php` stub for default currency and locale.
- PHPUnit 11 test suite covering arithmetic, comparison, allocation, parsing, serialisation, and the Eloquent cast.
- PHPStan level 8 configuration.
- Laravel Pint code-style configuration.
- GitHub Actions CI pipeline for PHP 8.2, 8.3, and 8.4.

[Unreleased]: https://github.com/philiprehberger/php-money/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/philiprehberger/php-money/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/philiprehberger/php-money/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/philiprehberger/php-money/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/philiprehberger/php-money/releases/tag/v1.0.0
