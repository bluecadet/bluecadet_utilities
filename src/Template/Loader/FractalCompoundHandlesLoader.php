<?php

namespace Drupal\bluecadet_utilities\Template\Loader;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Twig\Loader\FilesystemLoader as TwigFilesystemLoader;
use Twig\Source;

class FractalCompoundHandlesLoader extends TwigFilesystemLoader {

  const TWIG_EXTENSION = '.twig';
  const VARIANT_DELIMITER = '--';

  /**
   * @var ThemeManagerInterface
   */
  protected $theme_manager;

  /**
   * Constructs a new ComponentsLoader object.
   *
   * @param string|array $paths
   *   A path or an array of paths to check for templates.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   */
  public function __construct($paths, ModuleHandlerInterface $module_handler, ThemeManagerInterface $themeManager = NULL, array $twig_config = []) {
    $this->theme_manager = $themeManager;
    parent::__construct($paths);
  }

  /**
   * Just return the default namespace with the name.
   *
   * @param $name
   * @param string $default
   *
   * @return array
   */
  protected function parseName($name, $default = self::MAIN_NAMESPACE) {
    return [$default, $name];
  }

  /**
   * Change the # handle to the template name.
   *
   * @param string $name
   *
   * @return bool|string
   */
  public function getCacheKey(string $name): string {
    return parent::getCacheKey($this->convertToTwigPath($name));
  }

  /**
   * Run exists with the correct template path.
   *
   * @param string $name
   *
   * @return bool
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
   */
  public function isFresh(string $name, int $time): bool {
    return parent::isFresh($this->convertToTwigPath($name), $time);
  }

  /**
   *
   * Run getSourceContext with the correct template path.
   *
   * @param string $name
   *
   * @return \Twig_Source
   */
  public function getSourceContext(string $name): Source {
    return parent::getSourceContext($this->convertToTwigPath($name));
  }

  /**
   * @param string $handle
   * @param array $namespaces
   *
   * @return string
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
   *
   * Convert a fractal Handle '#componentName' to a twig template path.
   *
   * @param $handle
   *
   * @return string
   */
  private function convertToTwigPath($handle):string {
    $activeTheme = $this->theme_manager->getActiveTheme();
    $infoYml = $activeTheme->getExtension()->info;

    // we only want handles without file extension
    if (substr($handle, -1 * strlen($handle)) === self::TWIG_EXTENSION) {
      return $handle;
    }

    if (empty($infoYml['component-libraries']) && empty($infoYml['components'])) {
      return $handle;
    }

    if (!empty($infoYml['component-libraries'])) {
      $libs = $infoYml['component-libraries'];
      $namespace = $this->findCurrentNamespace($handle, array_keys($libs));

      // check for correct parsing and namespace
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

      // check for correct parsing and namespace
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
