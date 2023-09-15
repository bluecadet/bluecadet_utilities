<?php

namespace Drupal\bluecadet_utilities\Form;

use Drupal\bluecadet_utilities\DrupalStateTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

/**
 * Bluecadet Utility Settings Form.
 */
class ImageStyleGenerator extends FormBase {

  use DrupalStateTrait;
  use MessengerTrait;

  /**
   * Drupal Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandler;

  /**
   * Get module handler.
   */
  private function moduleHandler() {
    if (!$this->moduleHandler) {
      $this->moduleHandler = \Drupal::service('module_handler'); // phpcs:ignore
    }

    return $this->moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bcu_img_style_generator';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // phpcs:ignore
    // $this->drupalState()->delete(BCU_IMG_GEN_STATE);

    $settings = $this->drupalState()->get(BCU_IMG_GEN_STATE, NULL);

    if (is_null($settings)) {

      $message = "Please create ";
      $message .= Link::fromTextAndUrl("Size Settings", Url::fromRoute("bluecadet_utilities.image_style_generator_settings"))->toString();
      $message .= ".";

      $message = Markup::create($message);

      $this->messenger()->addWarning($message, FALSE);
      return;
    }

    $form['#tree'] = TRUE;

    $sizes_preview = [
      '#theme' => 'item_list',
      '#items' => [],
    ];
    foreach ($settings['sizes'] as $size) {
      $sizes_preview['#items'][] = $size['label'] . ": " . $size['size'] . "px";
    }

    $form['preview'] = [
      '#markup' => \Drupal::service('renderer')->render($sizes_preview),
    ];

    $form['msg'] = [
      '#markup' => '<p>Set the Aspect ratio.</p>',
    ];
    $form['width'] = [
      '#type' => 'number',
      '#title' => 'Width',
      '#description' => $this->t('Width of the ratio'),
      '#prefix' => "<div class=\"aspect-container\">",
    ];

    $form['height'] = [
      '#type' => 'number',
      '#title' => 'Height',
      '#description' => $this->t('Height of the ratio'),
      '#suffix' => "</div>",
    ];

    $options = [
      'image_scale_and_crop' => "Scale & Crop",
    ];

    // Check for Focal Point module.
    if ($this->moduleHandler()->moduleExists('focal_point')) {
      $options["focal_point_scale_and_crop"] = "Focal Point";
    }

    // phpcs:disable
    // Check for image_widget_crop module.
    // if ($this->moduleHandler()->moduleExists('image_widget_crop')) {
    //   $options["crop_crop"] = "Image Widget Crop";
    // }
    // phpcs:enable

    $form['image_style_effects_variations'] = [
      '#type' => 'checkboxes',
      '#title' => 'Image Style variations',
      '#description' => $this->t('Choose the variation you would like to create for each image style.'),
      '#options' => $options,
    ];

    // phpcs:disable
    // if ($this->moduleHandler()->moduleExists('image_widget_crop')) {

    //   $cropTypeStorage = \Drupal::service('entity_type.manager')->getStorage('crop_type');

    //   $crop_options = [];
    //   foreach ($cropTypeStorage->loadMultiple() as $c_id => $crop) {
    //     $crop_options[$c_id] = $crop->label();
    //   }

    //   $form['image_crop'] = [
    //     '#type' => 'select',
    //     '#title' => 'Which Crop',
    //     '#description' => $this->t('Choose the crop.'),
    //     '#options' => $crop_options,
    //   ];
    // }
    // phpcs:enable

    // Actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Image styles'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $settings = $this->drupalState()->get(BCU_IMG_GEN_STATE, NULL);

    $responses = [];

    foreach ($values['image_style_effects_variations'] as $key => $val) {
      if ($val) {
        foreach ($settings['sizes'] as $size) {
          $label = $size['label'];
          $w_size = $size['size'];

          $machinename = $this->getMachineName($values['width'] . "x" . $values['height'] . "__" . $key . "__" . $label);

          if (ImageStyle::load($machinename) == NULL) { // phpcs:ignore
            $image_style = ImageStyle::create([
              'status' => TRUE,
              'name' => $machinename,
              'label' => $machinename,
            ]);

            $image_style->addImageEffect([
              'id' => $key,
              'weight' => 1,
              'data' => [
                'width' => $w_size,
                'height' => ceil($w_size * $values['height'] / $values['width']),
              ],
            ]);

            $image_style->addImageEffect([
              'id' => 'image_convert',
              'weight' => 2,
              'data' => [
                'format' => "webp",
                'quality' => '92',
              ],
            ]);

            $image_style->save();

            $responses[] = $machinename;
          }
          else {
            $this->messenger()->addWarning("Already exists: " . $machinename);
          }
        }
      }
    }

    $message_render = [
      '#theme' => 'item_list',
      '#items' => $responses,
    ];
    // phpcs:ignore
    $msg = Markup::create("The following Image Styles have been created: " . \Drupal::service('renderer')->render($message_render));

    $this->messenger()->addMessage($msg);
  }

  /**
   * Generates a machine name from a string.
   *
   * This is basically the same as what is done in
   * \Drupal\Core\Block\BlockBase::getMachineNameSuggestion() and
   * \Drupal\system\MachineNameController::transliterate(), but it seems
   * that so far there is no common service for handling this.
   *
   * from: https://zgadzaj.com/development/php/drupal/8/how-to-generate-a-machine-name-in-drupal-8
   *
   * @param string $string
   *
   * @return string
   *
   * @see \Drupal\Core\Block\BlockBase::getMachineNameSuggestion()
   * @see \Drupal\system\MachineNameController::transliterate()
   */
  protected function getMachineName($string) {
    $transliterated = \Drupal::transliteration()->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = mb_strtolower($transliterated);

    $transliterated = preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);

    return $transliterated;
  }

}
