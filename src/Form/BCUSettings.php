<?php

namespace Drupal\bluecadet_utilities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Bluecadet Utility Settings Form.
 */
class BCUSettings extends ConfigFormBase {

  const SETTINGS = 'bluecadet_utilities.settings';

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;


  /**
   * Class constructor.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bcu_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $settings = \Drupal::state()->get('bluecadet_utilities.settings', array());
    $config = $this->config(static::SETTINGS);

    // $form['#tree'] = TRUE;

    $form['use_transliteration'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Enable File name transliteration"),
      '#default_value' => $config->get('use_transliteration'),
    ];

    $form['use_textfield_wysiwyg'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Use WYSIWYG on textfields"),
      '#default_value' => $config->get( 'use_textfield_wysiwyg'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Retrieve the configuration
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting
      ->set( 'use_transliteration', $form_state->getValue( 'use_transliteration'))
      ->set( 'use_textfield_wysiwyg', $form_state->getValue( 'use_textfield_wysiwyg'))
      ->save();

    parent::submitForm($form, $form_state);

    $this->messenger->addMessage('You have saved your settings.');
  }
}
