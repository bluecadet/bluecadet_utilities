<?php

namespace Drupal\bc_api_base;

/**
 * Provide methods to expose image based data for an API.
 */
class AssetApiServiceBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {}

  /**
   * Get relative path of an image file.
   */
  public function getRelativePath(string $path, $platform_flags) {
    $replaced_relative_path = '';
    if ($path !== '') {
      $relative_path = urldecode(file_url_transform_relative($path));
      // TODO: replace with actual public files path.
      $replaced_relative_path = str_replace('/sites/default/files/', '', $relative_path);
    }
    return $replaced_relative_path;
  }

}
