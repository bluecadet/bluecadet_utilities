<?php

namespace Drupal\bluecadet_utilities\Form;

use Drupal\bluecadet_utilities\DrupalStateTrait;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Bluecadet Utility Settings Form.
 */
class EntityReferenceFieldSearch extends FormBase {

  use DrupalStateTrait;
  use MessengerTrait;

  /**
   * Drupal Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandler;

  /**
   * Drupal Entity Field Manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * Drupal Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * File system Interface for reading and writing files.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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
   * Get Entity Field Manager.
   */
  private function entityFieldManager(): EntityFieldManager {
    if (!$this->entityFieldManager) {
      $this->entityFieldManager = \Drupal::service('entity_field.manager'); // phpcs:ignore
    }
    return $this->entityFieldManager;
  }

  /**
   * Get Entity Type Manager.
   */
  private function entityTypeManager(): EntityTypeManager {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager(); // phpcs:ignore
    }
    return $this->entityTypeManager;
  }

  /**
   * Get Entity Type Manager.
   */
  private function fileSystem(): FileSystemInterface {
    if (!$this->fileSystem) {
      $this->fileSystem = \Drupal::service('file_system'); // phpcs:ignore
    }
    return $this->fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bcu_search+ent_ref_fields';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $session_data = $_SESSION['bcu_ent_ref_search_results'] ?? [];

    $form['#tree'] = TRUE;

    // Build Entities Select Field.
    $entity_opts = [];
    foreach ($this->entityTypeManager()->getDefinitions() as $id => $ent_def) {
      $entity_opts[$id] = $ent_def->getLabel();
    }

    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t("Entities"),
      '#options' => $entity_opts,
      '#default_value' => $session_data[1]['entity_type'] ?? "",
    ];

    // Search field.
    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Entity Id"),
      '#step' => 1,
      // '#description' => $this->t("This is doing a full string search on the raw html of the text field values. You can use '%' as a wildcard."),
      '#default_value' => $session_data[1]['search_str'] ?? "",
      // '#placeholder' => "%class=\"material-icons\"% OR %<a name=\"%\"></a>%",
    ];

    $form['sep1'] = [
      '#markup' => "<hr>",
    ];

    // Include JSON output file.
    $form['json'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("JOSN output"),
      '#step' => 1,
      '#description' => $this->t("Should the form create an output file of the raw data?"),
      '#default_value' => FALSE,
    ];

    // Actions.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    // Build outpur results from previous run.
    $striper = [
      'transparent' => '#eeeeee',
      '#eeeeee' => 'transparent',
    ];
    $current_stripe = "#eeeeee";

    if (!empty($session_data)) {
      $form['results'] = [
        '#weight' => -1,
        'timing' => [
          '#prefix' => "<p>",
          '#markup' => "Results took " . $session_data[3],
          '#suffix' => "</p>",
        ],
        'download' => [],
        'results' => [],
      ];

      // Add in file download link if available.
      if (!empty($session_data[1]['file_link'])) {
        $form['results']['download']['#prefix'] = "<p>";
        $form['results']['download']['#suffix'] = "</p>";
        $form['results']['download']['download_data'] = $session_data[1]['file_link']->toRenderable();
      }

      foreach ($session_data[1]['data'] as $entity_type => $data) {

        $list = [
          '#theme' => 'item_list',
          '#title' => 'Results for: ' . $entity_type,
          '#items' => [],
        ];
        $current_stripe = "#eeeeee";

        foreach ($data as $id => $result_data) {

          $link = Link::fromTextAndUrl($result_data['label'], $result_data['url']);
          $list['#items'][] = [
            '#wrapper_attributes' => [
              'style' => "padding: 1em;background-color:" . $current_stripe,
            ],
            [
              '#markup' => "<p>Found " . $this->formatPlural($result_data['count'], "1 time", "@count times") . " on " . $link->toString() . "</p>",
            ],
            [
              '#theme' => 'item_list',
              '#title' => 'Fields:',
              '#items' => $result_data['fields'],
            ],
          ];
          $current_stripe = $striper[$current_stripe];
        }

        $form['results']['results'][] = $list;
      }
    }

    unset($_SESSION['bcu_ent_ref_search_results']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $batch = [
      'title' => $this->t('Searching...'),
      'operations' => [
        [
          [$this, 'setUpContext'],
          [$values['search'], $values['entity_type'], $form_state],
        ],
      ],
      'finished' => [$this, 'finishedCallback'],
    ];

    $fields = EntityReferenceFieldSearch::findFields($form_state);

    foreach ($fields as $field_type => $field_data) {
      foreach ($field_data as $entity_type => $fields2) {
        foreach ($fields2 as $f => $fdata) {
          $batch['operations'][] = [
            [$this, 'searchEntityRefFields'],
            [
              $field_type,
              $entity_type,
              [
                $f => $fdata,
              ],
              $values['search'],
            ],
          ];
        }
      }
    }

    $batch['operations'][] = [
      [$this, 'processResults'],
      [],
    ];

    if ($values['json'] == TRUE) {
      $batch['operations'][] = [
        [$this, 'createJsonOutput'],
        [],
      ];
    }

    batch_set($batch);
  }

  /**
   * Run the process, to look for viable fields to search.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Drupal's forms tate obj.
   *
   * @return array
   */
  public static function findFields(FormStateInterface $form_state): array {
    $values = $form_state->getValues();

    $entity_field_manager = \Drupal::service('entity_field.manager');  // phpcs:ignore
    $field_map = $entity_field_manager->getFieldMap();

    $field_type = [
      "entity_reference",
      "entity_reference_revisions",
      "entity_reference_entity_modify",
    ];

    $fields = [];
    foreach ($field_map as $entity => $entity_field_data) {
      foreach ($entity_field_data as $field_id => $field_data) {
        if (in_array($field_data['type'], $field_type)) {

          $field_storage_defs = $entity_field_manager->getFieldStorageDefinitions($entity);

          if (isset($field_storage_defs[$field_id])) {
            $field_storage_settings = $field_storage_defs[$field_id]->getSettings();
            if ($field_storage_settings['target_type'] == $values['entity_type']) {
              $fields[$field_data['type']][$entity][$field_id] = $field_data;
            }
          }
        }
      }
    }

    return $fields;
  }

  /**
   * Batch process to setup the $context array.
   */
  public static function setUpContext($search_string, $entity_type, $form_state, &$context) {
    $context['results']['search_str'] = $search_string;
    $context['results']['entity_type'] = $entity_type;
    $context['results']['raw'] = [];
    $context['results']['data'] = [];
    $context['results']['errors'] = [];
    $context['results']['form_state'] = $form_state;
  }

  /**
   * Batch Search process for each entity and field type combo.
   */
  public static function searchEntityRefFields($field_type, $entity_type, $fields, $search_string, &$context) {
    $query = \Drupal::entityQuery($entity_type)->accessCheck(FALSE);

    $group = $query->orConditionGroup();

    foreach ($fields as $field_id => $data) {
      $group->condition($field_id, $search_string, 'LIKE');
    }

    $query->condition($group);
    $r = $query->execute();

    if (!empty($r)) {
      $context['results']['raw'][$entity_type][$field_type][$field_id] = $r;
    }
  }

  /**
   * Process the results of all the queries.
   */
  public static function processResults(&$context) {

    if (!empty($context['results']['raw'])) {
      foreach ($context['results']['raw'] as $entity_type => $field_types) {
        foreach ($field_types as $fields) {
          foreach ($fields as $field_id => $results) {
            $method_name = "processResults_" . $entity_type;
            if (method_exists(__CLASS__, $method_name)) {
              EntityReferenceFieldSearch::$method_name($results, $field_id, $context);
            }
            else {

              $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
              $entities = $storage->loadMultiple($results);

              foreach ($entities as $entity) {
                try {
                  if (in_array('canonical', $entity->uriRelationships())) {
                    $data = $context['results']['data'][$entity_type][$entity->id()] ?? [
                      'url' => $entity->toUrl(),
                      'label' => $entity->label(),
                      'count' => 0,
                      'fields' => [],
                    ];
                    $data['count']++;

                    if (!in_array($field_id, $data['fields'])) {
                      $data['fields'][] = $field_id;
                    }

                    $context['results']['data'][$entity_type][$entity->id()] = $data;
                  }
                  else {
                    $context['results']['errors'][] = "Cannot create link for " . $entity->label();
                  }
                }
                catch (\Throwable $e) {
                  $context['results']['errors'][] = $e->getMessage();
                }
              }
            }
          }
        }
      }
    }
  }

  // phpcs:disable Drupal.NamingConventions.ValidFunctionName.ScopeNotCamelCaps

  /**
   * Process query results for paragraph entities.
   *
   * We have to separate this out b/c we need to look for its parent entity to
   * create a link to it.
   */
  public static function processResults_paragraph($results, $field_id, &$context) {
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

        $data = $context['results']['data']['paragraph'][$parent->id()] ?? [
          'url' => $parent->toUrl(),
          'label' => $parent->label(),
          'count' => 0,
          'fields' => [],
        ];
        $data['count']++;
        if (!in_array($field_id, $data['fields'])) {
          $data['fields'][] = $field_id;
        }

        $context['results']['data']['paragraph'][$parent->id()] = $data;
      }
      catch (\Throwable $e) {
        $context['results']['errors'][] = $e->getMessage();
      }
    }
  }

  // phpcs:enable Drupal.NamingConventions.ValidFunctionName.ScopeNotCamelCaps

  /**
   * Out put raw data to a JSON file.
   *
   * @todo need a cron job to clean up old files.
   */
  public static function createJsonOutput(&$context) {

    if ($context['results']['raw']) {
      $file_system = \Drupal::service('file_system');
      // @todo add to a settings config page.
      $destination = "public://data_search_exports/";
      // Set json data from the raw results.
      $data = json_encode($context['results']['raw']);

      $search_str_sanatized = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $context['results']['search_str']);
      $search_str_sanatized = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $search_str_sanatized);

      $filename = date("YmdHis") . "--" . $search_str_sanatized . "--" . "ent_ref_search.json";

      if (!$file_system->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY)) {
        // @todo Log an error.
        return FALSE;
      }

      $finale_file = $file_system->saveData($data, $destination . $filename, FileSystemInterface::EXISTS_REPLACE);

      if ($finale_file) {
        // Create temporary File entity.
        // We do this so Drupal will clean up the file eventually.
        $new_file = File::create(['uri' => $finale_file]);
        $new_file->setOwnerId(1);
        $new_file->setTemporary();
        $new_file->save();

        $new_file_url = URL::fromUserInput($new_file->createFileUrl(), [
          'attributes' => [
            'download' => TRUE,
          ],
        ]);

        $link = Link::fromTextAndUrl("Download JSON Data", $new_file_url);

        $context['results']['file_file'] = $new_file;
        $context['results']['file_link'] = $link;
      }
    }
  }

  /**
   * Batch finished callback.
   */
  public static function finishedCallback($success, $results, $operations, $elapsed) {

    $results['form_state']->setStorage([
      'raw' => $results['raw'],
    ]);

    // Just add all results to the session var to let the form render results.
    // todo change this over to use form_state storage.
    $_SESSION['bcu_ent_ref_search_results'] = [
      $success,
      $results,
      $operations,
      $elapsed,
    ];
  }

}
