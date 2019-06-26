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

    $build['table'] = [
      '#type' => 'table',
      '#caption' => $this->t('Paragraph Bundle Examples'),
      '#header' => array($this->t('Paragraph Name'), $this->t('Image')),
      '#rows' => [],
      '#empty' => $this->t('There are no bundles defined at this time.'),
      '#attached' => ['library' => ['bluecadet_utilities/paragraph_examples.display']],
    ];

    foreach ($bundles as $bundle_id => $bundle) {
      if (isset($settings[$bundle_id])) {
        $file = NULL;
        if (!empty($settings[$bundle_id]['images'])) {
          $img_render = [];

          foreach ($settings[$bundle_id]['images'] as $img_fid) {
            $file = \Drupal\file\Entity\File::load($img_fid);

            if ($file) {
              $img_render[] = [
                '#theme' => 'image_style',
                '#style_name' => 'bluecadet_utilities_paragraph_example_full',
                '#uri' => $file->getFileUri(),
              ];
            }
          }
        }

        $build['table']['#rows'][] = [
          'attributes' => [
            'class' => '',
          ],
          'data' => [
            [
              'data' => [
                '#markup' => '<h2>' . $bundle['label'] . '</h2><p>' . $settings[$bundle_id]['description'] . '</p>',
              ],
              'class' => 'name-cell',
            ],
            [
              'data' => [
                '#markup' => ($file)? render($img_render) : '--NO IMAGE--',
              ],
              'class' => 'image-cell',
            ],
          ]
        ];

        return $build;
      }
      $build['table']['#rows'][] = [
        'attributes' => [
          'class' => '',
        ],
        'data' => [
          [
            'data' => [
              '#markup' => '<p>No settings for: ' . $bundle['label'] . '</p>',
            ],
            'class' => 'name-cell',
          ],
          [
            'data' => [
              '#markup' => '',
            ],
            'class' => '',
          ],
        ]
      ];

    }

    return $build;
  }
}