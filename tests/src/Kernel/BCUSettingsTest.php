<?php

namespace Drupal\Tests\bluecadet_utilities\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\bluecadet_utilities\Form\BCUSettings;

/**
 * @coversDefaultClass \Drupal\bluecadet_utilities\Form\BCUSettings
 * @group bluecadet_utilities
 */
class BCUSettingsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'bluecadet_utilities'];

  /**
   * @covers ::getFormId
   * @covers ::getEditableConfigNames
   * @covers ::buildForm
   * @covers ::submitForm
   * @covers ::create
   */
  public function testBuildAndSubmit() {
    $this->installConfig(['bluecadet_utilities']);

    /** @var \Drupal\bluecadet_utilities\Form\BCUSettings $form_object */
    $form_object = BCUSettings::create($this->container);

    $this->assertSame('bcu_settings', $form_object->getFormId());

    $form_state = new FormState();
    $form = $form_object->buildForm([], $form_state);

    $this->assertSame('checkbox', $form['use_transliteration']['#type']);
    $this->assertSame('checkbox', $form['use_textfield_wysiwyg']['#type']);
    // Default install config ships both settings enabled.
    $this->assertTrue($form['use_transliteration']['#default_value']);
    $this->assertTrue($form['use_textfield_wysiwyg']['#default_value']);

    $form_state->setValue('use_transliteration', FALSE);
    $form_state->setValue('use_textfield_wysiwyg', FALSE);
    $form_object->submitForm($form, $form_state);

    $config = $this->config(BCUSettings::SETTINGS);
    $this->assertFalse($config->get('use_transliteration'));
    $this->assertFalse($config->get('use_textfield_wysiwyg'));
  }

}
