<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\bluecadet_utilities\TextFormatsTrait;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests the text formats trait on whichever core the suite runs against.
 *
 * @coversDefaultClass \Drupal\bluecadet_utilities\TextFormatsTrait
 * @group bluecadet_utilities
 */
class TextFormatsTraitTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'filter',
    'bluecadet_utilities',
  ];

  /**
   * Returns an object exposing the trait's protected method.
   */
  protected function getSubject(): object {
    return new class() {

      use TextFormatsTrait {
        getTextFormats as public;
      }

    };
  }

  /**
   * Enabled formats are returned keyed by ID and ordered by weight.
   *
   * @covers ::getTextFormats
   */
  public function testEnabledFormatsOrderedByWeight(): void {
    FilterFormat::create(['format' => 'heavy', 'name' => 'Heavy', 'weight' => 10])->save();
    FilterFormat::create(['format' => 'light', 'name' => 'Light', 'weight' => -10])->save();

    $formats = $this->getSubject()->getTextFormats();

    $this->assertSame(['light', 'heavy'], array_keys($formats));
    $this->assertSame('Light', $formats['light']->label());
  }

  /**
   * Disabled formats are excluded.
   *
   * @covers ::getTextFormats
   */
  public function testDisabledFormatsExcluded(): void {
    FilterFormat::create(['format' => 'on', 'name' => 'On', 'weight' => 0])->save();
    FilterFormat::create(['format' => 'off', 'name' => 'Off', 'weight' => 0, 'status' => FALSE])->save();

    $formats = $this->getSubject()->getTextFormats();

    $this->assertArrayHasKey('on', $formats);
    $this->assertArrayNotHasKey('off', $formats);
  }

}
