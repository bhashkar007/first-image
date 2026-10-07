# First Image Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - 2026-10-07

> The package has been renamed from `by/first-image` to `uxi360/first-image`. See the README for upgrade instructions.

### Added
- Craft CMS 5 compatibility.
- `getFirstImage` now accepts an image transform - a named transform handle or a transform definition - the same way `asset.getUrl()` does.
- CKEditor field support, alongside Redactor.
- `data-src` is used when an image has no `src`.

### Changed
- The package is now `uxi360/first-image` and the PHP namespace is `uxi360\firstimage`. The plugin handle is still `first-image`.
- The plugin now requires Craft CMS 5.0.0 and PHP 8.2 or later.
- Replaced the bundled PHP Simple HTML DOM Parser with the plugin's own lightweight image finder.
- Results are cached for the duration of the request.
- The plugin is now maintained by UXI360 InfoTech: the developer name, links (https://uxi360.com) and the copyright notice in `LICENSE.md` have been updated.

### Removed
- Removed the bundled PHP Simple HTML DOM Parser library, the unused translation file and the duplicate `LICENSE` file.

### Fixed
- Fixed an error that occurred when the field was empty or its content was larger than 600 KB.

## 2.0.0
### Changed
- Craft CMS 4 compatibility.

## 1.1.0 - 2022-06-02
### Changed
- Updated PHP Simple HTML DOM Parser to v1.9.1.

## 1.0.0 - 2018-04-24
### Added
- Initial release
