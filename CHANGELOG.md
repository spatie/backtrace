# Changelog

All notable changes to `backtrace` will be documented in this file

## 1.8.0 - 2025-08-25

### What's Changed

- Laravel's `artisan` file is now considered a vendor file, even though it's typically found in the application's root directory
- Statamic's `please` file is now considered a vendor file, even though it's typically found in the application's root directory

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.7.4...1.8.0

## 1.7.4 - 2025-05-08

### What's Changed

* Fix `getCodeSnippetProvider` when `file` is not in `open_basedir` by @tadhgboyle in https://github.com/spatie/backtrace/pull/32

### New Contributors

* @tadhgboyle made their first contribution in https://github.com/spatie/backtrace/pull/32

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.7.3...1.7.4

## 1.7.3 - 2025-05-07

### What's Changed

* Add test for enabling arguments with a throwable by @jivanf in https://github.com/spatie/backtrace/pull/31

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.7.2...1.7.3

## 1.7.2 - 2025-04-28

### What's Changed

* Update README.md by @jackbayliss in https://github.com/spatie/backtrace/pull/28
* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/spatie/backtrace/pull/29
* Add object property to frame by @jivanf in https://github.com/spatie/backtrace/pull/30

### New Contributors

* @jivanf made their first contribution in https://github.com/spatie/backtrace/pull/30

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.7.1...1.7.2

## 1.7.1 - 2024-12-02

- fix deprecation message

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.7.0...1.7.1

## 1.7.0 - 2024-12-02

### What's Changed

* Add PHP 8.4 Support by @sweptsquash in https://github.com/spatie/backtrace/pull/27

### New Contributors

* @sweptsquash made their first contribution in https://github.com/spatie/backtrace/pull/27

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.6.3...1.7.0

## 1.6.3 - 2024-11-18

### What's Changed

* trimFilePaths feature  by @jackbayliss in https://github.com/spatie/backtrace/pull/26

### New Contributors

* @jackbayliss made their first contribution in https://github.com/spatie/backtrace/pull/26

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.6.2...1.6.3

## 1.6.2 - 2024-07-22

### What's Changed

* Bump dependabot/fetch-metadata from 2.0.0 to 2.1.0 by @dependabot in https://github.com/spatie/backtrace/pull/24
* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot in https://github.com/spatie/backtrace/pull/25
* Correct with object check by @1RV34 in https://github.com/spatie/backtrace/pull/22

### New Contributors

* @1RV34 made their first contribution in https://github.com/spatie/backtrace/pull/22

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.6.1...1.6.2

## 1.6.1 - 2024-04-24

- Add check wether serializable closure is defined

## 1.6.0 - 2024-04-24

### What's Changed

* Serializeable closure support by @rubenvanassche in https://github.com/spatie/backtrace/pull/23

**Full Changelog**: https://github.com/spatie/backtrace/compare/1.5.3...1.6.0

## 1.5.3 - 2023-06-28

- Fix issue where arguments of throwable leaked

## 1.5.2 - 2023-06-28

- Another type update

## 1.5.1 - 2023-06-28

- Type update

## 1.5.0 - 2023-06-28

- Add support for reducing stack trace arguments (#16)

## 1.4.1 - 2023-06-13

- Allow `withArguments` to be specified with boolean

## 1.4.0 - 2023-03-04

Add `getSnippetAsString` to `Frame`

## 1.3.0 - 2023-03-04

- add `getAsString` to snippet

## 1.2.2 - 2023-02-21

- fix a misconfigured application path

## 1.2.1 - 2021-11-09

- Add a return typehint (#4)

## 1.2.0 - 2021-05-19

- add `firstApplicationFrameIndex`

## 1.1.0 - 2021-01-29

- add `snippetProperties`

## 1.0.1 - 2021-01-27

- add support for PHP 7.3

## 1.0.0 - 2020-11-24

- initial release
