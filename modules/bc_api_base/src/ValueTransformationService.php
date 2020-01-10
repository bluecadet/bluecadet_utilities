<?php

namespace Drupal\bc_api_base;

define('CINDER_ALLOWED_TAGS', '<b><i><br><em><strong>');

/**
 * Provide methods to transform data based on specific platforms.
 */
class ValueTransformationService {

  /**
   * The Plugin manager.
   *
   * @var \Drupal\bc_api_base\Plugin\PlatformManager
   */
  protected $pluginManager;

  /**
   * The Platform Plugin needed for transformations.
   *
   * @var \Drupal\bc_api_base\Plugin\PluginInspectionInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * Set the platform so we have the correct plugin.
   */
  public function setPlatform($platform_id) {
    $this->plugin = $this->pluginManager->createInstance($platform_id);
  }

  /**
   * Check the plugin so we have a plugin.
   */
  public function checkPlatform() {
    if (empty($this->plugin)) {
      $this->plugin = $this->pluginManager->createInstance('default');
    }
  }

  /**
   * Get Value of a text field, properlly transformed.
   */
  public function textFieldVal($entity, string $field) {
    $this->checkPlatform();
    return $this->plugin->textFieldVal($entity, $field);
  }

  /**
   * Get Value of a numeric field, properlly transformed.
   */
  public function numFieldVal($entity, string $field) {
    $this->checkPlatform();
    return $this->plugin->numFieldVal($entity, $field);
  }

  /**
   * Get Value of a bool field, properlly transformed.
   */
  public function boolFieldVal($entity, string $field) {
    $this->checkPlatform();
    return $this->plugin->boolFieldVal($entity, $field);
  }

  /**
   * Get Value of a taxonomy ref field, properlly transformed.
   */
  public function taxFieldVal($entity, string $field) {
    $this->checkPlatform();
    return $this->plugin->taxFieldVal($entity, $field);
  }

  /**
   * Get Value of a field on a taxonomy term, properlly transformed.
   */
  public function taxFieldSubfieldVal($entity, string $field, string $subField) {
    $this->checkPlatform();
    return $this->plugin->taxFieldSubfieldVal($entity, $field);
  }

  /**
   * Get Value of a date field, properlly transformed.
   */
  public function dateFieldVal($entity, string $field) {
    $this->checkPlatform();
    return $this->plugin->dateFieldVal($entity, $field);
  }

  /**
   * Get Value of a created/changed field, properlly transformed.
   */
  public function createdChangedFieldVals($entity) {
    $this->checkPlatform();
    return $this->plugin->createdChangedFieldVals($entity);
  }

  /**
   * Get Value of a serialized text field, properlly transformed.
   */
  public function serializedTextFieldVal($entity, string $field) {
    $this->checkPlatform();
    return $this->plugin->serializedTextFieldVal($entity, $field);
  }

}
