# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.4 - 2021-04-02


-----

### Release Notes for [1.0.4](https://github.com/mezzio/mezzio-cors/milestone/7)

This release only contains documentation changes.

1.0.x bugfix release (patch)

### 1.0.4

- Total issues resolved: **1**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Documentation

 - [26: docs: add quick start guide](https://github.com/mezzio/mezzio-cors/pull/26) thanks to @boesing

#### Bug,Documentation

 - [21: Guide for a quick start is needed](https://github.com/mezzio/mezzio-cors/issues/21) thanks to @froschdesign

#### Enhancement

 - [20: continous integration](https://github.com/mezzio/mezzio-cors/pull/20) thanks to @boesing

## 1.0.3 - 2020-12-31


-----

### Release Notes for [1.0.3](https://github.com/mezzio/mezzio-cors/milestone/6)

1.0.x bugfix release (patch)

### 1.0.3

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

 - [16: Fixed typo in docs where wrong headers are referenced](https://github.com/mezzio/mezzio-cors/pull/16) thanks to @acelaya

## 1.0.2 - 2020-10-31

### Fixed

- [#10](https://github.com/mezzio/mezzio-cors/pull/10) Per-route `explicit` configuration is now properly handled.

- [#13](https://github.com/mezzio/mezzio-cors/pull/13) Added missing default value for `allowed_max_age` which fixes [#12](https://github.com/mezzio/mezzio-cors/issues/12)


-----

### Release Notes for [1.0.2](https://github.com/mezzio/mezzio-cors/milestone/5)

1.0.x bugfix release (patch)

### 1.0.2

- Total issues resolved: **2**
- Total pull requests resolved: **2**
- Total contributors: **2**

#### Bug,hacktoberfest-accepted

 - [13: bugfix: ensure non-empty string default for allowedMaxAge configuration](https://github.com/mezzio/mezzio-cors/pull/13) thanks to @boesing and @cookieseller
 - [10: Prefer route configuration over automatic `explicit` detection](https://github.com/mezzio/mezzio-cors/pull/10) thanks to @boesing and @cookieseller

## 1.0.1 - 2020-09-02



-----

### Release Notes for [1.0.1](https://github.com/mezzio/mezzio-cors/milestone/2)

1.0.x bugfix release (patch)

### 1.0.1

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Documentation,Enhancement

 - [6: Updates documentation](https://github.com/mezzio/mezzio-cors/pull/6) thanks to @froschdesign
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
