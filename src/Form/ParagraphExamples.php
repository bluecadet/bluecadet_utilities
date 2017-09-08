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
class ParagraphExamples extends FormBase {
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
      $orig_imgs[$bundle_id] = isset($settings[$bundle_id]['images'])? $settings[$bundle_id]['images'] : NULL;
      $new_files[$bundle_id] = isset($values['pe'][$bundle_id]['images'])? $values['pe'][$bundle_id]['images'] : NULL;
    }

    $form_state->set('original_imgs', $orig_imgs);

    $form['pe']['#tree'] = TRUE;
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    foreach ($bundles as $bundle_id => $bundle) {
      $form['pe'][$bundle_id] = [
        '#type' => 'fieldset',
        '#title' => "Bundle: " . $bundle['label'],
        'images' => [],
        'description' => [
          '#type' => 'textarea',
          '#title' => 'Descriprion',
          '#default_value' => isset($settings[$bundle_id]['description'])? $settings[$bundle_id]['description'] : '',
        ]
      ];

      $form['pe'][$bundle_id]['images'] = [
        '#title' => 'Reference Image',
        '#type' => 'managed_file',
        '#default_value' => isset($settings[$bundle_id]['images'])? $settings[$bundle_id]['images'] : '',
        '#upload_location' => 'public://paragraph-examples/',
        '#multiple' => TRUE,
        '#upload_validators' => array(
          'file_validate_extensions' => array('png gif jpg jpeg'),
        ),
        'preview' => ['#markup' => '', '#prefix' => '<div class="img-thumbs">', '#suffix' => '</div>'],
      ];

      $file_upload_help = [
        '#theme' => 'file_upload_help',
        // '#description' => 'description',
        '#upload_validators' => ['file_validate_extensions' => ['png gif jpg jpeg']],
        // '#cardinality' => 1,
      ];
      $form['pe'][$bundle_id]['images']['#description'] = \Drupal::service('renderer')->renderPlain($file_upload_help);

      // Thumbnails
      if (isset($new_files[$bundle_id]) && !empty($new_files[$bundle_id])) {
        if (isset($new_files[$bundle_id]['fids'])) {
          $fids_to_use = explode(' ', $new_files[$bundle_id]['fids']);
        }
        else {
          $fids_to_use = $new_files[$bundle_id];
        }

        foreach($fids_to_use as $img_id) {
          $f = \Drupal\file\Entity\File::load($img_id);

          if (!empty($f)) {
            $render = [
              '#theme' => 'image_style',
              '#style_name' => 'thumbnail',
              '#uri' => $f->getFileUri(),
            ];
            $form['pe'][$bundle_id]['images']['preview']['#markup'] .= render($render);
          }
        }

      } elseif (isset($orig_imgs[$bundle_id]) && !empty($orig_imgs[$bundle_id])) {
        foreach($orig_imgs[$bundle_id] as $img_id) {
          $f = \Drupal\file\Entity\File::load($img_id);

          if (!empty($f)) {
            $render = [
              '#theme' => 'image_style',
              '#style_name' => 'thumbnail',
              '#uri' => $f->getFileUri(),
            ];
            $form['pe'][$bundle_id]['images']['preview']['#markup'] .= render($render);
          }
        }
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

    $settings = $values['pe'];
    \Drupal::state()->set('bluecadet_utilities.para_examples', $settings);

    $orig_images = $form_state->get('original_imgs');
    $file_usage = \Drupal::service('file.usage');

    $new_images = [];
    $old_images = [];

    foreach ($bundles as $bundle_id => $bundle) {
      $new_images = array_merge($new_images, $values['pe'][$bundle_id]['images']);
      $old_images = array_merge($old_images, $orig_images[$bundle_id]);
    }

    $images_to_add = array_diff($new_images, $old_images);
    $images_to_delete = array_diff($old_images, $new_images);

    foreach ($images_to_add as $fid_to_add) {
      if ($f = \Drupal\file\Entity\File::load($fid_to_add)) {
        $f->setPermanent();
        $f->save();
        $file_usage->add($f, 'bluecadet_utilities', 'config', 1, 1);
      }
    }

    foreach ($images_to_delete as $fid_to_delete) {
      if ($f2 = file_load($fid_to_delete)) {
        $file_usage->delete($f2, 'bluecadet_utilities', 'config', 1, 1);
      }
    }

    drupal_set_message('You have saved your settings.');
  }
}