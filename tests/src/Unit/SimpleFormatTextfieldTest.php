<?php

namespace Drupal\Tests\bluecadet_utilities\Unit;

use Drupal\Core\Form\FormState;
use Drupal\Tests\UnitTestCase;
use Drupal\bluecadet_utilities\Element\SimpleFormatTextfield;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Element\SimpleFormatTextfield
 * @group bluecadet_utilities
 */
class SimpleFormatTextfieldTest extends UnitTestCase {

  /**
   * Tests value callback sanitization.
   *
   * @param mixed $input
   *   The raw input value.
   * @param mixed $expected
   *   The expected processed value.
   *
   * @dataProvider providerValueCallback
   * @covers ::valueCallback
   */
  public function testValueCallback($input, $expected) {
    $element = [];
    $form_state = new FormState();
    $this->assertSame($expected, SimpleFormatTextfield::valueCallback($element, $input, $form_state));
  }

  /**
   * Provides data for self::testValueCallback().
   */
  public static function providerValueCallback() {
    return [
      'null input returns null' => [NULL, NULL],
      'false input returns null' => [FALSE, NULL],
      'non-scalar input is discarded' => [['array'], ''],
      'trims whitespace' => ['  hello  ', 'hello'],
      'strips newlines and carriage returns' => ["hello\r\nworld", 'helloworld'],
      'allows whitelisted tags' => ['<b>bold</b> <script>alert(1)</script>', '<b>bold</b> alert(1)'],
      'strips trailing br tag' => ['hello<br>', 'hello'],
      'strips trailing self-closing br tag' => ['hello<br/>', 'hello'],
    ];
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $element = new SimpleFormatTextfield([], 'simple_format_textfield', []);
    $info = $element->getInfo();

    $this->assertTrue($info['#input']);
    $this->assertTrue($info['#buttons']['bold']);
    $this->assertTrue($info['#buttons']['italic']);
    $this->assertTrue($info['#buttons']['underline']);
    $this->assertFalse($info['#buttons']['remove_formatting']);
    $this->assertFalse($info['#buttons']['toggle']);
    $this->assertSame('input__simple_format_textfield', $info['#theme']);
  }

  /**
   * @covers ::preRenderTextfield
   */
  public function testPreRenderTextfieldRendersRequestedButtons() {
    $element = [
      '#buttons' => [
        'bold' => TRUE,
        'italic' => TRUE,
        'underline' => TRUE,
        'remove_formatting' => TRUE,
        'toggle' => TRUE,
      ],
    ];

    $result = SimpleFormatTextfield::preRenderTextfield($element);

    $this->assertSame('input__simple_format_textfield', $result['#theme']);
    $this->assertCount(5, $result['#buttons_render']);
    $this->assertArrayHasKey('bold', $result['#buttons_render'][0]);
    $this->assertArrayHasKey('italic', $result['#buttons_render'][1]);
    $this->assertArrayHasKey('underline', $result['#buttons_render'][2]);
    $this->assertArrayHasKey('remove_formatting', $result['#buttons_render'][3]);
    $this->assertArrayHasKey('toggle', $result['#buttons_render'][4]);
    $this->assertContains('bluecadet_utilities/simple-formatter', $result['#attached']['library']);
  }

  /**
   * @covers ::preRenderTextfield
   */
  public function testPreRenderTextfieldWithNoButtonsRequested() {
    $element = ['#buttons' => []];

    $result = SimpleFormatTextfield::preRenderTextfield($element);

    $this->assertSame([], $result['#buttons_render']);
  }

}
