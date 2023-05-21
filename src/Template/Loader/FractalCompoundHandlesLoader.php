<?php

namespace Drupal\bluecadet_utilities\Template\Loader;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
use Twig\Source;

/**
 * Finds proper twig tamples.
 */
class FractalCompoundHandlesLoader extends TwigFilesystemLoader {

  const TWIG_EXTENSION = '.twig';
  const VARIANT_DELIMITER = '--';

  /**
   * The Theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Constructs a new ComponentsLoader object.
   *
   * @param string|array $paths
   *   A path or an array of paths to check for templates.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   */
  public function __construct($paths, ModuleHandlerInterface $module_handler, ThemeManagerInterface $themeManager = NULL, array $twig_config = []) {
    $this->themeManager = $themeManager;
    parent::__construct($paths);
  }

  /**
   * Just return the default namespace with the name.
   *
   * @param string $name
   *   The name to parse.
   * @param string $default
   *   The default namespace.
   *
   * @return array
   *   The array.
   */
  protected function parseName($name, $default = self::MAIN_NAMESPACE) {
    return [$default, $name];
  }

  /**
   * Change the # handle to the template name.
   *
   * @param string $name
   *   The given name.
   *
   * @return bool|string
   *   Returns false or the template name.
   */
  public function getCacheKey(string $name): string {
    return parent::getCacheKey($this->convertToTwigPath($name));
  }

  /**
   * Run exists with the correct template path.
   *
   * @param string $name
   *   The template path name.
   *
   * @return bool
   *   Whether it exists or not.
   */
  public function exists($name) {
    return parent::exists($this->convertToTwigPath($name));
  }

  /**
   * Run isFresh with the correct template path.
   *
   * @param string $name
   *   The name of the template to check.
   * @param int $time
   *   The datetime int to check against.
   *
   * @return bool
   *   Whether or not the given template is Fresh.
   */
  public function isFresh(string $name, int $time): bool {
    return parent::isFresh($this->convertToTwigPath($name), $time);
  }

  /**
   * Run getSourceContext with the correct template path.
   *
   * @param string $name
   *   The given name.
   *
   * @return \Twig_Source
   *   The Twig Source.
   */
  public function getSourceContext(string $name): Source {
    return parent::getSourceContext($this->convertToTwigPath($name));
  }

  /**
   * Find the current namespace from the given handle.
   *
   * @param string $handle
   *   The provided handle.
   * @param array $namespaces
   *   The provided array of namespaces to check.
   *
   * @return string
   *   The namespace.
   */
  private function findCurrentNamespace($handle, $namespaces) {
    foreach ($namespaces as $namespace) {
      if (stripos($handle, $namespace) === 1) {
        return $namespace;
      }
    }

    return '';
  }

  /**
   * Convert a fractal Handle '#componentName' to a twig template path.
   *
   * @param string $handle
   *   Fractal handle, like '#componentName'.
   *
   * @return string
   *   Twig template path.
   */
  private function convertToTwigPath($handle):string {
    $activeTheme = $this->themeManager->getActiveTheme();
    $infoYml = $activeTheme->getExtension()->info;

    // We only want handles without file extension.
    if (substr($handle, -1 * strlen($handle)) === self::TWIG_EXTENSION) {
      return $handle;
    }

    if (empty($infoYml['component-libraries']) && empty($infoYml['components'])) {
      return $handle;
    }

    if (!empty($infoYml['component-libraries'])) {
      $libs = $infoYml['component-libraries'];
      $namespace = $this->findCurrentNamespace($handle, array_keys($libs));

      // Check for correct parsing and namespace.
      if (empty($libs[$namespace]['paths'])) {
        return $handle;
      }

      $filename = $componentName = substr($handle, strlen($namespace) + 1);
      $subpaths = explode("/", $componentName);

      if (count($subpaths) > 1) {
        $filename = array_pop($subpaths);
      }

      $path = [
        $activeTheme->getPath(),
        reset($libs[$namespace]['paths']),
      ];
    }

    if (!empty($infoYml['components'])) {
      $libs = $infoYml['components'];
      $namespace = $this->findCurrentNamespace($handle, array_keys($libs['namespaces']));

      // Check for correct parsing and namespace.
      if (empty($libs['namespaces'][$namespace])) {
        return $handle;
      }

      $filename = $componentName = substr($handle, strlen($namespace) + 1);
      $subpaths = explode("/", $componentName);

      if (count($subpaths) > 1) {
        $filename = array_pop($subpaths);
      }

      $path = [
        $activeTheme->getPath(),
        reset($libs['namespaces'][$namespace]),
      ];
    }

    if (strpos($filename, self::VARIANT_DELIMITER) === FALSE) {
      $path[] = $componentName;
    }
    else {
      $path = array_merge($path, $subpaths);
      $variantParts = explode(self::VARIANT_DELIMITER, $filename);
      $path[] = $variantParts[0];
    }

    $path[] = $filename . self::TWIG_EXTENSION;
    $path = array_filter($path);

    return implode("/", $path);
  }

}
