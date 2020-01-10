<?php

namespace Drupal\bc_api_base;

/**
 * Provide methods to expose image based data for an API.
 */
class AssetApiService {

  /**
   * Connection to the file service.
   *
   * @var \Drupal\bc_api_base\FileApiService
   */
  public $file;

  /**
   * Connection to the image service.
   *
   * @var \Drupal\bc_api_base\ImageApiService
   */
  public $image;

  /**
   * Connection to the audio service.
   *
   * @var \Drupal\bc_api_base\AudioApiService
   */
  public $audio;

  /**
   * Connection to the video service.
   *
   * @var \Drupal\bc_api_base\VideoApiService
   */
  public $video;

  /**
   * Class constructor.
   */
  public function __construct(FileApiService $fileService, ImageApiService $imageService, AudioApiService $audioService, VideoApiService $videoService) {
    $this->file = $fileService;
    $this->image = $imageService;
    $this->audio = $audioService;
    $this->video = $videoService;
  }

}
