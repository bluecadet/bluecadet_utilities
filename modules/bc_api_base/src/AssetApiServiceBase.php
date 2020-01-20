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
   * Get basic information from a file.
   */
  public function getFileData($file) {
    $data = [
      'uri' => $file->getFileUri(),
      'url' => $file->url(),
      'relative_path' => $this->getRelativePath($file->url()),
    ];

    return $data;
  }

  /**
   * Get relative path of an image file.
   */
  public function getRelativePath(string $path) {
    $replaced_relative_path = '';
    if ($path !== '') {
      $relative_path = urldecode(file_url_transform_relative($path));
      // TODO: replace with actual public files path.
      $replaced_relative_path = str_replace('/sites/default/files/', '', $relative_path);
    }
    return $replaced_relative_path;
  }

}
