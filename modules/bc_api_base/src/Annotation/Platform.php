<?php

namespace Drupal\bc_api_base\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Class Platform.
 *
 * @package Drupal\bc_api_base\Annotation
 *
 * @Annotation
 */
class Platform extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin label.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The Site Monitor Test Type.
   *
   * Options: none/all/cinder.
   *
   * @var string
   */
  public $striphtml;

  /**
   * Whether or not to decode URLs.
   *
   * @var bool
   */
  public $urldecode;

  /**
   * Whether or not to unescape characters.
   *
   * @var bool
   */
  public $unescapechars;

  /**
   * Whether or not to include full image urls.
   *
   * @var bool
   */
  public $imageurl;

}
