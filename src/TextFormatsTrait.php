<?php

namespace Drupal\bluecadet_utilities;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Trait to get the site's enabled text formats on any supported core.
 *
 * Replaces the deprecated filter_formats(), which was deprecated in
 * drupal:11.4.0 and is removed from drupal:13.0.0. The
 * FilterFormatRepositoryInterface service only exists on Drupal 11.4+, so older
 * cores fall back to loading the same entities core does.
 */
trait TextFormatsTrait {

  /**
   * Gets all enabled text formats, ordered by weight.
   *
   * @return \Drupal\filter\FilterFormatInterface[]
   *   Text format entities keyed by format ID.
   */
  protected function getTextFormats(): array {
    $repository = 'Drupal\filter\FilterFormatRepositoryInterface';
    if (\Drupal::hasService($repository)) {
      return \Drupal::service($repository)->getAllFormats();
    }

    $formats = \Drupal::entityTypeManager()
      ->getStorage('filter_format')
      ->loadByProperties(['status' => TRUE]);
    uasort($formats, ConfigEntityBase::class . '::sort');

    return $formats;
  }

}
