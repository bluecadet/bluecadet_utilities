<?php

namespace Drupal\Tests\bc_sandbox\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the BC Display Title module.
 *
 * @group bc_sandbox
 */
class BcSandboxTest extends BrowserTestBase {

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
    'bc_sandbox',
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
   *
   * @covers Drupal\bc_sandbox\Controller\SandBox
   */
  public function testBasicFunc() {
    $session = $this->assertSession();

    $this->assertTrue(TRUE);

    $this->drupalGet('admin/config/bc/sandbox');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($this->authenticatedUser);
    $this->drupalGet('admin/config/bc/sandbox');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertText(t('Hello world! (BCS)'), 'The text "Hello world! (BCS)" appears on the page.');
  }

}
