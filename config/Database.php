<?php
/**
 * Database Helper Class
 * Handles all database operations with prepared statements to prevent SQL injection
 * 
 * Usage:
 *   $db = new Database($con);
 *   $result = $db->query("SELECT * FROM users WHERE id = ?", ["i", $userId]);
 *   $row = $result->fetch_assoc();
 */

class Database {
    private $connection;
    private $lastError;

    public function __construct($connection) {
        $this->connection = $connection;
        $this->lastError = null;
    }

    /**
     * Execute a prepared statement query
     * 
     * @param string $sql SQL query with ? placeholders
     * @param array $params Array with param types and values [["type", value], ["type", value]]
     *                      Types: "s"=string, "i"=integer, "d"=double, "b"=blob
     * @return mysqli_result|bool Query result or false on error
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                $this->lastError = "Prepare failed: " . $this->connection->error;
                error_log($this->lastError);
                return false;
            }

            // Bind parameters if provided
            if (!empty($params)) {
                $types = '';
                $values = [];
                
                foreach ($params as $param) {
                    $types .= $param[0];
                    $values[] = $param[1];
                }
                
                $stmt->bind_param($types, ...$values);
            }

            if (!$stmt->execute()) {
                $this->lastError = "Execute failed: " . $stmt->error;
                error_log($this->lastError);
                return false;
            }

            return $stmt->get_result();
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Database error: " . $this->lastError);
            return false;
        }
    }

    /**
     * Execute an INSERT/UPDATE/DELETE query
     * 
     * @param string $sql SQL query with ? placeholders
     * @param array $params Parameter array [["type", value], ...]
     * @return bool True on success, false on failure
     */
    public function execute($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result !== false;
    }

    /**
     * Get a single row from query result
     * 
     * @param mysqli_result $result Query result
     * @return array|null Associative array or null if no rows
     */
    public function fetchRow($result) {
        if (!$result) return null;
        return $result->fetch_assoc();
    }

    /**
     * Get all rows from query result
     * 
     * @param mysqli_result $result Query result
     * @return array Array of associative arrays
     */
    public function fetchAll($result) {
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Get the last error message
     * 
     * @return string|null Last error or null
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Get number of rows affected by last query
     * 
     * @return int Number of affected rows
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    /**
     * Get the last inserted ID
     * 
     * @return int Last insert ID
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
}
?>