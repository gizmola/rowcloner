<?php declare(strict_types=1);
namespace App\Gizmola;

use Monolog\Logger;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class RowCloner {
    use DebugTrait;

    private Connection $conn;
    private Logger $logger;

    public function __construct(Connection $conn, Logger $logger)
    {
        $this->conn = $conn;
        $this->logger = $logger;        
    }

    public function clone($table, $idName, $id, $nullColumns) {
        $sql = "SELECT * FROM `$table` WHERE `$idName` = ?";
        $this->debug("query sql: $sql",['id' => $id]);
        try {
            $row = $this->conn->fetchAssociative($sql, [$id]);
            if ($row) {
                $row = $this->setNullColumns($row, $nullColumns);
                $this->debug('nulled row object', $row);
                return ($this->duplicate($table, $row));
         
            } else {
                return false;
            }  
        } catch(Exception $e) {
            $this->debug('dbal exception:' .$e->getMessage() . '[' . $e->getCode() . ']', ['sql' => $sql, 'id' => $id]);
            return false;
        }
    }

    public function getNewId() {
        return $this->conn->lastInsertId();
    }

    private function duplicate($table, $row) {
        $this->conn->insert($table, $row);
        $this->debug("duplicate insert",['row' => $row]);
        return true;
    }

    private function setNullColumns($row, $nullColumns) {
        foreach ($nullColumns as $column) {
            $row[$column] = null;
        }
        return $row;
    }
}