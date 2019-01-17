<?php

/**
 * Hyper Host Provisioning Module v1
 * Developed by Tony James - me@tony.codes
 */

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
                '905' => 'Linux',
                '907' => 'WordPress',
            ],
            "Description" => "Select Linux if unsure, use WordPress only for WordPress sites",
            "Default" => "905",
        ],
    ];

}

/**
 * Provision a new instance of a product/service.
 */
function hyperhost_CreateAccount(array $params)
{

    try {

        $curl = curl_init();

        $post = [
            'platform' => $params['configoption1'],
            'domain' => $params['domain'],
            'hyper_host_id' => $params['clientsdetails']['customfields1'],
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/suser/" . $params['clientsdetails']['customfields1'] . "/package?api_token=" . $params['serverpassword'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
        ));

        curl_exec($curl);
        curl_error($curl);

        $responseCode = curl_getinfo($curl)['http_code'];

        if ($responseCode !== 200) {

            return 'Request failed with error code: ' . $responseCode;

        } else {

            return 'success';

        }

        curl_close($curl);

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.
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
 * Test connection with the given server parameters.
 */
function hyperhost_TestConnection(array $params)
{

    try {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/suser/" . $params['clientsdetails']['customfields1'] . "/package?api_token=" . $params['serverpassword'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        curl_exec($curl);
        curl_error($curl);

        $responseCode = curl_getinfo($curl)['http_code'];

        if ($responseCode !== 200) {

            $errorMsg = 'Request failed with error code: ' . $responseCode;

        } else {

            $success = true;

        }

        curl_close($curl);

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
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

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/sso/" . $params['clientsdetails']['customfields1'] . "?api_token=" . $params['serverpassword'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        curl_exec($curl);
        curl_error($curl);

        $response = curl_exec($curl);

        $responseCode = curl_getinfo($curl)['http_code'];

        if ($responseCode !== 200) {

            return 'Request failed with error code: ' . $responseCode;

        } else {

            return array(
                'success' => true,
                'redirectTo' => $response,
            );

        }

        curl_close($curl);

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.
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
 */
function hyperhost_SuspendAccount(array $params)
{

    try {

        $curl = curl_init();

        $post = [
            'new_status' => 'disabled',
            'hyper_host_id' => $params['clientsdetails']['customfields1'],
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/suser/status?api_token="  . $params['serverpassword'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
        ));

        curl_exec($curl);
        curl_error($curl);

        $responseCode = curl_getinfo($curl)['http_code'];

        if ($responseCode !== 200) {

            return 'Request failed with error code: ' . $responseCode;

        } else {

            return 'success';

        }

        curl_close($curl);

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.
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
*/
function hyperhost_UnsuspendAccount(array $params)
{

    try {

        $curl = curl_init();

        $post = [
            'new_status' => 'active',
            'hyper_host_id' => $params['clientsdetails']['customfields1'],
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://hyper.host/api/v1/suser/status?api_token="  . $params['serverpassword'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
        ));

        curl_exec($curl);
        curl_error($curl);

        $responseCode = curl_getinfo($curl)['http_code'];

        if ($responseCode !== 200) {

            return 'Request failed with error code: ' . $responseCode;

        } else {

            return 'success';

        }

        curl_close($curl);

    } catch (Exception $e) {

        // Record the error in WHMCS's module log.
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