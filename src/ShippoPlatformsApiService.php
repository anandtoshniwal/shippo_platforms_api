<?php

namespace Drupal\shippo_platforms_api;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * Service Patternry Shippo API.
 */
class ShippoPlatformsApiService {

  use ShippoPlatformsTrait;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new PatternryShippoApiService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The http client.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory, ClientInterface $http_client) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $logger_factory->get('patternry_shippo_api');
    $this->httpClient = $http_client;
    $this->setApiCredentials();
  }

  /**
   * Create merchant account for passed user id's user.
   *
   * @param int $uid
   *   An uid parameter for which merchant account will be create.
   * @param string $merchant_email
   *   An merchant email parameter for the calling endpoint api.
   * @param string $merchant_name
   *   A merchant name parameter for the calling endpoint api.
   *
   * @return int
   *   The Merchant account id.
   */
  public function createMerchantFromUserId($uid, $merchant_email, $merchant_name) {
    $userObj = $this->entityTypeManager->getStorage('user')->load($uid);
    $first_name = $last_name = $userObj->getDisplayName();
    $response = $this->createMerchant($merchant_email, $first_name, $last_name, $merchant_name);
    if (isset($response) && !empty($response)) {
      /*call the carrier create account for newly created merchant*/
      $this->createCarrierAccount($response->object_id, 'usps');
      return $response->object_id;
    }
  }

  /**
   * Update merchant account for passed user id's user.
   *
   * @param int $uid
   *   An uid parameter for which merchant account will be create.
   * @param int $merchant_id
   *   A merchant ID parameter user for which merchant account update.
   * @param string $merchant_email
   *   An merchant email parameter for the calling endpoint api.
   * @param string $merchant_name
   *   A merchant name parameter for the calling endpoint api.
   */
  public function updateMerchantFromMerchantId($uid, $merchant_id, $merchant_email, $merchant_name) {
    $userObj = $this->entityTypeManager->getStorage('user')->load($uid);
    $first_name = $last_name = $userObj->getDisplayName();
    $this->updateMerchant($merchant_id, $merchant_email, $first_name, $last_name, $merchant_name);
  }

  /**
   * Create merchant account for user.
   *
   * @param string $merchant_email
   *   An merchant email parameter for the calling endpoint api.
   * @param string $first_name
   *   A first name parameter for the calling endpoint api.
   * @param string $last_name
   *   A last name parameter for the calling endpoint api.
   * @param string $merchant_name
   *   A merchant name parameter for the calling endpoint api.
   *
   * @return array
   *   The API response.
   */
  protected function createMerchant($merchant_email, $first_name, $last_name, $merchant_name) {
    /*initialise passing parameters for request */
    $data = [
      'email' => $merchant_email,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'merchant_name' => $merchant_name,
    ];

    return $this->sendRequest('merchants', 'POST', $data);
  }

  /**
   * Update merchant for passed merchant account id.
   *
   * @param int $merchant_id
   *   A merchant ID parameter user for which merchant account update.
   * @param string $merchant_email
   *   An merchant email parameter for the calling endpoint api.
   * @param string $first_name
   *   A first name parameter for the calling endpoint api.
   * @param string $last_name
   *   A last name parameter for the calling endpoint api.
   * @param string $merchant_name
   *   A merchant name parameter for the calling endpoint api.
   */
  protected function updateMerchant($merchant_id, $merchant_email, $first_name, $last_name, $merchant_name) {
    /*initialise passing parameters for request */
    $data = [
      'email' => $merchant_email,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'merchant_name' => $merchant_name,
    ];
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to update merchant account api missing merchant id parmater.');
    }
    else {
      return $this->sendRequest('merchants/' . $merchant_id, 'PUT', $data);
    }
  }

  /**
   * Create carrier account for user.
   *
   * @param string $merchant_id
   *   An merchant id parameter for which carrier account will be create.
   * @param string $carrier_name
   *   A carrier name parameter denotes carrier service.
   *
   * @return array
   *   The API response.
   */
  protected function createCarrierAccount($merchant_id, $carrier_name) {
    /*initialise passing parameters for request */
    $merchant_id = strval($merchant_id);
    $data = [
      'carrier' => $carrier_name,
      'parameters' => [],
    ];
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to create carrier account api missing merchant id parmater.');
    }
    else {
      return $this->sendRequest('merchants' . '/' . $merchant_id . '/' . 'carrier_accounts/register/new', 'POST', $data);
    }
  }

  /**
   * Create shipment for merchant account.
   *
   * @param int $merchant_id
   *   An merchant id parmeter for user for which shipment will be create.
   * @param array $address_from
   *   An address from parameter for the shipment calling endpoint api.
   * @param array $address_to
   *   An address to parameter for the shipment calling endpoint api.
   * @param array $parcels
   *   The parcels parameter for the shipment calling endpoint api.
   *
   * @return object
   *   The shipment object.
   */
  public function createShipment($merchant_id, array $address_from, array $address_to, array $parcels) {
    $data = [
      'address_from' => $address_from,
      'address_to' => $address_to,
      'parcels' => $parcels,
    ];
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to create shipment api missing merchant id parmater.');
    }
    else {
      return $this->sendRequest('merchants' . '/' . $merchant_id . '/' . 'shipments', 'POST', $data);
    }
  }

  /**
   * Get rates from shipment id and merchant account.
   *
   * @param int $merchant_id
   *   A merchant_id parameter for which rates will be get.
   * @param int $shipment_id
   *   A shipment id parameter for which rates will be get.
   * @param string $currency_code
   *   A currency code parameter denotes currency which rates will be get.
   *
   * @return object
   *   The Rates object.
   */
  public function getRatesFromShipmentId($merchant_id, $shipment_id, $currency_code) {
    $shipment_id = strval($shipment_id);
    $endpoint = 'merchants' . '/' . $merchant_id . '/' . 'shipments' . '/' . $shipment_id . '/' . 'rates' . '/' . $currency_code;
    if (empty($merchant_id) || empty($shipment_id)) {
      $this->loggerChannel->error('An error occurred making a call to get rates api missing merchant id or shipment id parmaters.');
    }
    else {
      return $this->sendRequest($endpoint, 'GET');
    }
  }

  /**
   * Create shippo label.
   *
   * @param int $merchant_id
   *   A merchant_id parameter for which label will be generate.
   * @param int $rate_id
   *   A rate id against which label will be generated.
   * @param string $label_file_type
   *   Label_file type denotes type of label like pdf.
   *
   * @return object
   *   The label object.
   */
  public function createLabel($merchant_id, $rate_id, $label_file_type) {
    $data = [
      'rate' => $rate_id,
      'label_file_type' => $label_file_type,
      'async' => FALSE,
    ];
    return $this->sendRequest('merchants' . '/' . $merchant_id . '/' . 'transactions', 'POST', $data);
  }

  /**
   * Get carrier from rate id and merchant id.
   *
   * @param int $merchant_id
   *   A merchant id parameter denotes merchant account id.
   * @param int $rate_id
   *   A rate id parameter whose carrier need to fetch.
   *
   * @return object
   *   The Rate object.
   */
  public function getCarrierFromRateId($merchant_id, $rate_id) {
    $rate_id = strval($rate_id);
    $endpoint = 'merchants' . '/' . $merchant_id . '/' . 'rates' . '/' . $rate_id;
    if (empty($merchant_id) || empty($rate_id)) {
      $this->loggerChannel->error('An error occurred making a call to get rates api missing merchant id or rate id parmaters.');
    }
    else {
      return $this->sendRequest($endpoint, 'GET');
    }
  }

  /**
   * Register track shipment status.
   *
   * @param int $merchant_id
   *   An merchant id parmeter for user for which shipment will be create.
   * @param string $carrier
   *   Carrier is the name of carrier service.
   * @param int $tracking_number
   *   Tracking number of the shipment.
   *
   * @return object
   *   The shipment tracking status object.
   */
  public function registerTrackStatus($merchant_id, $carrier, $tracking_number) {
    if (!empty($this->mode['test']) && $this->mode['test'] == 'test') {
      $tracking_number = $this->trackingStatus;
      $carrier = 'shippo';
    }
    $data = [
      'carrier' => $carrier,
      'tracking_number' => $tracking_number,
    ];
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to register track status missing merchant id parmater.');
    }
    else {
      return $this->sendRequest('merchants' . '/' . $merchant_id . '/' . 'tracks', 'POST', $data);
    }
  }

  /**
   * Get track shipment status.
   *
   * @param int $merchant_id
   *   An merchant id parmeter for user for which shipment will be create.
   * @param string $carrier
   *   Carrier is the name of carrier service.
   * @param int $tracking_number
   *   Tracking number of the shipment.
   *
   * @return object
   *   The shipment tracking status object.
   */
  public function getTrackStatus($merchant_id, $carrier, $tracking_number) {
    if (!empty($this->mode['test']) && $this->mode['test'] == 'test') {
      $tracking_number = $this->trackingStatus;
      $carrier = 'shippo';
    }
    $endpoint = 'merchants' . '/' . $merchant_id . '/' . 'tracks' . '/' . $carrier . '/' . $tracking_number;
    if (empty($merchant_id) || empty($carrier) || empty($tracking_number)) {
      $this->loggerChannel->error('An error occurred making a call to get track status api missing merchant id or carrier name or tracking number parmaters.');
    }
    else {
      return $this->sendRequest($endpoint, 'GET');
    }
  }

  /**
   * Get Merchants.
   *
   * @param int $start_index
   *   Start index denotes start index of merchants.
   * @param int $end_index
   *   End index denotes end index of merchants.
   *
   * @return object
   *   The merchant listing.
   */
  public function getMerchants($start_index = 0, $end_index = 50) {
    $data = [];
    $endpoint = 'merchants';
    $parms = [
      'page' => $start_index,
      'results' => $end_index,
    ];
    return $this->sendRequest($endpoint, 'GET', $data, $parms);
  }

  /**
   * Get merchant carrier list.
   *
   * @param int $merchant_id
   *   An merchant id parmeter for which carrier list will be fetch.
   *
   * @return object
   *   The merchant carrier list.
   */
  public function getMerchantCarriers($merchant_id) {
    $endpoint = 'merchants/' . $merchant_id . '/carrier_accounts';
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to get carrier list missing merchant id parameters.');
    }
    else {
      return $this->sendRequest($endpoint, 'GET');
    }
  }

  /**
   * Get merchant shipment list.
   *
   * @param int $merchant_id
   *   An merchant id parmeter for which shipment list will be fetch.
   *
   * @return object
   *   The merchant shipment list.
   */
  public function getMerchantShipments($merchant_id) {
    $endpoint = 'merchants/' . $merchant_id . '/shipments';
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to get shipment list missing merchant id parameters.');
    }
    else {
      return $this->sendRequest($endpoint, 'GET');
    }
  }

  /**
   * Get merchant shippo label list.
   *
   * @param int $merchant_id
   *   An merchant id parmeter for which shipment list will be fetch.
   * @param int $start_index
   *   Start index denotes start index of merchants.
   * @param int $end_index
   *   End index denotes end index of merchants.
   *
   * @return object
   *   The merchant shipment list.
   */
  public function getMerchantLabels($merchant_id, $start_index = 0, $end_index = 10) {
    $data = [];
    $parms = [
      'page' => $start_index,
      'results' => $end_index,
    ];

    $endpoint = 'merchants/' . $merchant_id . '/transactions';
    if (empty($merchant_id)) {
      $this->loggerChannel->error('An error occurred making a call to get shippo label list missing merchant id parameters.');
    }
    else {
      return $this->sendRequest($endpoint, 'GET', $data, $parms);
    }
  }

  /**
   * Validate address.
   *
   * @param string $merchant_id
   *   A merchant_id parameter for which label will be generate.
   * @param array $address
   *   A rate id against which label will be generated.
   *
   * @return bool
   *   The address validation status.
   */
  public function validateAddress($merchant_id, array $address) {
    if (empty($merchant_id) || empty($address)) {
      $this->loggerChannel->error('An error occurred making a call to validate address api missing merchant id or address parameter.');
    }
    else {
      $result = $this->sendRequest('merchants' . '/' . $merchant_id . '/' . 'addresses', 'POST', $address);
      if (isset($result->validation_results)) {
        $valid_address = $result->validation_results;
        if ($result->is_complete == TRUE && $valid_address->is_valid == TRUE) {
          return TRUE;
        }
      }
    }
    return FALSE;

  }

  /**
   * Send Request.
   *
   * @param string $endpoint
   *   An endpoint of calling api.
   * @param string $method
   *   The method type of api get or post.
   * @param array $data
   *   An associative array containing the data send to the api.
   * @param array $parms
   *   The parameters send to the api.
   *
   * @return array
   *   The API response.
   */
  protected function sendRequest($endpoint, $method, array $data = [], array $parms = []) {
    // Set up request url.
    $postFields = '';
    $isSetCredentials = $this->checkApiCredentials();
    if (isset($isSetCredentials)) {
      $requestUrl = $this->apiUrl . '/' . $endpoint . '/';
      $requestUrl = $this->setRequestParams($requestUrl, $parms);
      $requestUrl = $this->checkRequestProtocol($requestUrl);
      if (isset($data) && !empty($data)) {
        $postFields = json_encode($data);
      }
      $headers = [
        'Authorization' => 'ShippoToken ' . $this->apiKey,
        'SHIPPO-API-VERSION' => static::$apiVersion,
        'Content-Type' => 'application/json',
      ];
      try {
        if ($method == 'POST') {
          $response = $this->httpClient->post($requestUrl,
              [
                'headers' => $headers,
                'body'    => $postFields,
              ]
          );
        }
        elseif ($method == 'PUT') {
          $response = $this->httpClient->put($requestUrl,
              [
                'headers' => $headers,
                'body'    => $postFields,
              ]
          );
        }
        else {
          $response = $this->httpClient->get($requestUrl,
              [
                'headers' => $headers,
              ]
          );
        }
      }
      catch (\Exception $e) {
        if ($method == 'POST') {
          $this->loggerChannel->error('An error occurred making a call to @endpoint with data @data. Exception: @exception.', [
            '@endpoint' => $endpoint,
            '@data' => $postFields,
            '@exception' => $e->getMessage(),
          ]);
        }
        else {
          $this->loggerChannel->error('An error occurred making a call to @endpoint. Exception: @exception.', [
            '@endpoint' => $endpoint,
            '@exception' => $e->getMessage(),
          ]);
        }

        return [];
      }

      $statusCode = $method == 'POST' ? 201 : 200;
      if ($response->getStatusCode() == $statusCode) {
        $responseRst = $response->getBody()->getContents();
        $data = json_decode($responseRst);
        if (isset($data->messages[0])) {
          $this->loggerChannel->error('An error occurred making a call to @endpoint with data @data. Exception: @exception.', [
            '@endpoint' => $endpoint,
            '@data' => $postFields,
            '@exception' => $data->messages[0]->text,
          ]);
        }
        $this->displayLogs($endpoint, $postFields, $responseRst);
        return $data;
      }
      elseif ($method == 'POST' && $response->getStatusCode() != $statusCode) {
        $responseRst = $response->getBody()->getContents();
        $data = json_decode($responseRst);
        $this->displayLogs($endpoint, $postFields, $responseRst);
        return $data;
      }
    }

    return [];
  }

}
