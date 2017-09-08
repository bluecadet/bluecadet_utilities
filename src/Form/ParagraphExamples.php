<?php

/**
 * @file
 * Contains \Drupal\bluecadet_utilities\Form\ParagraphExamples.
 */

namespace Drupal\bluecadet_utilities\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Configure Paragraph examples to upload images per para bundle.
 */
class ParagraphExamples extends FormBase  {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraph_example_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = \Drupal::state()->get('bluecadet_utilities.para_examples', array());
    $bundles = \Drupal::entityManager()->getBundleInfo('paragraph');
    $triggering_element = $form_state->getTriggeringElement();
    $values = $form_state->getValues();

    $orig_imgs = [];
    $new_files = [];
    foreach ($bundles as $bundle_id => $bundle) {
      $orig_imgs[$bundle_id] = isset($settings[$bundle_id][0])? $settings[$bundle_id][0] : NULL;
      $new_files[$bundle_id] = isset($values['images'][$bundle_id][0])? $values['images'][$bundle_id][0] : NULL;
    }
    $form_state->set('original_imgs', $orig_imgs);

    $form['images']['#tree'] = TRUE;
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    foreach ($bundles as $bundle_id => $bundle) {
      $form['images'][$bundle_id] = [
        '#title' => 'Reference Image for ' . $bundle['label'],
        '#type' => 'managed_file',
        '#default_value' => isset($settings[$bundle_id])? $settings[$bundle_id] : '',
        '#upload_location' => 'public://paragraph-examples/',
        '#multiple' => FALSE,
        '#upload_validators' => array(
          'file_validate_extensions' => array('png gif jpg jpeg'),
        ),
      ];

      $file_upload_help = [
        '#theme' => 'file_upload_help',
        // '#description' => 'description',
        '#upload_validators' => ['file_validate_extensions' => ['png gif jpg jpeg']],
        // '#cardinality' => 1,
      ];
      $form['images'][$bundle_id]['#description'] = \Drupal::service('renderer')->renderPlain($file_upload_help);

      // Thumbnail
      $f = NULL;
      if (isset($new_files[$bundle_id])) {
        $f = \Drupal\file\Entity\File::load($new_files[$bundle_id]);
      }
      elseif(isset($orig_imgs[$bundle_id])) {
        $f = \Drupal\file\Entity\File::load($orig_imgs[$bundle_id]);
      }

      if (!empty($f)) {
        $render = [
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => $f->getFileUri(),
        ];
        $form['images'][$bundle_id]['preview'] = [
          '#markup' => render($render),
        ];
      }

      // Remove preview on "Remove" btn trigger.
      if (!empty($triggering_element) && $triggering_element['#array_parents'][1] == $bundle_id && $triggering_element['#array_parents'][2] == 'remove_button') {
        $form['images'][$bundle_id]['preview'] = [];
      }
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $bundles = \Drupal::entityManager()->getBundleInfo('paragraph');

    $settings = $values['images'];
    \Drupal::state()->set('bluecadet_utilities.para_examples', $settings);

    $orig_images = $form_state->get('original_imgs');
    $file_usage = \Drupal::service('file.usage');

    foreach ($bundles as $bundle_id => $bundle) {
      $fid = isset($values['images'][$bundle_id][0])? $values['images'][$bundle_id][0] : NULL;
      $orig_fid = $orig_images[$bundle_id];

      // There is some sort of a change.
      if ($fid != $orig_fid) {
        // If there is a file, update it.
        if ($fid !== NULL) {
          $f = \Drupal\file\Entity\File::load($values['images'][$bundle_id][0]);
          $f->setPermanent();
          $f->save();
          $file_usage->add($f, 'bluecadet_utilities', 'config', 1);
        }

        // If there was a file, update it.
        if ($orig_fid != NULL) {
          if ($orig_fid !== NULL) {
            $f2 = file_load($orig_fid);
            $file_usage->delete($f2, 'bluecadet_utilities', 'config', 1);
          }
        }
      }
    }

    drupal_set_message('You have saved your settings.');
  }
}