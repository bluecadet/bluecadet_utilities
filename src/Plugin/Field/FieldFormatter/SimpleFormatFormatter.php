<?php

namespace Drupal\bluecadet_utilities\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'text_simple_format_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "text_simple_format_formatter",
 *   label = @Translation("Display String field with simple html."),
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
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
    return $elements;
  }

}
