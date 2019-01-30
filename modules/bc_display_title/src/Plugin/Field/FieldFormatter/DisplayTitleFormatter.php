<?php

namespace Drupal\bc_display_title\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'display_title_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "display_title_formatter",
 *   label = @Translation("Display Title/Remove P tags"),
 *   field_types = {
 *     "text",
 *     "textarea",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class DisplayTitleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      if ($item->format = 'plain_text_formatter') {
        $elements[$delta] = [
          // Strip p tags.
          '#markup' => preg_replace("/<\\/?p(.|\\s)*?>/", "", $item->value),
        ];
      }
      else {
        $elements[$delta] = [
          '#type' => 'processed_text',
          // Strip p tags.
          '#text' => preg_replace("/<\\/?p(.|\\s)*?>/", "", $item->value),
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
        ];
      }
    }
    return $elements;
  }

}
