<?php

namespace Drupal\ausy_event_registration;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A custom service 'ausy_event_registration.registration_manager'.
 */
class RegistrationManager {

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a registration service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to use.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Returns the number of registered users.
   */
  public function count() {
    $query = $this->connection->select('node__field_department', 'nfd')
    ->condition('nfd.bundle', 'registration')
    ->isNotNull('field_department_target_id');
    $count = $query->countQuery()->execute()->fetchField();

    return $count;
  }
}
