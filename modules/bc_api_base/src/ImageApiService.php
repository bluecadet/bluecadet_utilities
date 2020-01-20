<?php

namespace Drupal\bc_api_base;

use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\crop\Entity\Crop;

/**
 * Provide methods to expose image based data for an API.
 */
class ImageApiService extends AssetApiServiceBase {

  /**
   * Image Factory.
   *
   * @var Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Config Factory.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Image Factory.
   *
   * @var Drupal\focal_point\FocalPointManager|null
   */
  protected $focalPointManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ImageFactory $image_factory, ConfigFactoryInterface $config_factory) {
    parent::__construct();

    $this->imageFactory = $image_factory;
    $this->configFactory = $config_factory;

    // Load the focal point manager if it exists.
    // phpcs:ignore
    $this->focalPointManager = (\Drupal::hasService('focal_point.manager')) ? \Drupal::service('focal_point.manager') : NULL;
  }

  /**
   * Get ALL data for an image.
   */
  public function getImageData($file, $image_styles = []) {
    // $this->imageFactory->get($file->getFileUri());
    $image_file = $this->imageFactory->get($file->getFileUri());

    if (is_null($image_file)) {
      $data = NULL;
    }
    else {
      $crop_type = $this->configFactory->get('focal_point.settings')->get('crop_type');
      $crop = Crop::findCrop($file->getFileUri(), $crop_type);
      if ($crop) {
        $anchor = $this->focalPointManager->absoluteToRelative($crop->x->value, $crop->y->value, $image_file->getWidth(), $image_file->getHeight());
      }
      $data = [
        'uri' => $file->getFileUri(),
        'url' => $file->url(),
        'relative_path' => $this->getRelativePath($file->url()),
        'orig_size' => [
          'width' => $image_file->getWidth(),
          'height' => $image_file->getHeight(),
        ],
        'crop' => ($anchor) ?: [],
      ];

      foreach ($image_styles as $style_name) {
        $style = ImageStyle::load($style_name);
        $data[$style_name] = $style->buildUrl($file->getFileUri());
      }
    }

    return $data;
  }

}
