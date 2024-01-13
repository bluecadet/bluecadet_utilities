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
 *     "text",
 *     "string"
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
      'italic' => TRUE,
      'underline' => TRUE,
      'remove_formatting' => TRUE,
      'toggle' => TRUE,
      'default_format' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['bold'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Bold button'),
      '#default_value' => $this->getSetting('bold'),
    ];
    $element['italic'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Italic button'),
      '#default_value' => $this->getSetting('italic'),
    ];
    $element['underline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Underline button'),
      '#default_value' => $this->getSetting('underline'),
    ];
    $element['remove_formatting'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove Formatting button'),
      '#default_value' => $this->getSetting('remove_formatting'),
    ];
    $element['toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Toggle Source Code button'),
      '#default_value' => $this->getSetting('toggle'),
    ];

    if ($this->fieldDefinition->getType() == 'text') {

      // Text Format options.
      $formats = filter_formats();
      $format_options = ['' => '-- Select --'];

      foreach ($formats as $f_id => $f) {
        $format_options[$f_id] = $f->label();
      }

      $element['default_format'] = [
        '#type' => 'select',
        '#title' => $this->t('Default format for Text fields'),
        '#default_value' => $this->getSetting('default_format'),
        '#options' => $format_options,
      ];
    }


    // Text Format options.
    $formats = filter_formats();
    $format_options = ['' => '-- Select --'];

    foreach ($formats as $f_id => $f) {
      $format_options[$f_id] = $f->label();
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('bold')) {
      $summary[] = $this->t('Bold button');
    }
    if ($this->getSetting('italic')) {
      $summary[] = $this->t('Italic button');
    }
    if ($this->getSetting('underline')) {
      $summary[] = $this->t('Underline button');
    }
    if ($this->getSetting('remove_formatting')) {
      $summary[] = $this->t('Remove Formatting button');
    }
    if ($this->getSetting('toggle')) {
      $summary[] = $this->t('Toggle Source Code button');
    }
    if ($this->getSetting('default_format')) {
      $summary[] = $this->t('Defaul Formatter: %sum', ['%sum' => $this->getSetting('default_format')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // We are deailing with the individual form element here.
    $main_widget = parent::formElement($items, $delta, $element, $form, $form_state);

    $main_widget['value']['#buttons']['bold'] = $this->getSetting('bold');
    $main_widget['value']['#buttons']['italic'] = $this->getSetting('italic');
    $main_widget['value']['#buttons']['underline'] = $this->getSetting('underline');
    $main_widget['value']['#buttons']['remove_formatting'] = $this->getSetting('remove_formatting');
    $main_widget['value']['#buttons']['toggle'] = $this->getSetting('toggle');
    $main_widget['value']['#type'] = 'simple_format_textfield';

    // Check if we are dealing with a formatted field.
    if ($this->fieldDefinition->getType() == 'text') {
      // Always force default formatter.
      // todo: should we always force this?
      $main_widget['format'] = [
        '#type' => 'hidden',
        '#value' => $this->getSetting('default_format'),
      ];
    }
    return $main_widget;
  }

}
