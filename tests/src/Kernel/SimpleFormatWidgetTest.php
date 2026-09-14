<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\bluecadet_utilities\Plugin\Field\FieldWidget\SimpleFormatWidget;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Plugin\Field\FieldWidget\SimpleFormatWidget
 * @group bluecadet_utilities
 */
class SimpleFormatWidgetTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'filter',
    'entity_test',
    'bluecadet_utilities',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installConfig(['field']);

    FilterFormat::create([
      'format' => 'basic_html',
      'name' => 'Basic HTML',
    ])->save();
  }

  /**
   * Builds the widget plugin instance for a field of the given type.
   */
  protected function getWidget(string $field_name, string $field_type, array $settings) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'type' => $field_type,
    ])->save();

    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();

    $entity = EntityTest::create([
      'name' => 'Test entity',
      $field_name => ['value' => 'Existing value'],
    ]);
    $entity->save();

    $widget = \Drupal::service('plugin.manager.field.widget')->createInstance('text_simple_formatter', [
      'field_definition' => $entity->getFieldDefinition($field_name),
      'settings' => $settings + SimpleFormatWidget::defaultSettings(),
      'third_party_settings' => [],
    ]);

    return [$widget, $entity];
  }

  /**
   * @covers ::defaultSettings
   */
  public function testDefaultSettings() {
    $defaults = SimpleFormatWidget::defaultSettings();
    $this->assertTrue($defaults['bold']);
    $this->assertTrue($defaults['italic']);
    $this->assertTrue($defaults['underline']);
    $this->assertTrue($defaults['remove_formatting']);
    $this->assertTrue($defaults['toggle']);
    $this->assertSame('', $defaults['default_format']);
  }

  /**
   * @covers ::formElement
   */
  public function testFormElementOnStringFieldAppliesButtonSettings() {
    [$widget, $entity] = $this->getWidget('field_simple_string', 'string', [
      'bold' => TRUE,
      'italic' => FALSE,
      'underline' => TRUE,
      'remove_formatting' => FALSE,
      'toggle' => FALSE,
    ]);

    $form_state = new FormState();
    $form = [];
    $element = $widget->formElement($entity->get('field_simple_string'), 0, [], $form, $form_state);

    $this->assertSame('simple_format_textfield', $element['value']['#type']);
    $this->assertTrue($element['value']['#buttons']['bold']);
    $this->assertFalse($element['value']['#buttons']['italic']);
    $this->assertTrue($element['value']['#buttons']['underline']);
    $this->assertArrayNotHasKey('format', $element);
  }

  /**
   * @covers ::formElement
   */
  public function testFormElementOnTextFieldForcesHiddenFormat() {
    [$widget, $entity] = $this->getWidget('field_simple_text', 'text', [
      'default_format' => 'basic_html',
    ]);

    $form_state = new FormState();
    $form = [];
    $element = $widget->formElement($entity->get('field_simple_text'), 0, [], $form, $form_state);

    $this->assertSame('hidden', $element['format']['#type']);
    $this->assertSame('basic_html', $element['format']['#value']);
  }

  /**
   * @covers ::settingsForm
   */
  public function testSettingsFormOnTextFieldIncludesDefaultFormatSelect() {
    [$widget] = $this->getWidget('field_simple_settings_text', 'text', []);

    $form_state = new FormState();
    $element = $widget->settingsForm([], $form_state);

    $this->assertSame('checkbox', $element['bold']['#type']);
    $this->assertSame('select', $element['default_format']['#type']);
    $this->assertArrayHasKey('basic_html', $element['default_format']['#options']);
  }

  /**
   * @covers ::settingsForm
   */
  public function testSettingsFormOnStringFieldOmitsDefaultFormatSelect() {
    [$widget] = $this->getWidget('field_simple_settings_string', 'string', []);

    $form_state = new FormState();
    $element = $widget->settingsForm([], $form_state);

    $this->assertArrayNotHasKey('default_format', $element);
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryListsEnabledButtons() {
    [$widget] = $this->getWidget('field_simple_summary', 'string', [
      'bold' => TRUE,
      'italic' => TRUE,
      'underline' => FALSE,
      'remove_formatting' => FALSE,
      'toggle' => FALSE,
      'default_format' => 'basic_html',
    ]);

    $summary = array_map('strval', $widget->settingsSummary());

    $this->assertContains('Bold button', $summary);
    $this->assertContains('Italic button', $summary);
    $this->assertNotContains('Underline button', $summary);
  }

}
