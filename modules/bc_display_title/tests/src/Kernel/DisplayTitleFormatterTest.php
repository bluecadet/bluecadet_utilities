<?php

namespace Drupal\Tests\bc_display_title\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * @coversDefaultClass \Drupal\bc_display_title\Plugin\Field\FieldFormatter\DisplayTitleFormatter
 * @group bc_display_title
 */
class DisplayTitleFormatterTest extends KernelTestBase {

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
    'bc_display_title',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installConfig(['field']);

    FieldStorageConfig::create([
      'field_name' => 'field_display_title',
      'entity_type' => 'entity_test',
      'type' => 'text_long',
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_display_title',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();
  }

  /**
   * Builds the formatter plugin instance for the entity's field.
   */
  protected function getFormatter($entity) {
    return \Drupal::service('plugin.manager.field.formatter')->createInstance('display_title_formatter', [
      'field_definition' => $entity->getFieldDefinition('field_display_title'),
      'settings' => [],
      'label' => 'above',
      'view_mode' => 'default',
      'third_party_settings' => [],
    ]);
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElementsWithPlainTextFormatStripsParagraphTags() {
    $entity = EntityTest::create([
      'name' => 'Test entity',
      'field_display_title' => [
        'value' => '<p>Hello <strong>World</strong></p>',
        'format' => 'plain_text',
      ],
    ]);
    $entity->save();

    $formatter = $this->getFormatter($entity);
    $build = $formatter->viewElements($entity->get('field_display_title'), 'en');

    $this->assertSame('Hello <strong>World</strong>', $build[0]['#markup']);
  }

  /**
   * @covers ::viewElements
   */
  public function testViewElementsWithOtherFormatUsesProcessedText() {
    $entity = EntityTest::create([
      'name' => 'Test entity',
      'field_display_title' => [
        'value' => '<p>Hello <strong>World</strong></p>',
        'format' => 'basic_html',
      ],
    ]);
    $entity->save();

    $formatter = $this->getFormatter($entity);
    $build = $formatter->viewElements($entity->get('field_display_title'), 'en');

    $this->assertSame('processed_text', $build[0]['#type']);
    $this->assertSame('Hello <strong>World</strong>', $build[0]['#text']);
    $this->assertSame('basic_html', $build[0]['#format']);
  }

}
