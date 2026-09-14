<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Render\RenderContext;
use Drupal\KernelTests\KernelTestBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\bluecadet_utilities\Form\ImageStyleGenSettings;
use Drupal\bluecadet_utilities\Form\ImageStyleGenerator;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Form\ImageStyleGenerator
 * @group bluecadet_utilities
 */
class ImageStyleGeneratorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'image',
    'bluecadet_utilities',
  ];

  /**
   * Runs a callback inside a render context, as buildForm()/submitForm() do.
   */
  protected function inRenderContext(callable $callback) {
    return \Drupal::service('renderer')->executeInRenderContext(new RenderContext(), $callback);
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $form_object = ImageStyleGenerator::create($this->container);
    $this->assertSame('bcu_img_style_generator', $form_object->getFormId());
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithoutSizeSettingsShowsWarningAndNoForm() {
    $form_object = ImageStyleGenerator::create($this->container);
    $form_state = new FormState();

    $form = $form_object->buildForm([], $form_state);

    $this->assertNull($form);
    $warnings = \Drupal::messenger()->all()[MessengerInterface::TYPE_WARNING] ?? [];
    $this->assertNotEmpty($warnings);
  }

  /**
   * @covers ::buildForm
   */
  public function testBuildFormWithSizeSettingsShowsPreviewAndOptions() {
    \Drupal::state()->set(ImageStyleGenSettings::STATE_KEY, [
      'sizes' => [
        ['label' => 'Thumbnail', 'size' => 100],
      ],
    ]);

    $form_object = ImageStyleGenerator::create($this->container);
    $form_state = new FormState();

    $form = $this->inRenderContext(fn() => $form_object->buildForm([], $form_state));

    $this->assertStringContainsString('Thumbnail: 100px', (string) $form['preview']['#markup']);
    $this->assertArrayHasKey('image_scale', $form['image_style_effects_variations']['#options']);
    $this->assertArrayHasKey('image_scale_and_crop', $form['image_style_effects_variations']['#options']);
    $this->assertArrayNotHasKey('focal_point_scale_and_crop', $form['image_style_effects_variations']['#options']);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormCreatesScaleImageStyles() {
    \Drupal::state()->set(ImageStyleGenSettings::STATE_KEY, [
      'sizes' => [
        ['label' => 'thumb', 'size' => 100],
      ],
    ]);

    $form_object = ImageStyleGenerator::create($this->container);
    $form_state = new FormState();
    $form_state->setValues([
      'image_style_effects_variations' => ['image_scale' => 'image_scale'],
      'width' => 0,
      'height' => 0,
    ]);

    $form = [];
    $this->inRenderContext(function () use (&$form, $form_object, $form_state) {
      $form_object->submitForm($form, $form_state);
    });

    $style = ImageStyle::load('image_scale__thumb');
    $this->assertNotNull($style);
    $effects = iterator_to_array($style->getEffects());
    $effect = reset($effects);
    $this->assertSame('image_scale', $effect->getPluginId());
    $this->assertSame(100, $effect->getConfiguration()['data']['width']);
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormDoesNotDuplicateExistingImageStyle() {
    \Drupal::state()->set(ImageStyleGenSettings::STATE_KEY, [
      'sizes' => [
        ['label' => 'thumb', 'size' => 100],
      ],
    ]);

    ImageStyle::create([
      'status' => TRUE,
      'name' => 'image_scale__thumb',
      'label' => 'image_scale__thumb',
    ])->save();

    $form_object = ImageStyleGenerator::create($this->container);
    $form_state = new FormState();
    $form_state->setValues([
      'image_style_effects_variations' => ['image_scale' => 'image_scale'],
      'width' => 0,
      'height' => 0,
    ]);

    $form = [];
    $this->inRenderContext(function () use (&$form, $form_object, $form_state) {
      $form_object->submitForm($form, $form_state);
    });

    $warnings = \Drupal::messenger()->all()[MessengerInterface::TYPE_WARNING] ?? [];
    $this->assertNotEmpty(array_filter($warnings, fn($w) => str_contains((string) $w, 'Already exists')));
  }

  /**
   * @covers ::submitForm
   */
  public function testSubmitFormCreatesAspectRatioImageStyles() {
    \Drupal::state()->set(ImageStyleGenSettings::STATE_KEY, [
      'sizes' => [
        ['label' => 'thumb', 'size' => 100],
      ],
    ]);

    $form_object = ImageStyleGenerator::create($this->container);
    $form_state = new FormState();
    $form_state->setValues([
      'image_style_effects_variations' => ['image_scale_and_crop' => 'image_scale_and_crop'],
      'width' => 16,
      'height' => 9,
    ]);

    $form = [];
    $this->inRenderContext(function () use (&$form, $form_object, $form_state) {
      $form_object->submitForm($form, $form_state);
    });

    $style = ImageStyle::load('16x9__image_scale_and_crop__thumb');
    $this->assertNotNull($style);
    $effects = iterator_to_array($style->getEffects());
    $effect = reset($effects);
    $this->assertSame('image_scale_and_crop', $effect->getPluginId());
    $this->assertSame(100, $effect->getConfiguration()['data']['width']);
    // Height = ceil(100 * 9 / 16) = 57.
    $this->assertSame(57, $effect->getConfiguration()['data']['height']);
  }

}
