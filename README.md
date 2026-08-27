# Bluecadet Utilities

Adds utilities to aid in development of custom sites.

## Requirements

- Drupal 10 or Drupal 11
- PHP 7.4 or higher

## Versions

### 5.x Branch

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

This module includes automated tests that run via GitHub Actions against Drupal 10.1.x-10.3.x and 11.0.x (see `.github/workflows/drupal-tests-and-standards.yml` for the exact PHP/MariaDB matrix).

### Test Plan

#### Automated Tests (GitHub Actions)

The CI pipeline runs the following for each Drupal version:

1. **PHPCS** - Drupal coding standards validation (`Drupal` and `DrupalPractice` standards)
2. **Drupal-Check** - static analysis for deprecated API usage
3. **PHPUnit** - automated tests

#### Current coverage

- Unit test for `SanitizeName::sanitizeFilename()` (filename sanitization)
- Functional test (`BluecadetUtilitiesTest`) exercising the module against a content type

## Changelog

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
