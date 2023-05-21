<?php

namespace Drupal\bc_display_title\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Entity\FieldableEntityInterface;

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

  /**
   * Determines display title from the node title or field.
   *
   * @param Drupal\Core\Entity\FieldableEntityInterface $node
   *   The Node to find the title of.
   *
   * @return string
   *   Return the string for the title.
   */
  public function getDisplayTitle(FieldableEntityInterface $node) {
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
