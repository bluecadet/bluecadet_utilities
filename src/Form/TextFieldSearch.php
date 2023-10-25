<?php

namespace Drupal\bluecadet_utilities\Form;

use Drupal\bluecadet_utilities\DrupalStateTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

/**
 * Bluecadet Utility Settings Form.
 */
class TextFieldSearch extends FormBase {

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
   * Drupal Entity Field Manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * Get Entity Field Manager.
   */
  private function entityFieldManager() {
    if (!$this->entityFieldManager) {
      $this->entityFieldManager = \Drupal::service('entity_field.manager'); // phpcs:ignore
    }
    return $this->entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bcu_search+text_fields';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $session_data = $_SESSION['bcu_search_results']?? [];
    // ksm($session_data);

    $form['#tree'] = TRUE;

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Search string"),
      '#description' => $this->t("This is doing a full string search on the raw html of the text field values. You can use '%' as a wildcard."),
      '#default_value' => $session_data[1]['search_str']?? "",
      '#placeholder' => "%class=\"material-icons\"% OR %<a name=\"%\"></a>%",
    ];

    // Actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    if (!empty($session_data)) {
      $form['results'] = [
        '#weight' => -1,
        'timing' => [
          '#markup' => "Results took " . $session_data[3],
        ],
        'results' => [],
      ];

      foreach($session_data[1]['data'] as $entity_type => $data) {
        $list = [
          '#theme' => 'item_list',
          '#title' => 'Results for: ' . $entity_type,
          '#items' => [],
        ];

        foreach ($data as $id => $result_data) {
          // ksm($result_data);

          $link = Link::fromTextAndUrl($result_data['label'], $result_data['url']);
          $list['#items'][] = [
            '#markup' => "Found " . $this->formatPlural($result_data['count'], "1 time", "@count times") . " on " . $link->toString(),
          ];
        }

        $form['results']['results'][] = $list;
      }
    }

    unset($_SESSION['bcu_search_results']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // ksm($values);

    $field_map = $this->entityFieldManager()->getFieldMap();
    // ksm($field_map);

    $field_type = [
      "text",
      "text_long",
      "text_with_summary"
    ];

    $fields = [];
    foreach ($field_map as $entity => $entity_field_data) {
      foreach ($entity_field_data as $field_id => $field_data) {
        if (in_array($field_data['type'], $field_type)) {
          $fields[$field_data['type']][$entity][$field_id] = $field_data;
        }
      }
    }

    // ksm($fields);

    $batch = [
      'title' => $this->t('Searching...'),
      'operations' => [
        [
          [$this, 'setUpContext'],
          [$values['search']]
        ]
      ],
      'finished' => [$this, 'finished_callback'],
    ];

    foreach ($fields as $field_type => $field_data) {
      foreach ($field_data as $entity_type => $fields) {
        $batch['operations'][] = [
          [$this, 'searchTextFields'],
          [
            $field_type,
            $entity_type,
            $fields,
            $values['search']
          ]
        ];
      }
    }

    $batch['operations'][] = [
      [$this, 'processResults'],
      []
    ];

    // ksm($batch);

    batch_set($batch);

    // $message_render = [];
    // phpcs:ignore
    // $msg = Markup::create("You submitted... " . \Drupal::service('renderer')->render($message_render));
    // $this->messenger()->addMessage($msg);
  }

  public static function setUpContext($search_string, &$context) {
    $context['results']['search_str'] = $search_string;
    $context['results']['raw'] = [];
    $context['results']['data'] = [];
    $context['results']['errors'] = [];
  }

  public static function searchTextFields($field_type, $entity_type, $fields, $search_string, &$context) {


    // ksm($field_type, $entity_type, $fields, $context);

    // $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $query = \Drupal::entityQuery($entity_type)->accessCheck(FALSE);

    $group = $query->orConditionGroup();

    foreach ($fields as $field_id => $data) {
      $group->condition($field_id, $search_string, 'LIKE');
    }

    $query->condition($group);
    $r = $query->execute();

    if (!empty($r)) {
      $context['results']['raw'][$entity_type][$field_type] = $r;
    }
  }

  public static function processResults(&$context) {
    if (isset($context['results']['raw']) && !empty($context['results']['raw'])) {
      foreach ($context['results']['raw'] as $entity_type => $field_types) {
        foreach ($field_types as $results) {
          // ksm($results);
          $method_name = "processResults_" . $entity_type;
          if (method_exists(__CLASS__, $method_name)) {
            TextFieldSearch::$method_name($results, $context);
          }
          else {

            $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
            $entities = $storage->loadMultiple($results);
            // ksm($entities);

            foreach ($entities as $entity) {
              try {
                if (in_array('canonical', $entity->uriRelationships())) {
                  // ksm(current($entities)->toUrl(), current($entities)->uriRelationships());
                  $data = $context['results']['data']['paragraph'][$entity->id()]?? [
                    'url' => $entity->toUrl(),
                    'label' => $entity->label(),
                    'count' => 0,
                  ];
                  $data['count']++;

                  $context['results']['data'][$entity_type][$entity->id()] = $data;
                }
                else {
                  // todo: print error message.
                  $context['results']['errors'][] = "Cannot create link for " . $entity->label();
                }
              }
              catch(\Throwable $e) {
                $context['results']['errors'][] = $e->getMessage();
              }
            }
          }
        }
      }
    }
  }


  public static function processResults_paragraph($results, &$context) {
    $storage = \Drupal::entityTypeManager()->getStorage('paragraph');
    $entities = $storage->loadMultiple($results);

    foreach ($entities as $entity) {
      try {
        $continue = TRUE;
        $loop_count = 0;

        $entity_clone = clone $entity;

        while ($continue) {
          $parent = $entity_clone->getParentEntity();

          if ($parent && in_array('canonical', $parent->uriRelationships())) {
            $continue = FALSE;
          }

          $entity_clone = $parent;
          $loop_count++;
          if ($loop_count > 100) {
            $continue = FALSE;
          }
        }

        $data = $context['results']['data']['paragraph'][$parent->id()]?? [
          'url' => $parent->toUrl(),
          'label' => $parent->label(),
          'count' => 0,
        ];
        $data['count']++;

        $context['results']['data']['paragraph'][$parent->id()] = $data;
      }
      catch (\Throwable $e) {
        $context['results']['errors'][] = $e->getMessage();
      }
    }
  }

  public static function finished_callback($success, $results, $operations, $elapsed) {
    // ksm($success, $results, $operations, $elapsed);
    $_SESSION['bcu_search_results'] = [$success, $results, $operations, $elapsed];
  }

}
