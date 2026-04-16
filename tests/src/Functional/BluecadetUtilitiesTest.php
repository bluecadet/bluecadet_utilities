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
   * Test title field label and description overrides via the CT config form.
   *
   * Verifies that submitting custom values for "Title field label" and
   * "Title field description" on the content type edit form:
   *   1. Persists the values to a BaseFieldOverride config entity.
   *   2. Reflects the overridden label and description on the node add form.
   */
  public function testTitleFieldOverridesViaContentTypeForm(): void {
    $bundle = 'test_article';

    $admin = $this->drupalCreateUser([
      'access administration pages',
      'administer content types',
    ]);
    $this->drupalLogin($admin);

    // Create the content type via the admin form so hook_form_alter fires.
    $this->drupalGet('/admin/structure/types/add');
    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm([
      'name' => 'Test Article',
      'type' => $bundle,
      'title_label' => 'Administrative Title',
      'title_description' => 'For use in the CMS only.',
    ], 'Save and manage fields');

    // 1. BaseFieldOverride stores the overridden label and description.
    // Clear the field definition cache so the test container picks up the
    // BaseFieldOverride saved during the server-side form submission.
    $this->container->get('entity_field.manager')->clearCachedFieldDefinitions();
    $fields = $this->container->get('entity_field.manager')
      ->getFieldDefinitions('node', $bundle);
    $this->assertEquals('Administrative Title', $fields['title']->getLabel());
    $this->assertEquals('For use in the CMS only.', $fields['title']->getDescription());

    // 2. Node add form displays the overridden label and description.
    // The content type now exists, so the permission is valid.
    $creator = $this->drupalCreateUser([
      'access content',
      'create ' . $bundle . ' content',
    ]);
    $this->drupalLogin($creator);
    $this->drupalGet('/node/add/' . $bundle);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextContains('css', 'label[for*="edit-title"]', 'Administrative Title');
    $this->assertSession()->pageTextContains('For use in the CMS only.');
  }

}
