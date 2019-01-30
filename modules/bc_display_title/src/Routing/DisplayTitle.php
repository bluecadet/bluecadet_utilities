<?php

namespace Drupal\bc_display_title\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Entity\EntityInterface;

/**
 * Listens to the dynamic route events.
 */
class DisplayTitle extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Override the title callback function for nodes.
    if ($route = $collection->get('entity.node.canonical')) {
      $route->setDefault('_title_callback', '\Drupal\bc_display_title\Routing\DisplayTitle::getDisplayTitle');
    }
  }

  public function getDisplayTitle(EntityInterface $node) {
    if ($node->hasField('field_display_title')) {
      // Title.
      $title = $node->label();
      $display_title = $node->field_display_title->getValue();

      if (!empty($display_title)) {
        $display_title = current($display_title);
        $title = preg_replace("/<\\/?p(.|\\s)*?>/", "", $display_title['value']);
        return $title;
      }
    }

    return $node->label();
  }
}