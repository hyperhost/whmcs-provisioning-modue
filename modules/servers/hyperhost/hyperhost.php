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

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://hyper.host/api/v1/sub_users",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Bearer " . $params['serverpassword']
                ),
            ));

            $response = curl_exec($curl);
            $err      = curl_error($curl);

            curl_close($curl);

            if ($err) {

                return $err;

            } else {

                Capsule::table('tblcustomfieldsvalues')
                    ->where('fieldid', $hyper_host_field->id)
                    ->where('relid', $params['clientsdetails']['userid'])
                    ->update([
                        'value' => $response->hyper_host_id,
                    ]);

                return $response;

            }

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

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/packages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer " . $params['serverpassword']
            ),
        ));

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {

            return $err;

        } else {

            return 'success';

        }

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

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/packages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $params['serverpassword']
            ),
        ));

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $errorMsg = $err;
        } else {
            $success = true;
        }

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
 * @return array
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 */
function hyperhost_ServiceSingleSignOn(array $params)
{

    try {

        $hyperHostId = hyper_host_id($params);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/whmcs/sso",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(['identifier' => $params['domain']]),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $params['serverpassword']
            ),
        ));

        $response = curl_exec($curl);

        $err  = curl_error($curl);
        $info = curl_getinfo($curl);

        curl_close($curl);

        if ($err) {

            return array(
                'success' => false,
                'errorMsg' => $err,
            );

        } else {

            if ($info['http_code'] !== 200) {

                return array(
                    'success' => false,
                    'errorMsg' => $response,
                );

            }

            return array(
                'success' => true,
                'redirectTo' => $response,
            );

        }

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
        $hyperClient->put('sub_users/' . $hyperHostId, ['json' => $payload])->getBody()->getContents();

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
        $hyperClient->put('sub_users/' . $hyperHostId, ['json' => $payload])->getBody()->getContents();

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
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiToken
        ]
    ]);

}