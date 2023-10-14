<?php

namespace Drupal\bluecadet_utilities;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Trait to use Drupal State.
 */
class DisplayOnlyWidgetService {

  /**
   * List of widgets that are covered in this service.
   *
   * @var array
   */
  protected $availableWidgets = [
    'text_textarea',
    'string_textfield',
    'number',
    'boolean_checkbox',
    'datetime_default',
    'entity_reference_autocomplete',
    'entity_reference_autocomplete_tags',
  ];

  /**
   * Implements hook_field_widget_settings_summary_alter().
   */
  function fieldWidgetSettingsSummaryAlter(array &$summary, array $context): void {
    $plugin_id = $context['widget']->getPluginId();
    if (in_array($plugin_id, $this->availableWidgets) && $context['widget']->getThirdPartySetting('bluecadet_utilities', 'display_only')) {
      $summary[] = t('Display Only');
    }
  }

  /**
   * Implements hook_field_widget_third_party_settings_form().
   */
  function fieldWidgetThirdPartySettingsForm(WidgetInterface $plugin, FieldDefinitionInterface $field_definition, $form_mode, array $form, FormStateInterface $form_state): array {
    $plugin_id = $plugin->getPluginId();
    $element = [];

    if (in_array($plugin_id, $this->availableWidgets)) {
      $element['display_only'] = array(
        '#type' => 'checkbox',
        '#title' => t('Display Only'),
        '#description' => t('Dislay the field value in the form only. Do not allow Content Author edit the form element.'),
        '#default_value' => $plugin->getThirdPartySetting('bluecadet_utilities', 'display_only'),
      );
    }

    return $element;
  }

  /**
   * Implements hook_field_widget_single_element_form_alter().
   */
  public function fieldWidgetSingleElementFormAlter(array &$element, FormStateInterface $form_state, array $context): void {
    $plugin_id = $context['widget']->getPluginId();
    $settings = $context['widget']->getThirdPartySettings("bluecadet_utilities");

    // Check that we are in a supported widget, and we have custom settings.
    if (in_array($plugin_id, $this->availableWidgets) && isset($settings['display_only']) && $settings['display_only'] === "1") {

      switch($plugin_id) {
        case 'text_textarea':

          $element['#type'] = 'textarea';
          $element['#attributes']['disabled'] = TRUE;

          break;

        case 'string_textfield':
        case 'number':
        case 'boolean_checkbox':
        case 'datetime_default':

          $element['value']['#attributes']['disabled'] = TRUE;

          break;

        case 'entity_reference_autocomplete':
        case 'entity_reference_autocomplete_tags':
          $element['target_id']['#attributes']['disabled'] = TRUE;
          break;
      }
    }
  }

}
