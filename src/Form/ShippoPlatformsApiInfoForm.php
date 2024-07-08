<?php

namespace Drupal\shippo_platforms_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the class.
 *
 * @internal
 */
class ShippoPlatformsApiInfoForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'shippo_platforms_api.settings',
    ];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'shippo_paltforms_api_info_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('shippo_platforms_api.settings');
    $apiUrl = $config->get('api_url');
    $apiKey = $config->get('api_key');
    $mode = $config->get('mode');
    $enable_log = $config->get('enable_log');
    $tracking_status = $config->get('tracking_status');
    $tracking_number = $config->get('tracking_number');

    $form['shippo_platforms_api'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Patternry Shippo API Details'),
    ];
    $form['shippo_platforms_api']['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter API url'),
      '#default_value' => $apiUrl ?? '',
      '#required' => TRUE,
    ];
    $form['shippo_platforms_api']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter API Key'),
      '#default_value' => $apiKey ?? '',
      '#required' => TRUE,
    ];

    $form['shippo_platforms_api']['mode'] = [
      '#type' => 'checkboxes',
      '#options' => ['test' => $this->t('Test')],
      '#title' => $this->t('Mode'),
      '#default_value' => $mode ?? '',
    ];

    $form['patternry_shippo_api']['enable_log'] = [
      '#type' => 'checkboxes',
      '#options' => ['true' => $this->t('Yes')],
      '#title' => $this->t('Enable Logging'),
      '#default_value' => $enable_log ?? '',
    ];

    $tracking_status_list = [
      'SHIPPO_PRE_TRANSIT' => 'SHIPPO PRE TRANSIT',
      'SHIPPO_TRANSIT' => 'SHIPPO TRANSIT',
      'SHIPPO_DELIVERED' => 'SHIPPO DELIVERED',
      'SHIPPO_RETURNED' => 'SHIPPO RETURNED',
      'SHIPPO_FAILURE' => 'SHIPPO FAILURE',
      'SHIPPO_UNKNOWN' => 'SHIPPO UNKNOWN',
    ];

    $form['shippo_platforms_api']['tracking_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Tracking status'),
      '#default_value' => $tracking_status ?? '',
      '#options' => $tracking_status_list,
    ];

    $form['shippo_platforms_api']['tracking_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shippo label track number'),
      '#default_value' => $tracking_number ?? '',
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Configuration'),
      '#button_type' => 'primary',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    /*update the config file*/
    $this->config('shippo_platforms_api.settings')
      ->set('api_url', $form_state->getValue('api_url'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('mode', $form_state->getValue('mode'))
      ->set('enable_log', $form_state->getValue('enable_log'))
      ->set('tracking_status', $form_state->getValue('tracking_status'))
      ->set('tracking_number', $form_state->getValue('tracking_number'))
      ->save();
  }

}
