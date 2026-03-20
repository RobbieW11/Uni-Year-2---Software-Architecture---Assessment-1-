<?php

/**
 * KV5035 - Type Endpoint
 * Multiple Requests (GET, POST, PUT, PATCH, DELETE) to modify API Data.
 * @author Robbie Woodruff - w24013042
 * @return Correctly Modified Data.
 * @version 2026-assessment1
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
// Only allows these methods.
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');

// API Security - Ensures authentication and allows the authentication process to execute.
require_once 'api/api.php';

// Tells server what type of request is being made. 
$request_method = $_SERVER['REQUEST_METHOD'];

// Allows multiple methods to be used - Only methods to be used are in the header.
switch ($request_method) {
    case 'GET':
        // Converts the PHP into JSON after it checks if GET method is used.
        GET_types();
        break;
    case 'POST':
        // Inputs Data into Type Database.
        POST_types();
        break;
    case 'PUT':
    case 'PATCH':
        // Updates Data in the Type Database.
        PUT_types();
        break;
    case 'DELETE':
        // Converts the PHP into JSON after it checks if GET method is used.
        DELETE_types();
        break;
    default:
        // 'Method Not Allowed' Response Code - Accurate.
        http_response_code(405);
        echo json_encode("Method Not Allowed");
        break;      
}

/** 
 * Handles GET Requests for Types
 * This script GET's Data from the Types Database.
 * Outputs Data in an Array Format.
 * @author Robbie Woodruff - w24013042
 * @return Arrays of JSON Data from the Type Database - ID and Names.
 * @version 2026-assessment1
 */

function GET_types() {;
    // Imports the Database connection and execution process to connect to nuwebspace webserver.
    require_once 'database/execute_SQL.php';

    // This requests from the database tables.
    $sql = "SELECT id, name
    FROM type
    WHERE 1=1";

    $id = $_GET['id'] ?? '';

    $param = [];

    // Is there an id parameter?
    if (!empty($id)) {
        // Is the id paramater numeric?
        if (!is_numeric($id)){
            // 'bad request' Response Code - Accurate.
            http_response_code(400);
            echo json_encode("Invalid ID, Must Be Numeric!");
            return;
        }
        $sql .= " AND type.id = :id";
        $param[':id'] = (int) $id;
    }

    $data = execute_SQL($sql, $param);

    // Ensures that if the data isn't read correctly, it is displayed. Helps debugging.
    if (empty($data)) {
        // 'not found' Response Code - Accurate.
        http_response_code(404);
        echo json_encode("No Types Found!");
        return;
    }
    
    // If everything works correctly, responds with 'ok' code.
    http_response_code(200);
    echo json_encode($data); 

}

/** 
 * Handles POST Requests for Types
 * This script inputs into the Types Database.
 * A Name is Required to be given.
 * Postman Inputting: Body -> Raw - JSON -> Example Input: {"name": "Name Input"}
 * @author Robbie Woodruff - w24013042
 * @return an Input of Data into the Types Database.
 * @version 2026-assessment1
 */

function POST_types() {
    // Imports the Database connection and execution process to connect to nuwebspace webserver.
    require_once 'database/execute_SQL.php';

    // This requests from the database tables.
    $sql = "INSERT INTO type (name) VALUES (:name)";

    // Check for JSON Data Posted to the API
    $request_body = file_get_contents('php://input');
    $request_body = json_decode($request_body, true);

    if ($request_body === null) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: Invalid JSON in Request Body.");
        exit();
    }

    if (!array_key_exists('name', $request_body)) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: name is Required.");
        exit();
    }
    
    $name = $request_body['name'];

    // Checks if an ID is provided.
    if (array_key_exists('id', $request_body)) {
        if (!is_numeric($request_body['id'])) {
            // 'bad request' Response Code - Accurate.
            http_response_code(400);
            echo json_encode("Error: id must be a Number.");
            exit();
        }
        $id = (int) $request_body['id'];
        $sql = "INSERT INTO type (id, name) VALUES (:id, :name)";
        $param = [':id' => $id, ':name' => $name];
    } else {
        // If there is no id provided, the name will be added to the end of the database.
        $sql = "INSERT INTO type(name) VALUES (:name)";
        $param = [':name' => $name];
    }
    
    execute_SQL($sql, $param);

    // If everything works correctly, responds with, correlating with this method, 'created' code.
    http_response_code(201);
    echo json_encode("Successfully Created!"); // Debugging Purposes.

}

/** 
 * Handles PUT & PATCH Requests for Types.
 * This script updates Types in the Database.
 * An type_id & name is Required -> Example: {"type_id": 7, "name": "Different Input"} 
 * @author Robbie Woodruff - w24013042
 * @return Updated Data in the Types Database.
 * @version 2026-assessment1
 */

function PUT_types() {
    // Imports the Database connection and execution process to connect to nuwebspace webserver.
    require_once 'database/execute_SQL.php';

    // This requests from the database tables.
    $sql = "UPDATE type
    SET name = :name
    WHERE type.id = :type_id";

    // Check for JSON Data Posted to the API
    $request_body = file_get_contents('php://input');
    $request_body = json_decode($request_body, true);

    if ($request_body === null) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: Invalid JSON in Request Body.");
        exit();
    }

    if (array_key_exists('type_id', $request_body)) {
        $id = $request_body['type_id'];
    } else {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: type_id is Required.");
        exit();
    }

    if (array_key_exists('name', $request_body)) {
        $name = $request_body['name'];
    } else {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: name is Required.");
        exit();
    }

    $param = [
        ':name' => $name,
        ':type_id' => $id
    ];
    
    $data = execute_SQL($sql, $param);

    // If everything works correctly, responds with 'ok' code.
    http_response_code(200);
    echo json_encode($data);
    echo json_encode("Successful!"); // Debugging Purposes.

}

/** 
 * Handles DELETE Requests for Types.
 * This script Deletes Types in the Database.
 * A type_id is Required -> Example: {"type_id": 5}
 * @author Robbie Woodruff - w24013042
 * @return Deletes Data in the Types Database.
 * @version 2026-assessment1
 */

function DELETE_types() {
    // Imports the Database connection and execution process to connect to nuwebspace webserver.
    require_once 'database/execute_SQL.php';

    // This requests from the database tables.
    $sql = "DELETE FROM type WHERE type.id = :type_id";

    // Check for JSON Data Posted to the API
    $request_body = file_get_contents('php://input');
    $request_body = json_decode($request_body, true);

    if ($request_body === null) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: Invalid JSON in Request Body.");
        exit();
    }

    if (!isset($request_body['type_id']) || !is_int($request_body['type_id'])) {
        // 'bad request' Response Code - Accurate.
        http_response_code(400);
        echo json_encode("Error: type_id is Required and Must be an Integer");
        exit();
    }

    $param = [
        ':type_id' => $request_body['type_id']];
    
    $data = execute_SQL($sql, $param);

    // If everything works correctly, responds with 'ok' code.
    http_response_code(200);
    echo json_encode($data);
    echo json_encode("Successful!"); // Debugging Purposes.

}


?>

