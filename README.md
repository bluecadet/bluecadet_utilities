## Versions

### 1.x Branch

Completely old and outdated... do not use.

### 2.x Branch

< Drupal 8.9 compatable

### 3.x Branch

Drupal 8.9 and Drupal 9 Compatible.

- Removed all code related to Paragraphs preview
- Removed all code related to preview Display modes of nodes. THis should now all be included in Core Previews.

## Includes
<!--
- Node View All Display Modes.
- Paragraph Examples.
-->
- Theme for formatting svg files
- Transliteration for file uploads ([Transliteration Module as Source](https://www.drupal.org/project/transliteration))
- Enable WYSIWYG on textfield fields
- Fractal Component template loader (copy of https://github.com/wearewondrous/fractal_compound_handles to handle windows dev environments)


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
- Update FractalCompoundHandlesLoader class to use new components API

### 8.x-3.0.1

- Updated dependencies so we can use Composer v2
### 8.x-3.0.1

- Added in styles for Claro Admin theme.
