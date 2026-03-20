<?php

/** 
 * API Config & Error/Exception Handling
 * @author Robbie Woodruff - w24013042
 */

header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Content-Type: application/json');
 
// Exception Handler Executes and ensures if a problem is seen, the execution process stops - exit().
require_once 'exception_handler.php';

// get the request headers
$allHeaders = getallheaders();
 
// convert header keys to lowercase for case-insensitive access
$allHeaders = array_change_key_case($allHeaders, CASE_LOWER);
 
// check for the presence of the Authorization header
if (array_key_exists('authorization', $allHeaders)) {
    $authorizationHeader = $allHeaders['authorization'];
} else {
    throw new Exception("Authorization Header Not Found", 401);
}
 
// extract the API key from the Authorization header
$api_key = str_replace('Bearer ', '', $authorizationHeader);

require_once 'api_key.php';

if ($api_key !== $api_key_access) {
    // 'unauthorised' Response Code - Accurate.
    throw new Exception("Invalid API Key", 401);
}

?>