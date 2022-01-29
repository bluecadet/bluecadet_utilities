<?php

namespace Drupal\bluecadet_utilities\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'text_simple_format_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "text_simple_format_formatter",
 *   label = @Translation("Text field Simple Formatter"),
 *   field_types = {
 *     "text",
 *     "string"
 *   }
 * )
 */
class SimpleFormatFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'format' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    // Text Format options.
    $formats = filter_formats();
    $format_options = ['' => '-- Select --'];

    foreach ($formats as $f_id => $f) {
      $format_options[$f_id] = $f->label();
    }

    $element['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Text Format'),
      '#description' => $this->t('Select a format to process this text. Otherwise, all html will be stripped minus bold, italics, and underline.'),
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

    if ($this->getSetting('format')) {
      $summary[] = $this->t('Format: %sum', ['%sum' => $this->getSetting('format')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $format = $this->getSetting('format');

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      if ($format && !is_null($format)) {
        $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => $item->value,
          '#format' => $format,
          '#langcode' => $item->getLangcode(),
        ];
      }
      else {
        $elements[$delta] = [
          '#markup' => strip_tags($item->value, [
            '<b>',
            '<i>',
            '<u>',
            '<em>',
            '<strong>',
          ]),
        ];
      }
    }
    return $elements;
  }

}
