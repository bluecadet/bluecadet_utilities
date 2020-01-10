<?php

namespace Drupal\bc_api_example\Controller;

use Drupal\node\Entity\Node;
use Drupal\bc_api_base\Controller\ApiControllerBase;

/**
 * Example API Controller Class.
 */
class ApiControllerExample extends ApiControllerBase {

  /**
   * {@inheritdoc}
   */
  public function getApiCacheTime($id = NULL) {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheId() {
    $cid = "SOMETHING_UNIQUE";

    if (!empty($this->params)) {
      $cid .= ":" . implode(":", $this->params);
    }

    $cid .= ":page-" . $this->page;
    $cid .= ":limit-" . $this->limit;

    return $cid;
  }

  /**
   * {@inheritdoc}
   */
  public function initCacheTags() {
    $this->cacheTags = [
      'myAwesomeCoolCacheTag',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setParams() {
    $this->privateParams['status'] = 1;

    // Here we also want to handle any bad param errors...
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultPlatform() {
    return "cinder";
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceData() {
    // This method should be overridden for any endpoint.
    $data = [];
    $query = \Drupal::entityQuery('node');
    $query->condition('status', $this->privateParams['status']);

    $count_query = clone $query;

    $query->range(($this->page * $this->limit), $this->limit);
    $entity_ids = $query->execute();

    // Must set total result count so we can properly page.
    $this->resultTotal = $count_query->count()->execute();

    // Process Items.
    $nodes = Node::loadMultiple($entity_ids);

    foreach ($nodes as $node) {
      $this->cacheTags = array_merge($this->cacheTags, $node->getCacheTags());
      $item = [
        'nid' => (int) $node->id(),
        // 'field_sync_id' => $this->transformer->textFieldVal($node, 'field_sync_id'),
      ];

      $data[] = $item;
    }

    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function buildLinks() {
    // This method should be overridden for any endpoint.
    $base_url = $this->request->getSchemeAndHttpHost() . $this->request->getPathInfo();
    $tmp_query_params = $this->params;
    $tmp_query_params['platform'] = $this->platform;
    $tmp_query_params['limit'] = $this->limit;

    if ($this->page == 0) {
      $this->prev = "";
    }
    else {
      $tmp_query_params['page'] = $this->page - 1;
      $this->prev = $base_url . "?" . http_build_query($tmp_query_params);
    }

    if ($this->resultTotal > (($this->page + 1) * $this->limit)) {
      $tmp_query_params['page'] = $this->page + 1;
      $this->next = $base_url . "?" . http_build_query($tmp_query_params);
    }
    else {

      $this->next = "";
    }

  }

}
