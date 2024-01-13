## Versions

### 1.x Branch

Completely old and outdated... do not use.

### 2.x Branch

< Drupal 8.9 compatable

### 3.x Branch

Drupal 8.9 and Drupal 9 Compatible.

- Removed all code related to Paragraphs preview
- Removed all code related to preview Display modes of nodes. This should now
  all be included in Core Previews.

### 4.0.x Branch

Drupal 9 Compatabile. Due to twig file loading easiest to keep seperate branches for D9 and D10.

### 4.1.x Branch

Drupal 10 Compatabile. Due to twig file loading easiest to keep seperate branches for D9 and D10.

## Includes
<!--
- Node View All Display Modes.
- Paragraph Examples.
-->
- Theme for formatting svg files
- Transliteration for file uploads ([Transliteration Module as Source](https://www.drupal.org/project/transliteration))
- Enable WYSIWYG on textfield fields
- Fractal Component template loader (copy of https://github.com/wearewondrous/fractal_compound_handles to handle windows dev environments)
- Image Style generator, based on aspect ratios.
- text string search for searching html strings in text fields (eg, search for a specific link or class name being used)


### Submodules

- bc_display_title: Provide functionality around the use of the display title field we normally use
- bc_sandbox: provide an easy page to play with


<hr>

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
## Changelog

- Adding in basic text search.

### 4.1.1
- Updating dev tools. Drupal module not really effected.
- Update SimpleFormatWidget to handle text fields and the “format” subfield, so it doesn’t store null in DB

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
