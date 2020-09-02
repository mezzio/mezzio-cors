# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - 2020-09-02

### Added

- The initial stable release provides the core base functionality, including:

  - CORS preflight detection and responses, per-route, configured per-HTTP method.
  - Generation of CORS headers for your application.

  The functionality is accomplished via `CorsMiddleware` provided with the package, which in turn consumes CORS configuration as defined in the shipped `ConfigurationInterface`

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.


-----

### Release Notes for [1.0.0](https://github.com/mezzio/mezzio-cors/milestone/1)



### 1.0.0

- Total issues resolved: **0**
- Total pull requests resolved: **5**
- Total contributors: **1**

#### Enhancement

 - [5: Add support for PHP 8.0](https://github.com/mezzio/mezzio-cors/pull/5) thanks to @boesing
 - [4: Psalm](https://github.com/mezzio/mezzio-cors/pull/4) thanks to @boesing
 - [1: Upgrade phpstan to 0.12](https://github.com/mezzio/mezzio-cors/pull/1) thanks to @boesing

#### Bug,Documentation

 - [3: Use proper branches for badges](https://github.com/mezzio/mezzio-cors/pull/3) thanks to @boesing
 - [2: Documentation fixes](https://github.com/mezzio/mezzio-cors/pull/2) thanks to @boesing
