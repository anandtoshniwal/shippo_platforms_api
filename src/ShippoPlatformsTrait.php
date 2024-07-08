<?php

namespace Drupal\shippo_platforms_api;

/**
 * Trait for patternry shippo api.
 */
trait ShippoPlatformsTrait {

  /**
   * Initialise API version.
   *
   * @var string
   */
  protected static $apiVersion = '2018-02-08';

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * API Key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * API URL.
   *
   * @var string
   */
  protected $apiUrl;

  /**
   * Mode.
   *
   * @var string
   */
  protected $mode;

  /**
   * Tracking number.
   *
   * @var string
   */
  protected $trackingStatus;


  /**
   * Tracking number.
   *
   * @var string
   */
  protected $trackingNumber;

  /**
   * Enable Log.
   *
   * @var string
   */
  protected $enableLog;

  /**
   * Request to set api credentials.
   */
  public function setApiCredentials() {
    $this->configFactory = \Drupal::service('config.factory');
    $this->apiUrl = $this->configFactory->get('patternry_shippo_api.settings')->get('api_url');
    $this->apiKey = $this->configFactory->get('patternry_shippo_api.settings')->get('api_key');
    $this->mode = $this->configFactory->get('patternry_shippo_api.settings')->get('mode');
    $this->trackingStatus = $this->configFactory->get('patternry_shippo_api.settings')->get('tracking_status');
    $this->trackingNumber = $this->configFactory->get('patternry_shippo_api.settings')->get('tracking_number');
    $this->enableLog = $this->configFactory->get('patternry_shippo_api.settings')->get('enable_log');
  }

  /**
   * Check the calling api's base url is https however set https in calling api.
   *
   * @param string $request_url
   *   A request url of calling api.
   *
   * @return string
   *   The Https protocol request_url.
   */
  public function checkRequestProtocol($request_url) {
    $requestUrl = $request_url;
    if (substr($request_url, 0, 8) !== "https://") {
      $requestUrl = 'https://' . $requestUrl;
    }
    return $requestUrl;
  }

  /**
   * Request to check api credentials set or not.
   */
  public function checkApiCredentials() {
    if (empty($this->apiUrl) || empty($this->apiKey)) {
      $credentialsUrl = '<a href="/admin/config/patternry-shippo-api/info/settings"> Click here </a>';
      $msg = 'API credentials are missing' . $credentialsUrl . 'to set the credentials of shippo api.';
      \Drupal::logger('patternry_shippo_api')->warning('<pre><code>' . print_r($msg, TRUE) . '</code></pre>');
    }
    else {
      return TRUE;
    }
  }

  /**
   * Request to print logs.
   */
  public function displayLogs($endpoint, $postFields, $data) {
    if (!empty($this->enableLog['true']) && $this->enableLog['true'] == 'true') {
      \Drupal::logger('patternry_shippo_api')->notice('A making call to @endpoint with data @data Result: @result', [
        '@endpoint' => $endpoint,
        '@data' => $postFields,
        '@result' => $data,
      ]);
    }
  }

  /**
   * Request to set request parameters.
   */
  public function setRequestParams($requestUrl, $params) {
    if (count($params) > 0) {
      $requestUrl = $requestUrl . '?';
    }
    foreach ($params as $key => $value) {
      $requestUrl .= '&' . $key . '=' . $value;
    }
    return $requestUrl;
  }

}
