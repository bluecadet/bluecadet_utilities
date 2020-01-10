<?php

namespace Drupal\bc_api_base\Plugin;

use Drupal\Core\Config\Entity\EntityInterface;

/**
 * Defines an interface for EsmTestRunner plugins.
 */
interface PlatformTransformInterface {

  /**
   * Get Value of a text field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values.
   */
  public function textFieldVal(EntityInterface $entity, string $field);

  /**
   * Get Value of a numeric field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values
   */
  public function numFieldVal(EntityInterface $entity, string $field);

  /**
   * Get Value of a bool field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values
   */
  public function boolFieldVal(EntityInterface $entity, string $field);

  /**
   * Get Value of a taxonomy ref field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values
   */
  public function taxFieldVal(EntityInterface $entity, string $field);

  /**
   * Get Value of a field on a taxonomy term, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   * @param string $subField
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values
   */
  public function taxFieldSubfieldVal(EntityInterface $entity, string $field, string $subField);

  /**
   * Get Value of a date field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values
   */
  public function dateFieldVal(EntityInterface $entity, string $field);

  /**
   * Get Value of a created/changed field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   *
   * @return array|string|null
   *   Array of with created value first, and changed value second.
   */
  public function createdChangedFieldVals(EntityInterface $entity);

  /**
   * Get Value of a serialized text field, properlly transformed.
   *
   * @param \Drupal\Core\Config\Entity\EntityInterface $entity
   *   The Entity.
   * @param string $field
   *   The Field.
   *
   * @return array|string|null
   *   Array of field values
   */
  public function serializedTextFieldVal(EntityInterface $entity, string $field);

  /**
   * Apply string transformations based on the platform.
   *
   * @param string $text
   *   The String.
   *
   * @return string
   *   Transformed string.
   */
  public function applyPlatformTransformations(string $text);

  /**
   * Recursive object/array.
   *
   * @param string|object $obj
   *   The Entity.
   *
   * @return string|object|null
   *   Array of field values
   */
  public function recursiveDataPlatformTransformation($obj);

}
