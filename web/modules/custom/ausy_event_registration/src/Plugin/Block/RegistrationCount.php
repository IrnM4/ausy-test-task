<?php

namespace Drupal\ausy_event_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an RegistrationCount block.
 *
 * @Block(
 *   id = "ausy_registration_count_block",
 *   admin_label = @Translation("Registration count"),
 *   category = @Translation("AUSY Event Registration")
 * )
 */
class RegistrationCount extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $count = \Drupal::service('ausy_event_registration.registration_manager')->count();

    $build['content'] = [
      '#markup' => $this->t('Registered users: @count', ['@count' => $count]),
    ];

    return $build;
  }

  public function blockForm($form, FormStateInterface $form_state) {
    return $form;
  }

}
