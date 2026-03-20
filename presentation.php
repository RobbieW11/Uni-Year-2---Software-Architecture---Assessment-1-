<?php

/**
 * KV5035 - Presentation Endpoint
 * GET's Data from Presentation Database.
 * 
 * @author Robbie Woodruff - w24013042
 * @return arrays of JSON objects that gives data about presentations.
 * 
 * @version 2026-assessment1
 * The part of the function that checks if Paramater is Numeric is from AI Debugging & Critiquing. 
 * @generated Claude Used to Debug & Critique code, this was useful to ensure only numbers would 
 * be entered into the query.
 */

header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json');
// Only allows these methods.
header('Access-Control-Allow-Methods: GET, PATCH');

// API Security - Ensures authentication and allows the authentication process to execute.
require_once 'api/api.php';

// Tells server what type of request is being made. 
$request_method = $_SERVER['REQUEST_METHOD'];

// Allows multiple methods to be used - Only methods to be used are in the header.
switch ($request_method) {
    case 'GET':
        // Converts the PHP into JSON after it checks if GET method is used.
        GET_presentations();
        break;
    case 'PATCH':
        PATCH_presentations();
        break;
    default:
        // 'Method Not Allowed' Response Code - Accurate.
        http_response_code(405);
        echo json_encode("Method Not Allowed");
        break;      
}

function GET_presentations() {
    // Imports the Database connection and execution process to connect to nuwebspace webserver.
    require_once 'database/execute_SQL.php';
    // This requests from the database tables - Includes challenge task (Change Presentation Type).
    // type_id added after trying the PATCH function method, but the PATCH method changes from type_name to type_id so it 
    // would remove the whole row of data from the GET function.
    // Summary: Type_ID Added to Show PATCH method works with type_id, not type_name.
    $sql = "SELECT presentation.id AS presentation_id, presentation.title AS title,
    presentation.abstract AS abstract, presentation.doi AS doi, 
    presentation.video AS video,
    type.id AS type_id, 
    type.name AS type_name
    FROM presentation
    LEFT JOIN type
    ON presentation.type_id = type.id
    WHERE 1=1";

    $presentation_id = $_GET['presentation-id'] ?? '';
    $page = $_GET['page'] ?? '';
    $size = $_GET['size'] ?? '';
    
    $param = [];

    // Is there an presentation_id parameter?
    if (!empty($presentation_id)) {
        // Is the presentation_id paramater numeric?
        if (!is_numeric($presentation_id)){
            // 'bad request' Response Code - Accurate.
            http_response_code(400);
            echo json_encode("Invalid Presentation ID, Must Be Numeric!");
            return;
        }
        $sql .= " AND presentation.id = :presentation_id";
        $param[':presentation_id'] = (int) $presentation_id;
    }
    
    
    // PHP counts zero as empty, this adds a check for this.
    if (!empty($page) || $page === '0') {
        // Ensures that if using page parameter, must use size parameter also.
        // @generated Claude Debug & Critiquing - https://claude.ai/share/52eac72d-52df-4a93-b95a-046108ed355c
        if (!isset($_GET['size']) || $_GET['size'] === '') {
            // 'bad request' Response Code - Accurate.
            http_response_code(400);
            echo json_encode("Size Parameter is required when using Page.");
            return;
        }
        // Checks if the Size Parameter is a Number. 
        if (!is_numeric($size) || (int)$size <= 0) {
            // 'bad request' Response Code - Accurate.
            http_response_code(400);
            echo json_encode("Size Parameter must be a Positive Integer.");
            return;
        }
        $size = (int) $size;
        // Check if Page Parameter is Not Numeric
        if (!is_numeric($page)) {
            echo json_encode("Page Parameter must be an Integer.");
            exit();
        }
        // Check if the Page Number is zero or lower.
        if ($page <= 0) {
            echo json_encode("Page Parameter must be Greater than 0.");
            exit();
        }
        // Calculate an Offset using the Page and Size Number.
        $offset = ($page - 1) * $size;

        // Add an ORDER BY, LIMIT and OFFSET to the SQL Statement
        $sql .= " ORDER BY presentation_id LIMIT $size OFFSET $offset";
    }

    $data = execute_SQL($sql, $param);

    // Ensures that if the data isn't read correctly, it is displayed. Helps debugging.
    if (empty($data)) {
        // 'not found' Response Code - Accurate.
        http_response_code(404);
        echo json_encode("No Presentations Found!");
        return;
    }
    
    // If everything works correctly, responds with 'ok' code.
    http_response_code(200);
    echo json_encode($data); 

}

function PATCH_presentations() {
    // Imports the Database connection and execution process to connect to nuwebspace webserver.
    require_once 'database/execute_SQL.php';

    // Check for JSON Data Posted to the API
    $request_body = file_get_contents('php://input');
    $request_body = json_decode($request_body, true);

    if ($request_body === null) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: Invalid JSON in Request Body.");
        exit();
    }

    // Check presentation_id 
    if (!isset($request_body['presentation_id']) || !is_numeric($request_body['presentation_id'])) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: presentation_id is Required and must be a Number.");
        exit();
    }

    // Check type_id
    if (!isset($request_body['type_id']) || !is_numeric($request_body['type_id'])) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: type_id is Required and must be a Number.");
        exit();
    }

    $presentation_id = (int) $request_body['presentation_id'];
    $type_id = $request_body['type_id'];

     
    // Update Presentation with correct type_id - Correlation with GET Request changing type_id to type_name
    $sql = "UPDATE presentation 
        JOIN type ON type.id = :type_id_check
        SET presentation.type_id = :type_id 
        WHERE presentation.id = :presentation_id";

    $param = [
        ':type_id' => $type_id,
        ':presentation_id' => $presentation_id,
        ':type_id_check' => $type_id];

    execute_SQL($sql, $param);

    // If everything works correctly, responds with 'ok' code.
    http_response_code(200);
    echo json_encode("Successfully Changed Presentation Type!"); // Debugging Purposes.
}

?>
