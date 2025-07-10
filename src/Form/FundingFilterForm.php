<?php

namespace Drupal\frp_funding_filter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a funding opportunity filter form.
 */
class FundingFilterForm extends FormBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'frp_funding_filter_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Region dropdown
    $form['region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#options' => $this->getTaxonomyOptions('region'),
      '#empty_option' => $this->t('- All -'),
    ];

    // Category dropdown
    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => $this->getTaxonomyOptions('funding_category'),
      '#empty_option' => $this->t('- All -'),
    ];

    // Submit button with AJAX
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#ajax' => [
        'callback' => '::ajaxFilterResults',
        'wrapper' => 'funding-results',
      ],
    ];

    // Results container
    $form['results'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'funding-results'],
      '#markup' => '<div class="placeholder-text">' . $this->t('Select filters to view opportunities.') . '</div>',
    ];

    // Attach CSS for styling
    $form['#attached']['library'][] = 'frp_funding_filter/filter_styles';

    return $form;
  }

  protected function getTaxonomyOptions($vocabulary) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadTree($vocabulary);
    
    $options = [];
    foreach ($terms as $term) {
      $options[$term->tid] = $term->name;
    }
    return $options;
  }

  public function ajaxFilterResults(array &$form, FormStateInterface $form_state) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'funding_opportunity')
      ->condition('status', 1)
      ->accessCheck(TRUE);

    if ($region = $form_state->getValue('region')) {
      $query->condition('field_region', $region);
    }
    if ($category = $form_state->getValue('category')) {
      $query->condition('field_category', $category);
    }

    $nids = $query->execute();
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $results = [];
    foreach ($nodes as $node) {
      $results[] = [
        '#type' => 'link',
        '#title' => $node->label(),
        '#url' => $node->toUrl(),
      ];
    }

    if (empty($results)) {
      return [
        '#markup' => '<div class="no-results">' . $this->t('No opportunities match your criteria.') . '</div>',
      ];
    }

    return [
      '#theme' => 'item_list',
      '#items' => $results,
      '#attributes' => ['class' => ['funding-opportunities-list']],
    ];
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Optional: Add non-AJAX submission handling if needed
  }
}