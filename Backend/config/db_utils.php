<?php
/**
 * Database utility functions for the e-commerce application
 */

/**
 * Safely bind parameters to a prepared statement
 * 
 * @param mysqli_stmt $stmt The prepared statement
 * @param string $types The types of parameters (i, d, s, b)
 * @param array $params The parameters to bind
 * @return bool Whether the binding was successful
 */
function bindParams($stmt, $types, $params) {
    // Create a reference array for bind_param
    $bind_params = array();
    $bind_params[] = $types;
    
    // Add references to the parameters
    for ($i = 0; $i < count($params); $i++) {
        $bind_params[] = &$params[$i];
    }
    
    // Call bind_param with the reference array
    return call_user_func_array(array($stmt, 'bind_param'), $bind_params);
}

/**
 * Execute a query and return the result as an associative array
 * 
 * @param mysqli $conn The database connection
 * @param string $sql The SQL query
 * @param string $types The types of parameters (i, d, s, b)
 * @param array $params The parameters to bind
 * @return array The result as an associative array
 */
function executeQuery($conn, $sql, $types = '', $params = array()) {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return array('error' => 'Failed to prepare statement: ' . $conn->error);
    }
    
    if (!empty($params)) {
        if (!bindParams($stmt, $types, $params)) {
            $stmt->close();
            return array('error' => 'Failed to bind parameters: ' . $stmt->error);
        }
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        return array('error' => 'Failed to execute query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $data = array();
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $stmt->close();
    return $data;
}

/**
 * Execute a query that doesn't return results (INSERT, UPDATE, DELETE)
 * 
 * @param mysqli $conn The database connection
 * @param string $sql The SQL query
 * @param string $types The types of parameters (i, d, s, b)
 * @param array $params The parameters to bind
 * @return array Result with success status and affected rows or error message
 */
function executeNonQuery($conn, $sql, $types = '', $params = array()) {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return array('success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error);
    }
    
    if (!empty($params)) {
        if (!bindParams($stmt, $types, $params)) {
            $stmt->close();
            return array('success' => false, 'error' => 'Failed to bind parameters: ' . $stmt->error);
        }
    }
    
    if (!$stmt->execute()) {
        $stmt->close();
        return array('success' => false, 'error' => 'Failed to execute query: ' . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $insert_id = $stmt->insert_id;
    
    $stmt->close();
    return array(
        'success' => true,
        'affected_rows' => $affected_rows,
        'insert_id' => $insert_id
    );
}
?> 