<?php

namespace Drupal\Tests\bc_display_title\Unit;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\bc_display_title\Routing\DisplayTitle;

/**
 * @coversDefaultClass \Drupal\bc_display_title\Routing\DisplayTitle
 * @group bc_display_title
 */
class DisplayTitleTest extends UnitTestCase {

  /**
   * @covers ::getDisplayTitle
   */
  public function testGetDisplayTitleReturnsNodeLabelWhenFieldIsAbsent() {
    $node = $this->createMock(FieldableEntityInterface::class);
    $node->method('hasField')->with('field_display_title')->willReturn(FALSE);
    $node->method('label')->willReturn('Default Title');

    $display_title = new DisplayTitle();
    $this->assertSame('Default Title', $display_title->getDisplayTitle($node));
  }

  /**
   * @covers ::getDisplayTitle
   */
  public function testGetDisplayTitleReturnsNodeLabelWhenFieldIsEmpty() {
    $field = $this->createMock(FieldItemListInterface::class);
    $field->method('getValue')->willReturn([]);

    $node = $this->createMock(FieldableEntityInterface::class);
    $node->method('hasField')->with('field_display_title')->willReturn(TRUE);
    $node->method('get')->with('field_display_title')->willReturn($field);
    $node->method('label')->willReturn('Default Title');

    $display_title = new DisplayTitle();
    $this->assertSame('Default Title', $display_title->getDisplayTitle($node));
  }

  /**
   * @covers ::getDisplayTitle
   */
  public function testGetDisplayTitleStripsParagraphTagsFromFieldValue() {
    $field = $this->createMock(FieldItemListInterface::class);
    $field->method('getValue')->willReturn([
      ['value' => '<p>Custom Title</p>', 'format' => 'basic_html'],
    ]);

    $node = $this->createMock(FieldableEntityInterface::class);
    $node->method('hasField')->with('field_display_title')->willReturn(TRUE);
    $node->method('get')->with('field_display_title')->willReturn($field);
    $node->method('label')->willReturn('Default Title');

    $display_title = new DisplayTitle();
    $this->assertSame('Custom Title', $display_title->getDisplayTitle($node));
  }

}
