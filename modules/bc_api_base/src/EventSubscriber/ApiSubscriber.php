<?php

namespace Drupal\bc_api_base\EventSubscriber;

use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\State\State;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Redirect 403 to User Login event subscriber.
 */
class ApiSubscriber extends HttpExceptionSubscriberBase {

  /**
   * Url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Drupal State obj.
   *
   * @var Drupal\Core\State\State
   */
  private $drupalState = [];

  /**
   * Drupal Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a new R4032LoginSubscriber.
   */
  public function __construct(UrlGeneratorInterface $url_generator, State $drupal_state, LoggerChannelFactoryInterface $logger) {
    $this->urlGenerator = $url_generator;
    $this->drupalState = $drupal_state;
    $this->logger = $logger->get("API");
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    return 250;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function onException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();

    // Make the exception available for example when rendering a block.
    $request = $event->getRequest();
    $request->attributes->set('exception', $exception);

    $handled_formats = $this->getHandledFormats();

    $format = $request->query->get(MainContentViewSubscriber::WRAPPER_FORMAT, $request->getRequestFormat());

    if ($exception instanceof HttpExceptionInterface && empty($handled_formats) || in_array($format, $handled_formats)) {
      $method = 'on' . $exception->getStatusCode();
      // Keep just the leading number of the status code to produce either a
      // on400 or a 500 method callback.
      $method_fallback = 'on' . substr($exception->getStatusCode(), 0, 1) . 'xx';
      // We want to allow the method to be called and still not set a response
      // if it has additional filtering logic to determine when it will apply.
      // It is therefore the method's responsibility to set the response on the
      // event if appropriate.
      if (method_exists($this, $method)) {
        $this->$method($event);
      }
      elseif (method_exists($this, $method_fallback)) {
        $this->$method_fallback($event);
      }
    }

    if ($exception instanceof ParamNotConvertedException) {
      $request = $event->getRequest();

      if (strpos($request->getRequestUri(), "/api/") === 0 || $request->getRequestUri() == "/api") {
        $data = [
          'status' => '404',
          'error_msg' => 'Not Found',
          'full_msg' => $exception->getMessage(),
        ];
        $response = new JsonResponse($data);
        $event->setResponse($response);
      }
    }
  }

  /**
   * Redirects on 403 Access Denied kernel exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function on403(GetResponseEvent $event) {

    $request = $event->getRequest();
    $exception = $event->getException();

    if (strpos($request->getRequestUri(), "/api/") === 0 || $request->getRequestUri() == "/api") {

      $data = [
        'status' => $exception->getStatusCode(),
        'error_msg' => 'Access Denied',
      ];
      $response = new JsonResponse($data);
      $event->setResponse($response);
    }

  }

  /**
   * Redirects on 404 Not Found kernel exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function on404(GetResponseEvent $event) {

    $request = $event->getRequest();
    $exception = $event->getException();

    if (strpos($request->getRequestUri(), "/api/") === 0 || $request->getRequestUri() == "/api") {
      $data = [
        'status' => $exception->getStatusCode(),
        'error_msg' => 'Not Found',
        'full_msg' => $exception->getMessage(),
      ];
      $response = new JsonResponse($data);
      $event->setResponse($response);
    }

  }

}
