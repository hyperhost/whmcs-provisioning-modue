<?php

/**
 * Hyper Host Provisioning Module v1.1.3
 * Developed by Tony James - me@tony.codes
 */

use GuzzleHttp\Client;
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Module related meta data.
 */
function hyperhost_MetaData()
{

    return array(
        'DisplayName' => 'Hyper Host',
        'APIVersion' => '1',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '1111',
        'DefaultSSLPort' => '1112',
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
    );

}

/**
 * Define product configuration options.
 */
function hyperhost_ConfigOptions()
{

    return [
        "platformType" => [
            "FriendlyName" => "Platform",
            "Type" => "dropdown",
            "Options" => [
                '9739' => 'Linux',
                '907' => 'WordPress',
                '15809' => 'Windows',
            ],
            "Description" => "Select Linux if unsure, use WordPress only for WordPress sites",
            "Default" => "9739",
        ],
    ];

}

/**
 * Get or create the custom hyper_host_id.
 *
 * @param array $params
 *
 * @return string
 */
function hyper_host_id(array $params)
{

    /**
     * Attempt to find the custom client field
     */
    $hyper_host_field = Capsule::table('tblcustomfields')
        ->where('fieldname', 'hyper_host_id')
        ->first();

    /**
     * If it cannot be found, return friendly error
     */
    if (!$hyper_host_field->id) {

        return 'Failed to create hyper_host_id, have you added your custom client field?';

    }

    /**
     * If found, check if it has a value set for this Customer
     */
    $hyper_host_id = Capsule::table('tblcustomfieldsvalues')
        ->where('fieldid', $hyper_host_field->id)
        ->where('relid', $params['clientsdetails']['userid'])
        ->first()->value;

    /**
     * If its got a value, return it
     */
    if (!empty($hyper_host_id)) {

        return $hyper_host_id;

    /**
     * If not set try and populate the value
     */
    } else {

        try {

            $payload = [
                'name' => $params['clientsdetails']['firstname'] . ' ' . $params['clientsdetails']['lastname'],
                'email' => $params['clientsdetails']['email'],
                'plan_unique_id' => 'whmcs', // Your WHMCS plan defined at Hyper Host, needs to be named whmcs
                'status' => 'active', // Activate this user
            ];

            $hyperClient = hyperhost_Client($params['serverpassword']);
            $response    = $hyperClient->post('customers', ['json' => $payload])->getBody()->getContents();

            Capsule::table('tblcustomfieldsvalues')
                ->where('fieldid', $hyper_host_field->id)
                ->where('relid', $params['clientsdetails']['userid'])
                ->update([
                    'value' => $response,
                ]);

            return $response;

        } catch (Throwable $e) {

            logModuleCall(
                'provisioningmodule',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            return $e->getMessage();

        }

    }

}

/**
 * Provision a new instance of a product/service.
 *
 * @param array $params
 *
 * @return string
 */
function hyperhost_CreateAccount(array $params)
{

    try {

        $hyperHostId = hyper_host_id($params);

        $payload = [
            'platform' => $params['configoption1'],
            'domain' => $params['domain'],
            'hyper_host_id' => $hyperHostId,
        ];

        $hyperClient = hyperhost_Client($params['serverpassword']);
        $hyperClient->post('packages', ['json' => $payload])->getBody()->getContents();

        return 'success';

    } catch (Throwable $e) {

        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();

    }

}

/**
 * Test connection with the given server parameters. Expected response is JSON,
 * if its not an exception will be thrown, failing the test.
 *
 * @param array $params
 *
 * @return array
 */
function hyperhost_TestConnection(array $params)
{

    try {

        $hyperClient = hyperhost_Client($params['serverpassword']);
        $hyperClient->get('packages')->json();
        $success = true;

    } catch (Throwable $e) {

        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success  = false;
        $errorMsg = $e->getMessage();

    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );

}

/**
 * Perform single sign-on for a given instance of a product/service.
 * Called when single sign-on is requested for an instance of a product/service.
 * When successful, returns a URL to which the user should be redirected.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @return array
 */
function hyperhost_ServiceSingleSignOn(array $params)
{

    try {

        $hyperHostId = hyper_host_id($params);

        $hyperClient = hyperhost_Client($params['serverpassword']);
        $response    = $hyperClient->get('customers/' . $hyperHostId . '/sso')->getBody()->getContents();

        return array(
            'success' => true,
            'redirectTo' => $response,
        );

    } catch (Throwable $e) {

        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );

    }

}

/**
 * Suspend a customer.
 *
 * @param array $params
 *
 * @return string
 */
function hyperhost_SuspendAccount(array $params)
{

    try {

        $hyperHostId = hyper_host_id($params);

        $payload = [
            'status' => 'disabled',
        ];

        $hyperClient = hyperhost_Client($params['serverpassword']);
        $hyperClient->put('customers/' . $hyperHostId, ['json' => $payload])->getBody()->getContents();

        return 'success';

    } catch (Throwable $e) {

        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

}

/**
 * Un-suspend a customer.
 *
 * @param array $params
 *
 * @return string
 */
function hyperhost_UnsuspendAccount(array $params)
{

    try {

        $hyperHostId = hyper_host_id($params);

        $payload = [
            'status' => 'active',
        ];

        $hyperClient = hyperhost_Client($params['serverpassword']);
        $hyperClient->put('customers/' . $hyperHostId, ['json' => $payload])->getBody()->getContents();

        return 'success';

    } catch (Throwable $e) {

        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

}

/**
 * @param $apiToken
 *
 * @return Client
 */
function hyperhost_Client($apiToken)
{

    /**
     * Setup a Guzzle Client just for this Auth request
     */
    return new Client([
        'base_url' => ['https://hyper.host/api/v1/', ['version' => 'v1']],
        'defaults' => [
            'query' => [
                'api_token' => $apiToken
            ]
        ]
    ]);

}