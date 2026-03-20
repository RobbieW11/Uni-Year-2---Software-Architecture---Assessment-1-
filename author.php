<?php

/**
 * KV5035 - Author Endpoint
 * Gets Author IDs and Names from Presentations via the API and Outputs it in JSON.
 * @author Robbie Woodruff - w24013042
 * @return arrays of JSON objects that gives data about authors.
 * @version 2026-assessment1
 */

header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: *'); 
// Only allows these methods.
header('Access-Control-Allow-Methods: GET'); 

// API Security - Ensures authentication and allows the authentication process to execute. 
require_once 'api/api.php'; 

// Tells server what type of request is being made. 
$request_method = $_SERVER['REQUEST_METHOD']; 

// Allows multiple methods to be used - Only methods to be used are in the header.
switch ($request_method) { 
    case 'GET': 
        // Converts the PHP into JSON after it checks if GET method is used. 
        GET_authors(); 
        break; 
    default: 
    // 'Method Not Allowed' Response Code - Accurate. 
    http_response_code(405); 
    echo json_encode("Method Not Allowed"); 
    break; 
} 

function GET_authors() { 
    // Imports the Database connection and execution process to connect to nuwebspace webserver. 
    require_once 'database/execute_SQL.php'; 

    // This requests from the database tables - Includes challenge task (Affiliations).
    $sql = "SELECT author.id AS author_id, author.name,
    affiliation.institution 
    FROM author 
    LEFT JOIN affiliation ON author.id = affiliation.author_id 
    WHERE 1=1"; 
    
    // Enables future paramater usage for author-id and presentation-id for this endpoint.
    $author_id = $_GET['author-id'] ?? ''; 
    $presentation_id = $_GET['presentation-id'] ?? ''; 
    // For page & size task, allows user to select certain page & amount of outputs per page. 
    $page = $_GET['page'] ?? ''; 
    $size = $_GET['size'] ?? ''; 
    
    $param = []; 
    
    // Is there an author_id parameter? 
    if (!empty($author_id)) { 
        // Is the author_id paramater numeric? 
        if (!is_numeric($author_id)){ 
            // 'bad request' Response Code - Accurate. 
            http_response_code(400); 
            echo json_encode("Invalid Author ID, Must Be Numeric!"); 
            return;
            } 
            
        // Author id paramater, allows user to search for specific author id's.
        $sql .= " AND author.id = :author_id"; 
        $param[':author_id'] = $author_id; 
        } 
        
        // Is there an presentation_id parameter? 
        if (!empty($presentation_id)) {  
        // Is the presentation_id paramater numeric? 
        if (!is_numeric($presentation_id)){ 
            // 'bad request' Response Code - Accurate. 
            http_response_code(400); 
            echo json_encode("Invalid Presentation ID, Must Be Numeric!"); 
            return; 
        } 

            // Ensures author id output also is paired with an output of presentations.
            $sql .= " AND author.id IN (
            SELECT author_id FROM presentation_has_author WHERE presentation_id = :presentation_id)";  

            // Ensures presentation id output also is paired with an output of affiliation - After challenge task.
            $sql .= " AND affiliation.presentation_id = :presentation_id";
            $param[':presentation_id'] = $presentation_id;
        }

        // Groups output by author id's, not presentation id or affiliations. 
        $sql .= " GROUP BY author.id";

        // PHP counts zero as empty, this adds a check for this. 
        if (!empty($page) || $page === '0') { 
            // Ensures that if using page parameter, must use size parameter also. 
            // @generated Claude Debug & Critiquing - https://claude.ai/share/52eac72d-52df-4a93-b95a-046108ed355c 
            // (This is the Presentation Debug, but was used to enhance this also) 
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
                // 'bad request' Response Code - Accurate. 
                http_response_code(400); 
                echo json_encode("Page Parameter must be an Integer."); 
                return; 
            } 

            // Check if the Page Number is zero or lower. 
            if ((int)$page <= 0) { 
                // @generated Claude Code - Adding additional http_response_code codes to polish code - https://claude.ai/share/73825f50-4968-43a6-bdec-4017197ffb23
                // 'bad request' Response Code - Accurate. 
                http_response_code(400); 
                echo json_encode("Page Parameter must be Greater than 0."); 
                return; 
            } 

            // @generated by Claude Code - Debugging and Fixing - https://claude.ai/share/73825f50-4968-43a6-bdec-4017197ffb23
            $page = (int)$page;
            // Calculate an Offset using the Page and Size Number. 
            $offset = ($page - 1) * $size; 
            // Add an ORDER BY, LIMIT and OFFSET to the SQL Statement 
            $sql .= " ORDER BY author_id LIMIT $size OFFSET $offset"; 
            } 
            $authors = execute_SQL($sql, $param); 
            // Ensures that if the data isn't read correctly, it is displayed. Helps debugging. 
            if (empty($authors)) { 
                // 'not found' Response Code - Accurate. 
                http_response_code(404); 
                echo json_encode("No Authors Found!"); 
                return; 
            } 
                // If everything works correctly, responds with 'ok' code. 
                http_response_code(200); 
                echo json_encode($authors); 
            }
?>