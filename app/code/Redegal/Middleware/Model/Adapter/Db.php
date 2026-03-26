<?php

namespace Redegal\Middleware\Model\Adapter;

class Db
{
    public $resourceConnection;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTableName($tablename)
    {
        return $this->resourceConnection->getTableName($tablename);
    }

    public function quote($value)
    {
        return $this->resourceConnection->quote($value);
    }

    public function insert($tablename, array $bind)
    {
        $tablename = $this->getTableName($tablename);
        $connection = $this->getConnection();
        $connection->insert($tablename, $bind);
        return $connection->lastInsertId($tablename);
    }

    public function insertOnDuplicate($tablename, array $data, array $fields = [])
    {
        $tablename = $this->getTableName($tablename);
        $connection = $this->getConnection();
        $connection->insertOnDuplicate($tablename, $data, $fields);
        return $connection->lastInsertId($tablename);
    }

    public function update($tablename, array $bind, $where = '')
    {
        $tablename = $this->getTableName($tablename);
        $connection = $this->getConnection();
        return $connection->update($tablename, $bind, $where);
    }

    public function delete($tablename, $where = [])
    {
        $tablename = $this->getTableName($tablename);
        $connection = $this->getConnection();
        return $connection->delete($tablename, $where);
    }

    public function select($tablename, array $fields, $where = [])
    {
        $connection = $this->getConnection();
        $sql = $this->buildSelect($tablename, $fields, $where);
        return $connection->fetchAll($sql);
    }

    public function first($tablename, array $fields, $where = [])
    {
        $connection = $this->getConnection();
        $sql = $this->buildSelect($tablename, $fields, $where);
        return $connection->fetchRow($sql);
    }

    public function count($tablename, array $fields = ['COUNT(*)'], $where = [])
    {
        $connection = $this->getConnection();
        $sql = $this->buildSelect($tablename, $fields, $where);
        return intval($connection->fetchOne($sql));
    }

    public function setAutoIncrement($tablename, $value = null)
    {
        $connection = $this->getConnection();
        $value = ($value == null) ? ($this->count($tablename) + 1) : $value;
        $sql = "ALTER TABLE $tablename AUTO_INCREMENT = $value";
        return  $connection->query($sql);
    }

    public function getColumn($tablename, $field, $where = [])
    {
        return $this->first($tablename, [$field], $where)[$field];
    }

    public function buildSelect($tablename, array $fields, $where = [])
    {
        $tablename = $this->getTableName($tablename);
        $connection = $this->getConnection();
        $sql = $connection->select()->from($tablename, $fields);
        foreach ($where as $condition => $value) {
            $sql = $sql->where($condition, $value);
        }
        return $sql;
    }

    public function transaction($operation, $value = true)
    {
        if ($value) {
            $this->$operation();
        }
    }

    public function __call($method, $arguments)
    {
        $connection = $this->getConnection();
        return call_user_func_array(array($connection, $method), $arguments);
    }

    public function dbNow()
    {
        return new \Zend_Db_Expr('NOW()');
    }

}
