<?php

namespace Drupal\bluecadet_utilities\Controller;

use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityViewBuilderInterface;

class ViewDisplayModes extends ControllerBase {

    /**
    * {@inheritdoc}
    */
    public function __construct(EntityDisplayRepository $entity_display_repository, EntityViewBuilderInterface $entity_view_builder)
    {
        $this->entityDisplayRepository = $entity_display_repository;
        $this->viewBuilder = $entity_view_builder;
    }

    /**
    * {@inheritdoc}
    */
    public static function create(ContainerInterface $container) {
        $entity_display_repository = $container->get('entity_display.repository');
        $entity_type_manager = $container->get('entity_type.manager');
        return new static(
            $entity_display_repository,
            $entity_view_builder = $entity_type_manager->getViewBuilder('node')
        );
    }

    /**
    * {@inheritdoc}
    */
    public function viewAllDisplayModes($node) {
        $build = [];
        $entity_type_id = $node->getEntityTypeId();
        $bundle = $node->bundle();
        $view_modes = $this->entityDisplayRepository->getViewModes($entity_type_id);
        $view_modes_in_use = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity_type_id, $bundle);

        foreach ($view_modes as $key => $view_mode) {
          foreach ($view_modes_in_use as $key_in_use => $view_mode_in_use) {
            if ($key == $key_in_use) {
              $build[$key] = array (
                "#prefix" => '<div class="view-mode view-mode-'.$key.'"><h2 class="f-sub-title view-mode">'.$view_mode['label'].'</h2>',
                "#suffix" => '</div>',
                'build' => $this->viewBuilder->view($node, $key)
              );
            }
          }
        }
        $build['#prefix'] = '<div class="view-modes-wrapper">';
        $build['#suffix'] = '</div>';
        return $build;
    }
}