<?php

namespace Drupal\frp_funding_filter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic funding opportunity filter form.
 */
class FundingFilterForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FundingFilterForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'frp_funding_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load taxonomy terms for regions and categories.
    $region_terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadTree('region');
    $category_terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadTree('funding_category');

    // Build dropdown options.
    $region_options = ['' => $this->t('All')];
    foreach ($region_terms as $term) {
      $region_options[$term->tid] = $term->name;
    }

    $category_options = ['' => $this->t('All')];
    foreach ($category_terms as $term) {
      $category_options[$term->tid] = $term->name;
    }

    // Form elements.
    $form['region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#options' => $region_options,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => $category_options,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#ajax' => [
        'callback' => '::ajaxFilterResults',
        'wrapper' => 'funding-results',
      ],
    ];

    $form['results'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'funding-results'],
      '#markup' => $this->t('Select filters to view opportunities.'),
    ];

    return $form;
  }

  /**
   * AJAX callback to return filtered results.
   */
  public function ajaxFilterResults(array &$form, FormStateInterface $form_state) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'funding_opportunity')
      ->condition('status', 1);

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
      $results[] = $node->toLink()->toString();
    }

    $form['results']['#markup'] = $results 
      ? implode('<br>', $results) 
      : $this->t('No opportunities found.');

    return $form['results'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No traditional submit needed for AJAX forms.
  }
}