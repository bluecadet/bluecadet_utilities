<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\bluecadet_utilities\Form\TextFieldSearch;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Form\TextFieldSearch
 * @group bluecadet_utilities
 */
class TextFieldSearchTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'entity_test',
    'bluecadet_utilities',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installConfig(['field']);

    FieldStorageConfig::create([
      'field_name' => 'field_search_text',
      'entity_type' => 'entity_test',
      'type' => 'text',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_search_text',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    unset($_SESSION['bcu_search_results']);
    parent::tearDown();
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $form_object = TextFieldSearch::create($this->container);
    $this->assertSame('bcu_search+text_fields', $form_object->getFormId());
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithNoSessionDataShowsEmptySearch() {
    unset($_SESSION['bcu_search_results']);

    $form_object = TextFieldSearch::create($this->container);
    $form = $form_object->buildForm([], new FormState());

    $this->assertSame('textfield', $form['search']['#type']);
    $this->assertSame('', $form['search']['#default_value']);
    $this->assertArrayNotHasKey('results', $form);
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithSessionDataShowsResultsAndClearsSession() {
    $link = Link::fromTextAndUrl('Test entity', Url::fromUri('base:/test'));

    $_SESSION['bcu_search_results'] = [
      TRUE,
      [
        'search_str' => 'hello',
        'data' => [
          'entity_test' => [
            1 => [
              'url' => $link->getUrl(),
              'label' => 'Test entity',
              'count' => 2,
              'fields' => ['field_search_text'],
            ],
          ],
        ],
      ],
      [],
      '0.42 sec',
    ];

    $form_object = TextFieldSearch::create($this->container);
    $form = $form_object->buildForm([], new FormState());

    $this->assertSame('hello', $form['search']['#default_value']);
    $this->assertStringContainsString('0.42 sec', (string) $form['results']['timing']['#markup']);
    $this->assertNotEmpty($form['results']['results']);
    // The session should be cleared after being rendered once.
    $this->assertArrayNotHasKey('bcu_search_results', $_SESSION);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormRegistersBatchOperationsPerTextField() {
    $form_object = TextFieldSearch::create($this->container);
    $form_state = new FormState();
    $form_state->setValues(['search' => 'hello']);

    $form = [];
    $form_object->submitForm($form, $form_state);

    $batch = batch_get();
    $this->assertNotEmpty($batch['sets'][0]['operations']);
    // At least one operation should target our text field on entity_test.
    $targets_field = FALSE;
    foreach ($batch['sets'][0]['operations'] as $operation) {
      if ($operation[0][1] === 'searchTextFields' && isset($operation[1][2]['field_search_text'])) {
        $targets_field = TRUE;
      }
    }
    $this->assertTrue($targets_field);
  }

  /**
   * @covers ::setUpContext
   * @covers ::searchTextFields
   * @covers ::processResults
   * @covers ::finishedCallback
   */
  public function testBatchPipelineFindsMatchingEntities() {
    $match = EntityTest::create([
      'name' => 'Match',
      'field_search_text' => ['value' => 'contains hello world'],
    ]);
    $match->save();

    $no_match = EntityTest::create([
      'name' => 'No match',
      'field_search_text' => ['value' => 'nothing interesting'],
    ]);
    $no_match->save();

    $context = [];
    TextFieldSearch::setUpContext('%hello%', $context);
    $this->assertSame('%hello%', $context['results']['search_str']);

    TextFieldSearch::searchTextFields('text', 'entity_test', [
      'field_search_text' => ['type' => 'text'],
    ], '%hello%', $context);

    $this->assertArrayHasKey($match->id(), array_flip($context['results']['raw']['entity_test']['text']['field_search_text']));
    $this->assertArrayNotHasKey($no_match->id(), array_flip($context['results']['raw']['entity_test']['text']['field_search_text']));

    TextFieldSearch::processResults($context);

    $this->assertArrayHasKey($match->id(), $context['results']['data']['entity_test']);
    $this->assertSame(1, $context['results']['data']['entity_test'][$match->id()]['count']);
    $this->assertSame('Match', $context['results']['data']['entity_test'][$match->id()]['label']);

    TextFieldSearch::finishedCallback(TRUE, $context['results'], [], '0.1 sec');
    $this->assertSame($context['results'], $_SESSION['bcu_search_results'][1]);
  }

}
