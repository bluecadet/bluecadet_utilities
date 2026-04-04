<?php

namespace Drupal\Tests\bluecadet_utilities\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Test the Bluecadet Utilities module.
 *
 * @group bluecadet_utilities
 */
class BluecadetUtilitiesTest extends BrowserTestBase {

  use ContentTypeCreationTrait;

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'field',
    'field_ui',
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

  /**
   * Test Title widget third-party settings summary and form element overrides.
   *
   * Verifies that configuring title_label and title_description as third-party
   * settings on the Title widget for a node bundle:
   *   1. Shows the configured values in the form display settings summary.
   *   2. Applies the overridden label and description on the node add form.
   */
  public function testTitleWidgetThirdPartySettings(): void {
    $bundle = 'test_article';
    $this->drupalCreateContentType(['type' => $bundle, 'name' => 'Test Article']);

    // Programmatically configure the Title widget's third-party settings on
    // the default form display for the new content type.
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->load('node.' . $bundle . '.default');
    $component = $form_display->getComponent('title');
    $component['third_party_settings']['bluecadet_utilities']['title_label'] = 'Administrative Title';
    $component['third_party_settings']['bluecadet_utilities']['title_description'] = 'For use in the CMS only.';
    $form_display->setComponent('title', $component)->save();

    // 1. Widget settings summary shows the overrides on the form display page.
    $admin = $this->drupalCreateUser([
      'access administration pages',
      'administer content types',
      'administer node form display',
    ]);
    $this->drupalLogin($admin);
    $this->drupalGet('/admin/structure/types/manage/' . $bundle . '/form-display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Label: Administrative Title');
    $this->assertSession()->pageTextContains('Description: For use in the CMS only.');

    // 2. Node add form displays the overridden label and description.
    $creator = $this->drupalCreateUser([
      'access content',
      'create ' . $bundle . ' content',
    ]);
    $this->drupalLogin($creator);
    $this->drupalGet('/node/add/' . $bundle);
    $this->assertSession()->statusCodeEquals(200);
    // The title field's <label> text should reflect the override.
    $this->assertSession()->elementTextContains('css', 'label[for*="edit-title"]', 'Administrative Title');
    // The title field's description text should reflect the override.
    $this->assertSession()->pageTextContains('For use in the CMS only.');
  }

}
