<?php

namespace Drupal\bucknell_utility\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Output paths from given menus so that we can get a quick list of all urls.
 */
class OutputPaths extends ControllerBase {

  /**
   * Menus we want paths from.
   *
   * @var array
   */
  public $menus = [
    'main',
    'footer',
    'helpful-links',
    'utility-navigation',
    'secondary-navigation',
  ];

  /**
   * Array of paths.
   *
   * @var array
   */
  public $paths = [];

  /**
   * Build render array.
   */
  public function build() {
    $build = [];

    $str = "$(URL)";

    foreach ($this->menus as $menu_name) {
      $tree = \Drupal::menuTree()->load($menu_name, new MenuTreeParameters());
      $this->processTree($tree);
    }

    $str .= implode("\r\n$(URL)", $this->paths);

    $build = [
      'field' => [
        '#markup' => '<textarea  rows="20" cols="100">' . $str . '</textarea>',
        '#allowed_tags' => ['textarea'],
      ],
    ];

    return $build;
  }

  /**
   * Process the entire tree.
   */
  protected function processTree($tree) {

    foreach ($tree as $item) {
      if (!$item->link->getUrlObject()->isExternal()) {
        $this->paths[] = $item->link->getUrlObject()->toString();
      }

      if ($item->hasChildren) {
        $this->processTree($item->subtree);
      }
    }
  }

}
