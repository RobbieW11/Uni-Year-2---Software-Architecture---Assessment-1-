<?php

/**
 * KV5035 - About Endpoint
 * Authorises API with API Key, Gets data from API and Outputs it in JSON.
 * @author Robbie Woodruff - w24013042
 * @return array response variable and its values.
 * @version 2026-assessment1
 */

header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json'); 
// Only allows these methods.
header('Access-Control-Allow-Methods: GET');

// API Security - Ensures authentication and allows the authentication process to execute.
require_once 'api/api.php';

/**
 * This function creates the response with module and developer 
 * values given to output with the following echo json_encode
 */
function response() {
    return [
        "module" => "KV5035 Software Architecture",
        "developer" => "Robbie Woodruff"
    ];
}

// Tells server what type of request is being made. 
$request_method = $_SERVER['REQUEST_METHOD'];

// Allows multiple methods to be used - Only methods to be used are in the header.
switch ($request_method) {
    case 'GET':
        // Outputs the json encoding of the function.
        echo json_encode(response());
        // If everything works correctly, responds with 'ok' code.
        http_response_code(200);
        break;
    default:
        // 'Method Not Allowed' Response Code - Accurate.
        http_response_code(405);
        echo json_encode("Method Not Allowed");
        break;      
}

?>