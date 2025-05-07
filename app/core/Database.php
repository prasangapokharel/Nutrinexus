<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database class
 * Handles database connections and queries
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $stmt;

    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Prepare statement with query
     *
     * @param string $sql The SQL query
     * @param array $params Optional parameters to bind
     * @return $this
     */
    public function query($sql, $params = [])
    {
        $this->stmt = $this->pdo->prepare($sql);
        if (!empty($params)) {
            $this->bind($params);
        }
        return $this;
    }

    /**
     * Bind values to prepared statement
     *
     * @param array $params Parameters to bind
     * @return $this
     */
    public function bind($params)
    {
        foreach ($params as $key => $value) {
            $paramType = $this->determineParamType($value);
            $this->stmt->bindValue(
                is_numeric($key) ? $key + 1 : $key,
                $value,
                $paramType
            );
        }
        return $this;
    }

    /**
     * Execute the prepared statement
     *
     * @param array $params Optional parameters to bind
     * @return bool
     */
    public function execute($params = [])
    {
        if (!empty($params)) {
            $this->bind($params);
        }
        return $this->stmt->execute();
    }

    /**
     * Determine PDO parameter type based on value
     */
    private function determineParamType($value)
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        }
        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }
        if (is_null($value)) {
            return PDO::PARAM_NULL;
        }
        return PDO::PARAM_STR;
    }

    /**
     * Get a single record
     */
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records
     */
    public function all()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get row count
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Get the PDO instance
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Execute a raw query and return statement
     * Useful for operations that don't need parameter binding
     */
    public function rawQuery($sql)
    {
        return $this->pdo->query($sql);
    }
}
