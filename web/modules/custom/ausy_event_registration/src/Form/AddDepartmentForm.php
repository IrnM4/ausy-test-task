<?php

namespace Drupal\ausy_event_registration\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An 'ausy_registration_form' form object.
 */
class AddDepartmentForm extends FormBase {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RegistrationForm object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ausy_add_department_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $department = NULL) {
    $form['department_name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Department name'),
      '#required' => TRUE,
    ];

    $form['department_machine_name'] = [
      '#type' => 'machine_name',
      '#required' => TRUE,
      '#maxlength' => 64,
      '#description' => $this
        ->t('A unique name for this department. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this
        ->t('Save Department'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $department = Html::escape($form_state->getValue('department_machine_name'));

    if ($this->departmentExists($department)) {
      $form_state->setErrorByName('department_machine_name', $this->t('The machine name already exists.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $departmentName = Html::escape($form_state->getValue('department_name'));
    $department = Html::escape($form_state->getValue('department_machine_name'));

    $newDepartment = $this->entityTypeManager->getStorage('taxonomy_term')->create([
      'vid' => 'department',
      'name' => $departmentName,
      'field_machine_name' => $department,
    ]);
    $newDepartment->save();
  }

  /**
   * Checks if a department exists by the field field_machine_name.
   *
   * @param string $department
   *  Department machine name.
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function departmentExists($department) {
    $termID = FALSE;
    $departments = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => 'department',
        'field_machine_name' => $department,
      ]);

    if ($departments) {
      $termID = TRUE;
    }

    return $termID;
  }

}
