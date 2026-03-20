<?php
/** 
 * Connect to the database
 * 
 * Creates a database connection to the MariaDB database 
 * hosted on the same server as the web application.  
 * 
 * @author Robbie Woodruff w24013042
 * 
 * @return PDO connection to the database
 */

function database_connection() {
    /**
     * Import the database credentials. Adding _DIR_ at the start
     * means PHP will look in the same folder as the current script
     */
    require_once __DIR__."/credentials.php";
 
    try {
        $connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
 
    } catch( PDOException $e ) {
 
        // Response code 500 means there was an error on the server
        http_response_code(500);
 
        $error = "Database Connection Error: " . $e->getMessage();
        echo json_encode($error);
        exit();
    }
}