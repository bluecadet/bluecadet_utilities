<?php

namespace Drupal\Tests\bluecadet_utilities\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 *
 *
 * @group bluecadet_utilities
 */
class BluecadetUtilitiesTest extends BrowserTestBase {

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'field',
    'text',
    'options',
    'bluecadet_utilities',
  ];

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'claro';

  /**
   * A user with administration rights.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authenticatedUser;

  /**
   * A test menu.
   *
   * @var \Drupal\system\Entity\Menu
   */
  protected $menu;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
    ]);
    $this->authenticatedUser = $this->drupalCreateUser([]);

  }

  /**
   * Test Basic Functionality.
   */
  public function testBasicFunc() {
    $session = $this->assertSession();

    $this->assertTrue(TRUE);

    $this->drupalGet('<front>');
    $this->assertSession()->statusCodeEquals(200);
  }

}
