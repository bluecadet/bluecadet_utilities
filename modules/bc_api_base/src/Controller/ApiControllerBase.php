<?php

namespace Drupal\bc_api_base\Controller;

use Drupal\bc_api_base\AssetApiService;
use Drupal\bc_api_base\ValueTransformationService;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API Controll Base.
 *
 * This includes:
 * - platform flags
 * - Connection to Image service
 * That is all.
 */
class ApiControllerBase extends ControllerBase implements ApiControllerInterface {

  /**
   * The initial Request Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request = 0;

  /**
   * Connection to the image service.
   *
   * @var \Drupal\bc_api_base\AssetApiService
   */
  protected $assetService;

  /**
   * Connection to the image service.
   *
   * @var \Drupal\bc_api_base\ValueTransformationService
   */
  protected $transformer;

  /**
   * Cache service to use to cache data related to this endpoint.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Requested or default platform.
   *
   * @var string
   */
  protected $platform = '';

  /**
   * Processed Data.
   *
   * @var array
   */
  protected $data = [];

  /**
   * Processed CacheTags.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * QueryParams.
   *
   * @var array
   */
  protected $params = [];

  /**
   * Params not exposed in the url.
   *
   * These are static for this endpoint or derived through code. Not alterable
   * through the url.
   *
   * @var array
   */
  protected $privateParams = [];

  /**
   * Page of results we are on.
   *
   * @var int
   */
  protected $page = 0;

  /**
   * Limit the number of results.
   *
   * We set this high because we don't normally page results.
   *
   * @var int
   */
  protected $limit = 500;

  /**
   * Total number of possible results.
   *
   * @var int
   */
  protected $resultTotal = 0;

  /**
   * Limit the number of results.
   *
   * @var int
   */
  protected $prev = "";

  /**
   * Limit the number of results.
   *
   * @var int
   */
  protected $next = "";

  /**
   * Specific resource request.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $resource = NULL;

  /**
   * Current Route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute = NULL;

  /**
   * Class constructor.
   */
  public function __construct(AssetApiService $assetService, ValueTransformationService $transformer, CacheBackendInterface $cache, CurrentRouteMatch $current_route) {
    $this->assetService = $assetService;
    $this->initCacheTags();
    $this->transformer = $transformer;
    $this->cache = $cache;
    $this->currentRoute = $current_route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('bc_api_base.asset'),
      $container->get('bc_api_base.valueTransformer'),
      $container->get('cache.bc_api_base'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function initCacheTags() {}

  /**
   * {@inheritdoc}
   */
  public function getDefaultPlatform() {
    return "default";
  }

  /**
   * {@inheritdoc}
   */
  public function setPlatform() {
    // Check if there’s a platform parameter.
    $platform = $this->request->query->get('platform');
    if ($platform == NULL) {
      $platform = $this->getDefaultPlatform();
    }

    $this->platform = $platform;
    $this->transformer->setPlatform($platform);
  }

  /**
   * {@inheritdoc}
   */
  public function setParams() {

    // Set values if we are asking for a specific resource.
    $parameters = $this->currentRoute->getParameters()->all();

    if (isset($parameters['nid'])) {
      $this->privateParams['nid'] = $parameters['nid']->id();
      $this->resource = $parameters['nid'];
    }

    // Add in debugging.
    $this->privateParams['debug'] = filter_var($this->request->get('debug'), FILTER_VALIDATE_BOOLEAN);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheId() {
    $cid = get_class($this);

    if (!empty($this->params)) {
      foreach ($this->params as $key => $param) {
        $cid .= ":" . $key . "=" . $param;
      }
    }

    $cid .= ":page-" . $this->page;
    $cid .= ":limit-" . $this->limit;

    if (!empty($this->privateParams)) {
      foreach ($this->privateParams as $key => $param) {
        $cid .= ":" . $key . "=" . $param;
      }
    }

    return $cid;
  }

  /**
   * {@inheritdoc}
   */
  public function getApiCacheTime($id) {

    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(Request $request) {
    $this->request = $request;
    // Set Page.
    $this->page = ($request->query->get('page')) ? $request->query->get('page') : $this->page;

    // Set Limit.
    $this->limit = ($request->query->get('limit')) ? $request->query->get('limit') : $this->limit;

    // Check if there’s a platform parameter.
    $this->setPlatform($request);

    $this->setParams($request);

    $cid = $this->getCacheId();

    $cache_time = $this->getApiCacheTime();

    $this->return_data = [
      'status' => 200,
      'data' => [],
      'resultTotal' => 0,
      'pageCount' => 0,
      'prev' => '',
      'next' => '',
    ];

    if ($cache = $this->cache->get($cid)) {
      $this->return_data = $cache->data;
    }
    else {

      $this->getResourceData();
      $this->buildLinks();

      $this->return_data['data'] = $this->data;
      $this->return_data['resultTotal'] = $this->resultTotal;
      $this->return_data['pageCount'] = $this->page;
      $this->return_data['prev'] = $this->prev;
      $this->return_data['next'] = $this->next;

      $this->cache->set($cid, $this->data, (time() + $cache_time), $this->cacheTags);
    }

    if ($this->privateParams['debug']) {
      ksm($this->return_data);
      return [];
    }

    return new JsonResponse($this->return_data);
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceData() {
    $this->data = [];
    $this->resultTotal = 0;
    $this->pageCount = 0;
    $this->prev = NULL;
    $this->next = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildLinks() {
    $this->prev = "";
    $this->next = "";
  }

}
