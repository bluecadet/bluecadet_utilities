<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\bluecadet_utilities\Form\EntityReferenceFieldSearch;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Form\EntityReferenceFieldSearch
 * @group bluecadet_utilities
 */
class EntityReferenceFieldSearchTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'entity_test',
    'file',
    'bluecadet_utilities',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
    $this->installConfig(['field']);
    $this->installSchema('file', ['file_usage']);

    FieldStorageConfig::create([
      'field_name' => 'field_ref',
      'entity_type' => 'entity_test',
      'type' => 'entity_reference',
      'settings' => ['target_type' => 'entity_test'],
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_ref',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    unset($_SESSION['bcu_ent_ref_search_results']);
    parent::tearDown();
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $form_object = EntityReferenceFieldSearch::create($this->container);
    $this->assertSame('bcu_search+ent_ref_fields', $form_object->getFormId());
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithNoSessionDataListsEntityTypes() {
    unset($_SESSION['bcu_ent_ref_search_results']);

    $form_object = EntityReferenceFieldSearch::create($this->container);
    $form = $form_object->buildForm([], new FormState());

    $this->assertSame('select', $form['entity_type']['#type']);
    $this->assertArrayHasKey('entity_test', $form['entity_type']['#options']);
    $this->assertArrayNotHasKey('results', $form);
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithSessionDataShowsResultsAndClearsSession() {
    $link = Link::fromTextAndUrl('Test entity', Url::fromUri('base:/test'));

    $_SESSION['bcu_ent_ref_search_results'] = [
      TRUE,
      [
        'entity_type' => 'entity_test',
        'search_str' => '5',
        'data' => [
          'entity_test' => [
            1 => [
              'url' => $link->getUrl(),
              'label' => 'Test entity',
              'count' => 1,
              'fields' => ['field_ref'],
            ],
          ],
        ],
      ],
      [],
      '0.5 sec',
    ];

    $form_object = EntityReferenceFieldSearch::create($this->container);
    $form = $form_object->buildForm([], new FormState());

    $this->assertSame('entity_test', $form['entity_type']['#default_value']);
    $this->assertSame('5', $form['search']['#default_value']);
    $this->assertNotEmpty($form['results']['results']);
    $this->assertArrayNotHasKey('bcu_ent_ref_search_results', $_SESSION);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormRegistersBatchOperationsIncludingJsonExport() {
    $form_object = EntityReferenceFieldSearch::create($this->container);
    $form_state = new FormState();
    $form_state->setValues([
      'search' => '5',
      'entity_type' => 'entity_test',
      'json' => TRUE,
    ]);

    $form = [];
    $form_object->submitForm($form, $form_state);

    $batch = batch_get();
    $operation_methods = array_column(array_column($batch['sets'][0]['operations'], 0), 1);
    $this->assertContains('searchEntityRefFields', $operation_methods);
    $this->assertContains('processResults', $operation_methods);
    $this->assertContains('createJsonOutput', $operation_methods);
  }

  /**
   * @covers ::findFields
   * @covers ::setUpContext
   * @covers ::searchEntityRefFields
   * @covers ::processResults
   * @covers ::finishedCallback
   */
  public function testBatchPipelineFindsReferencingEntities() {
    $target = EntityTest::create(['name' => 'Target']);
    $target->save();

    $referrer = EntityTest::create([
      'name' => 'Referrer',
      'field_ref' => ['target_id' => $target->id()],
    ]);
    $referrer->save();

    $other = EntityTest::create(['name' => 'Unrelated']);
    $other->save();

    $form_state = new FormState();
    $form_state->setValues(['entity_type' => 'entity_test']);

    $fields = EntityReferenceFieldSearch::findFields($form_state);
    $this->assertArrayHasKey('entity_reference', $fields);
    $this->assertArrayHasKey('field_ref', $fields['entity_reference']['entity_test']);

    $context = [];
    EntityReferenceFieldSearch::setUpContext((string) $target->id(), 'entity_test', $form_state, $context);
    $this->assertSame('entity_test', $context['results']['entity_type']);

    EntityReferenceFieldSearch::searchEntityRefFields('entity_reference', 'entity_test', [
      'field_ref' => $fields['entity_reference']['entity_test']['field_ref'],
    ], (string) $target->id(), $context);

    $this->assertSame([$referrer->id()], array_values($context['results']['raw']['entity_test']['entity_reference']['field_ref']));

    EntityReferenceFieldSearch::processResults($context);

    $this->assertArrayHasKey($referrer->id(), $context['results']['data']['entity_test']);
    $this->assertSame('Referrer', $context['results']['data']['entity_test'][$referrer->id()]['label']);
    $this->assertArrayNotHasKey($other->id(), $context['results']['data']['entity_test'] ?? []);

    EntityReferenceFieldSearch::finishedCallback(TRUE, $context['results'], [], '0.2 sec');
    $this->assertSame($context['results'], $_SESSION['bcu_ent_ref_search_results'][1]);
  }

  /**
   * @covers ::createJsonOutput
   */
  public function testCreateJsonOutputWritesFileAndSetsLink() {
    $context = [
      'results' => [
        'raw' => ['entity_test' => ['entity_reference' => ['field_ref' => [1]]]],
        'search_str' => 'hello world',
      ],
    ];

    EntityReferenceFieldSearch::createJsonOutput($context);

    $this->assertNotEmpty($context['results']['file_file']);
    $this->assertNotEmpty($context['results']['file_link']);
    $this->assertStringContainsString('ent_ref_search.json', $context['results']['file_file']->getFilename());
  }

}
