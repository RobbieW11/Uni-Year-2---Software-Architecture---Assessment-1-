<?php
// import the database connection function
require_once 'database_connection.php';
 
/** 
* Execute an SQL query
* 
* This function executes an SQL query and returns the results as an array. 
* It uses the database connection function to connect to the database and 
* prepares and executes the SQL query using PDO. If there is an error, it 
* returns a JSON-encoded error message.
*/
function execute_SQL($sql = "", $param = []) {
    /**
     * Import the database connection function. Adding _DIR_ at the start
     * means PHP will look in the same folder as the current script
     */
    try {
        // connect to the database
        $conn = database_connection();
        // prepare and execute the SQL statement, passing in the parameters
        $result = $conn->prepare($sql);
        $result->execute($param); 
        // fetch the results as an associative array and return it
        $data = $result->fetchAll(PDO::FETCH_ASSOC);
        return $data;
 
    } catch( PDOException $e ) {
        // If there is an exception, output the error message
        $error = "SQL Error: " . $e->getMessage();
        echo json_encode($error);
        exit();
    }
}
?>