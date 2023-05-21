<?php

namespace Drupal\bc_sandbox\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sandbox Controller.
 */
class SandBox extends ControllerBase {

  /**
   * Build a render array.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The page request.
   *
   * @return array
   *   The render array.
   */
  public function build(Request $request):array {

    return [];
  }

}
