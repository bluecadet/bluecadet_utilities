<?php

namespace Drupal\Tests\bc_sandbox\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\bc_sandbox\Controller\SandBox;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\bc_sandbox\Controller\SandBox
 * @group bc_sandbox
 */
class SandBoxTest extends UnitTestCase {

  /**
   * @covers ::build
   */
  public function testBuildReturnsEmptyRenderArray() {
    $controller = new SandBox();
    $this->assertSame([], $controller->build(Request::create('/sandbox')));
  }

}
