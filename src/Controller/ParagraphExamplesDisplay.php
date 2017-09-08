<?php

namespace Drupal\bluecadet_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;

class ParagraphExamplesDisplay extends ControllerBase {

  /**
  * {@inheritdoc}
  */
  public function viewParagraphExamples() {
    $build = [];

    $settings = \Drupal::state()->get('bluecadet_utilities.para_examples', array());
    $bundles = \Drupal::entityManager()->getBundleInfo('paragraph');
    // $f = \Drupal\file\Entity\File::load($new_files[$bundle_id]);
ksm($settings, $bundles);
dsm($bundles);
    $build['table'] = [
      '#type' => 'table',
      '#caption' => $this->t('Paragraph Bundle Examples'),
      '#header' => array($this->t('Paragraph Name'), $this->t('Image')),
      '#rows' => [],
      '#empty' => $this->t('There are no bundles defined at this time.'),
    ];

    foreach ($bundles as $bundle_id => $bundle) {
      $file = NULL;
      if (!empty($settings[$bundle_id])) {
        $file = \Drupal\file\Entity\File::load($settings[$bundle_id][0]);

        $img_render = [
          '#theme' => 'image_style',
          '#style_name' => 'bluecadet_utilities_paragraph_example_full',
          '#uri' => $file->getFileUri(),
        ];

      }

      $build['table']['#rows'][] = ['data' => [
        [
          'data' => [
            '#markup' => $bundle['label'],
          ]
        ],
        [
          'data' => [
            '#markup' => ($file)? render($img_render) : '--NO IMAGE--',
          ]
        ],
      ]];
    }

ksm($build['table']);
    return $build;
  }
}