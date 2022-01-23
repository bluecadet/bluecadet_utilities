<?php

namespace Drupal\bluecadet_utilities;

/**
 * Trait to use Drupal State.
 */
trait DrupalStateTrait {

  /**
   * Drupal State obj.
   *
   * @var Drupal\Core\State\State
   */
  protected $drupalState = [];

  /**
   * Get Drupal state service. Handles for static method calls as well.
   */
  protected function drupalState() {
    if (!$this->drupalState) {
      $this->drupalState = \Drupal::service('state');
    }

    return $this->drupalState;
  }

}
