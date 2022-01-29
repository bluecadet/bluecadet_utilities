<?php

namespace Drupal\bluecadet_utilities\Element;

use Drupal\Core\Render\Element\Textfield;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a one-line text field form element with simple WYSIWYG controls.
 *
 * @FormElement("simple_format_textfield")
 */
class SimpleFormatTextfield extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#maxlength' => 128,
      '#buttons' => [
        'bold' => TRUE,
        'italic' => TRUE,
        'underline' => TRUE,
        'remove_formatting' => FALSE,
        'toggle' => FALSE,
      ],
      '#autocomplete_route_name' => FALSE,
      '#process' => [
        [$class, 'processAutocomplete'],
        [$class, 'processAjaxForm'],
        [$class, 'processPattern'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderTextfield'],
        [$class, 'preRenderGroup'],
      ],
      '#theme' => 'input__simple_format_textfield',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE && $input !== NULL) {
      // This should be a string, but allow other scalars since they might be
      // valid input in programmatic form submissions.
      if (!is_scalar($input)) {
        $input = '';
      }

      $input = trim($input);
      $input = str_replace(["\r", "\n"], '', $input);
      $input = strip_tags($input, "<i><b><em><strong><u><br>");
      $input = preg_replace('/<br>$/', '', $input);
      $input = preg_replace('/<br\/>$/', '', $input);

      return $input;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderTextfield($element) {
    // Set Defaults...
    $element['#buttons'] += [
      'bold' => FALSE,
      'italic' => FALSE,
      'underline' => FALSE,
      'remove_formatting' => FALSE,
      'toggle' => FALSE,
    ];

    $element = parent::preRenderTextfield($element);
    $element['#theme'] = 'input__simple_format_textfield';

    // Add buttons.
    $element['#buttons_render'] = [];
    // Bold.
    if ($element['#buttons']['bold']) {
      $element['#buttons_render'][] = [
        'bold' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => '<b>B</b>',
          '#attributes' => [
            'class' => ['simple-editor-button', 'simple-editor-button--bold'],
          ],
        ],
      ];
    }

    // Italic.
    if ($element['#buttons']['italic']) {
      $element['#buttons_render'][] = [
        'italic' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => '<i>I</i>',
          '#attributes' => [
            'class' => ['simple-editor-button', 'simple-editor-button--italic'],
          ],
        ],
      ];
    }

    // Underline.
    if ($element['#buttons']['underline']) {
      $element['#buttons_render'][] = [
        'underline' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => '<u>U</u>',
          '#attributes' => [
            'class' => ['simple-editor-button', 'simple-editor-button--underline'],
          ],
        ],
      ];
    }

    // Remove Formatting.
    if ($element['#buttons']['remove_formatting']) {
      $element['#buttons_render'][] = [
        'remove_formatting' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => '<i>T<sub>x</sub></i>',
          '#attributes' => [
            'class' => ['simple-editor-button', 'simple-editor-button--remove_formatting'],
          ],
        ],
      ];
    }

    // Toggle Source Code.
    if ($element['#buttons']['toggle']) {
      $element['#buttons_render'][] = [
        'toggle' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => '<b><i><></i></b>',
          '#attributes' => [
            'class' => ['simple-editor-button', 'simple-editor-button--toggle-source'],
          ],
        ],
      ];
    }

    $element['#attached']['library'][] = 'bluecadet_utilities/simple-formatter';

    return $element;
  }

}
