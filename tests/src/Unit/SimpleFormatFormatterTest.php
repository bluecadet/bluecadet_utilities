<?php

namespace Drupal\Tests\bluecadet_utilities\Unit;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\bluecadet_utilities\Plugin\Field\FieldFormatter\SimpleFormatFormatter;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Plugin\Field\FieldFormatter\SimpleFormatFormatter
 * @group bluecadet_utilities
 */
class SimpleFormatFormatterTest extends UnitTestCase {

  /**
   * Builds a formatter instance with the given settings.
   */
  protected function buildFormatter(array $settings): SimpleFormatFormatter {
    $field_definition = $this->createMock(FieldDefinitionInterface::class);

    $formatter = new SimpleFormatFormatter(
      'text_simple_format_formatter',
      [],
      $field_definition,
      $settings,
      'above',
      'default',
      []
    );
    $formatter->setStringTranslation($this->getStringTranslationStub());

    return $formatter;
  }

  /**
   * @covers ::defaultSettings
   */
  public function testDefaultSettings() {
    $this->assertNull(SimpleFormatFormatter::defaultSettings()['format']);
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryIncludesFormatWhenSet() {
    $formatter = $this->buildFormatter(['format' => 'basic_html']);
    $summary = $formatter->settingsSummary();

    $this->assertNotEmpty(array_filter($summary, fn($line) => str_contains((string) $line, 'basic_html')));
  }

  /**
   * @covers ::settingsSummary
   */
  public function testSettingsSummaryOmitsFormatWhenNotSet() {
    $formatter = $this->buildFormatter(['format' => NULL]);
    $summary = $formatter->settingsSummary();

    $this->assertEmpty(array_filter($summary, fn($line) => str_contains((string) $line, 'Format:')));
  }

}
