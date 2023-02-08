<?php

namespace Drupal\ausy_event_registration\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * An 'ausy_registration_form' form object.
 */
class RegistrationForm extends FormBase {

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
    return 'ausy_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $department = NULL) {
    $form['employee_name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name of the employee'),
      '#required' => TRUE,
    ];

    $form['one_plus'] = [
      '#type' => 'radios',
      '#title' => $this
        ->t('One plus'),
      '#options' => array(
        0 => $this
          ->t('No'),
        1 => $this
          ->t('Yes'),
      ),
      '#required' => TRUE,
    ];

    $form['amount_of_kids'] = [
      '#type' => 'number',
      '#title' => $this
        ->t('Amount of kids'),
      '#required' => TRUE,
    ];

    $form['amount_of_vegetarians'] = [
      '#type' => 'number',
      '#title' => $this
        ->t('Amount of vegetarians'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this
        ->t('Email address'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this
        ->t('Register'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $onePlus = $form_state->getValue('one_plus');
    $amountOfKids = $form_state->getValue('amount_of_kids');
    $amountOfVegetarians = $form_state->getValue('amount_of_vegetarians');

    if ($this->isRegistred($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('Email address already registered.'));
    }

    if ($amountOfVegetarians > $this->amountOfPeople($onePlus, $amountOfKids)) {
      $form_state->setErrorByName('email', $this->t('Amount of vegetarians can not be higher than the total amount of people.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $employeeName = $form_state->getValue('employee_name');
    $onePlus = $form_state->getValue('one_plus');
    $amountOfKids = $form_state->getValue('amount_of_kids');
    $amountOfVegetarians = $form_state->getValue('amount_of_vegetarians');
    $email = $form_state->getValue('email');
    $department = $this->routeMatch->getParameter('department');

    $registrationNode = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'registration',
      'title' => $employeeName,
      'field_name_of_the_employee' => $employeeName,
      'field_one_plus' => $onePlus,
      'field_amount_of_kids' => $amountOfKids,
      'field_amount_of_vegetarians' => $amountOfVegetarians,
      'field_email_address' => $email,
    ]);

    if ($this->getDepartment($department)) {
      $registrationNode->set('field_department', $this->getDepartment($department));
    }

    $registrationNode->enforceIsNew();
    $registrationNode->save();
  }

  /**
   * Checks a node (CT Registration) by email.
   *
   * @param string $email
   *  Email address.
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function isRegistred($email) {
    $isRegistred = FALSE;

    $registrationNode = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'registration',
      'field_email_address' => $email,
    ]);

    if ($registrationNode) {
      $isRegistred = TRUE;
    }

    return $isRegistred;
  }

  /**
   * Counts amount of people that register on an event.
   *
   * @param bool $onePlus
   * @param integer $amountOfKids
   * @return int
   */
  public function amountOfPeople($onePlus, $amountOfKids) {
    $amountOfPeople = 1 + $amountOfKids;

    if ($onePlus == 1) {
      $amountOfPeople++;
    }

    return $amountOfPeople;
  }

  /**
   * Returns a department term id.
   *
   * @param string $department
   *  Department machine_name.
   *
   * @return int|string|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getDepartment($department) {
    $termID = NULL;
    $departments = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => 'department',
        'field_machine_name' => $department,
      ]);

    if ($departments) {

      foreach ($departments as $departmentTerm) {
        $termID = $departmentTerm->id();
      }
    }

    return $termID;
  }

}
