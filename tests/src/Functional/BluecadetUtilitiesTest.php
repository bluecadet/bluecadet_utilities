<?php

namespace Drupal\Tests\bluecadet_utilities\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the Bluecadet Utilities module.
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
      'administer site configuration',
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

  /**
   * Test Access to TextFieldSearch form.
   */
  public function testTextFieldSearch() {
    $session = $this->assertSession();

    $this->assertTrue(TRUE);

    // Should get a 403 when logged out.
    $this->drupalGet('/admin/reports/textfield-search');
    $this->assertSession()->statusCodeEquals(403);

    // Should get a 200 when logged in as an admin.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/reports/textfield-search');
    $this->assertSession()->statusCodeEquals(200);
  }

}
