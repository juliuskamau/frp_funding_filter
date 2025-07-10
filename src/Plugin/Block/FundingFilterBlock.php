<?php

namespace Drupal\frp_funding_filter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Funding Filter' block.
 *
 * @Block(
 *   id = "funding_filter_block",
 *   admin_label = @Translation("Funding Filter Block"),
 *   category = @Translation("Forms")
 * )
 */
class FundingFilterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $formBuilder;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  public function build() {
    return $this->formBuilder->getForm('\Drupal\frp_funding_filter\Form\FundingFilterForm');
  }
}