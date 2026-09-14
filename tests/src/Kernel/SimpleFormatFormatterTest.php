<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Plugin\Field\FieldFormatter\SimpleFormatFormatter
 * @group bluecadet_utilities
 */
class SimpleFormatFormatterTest extends KernelTestBase {

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

    FieldStorageConfig::create([
      'field_name' => 'field_simple_text',
      'entity_type' => 'entity_test',
      'type' => 'text',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_simple_text',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();

    FilterFormat::create([
      'format' => 'basic_html',
      'name' => 'Basic HTML',
    ])->save();
  }

  /**
   * Builds the formatter plugin instance for the entity's field.
   */
  protected function getFormatter(EntityTest $entity, array $settings) {
    return \Drupal::service('plugin.manager.field.formatter')->createInstance('text_simple_format_formatter', [
      'field_definition' => $entity->getFieldDefinition('field_simple_text'),
      'settings' => $settings,
      'label' => 'above',
      'view_mode' => 'default',
      'third_party_settings' => [],
    ]);
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElementsWithNoFormatStripsDisallowedTags() {
    $entity = EntityTest::create([
      'name' => 'Test entity',
      'field_simple_text' => [
        'value' => '<b>Bold</b><script>alert(1)</script>',
      ],
    ]);
    $entity->save();

    $formatter = $this->getFormatter($entity, ['format' => NULL]);
    $build = $formatter->viewElements($entity->get('field_simple_text'), 'en');

    $this->assertSame('<b>Bold</b>alert(1)', (string) $build[0]['#markup']);
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElementsWithFormatUsesProcessedText() {
    $entity = EntityTest::create([
      'name' => 'Test entity',
      'field_simple_text' => [
        'value' => '<p>Hello</p>',
      ],
    ]);
    $entity->save();

    $formatter = $this->getFormatter($entity, ['format' => 'basic_html']);
    $build = $formatter->viewElements($entity->get('field_simple_text'), 'en');

    $this->assertSame('processed_text', $build[0]['#type']);
    $this->assertSame('<p>Hello</p>', $build[0]['#text']);
    $this->assertSame('basic_html', $build[0]['#format']);
    $this->assertSame('en', $build[0]['#langcode']);
  }

  /**
   * @covers ::settingsForm
   */
  public function testSettingsFormListsAvailableFormats() {
    $entity = EntityTest::create(['name' => 'Test entity']);
    $entity->save();

    $formatter = $this->getFormatter($entity, ['format' => 'basic_html']);
    $element = $formatter->settingsForm([], new FormState());

    $this->assertSame('select', $element['format']['#type']);
    $this->assertSame('basic_html', $element['format']['#default_value']);
    $this->assertArrayHasKey('basic_html', $element['format']['#options']);
  }

}
