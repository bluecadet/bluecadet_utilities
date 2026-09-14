# Bluecadet Utilities

Adds utilities to aid in development of custom sites.

## Requirements

- Drupal 10 or Drupal 11
- PHP 7.4 or higher

## Versions

### 5.x Branch

- **5.1.x**: Drupal 10 & 11 compatible (PHP 7.4+). No breaking changes -- CI modernization, PHPCS/PHPStan cleanup, and test coverage work only.
- **5.0.x**: Drupal 10 & 11 compatible (PHP 7.4+). **Breaking:** Removed `FractalCompoundHandlesLoader` and the `bluecadet_utilities.loader.fractal_compound_handles` Twig loader service. Sites relying on Fractal-style `#handle` or `@namespace/component` syntax in Twig templates must migrate to an alternative (e.g. Drupal core SDC or the `components` contrib module).

### 4.x Branch

- **4.2.x**: Drupal 10 & 11 compatible.
- **4.1.x**: Drupal 10 compatible. Due to twig file loading, easiest to keep separate branches for D9 and D10.
- **4.0.x**: Drupal 9 compatible. Due to twig file loading, easiest to keep separate branches for D9 and D10.

### 3.x Branch — Drupal 8.9 and Drupal 9 compatible.

- Removed all code related to Paragraphs preview.
- Removed all code related to preview Display modes of nodes. This should now all be included in Core Previews.

### 2.x Branch — < Drupal 8.9 compatible.

### 1.x Branch — completely outdated, do not use.

## Includes

- Theme for formatting svg files
- Transliteration for file uploads ([Transliteration Module as Source](https://www.drupal.org/project/transliteration))
- Enable WYSIWYG on textfield fields
- Image Style generator, based on aspect ratios
- Text string search for searching html strings in text fields (eg, search for a specific link or class name being used)

### Submodules

- bc_display_title: Provide functionality around the use of the display title field we normally use
- bc_sandbox: provide an easy page to play with

## Not using Composer

If you are not using composer, you can delete all unneeded files.

- composer.json

## Using Composer

If you are using composer to manage Drupal modules, make sure you add custom
location for this module to be downloaded to. You must add the installer types
line as well as the location for the module.

```json
  ...
  "installer-types": ["custom-drupal-module"],
  "installer-paths": {
    "web/core": ["type:drupal-core"],
    "web/modules/contrib/{$name}": ["type:drupal-module"],
    "web/modules/custom/{$name}": ["type:custom-drupal-module"],
    "web/profiles/contrib/{$name}": ["type:drupal-profile"],
    "web/themes/contrib/{$name}": ["type:drupal-theme"],
    "drush/contrib/{$name}": ["type:drupal-drush"]
  },
  ...
```

## Testing

This module includes automated tests that run via GitHub Actions against Drupal 10.5.x-10.6.x and 11.2.x-11.3.x (see `.github/workflows/drupal-tests-and-standards.yml` for the exact PHP/MariaDB matrix).

### Test Plan

#### Automated Tests (GitHub Actions)

The CI pipeline runs the following for each Drupal version:

1. **PHPCS** - Drupal coding standards validation (`Drupal` and `DrupalPractice` standards)
2. **PHPStan** - static analysis for deprecated API usage (via `mglaman/phpstan-drupal`)
3. **PHPUnit** - Unit, Kernel, and Functional tests, with code coverage reporting

#### Current coverage

~91% line coverage across `src/` and both submodules.

- Unit tests for pure logic: `SanitizeName`, `SimpleFormatTextfield`, `DrupalStateTrait`
- Kernel tests for Drupal-integrated behavior: field formatters/widgets, settings forms, the image style generator, and the entity-reference/text-field search tools (including their batch pipelines)
- Functional test (`BluecadetUtilitiesTest`) exercising the module against a real content type and admin routes

## Changelog

### 5.1.0-alpha.1

- Modernized CI to a reusable-workflow architecture, matrix-tested against Drupal 10.5.x-10.6.x and 11.2.x-11.3.x across PHP 8.2-8.4
- Fixed all PHPCS/PHPStan violations, including converting `EntityReferenceFieldSearch`, `TextFieldSearch`, and `ImageStyleGenerator` to real constructor-based dependency injection and removing dead/duplicate code
- Fixed a PCOV bug that was silently reporting 0% test coverage in CI, and raised automated test coverage from ~5% to ~91% (Unit and Kernel tests across forms, field formatters/widgets, and both submodules)
- Bumped `@bluecadet/bldr` to `2.0.0-alpha.12`, `@bluecadet/drops` to `^1.1.0`, and `bluecadet/bc_drupal_package_manager` to `^1.1`; disabled bldr's new v2 ESLint/Stylelint providers since this repo has no config for either
- Excluded the submodules' `composer.json` stubs (added purely to break out per-submodule CI coverage reporting) from the release tarball via `.gitattributes`

### 5.0.0

- Adding in basic text search.
- **Breaking:** Removed `FractalCompoundHandlesLoader` and the `bluecadet_utilities.loader.fractal_compound_handles` Twig loader service. Sites relying on Fractal-style `#handle` or `@namespace/component` syntax in Twig templates must migrate to an alternative (e.g. Drupal core SDC or the `components` contrib module).

### 4.2.x

- Enforcing Drupal 11 compatibility.
- Bug fixes for `simple-formatter.js`
- Update bldr to 1.1.0
- Add in schema definitions for widget and formatter settings

### 4.1.1

- Updating dev tools. Drupal module not really effected.
- Update SimpleFormatWidget to handle text fields and the "format" subfield, so it doesn't store null in DB

### 4.1.0

- D10 compatibility
- Updated twig handler for D10

### 4.0.3

- Updating dev tools. Drupal module not really effected.

### 4.0.2

- Adding in utilities for github/building/composer etc.

### 4.0.1

- Added in Image Style generator.
- Added Simple Format Formatter so we can have min html in a text field.
- Updated simple Format formatter styles for buttons in Claro
- Added formatter settings if you want to run the text through a system text formatter.

### 8.x-3.0.3

- Update FractalCompoundHandlesLoader class to use new components API
- Fix multiple uses of the simple formatter

### 8.x-3.0.2

- Updated dependencies so we can use Composer v2

### 8.x-3.0.1

- Added in styles for Claro Admin theme.

<br>
<br>
<br>

## Proudly developed @ Bluecadet

<p style="background-color: white; padding: 20px">
  <a href="https://www.bluecadet.com/"><img style="max-width: 50%; min-width: 300px; background: white; padding: 20px;" src="https://www.bluecadet.com/wp-content/themes/bluecadet-2018/images/logo/logo-bluecadet-black.svg" alt="Bluecadet"></a>
</p>
