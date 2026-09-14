<?php

namespace Drupal\Tests\bluecadet_utilities\Unit;

use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\bluecadet_utilities\DrupalStateTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\DrupalStateTrait
 * @group bluecadet_utilities
 */
class DrupalStateTraitTest extends UnitTestCase {

  /**
   * @covers ::drupalState
   */
  public function testDrupalStateFetchesAndCachesTheStateService() {
    $state = $this->createMock(StateInterface::class);

    $container = $this->createMock(ContainerInterface::class);
    // The service should only be fetched from the container once, even
    // though drupalState() is called twice below.
    $container->expects($this->once())
      ->method('get')
      ->with('state')
      ->willReturn($state);
    \Drupal::setContainer($container);

    $object = new class() {
      use DrupalStateTrait;

      /**
       * Exposes the protected drupalState() for testing.
       */
      public function getState() {
        return $this->drupalState();
      }

    };

    $this->assertSame($state, $object->getState());
    // Calling it again should return the cached instance rather than
    // re-fetching from the container.
    $this->assertSame($state, $object->getState());
  }

}
