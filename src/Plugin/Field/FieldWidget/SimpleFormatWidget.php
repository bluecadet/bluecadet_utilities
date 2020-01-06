<?php

namespace Drupal\bluecadet_utilities\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;

/**
 * Plugin implementation of the 'text_simple_formatter' widget.
 *
 * @FieldWidget(
 *   id = "text_simple_formatter",
 *   label = @Translation("Text field Simple Formatter"),
 *   field_types = {
 *     "text"
 *   },
 * )
 */
class SimpleFormatWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'bold' => TRUE,
      'italics' => TRUE,
      'underline' => TRUE,
      'remove_formatting' => TRUE,
      'toggle' => TRUE,
      'format' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['bold'] = [
      '#type' => 'checkbox',
      '#title' => t('Bold button'),
      '#default_value' => $this->getSetting('bold'),
    ];
    $element['italics'] = [
      '#type' => 'checkbox',
      '#title' => t('Italics button'),
      '#default_value' => $this->getSetting('italics'),
    ];
    $element['underline'] = [
      '#type' => 'checkbox',
      '#title' => t('Underline button'),
      '#default_value' => $this->getSetting('underline'),
    ];
    $element['remove_formatting'] = [
      '#type' => 'checkbox',
      '#title' => t('Remove Formatting button'),
      '#default_value' => $this->getSetting('remove_formatting'),
    ];
    $element['toggle'] = [
      '#type' => 'checkbox',
      '#title' => t('Toggle Source Code button'),
      '#default_value' => $this->getSetting('toggle'),
    ];

    // Text Format options.
    $formats = filter_formats();
    $format_options = [];

    foreach ($formats as $f_id => $f) {
      $format_options[$f_id] = $f->label();
    }

    $element['format'] = [
      '#type' => 'select',
      '#title' => t('Text Format to use for this field.'),
      '#default_value' => $this->getSetting('format'),
      '#options' => $format_options,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('bold')) {
      $summary[] = t('Bold button');
    }
    if ($this->getSetting('italics')) {
      $summary[] = t('Italics button');
    }
    if ($this->getSetting('underline')) {
      $summary[] = t('Underline button');
    }
    if ($this->getSetting('remove_formatting')) {
      $summary[] = t('Remove Formatting button');
    }
    if ($this->getSetting('toggle')) {
      $summary[] = t('Toggle Source Code button');
    }
    if ($this->getSetting('format')) {
      $sum = 'Format: ' . $this->getSetting('format');
      $summary[] = t($sum);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $main_widget = parent::formElement($items, $delta, $element, $form, $form_state);
    $main_widget['value']['#buttons']['bold'] = $this->getSetting('bold');
    $main_widget['value']['#buttons']['italics'] = $this->getSetting('italics');
    $main_widget['value']['#buttons']['underline'] = $this->getSetting('underline');
    $main_widget['value']['#buttons']['remove_formatting'] = $this->getSetting('remove_formatting');
    $main_widget['value']['#buttons']['toggle'] = $this->getSetting('toggle');
    $main_widget['value']['#type'] = 'simple_format_textfield';

    $main_widget['format']['#type'] = 'hidden';
    $main_widget['format']['#value'] = $this->getSetting('format');

    return $main_widget;
  }

}
