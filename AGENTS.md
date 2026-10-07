# AGENTS.md

Guidance for AI coding agents (Claude Code, Copilot, Codex, etc.) working in this repository.

## What this is

`bluecadet_utilities` is a Bluecadet Drupal module of shared developer/admin utilities, plus two submodules. It is consumed by Drupal sites via Composer (package type `custom-drupal-module`, installed to `modules/bluecadet/bluecadet_utilities`), so it does not run standalone: testing and running need a full Drupal install (see below).

Requires PHP 8.2+. Tested on Drupal 10.6, 11.3, 11.4 and 11.5 (12.0 runs as an early signal and is expected to fail for now). Current line is `5.x`; `4.x` is the maintained previous major.

## Architecture

Runtime behavior is split across the `.module` file and `src/`:

- `bluecadet_utilities.module`
  - `hook_theme()` + preprocess for `svg_image` (inlines an SVG file's markup) and `input__simple_format_textfield`.
  - `hook_editor_info_alter()` and `hook_field_widget_form_alter()` enable CKEditor on plain textfields when `use_textfield_wysiwyg` is on.
  - `hook_form_alter()` attaches the `img-gen` library to the two image style generator forms, matching on their form IDs (`bcu_img_style_generator`, `bcu_image_style_generator_settings`). If you rename a form ID, update this too.
  - `hook_form_node_type_form_alter()` + submit handler add a "Title field description" setting to the node type form.
  - `hook_update_status_alter()` wires in `bluecadet/bc_drupal_package_manager`'s `Checker`. The module list is hardcoded here (`bluecadet_utilities`, `bluecadet_file_struct`).
- `src/Form/` (admin forms, routes in `bluecadet_utilities.routing.yml`, all require `administer site configuration`):
  - `BCUSettings` (`/admin/config/system/bluecadet-utilities`): the `use_transliteration` (deprecated) and `use_textfield_wysiwyg` settings.
  - `ImageStyleGenerator` / `ImageStyleGenSettings` (`/admin/config/media/bc-image-style-gen`): generates image styles from aspect ratios and sizes stored in State under `ImageStyleGenSettings::STATE_KEY`.
  - `TextFieldSearch` (`/admin/reports/textfield-search`) and `EntityReferenceFieldSearch` (`/admin/reports/entreffield-search`): Batch API searches across fields (text strings including HTML; entity reference IDs). The entity reference search writes a JSON export under `public://data_search_exports/`. Their form IDs contain a `+`.
- `src/Element/SimpleFormatTextfield.php`, `src/Plugin/Field/FieldWidget/SimpleFormatWidget.php`, `src/Plugin/Field/FieldFormatter/SimpleFormatFormatter.php`: a one-line textfield with bold/italic/underline controls. Widget and formatter share `TextFormatsTrait` for the list of text formats.
- `src/TextFormatsTrait.php`: replacement for the deprecated `filter_formats()`. Uses `FilterFormatRepositoryInterface` when the service exists (11.4+), otherwise loads enabled `filter_format` entities. Use it instead of `filter_formats()`.
- `src/DrupalStateTrait.php`: lazy `drupalState()` accessor.
- `src/SanitizeName.php` (service `bluecadet_utilities.sanitize_name`): deprecated, see below.
- Submodules, each under `modules/<name>/` (never at the repo root, or the shared CI skips their tests):
  - `bc_display_title`: a display-title formatter, a route subscriber that overrides the node title callback, and a `title-with-override` token.
  - `bc_sandbox`: a placeholder admin page at `/admin/config/bc/sandbox`.
- Compiled assets live in `assets/dist/` and are **committed**. Edit sources in `assets/src/` and run `npm run build`, then commit the dist output with your change.

## Deprecated (5.1.0, removal in 6.0.0)

File name transliteration: `bluecadet_utilities_file_validate()`, `bluecadet_utilities_transliterate_filenames_transliteration()`, `SanitizeName`, and the `use_transliteration` setting. It only ran through `hook_file_validate()`, which Drupal 11 removed. Do not extend it; do not add new callers.

## PHP testing and standards

Run these from the Drupal root of an install that has this module at `modules/bluecadet/bluecadet_utilities`, not from this repo's root.

```bash
# PHPUnit (unit, kernel, functional per phpunit.xml)
vendor/bin/phpunit --bootstrap core/tests/bootstrap.php \
  -c modules/bluecadet/bluecadet_utilities/phpunit.xml \
  modules/bluecadet/bluecadet_utilities

# Coding standards
vendor/bin/phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme,css,info,txt \
  modules/bluecadet/bluecadet_utilities
vendor/bin/phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme,css,info,txt \
  modules/bluecadet/bluecadet_utilities

# PHPStan (level 2, Drupal deprecation checks)
vendor/bin/phpstan analyse --configuration modules/bluecadet/bluecadet_utilities/phpstan.neon.dist \
  modules/bluecadet/bluecadet_utilities
```

Conventions:

- Every `@deprecated` tag needs a `@see` tag with a drupal.org URL right after it, or PHPCS fails.
- Fix PHPStan deprecation findings in code. Don't add PHPStan ignores for them.
- Tests for deprecated code carry the same `@deprecated` note, so PHPStan doesn't flag them.
- Tests live in `tests/src/{Unit,Kernel,Functional}` and in each submodule's `tests/src/`.

## CI

`.github/workflows/drupal-tests-and-standards.yml` calls the shared `bluecadet/web-gh-actions` workflow (pinned by tag), which clones Drupal core, symlinks this module in, then runs PHPCS, PHPStan and PHPUnit. The matrix lives in `.github/drupal-ci.yml`:

- Pull requests: the latest release of each supported major (10.6.x, 11.4.x).
- Push to `5.x`, monthly schedule, and manual dispatch: every supported minor plus the next in development (10.6.x, 11.3.x, 11.4.x, 11.5.x, 12.0.x). The 12.0.x cell is expected to be red until Drupal 12 support lands.
- When Drupal ships or retires a minor, update `drupal-ci.yml` and the "Tested Drupal versions" section of `README.md`.
- On Drupal 10.5.x or 11.2.x (no longer tested), Twig 3.30 breaks rendering; pin `twig/twig` to `<3.30` there.

## Branching and versioning

- Only version-number branches (`5.x`, `4.x`) and `feature/*` working branches. No `main`/`master`. Retired lines are `archive/*`.
- The version lives in `bluecadet_utilities.info.yml`, both submodule `.info.yml` files, `package.json` and `package-lock.json`. Don't edit it by hand.
- To release, from a clean tree on `5.x` (after the README changelog entry and the `extra.bluecadet-package-manager` `recommended` entry in `composer.json` are committed):

  ```bash
  ./node_modules/.bin/set-version -v 5.1.0 -c
  ```

  `-c` runs `git add .`, commits `chore(release): bump version to X` and creates a lightweight tag. Run the binary directly: `npx set-version -v ...` can misparse `-v`. Then push the branch and the tag.
- The package manager's `Checker` reads `extra.bluecadet-package-manager` from the highest-versioned tag, so that block must be up to date before tagging.
- Commit messages follow Conventional Commits.

## Keeping this file current

When you change the architecture above (a new hook, form, route, plugin or submodule), the CI matrix, or the release process, update this file in the same change.
