
# Shippo Platforms API Module

## Introduction

This module provides integration with the Shippo API for managing merchant accounts, shipments, labels, and tracking. It includes methods for creating and updating merchant accounts, managing carrier accounts, creating shipments, generating labels, retrieving rates, and tracking shipments.

## Installation

1. Download the using composer or clone it from the repository.
2. If cloned, place the module in the `modules/custom` directory of your Drupal installation.


## Configuration

1. Obtain API credentials from Shippo.
2. Configure the API credentials in the Drupal configuration file or settings.
3. Configure permissions for users who should access Shippo API features.


# Shippo Platforms API Documentation

## Introduction

This API documentation provides details on the endpoints and methods available in the Shippo Platforms API module for Drupal.

### Base URL

The base URL for all API requests is determined by your Drupal installation.

### Authentication

Authentication to the Shippo API is handled using an API token (`$this->apiKey`) provided by Shippo. Ensure that the token is correctly configured in your Drupal environment.

### Error Handling

Errors and exceptions are logged using Drupal's logger (`$this->loggerChannel`). Check Drupal logs for details on API calls and responses.

## Endpoints and Methods

### 1. `createMerchantFromUserId()`

   Creates a merchant account for a specified user.
   
   - **Method**: POST
   - **Endpoint**: `/merchants`
   - **Parameters**:
     - `$uid`: User ID for which the merchant account will be created.
     - `$merchant_email`: Email of the merchant.
     - `$merchant_name`: Name of the merchant.
   - **Returns**: Merchant account ID.

### 2. `updateMerchantFromMerchantId()`

   Updates a merchant account for a specified merchant ID.
   
   - **Method**: PUT
   - **Endpoint**: `/merchants/{merchant_id}`
   - **Parameters**:
     - `$uid`: User ID associated with the merchant.
     - `$merchant_id`: ID of the merchant account to update.
     - `$merchant_email`: Updated email of the merchant.
     - `$merchant_name`: Updated name of the merchant.

### 3. `createShipment()`

   Creates a shipment for a merchant account.
   
   - **Method**: POST
   - **Endpoint**: `/merchants/{merchant_id}/shipments`
   - **Parameters**:
     - `$merchant_id`: ID of the merchant account.
     - `$address_from`: Address from which the shipment originates.
     - `$address_to`: Address to which the shipment is sent.
     - `$parcels`: Parcels included in the shipment.
   - **Returns**: Shipment object.

### 4. `createLabel()`

   Creates a label for a shipment using a specified rate ID.
   
   - **Method**: POST
   - **Endpoint**: `/merchants/{merchant_id}/transactions`
   - **Parameters**:
     - `$merchant_id`: ID of the merchant account.
     - `$rate_id`: Rate ID against which the label will be generated.
     - `$label_file_type`: Type of label file (e.g., PDF).
   - **Returns**: Label object.

### 5. `registerTrackStatus()`

   Registers shipment tracking status.
   
   - **Method**: POST
   - **Endpoint**: `/merchants/{merchant_id}/tracks`
   - **Parameters**:
     - `$merchant_id`: ID of the merchant account.
     - `$carrier`: Name of the carrier service.
     - `$tracking_number`: Tracking number of the shipment.
   - **Returns**: Shipment tracking status object.

### 6. `getTrackStatus()`

   Retrieves shipment tracking status.
   
   - **Method**: GET
   - **Endpoint**: `/merchants/{merchant_id}/tracks/{carrier}/{tracking_number}`
   - **Parameters**:
     - `$merchant_id`: ID of the merchant account.
     - `$carrier`: Name of the carrier service.
     - `$tracking_number`: Tracking number of the shipment.
   - **Returns**: Shipment tracking status object.

### 7. `getRatesFromShipmentId()`

   Retrieves rates for a shipment ID and merchant account.
   
   - **Method**: GET
   - **Endpoint**: `/merchants/{merchant_id}/shipments/{shipment_id}/rates/{currency_code}`
   - **Parameters**:
     - `$merchant_id`: ID of the merchant account.
     - `$shipment_id`: ID of the shipment.
     - `$currency_code`: Currency code for the rates.
   - **Returns**: Rates object.

### 8. `getMerchants()`

   Retrieves a list of merchants.
   
   - **Method**: GET
   - **Endpoint**: `/merchants`
   - **Parameters**:
     - `$start_index`: Start index for pagination (optional).
     - `$end_index`: End index for pagination (optional).
   - **Returns**: List of merchants.

### 9. `getMerchantCarriers()`

   Retrieves carrier accounts for a merchant.
   
   - **Method**: GET
   - **Endpoint**: `/merchants/{merchant_id}/carrier_accounts`
   - **Parameters**:
     - `$merchant_id`: ID of the merchant account.
   - **Returns**: List of carrier accounts.

### 10. `getMerchantShipments()`

Retrieves shipments for a merchant.

- **Method**: GET
- **Endpoint**: `/merchants/{merchant_id}/shipments`
- **Parameters**:
  - `$merchant_id`: ID of the merchant account.
- **Returns**: List of shipments.

---

### 11. `getMerchantLabels()`

Retrieves labels for a merchant.

- **Method**: GET
- **Endpoint**: `/merchants/{merchant_id}/transactions`
- **Parameters**:
  - `$merchant_id`: ID of the merchant account.
  - `$start_index`: Start index for pagination (optional).
  - `$end_index`: End index for pagination (optional).
- **Returns**: List of labels.

---

### 12. `validateAddress()`

Validates an address for a merchant.

- **Method**: POST
- **Endpoint**: `/merchants/{merchant_id}/addresses`
- **Parameters**:
  - `$merchant_id`: ID of the merchant account.
  - `$address`: Address to validate.
- **Returns**: Boolean indicating address validation status.

## Notes

This API documentation covers the essential endpoints and methods available in the Shippo Platforms API module for integrating Shippo services with your Drupal application.
