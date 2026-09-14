<?php

namespace Drupal\Tests\bluecadet_utilities\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\bluecadet_utilities\Form\ImageStyleGenSettings;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Form\ImageStyleGenSettings
 * @group bluecadet_utilities
 */
class ImageStyleGenSettingsTest extends UnitTestCase {

  /**
   * The state values keyed by state key, used by the mocked state service.
   *
   * @var array
   */
  protected $stateValues = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->stateValues = [];

    $state = $this->createMock(StateInterface::class);
    $state->method('get')->willReturnCallback(function ($key, $default = NULL) {
      return $this->stateValues[$key] ?? $default;
    });
    $state->method('set')->willReturnCallback(function ($key, $value) {
      $this->stateValues[$key] = $value;
    });

    $container = new ContainerBuilder();
    $container->set('state', $state);
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('messenger', $this->createMock(MessengerInterface::class));
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $form = new ImageStyleGenSettings();
    $this->assertSame('bcu_image_style_generator_settings', $form->getFormId());
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithNoExistingSettingsShowsOneEmptySize() {
    $form_object = new ImageStyleGenSettings();
    $form_state = new FormState();

    $form = $form_object->buildForm([], $form_state);

    $this->assertCount(1, array_filter(array_keys($form['sizes']), 'is_int'));
    $this->assertSame('', $form['sizes'][0]['label']['#default_value']);
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormPrefillsFromExistingSettings() {
    $this->stateValues[ImageStyleGenSettings::STATE_KEY] = [
      'sizes' => [
        ['label' => 'Thumbnail', 'size' => 100],
      ],
    ];

    $form_object = new ImageStyleGenSettings();
    $form_state = new FormState();

    $form = $form_object->buildForm([], $form_state);

    $this->assertSame('Thumbnail', $form['sizes'][0]['label']['#default_value']);
    $this->assertSame(100, $form['sizes'][0]['size']['#default_value']);
    // One extra blank row is always appended after existing sizes.
    $this->assertArrayHasKey(1, $form['sizes']);
    $this->assertSame('', $form['sizes'][1]['label']['#default_value']);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormSavesNonEmptySizesAndSkipsAddSizeButton() {
    $form_object = new ImageStyleGenSettings();
    $form_state = new FormState();
    $form_state->setValues([
      'sizes' => [
        0 => ['label' => 'Thumbnail', 'size' => 100],
        1 => ['label' => '', 'size' => ''],
        'add_size' => 'Add one more',
      ],
    ]);

    $form = [];
    $form_object->submitForm($form, $form_state);

    $this->assertSame([
      'sizes' => [
        ['label' => 'Thumbnail', 'size' => 100],
      ],
    ], $this->stateValues[ImageStyleGenSettings::STATE_KEY]);
  }

  /**
   * @covers ::ajaxExampleAddMoreAddOne
   */
  public function testAjaxAddOneIncrementsSizeCount() {
    $form_object = new ImageStyleGenSettings();
    $form_state = new FormState();
    $form_state->set('num_of_sizes', 1);

    $form = [];
    $form_object->ajaxExampleAddMoreAddOne($form, $form_state);

    $this->assertSame(2, $form_state->get('num_of_sizes'));
    $this->assertTrue($form_state->isRebuilding());
  }

  /**
   * @covers ::ajaxExampleAddMoreRemoveOne
   */
  public function testAjaxRemoveOneDecrementsSizeCountButNeverBelowOne() {
    $form_object = new ImageStyleGenSettings();
    $form_state = new FormState();
    $form_state->set('num_of_sizes', 1);

    $form = [];
    $form_object->ajaxExampleAddMoreRemoveOne($form, $form_state);
    $this->assertSame(1, $form_state->get('num_of_sizes'));

    $form_state->set('num_of_sizes', 2);
    $form_object->ajaxExampleAddMoreRemoveOne($form, $form_state);
    $this->assertSame(1, $form_state->get('num_of_sizes'));
  }

  /**
   * @covers ::ajaxExampleAddMoreCallback
   */
  public function testAjaxCallbackReturnsSizesFieldset() {
    $form_object = new ImageStyleGenSettings();
    $form_state = new FormState();
    $form = ['sizes' => ['#type' => 'details']];

    $this->assertSame($form['sizes'], $form_object->ajaxExampleAddMoreCallback($form, $form_state));
  }

}
