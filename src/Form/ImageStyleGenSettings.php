<?php

namespace Drupal\bluecadet_utilities\Form;

use Drupal\bluecadet_utilities\DrupalStateTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Bluecadet Utility Settings Form.
 */
class ImageStyleGenSettings extends FormBase {

  use DrupalStateTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bcu_image_style_generator_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->drupalState()->get(BCU_IMG_GEN_STATE, []);

    if (is_null($form_state->get('num_of_sizes'))) {
      $v = isset($settings['sizes']) ? count($settings['sizes']) + 1 : 1;
      $form_state->set('num_of_sizes', $v);
    }

    $num_of_sizes = $form_state->get('num_of_sizes');

    $form['#tree'] = TRUE;
    $form['sizes'] = [
      '#type' => 'details',
      '#title' => $this->t('Sizes'),
      '#open' => TRUE,
      // Set up the wrapper so that AJAX will be able to replace the fieldset.
      '#prefix' => '<div id="sizes-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $num_of_sizes; $i++) {
      $form['sizes'][$i] = [
        '#type' => 'fieldset',
        '#prefix' => '<div class="size-group">',
        '#suffix' => '</div>',
      ];

      $form['sizes'][$i]['label'] = [
        '#type' => 'textfield',
        '#title' => 'Label',
        '#size' => "auto",
        // phpcs:ignore SlevomatCodingStandard.ControlStructures.RequireNullCoalesceOperator.NullCoalesceOperatorNotUsed
        '#default_value' => isset($settings['sizes'][$i]['label']) ? $settings['sizes'][$i]['label'] : "",
      ];

      $form['sizes'][$i]['size'] = [
        '#type' => 'number',
        '#title' => 'Size',
        '#description' => $this->t('Width in px'),
        '#field_suffix' => "px",
        '#attributes' => [
          'class' => ["with-suffix"],
        ],
        // phpcs:ignore SlevomatCodingStandard.ControlStructures.RequireNullCoalesceOperator.NullCoalesceOperatorNotUsed
        '#default_value' => isset($settings['sizes'][$i]['size']) ? $settings['sizes'][$i]['size'] : "",
      ];
    }

    $form['sizes']['add_size'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => [
        [$this, 'ajaxExampleAddMoreAddOne'],
      ],
      // See the examples in ajax_example.module for more details on the
      // properties of #ajax.
      '#ajax' => [
        'callback' => '::ajaxExampleAddMoreCallback',
        'wrapper' => 'sizes-fieldset-wrapper',
      ],
    ];

    // Actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Settings'),
    ];

    return $form;
  }

  // phpcs:disable

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  // phpcs:enable

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->drupalState()->get(BCU_IMG_GEN_STATE, []);
    $values = $form_state->getValues();

    $size_values_to_save = [];
    foreach ($values['sizes'] as $key => $item) {
      if ($key !== "add_size" && !empty($item['label']) && !empty($item['size'])) {
        $size_values_to_save[] = $item;
      }
    }

    $settings['sizes'] = $size_values_to_save;
    $this->drupalState()->set(BCU_IMG_GEN_STATE, $settings);

    $this->messenger()->addMessage('You have saved your settings.');
  }

  /**
   * Add more.
   */
  public function ajaxExampleAddMoreAddOne(array &$form, FormStateInterface $form_state) {
    $num_of_sizes = $form_state->get('num_of_sizes');

    $num_of_sizes++;
    $form_state->set('num_of_sizes', $num_of_sizes);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Add more callback.
   */
  public function ajaxExampleAddMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['sizes'];
  }

  /**
   * Remove one.
   */
  public function ajaxExampleAddMoreRemoveOne(array &$form, FormStateInterface $form_state) {
    $num_of_sizes = $form_state->get('num_of_sizes');
    if ($num_of_sizes > 1) {
      $num_of_sizes--;
      $form_state->set('num_of_sizes', $num_of_sizes);
    }
    $form_state->setRebuild(TRUE);
  }

}
