<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 8/16/2017
 * Time: 6:42 AM
 */

namespace Tops\db;

use \PDO;
use PDOStatement;
use Tops\sys\TIdentifier;

abstract class TEntityRepository extends TPdoQueryManager implements IEntityRepository
{

    private $fieldDefinitions;

    protected abstract function getFieldDefinitionList();

    protected abstract function getClassName();

    protected abstract function getTableName();

    private function getFieldDefinitions()
    {
        if (!isset($this->fieldDefinitions)) {
            $this->fieldDefinitions = $this->getFieldDefinitionList();
        }
        return $this->fieldDefinitions;
    }

    protected function getLookupField()
    {
        return 'id';
    }

    private $lastErrorCode = PDO::ERR_NONE;
    public function getLastErrorCode() {
        return $this->lastErrorCode;
    }

    /**
     * @param $id
     * @return object | bool
     */
    public function get($id)
    {
        return $this->getSingleEntity('id = ?', [$id], true);
    }

    public function filterRecordSet(array $ids,$condition='',$delete=false) {
        $count = @count($ids);
        if ($count > 0) {
            $set = '';
            for ($i=0;$i<$count;$i++)
            foreach ($ids as $id) {
                if ($i>0) {
                    $set .= ',';
                }
                $set .= $id;
            }
            if ($condition) {
                $condition = " AND ($condition)";
            }

            $sql = $delete ?
                sprintf("DELETE FROM %s WHERE (id NOT IN (%s)) ",$this->getTableName(),$set).$condition :
                sprintf('UPDATE %s SET active=0 WHERE (id NOT IN (%s)) ',$this->getTableName(),$set).$condition;
            return $this->executeStatement($sql);
        }
    }

    public function filterEntities(array $entities,$condition='',$delete=false) {
        $ids = [];
        foreach ($entities as $entity) {
            $ids[] = $entity->id;
        }
        return $this->filterRecordSet($ids,$condition,$delete);
    }

    public function executeEntityQuery($where, $params, $includeInactive = false, $orderAndLimit = null)
    {
        $sql = $this->addSqlConditionals(
            'SELECT * ' . 'FROM ' . $this->getTableName(),
            $includeInactive,
            $where
        );
        if ($orderAndLimit) {
            $sql .= ' '.$orderAndLimit;
        }
        $stmt = $this->executeStatement($sql, $params);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->getClassName());
        return $stmt;
    }

    public function getRecordCount($condition,$params, $includeInactive=false) {
        $sql = 'SELECT COUNT(*) FROM '.$this->getTableName().' WHERE '.$condition;
        if (!$includeInactive) {
            $sql .= ' AND active = 1';
        }
        $stmt = $this->executeStatement($sql,$params);
        $result = $stmt->fetch(PDO::FETCH_COLUMN);
        return ($result ?? 0);
    }
    public function getCount($includeInactive=false, $where='', $clauses='') {
        $sql = $this->addSqlConditionals(
            'SELECT COUNT(*) FROM '.$this->getTableName(),
            $includeInactive,
            $where
        );
        $stmt = $this->executeStatement($sql);
        $result = $stmt->fetch();
        return (empty($result) ?  0 : $result[0]);
    }

    public function addSqlConditionals($sql, $includeInactive, $where, $clauses='')
    {
        $activeOnly = (
            (!$includeInactive)
            && array_key_exists('active', $this->getFieldDefinitionList())
            && strpos($where, 'active=') === false
        );

        if ($activeOnly) {
            $sql .= ' WHERE active=1 ';
        }

        if (!empty($where)) {
            $sql .= $activeOnly ? ' AND (' . $where . ')' : ' WHERE ' . $where;
        }

        if (!empty($clauses)) {
            $sql = "$sql $clauses";
        }
        return $sql;
    }

    public function getSingleEntity($where, $params, $includeInactive = false)
    {
        $stmt = $this->executeEntityQuery($where, $params, $includeInactive);
        return $stmt->fetch();
    }

    public function getEntityCollection($where, $params, $includeInactive = false, $orderAndLimit = null)
    {
        $stmt = $this->executeEntityQuery($where, $params, $includeInactive,$orderAndLimit);
        $result = $stmt->fetchAll();
        if (empty($result)) {
            return false;
        }
        return $result;
    }

    public function updateValues($id, array $fields, $userName = 'admin')
    {
        $dbh = $this->getConnection();
        $sql = array('UPDATE ' . $this->getTableName() . ' SET');
        $names = array_keys($fields);
        $lastField = sizeof($fields) - 1;
        for ($i = 0; $i <= $lastField; $i++) {
            $name = $names[$i];
            $sql[] = "$name = :$name" . ($i == $lastField ? '' : ',');
        }
        $sql[] = " WHERE id = :id";

        $today = new \DateTime();
        $date = $today->format('Y-m-d H:i:s');

        /**
         * @var PDOStatement
         */
        $stmt = $dbh->prepare(join("\n", $sql));
        $fieldDefinitions = $this->getFieldDefinitions();
        foreach ($fields as $name => $value) {
            switch ($name) {
                case 'uid':
                    // ignore
                    break;
                case 'createdon':
                    // ignore
                    break;
                case 'createdby':
                    // ignore
                    break;
                case 'changedby':
                    $stmt->bindValue(":$name", $userName, $fieldDefinitions[$name]);
                    break;
                case 'changedon':
                    $stmt->bindValue(":$name", $date, $fieldDefinitions[$name]);
                    break;
                default:
                    $stmt->bindValue(":$name", $value, $fieldDefinitions[$name]);
                    break;
            }
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        // $count = $stmt->execute();
        $stmt->execute();
        $this->lastErrorCode = $dbh->errorCode();
        $result = $stmt->rowCount();
        return $result;
    }

    public function executeUpdateStatement($sql,$parameters = []) {
        $dbh = $this->getConnection();
        $stmt = $this->executeStatement($sql,$parameters);
        $this->lastErrorCode = $dbh->errorCode();
        $result = $stmt->rowCount();
        return $result;
    }

    public function update($dto, $userName = 'admin')
    {
        $updateValues = array();
        $fieldNames =  array_keys($this->getFieldDefinitions());
        foreach ($dto as $name => $value) {
            if ($name != 'id' && $name != 'uid' && $name != 'createdby' && $name != 'createdon' && in_array($name,$fieldNames)) {
                $updateValues[$name] = $value;
            }
        }
        return $this->updateValues($dto->id, $updateValues, $userName);
    }

    public function insert($dto, $userName = 'admin')
    {
        $dbh = $this->getConnection();
        $sql = array('INSERT ' . 'INTO ' . $this->getTableName() . ' ( ');
        $fieldDefinitions = $this->getFieldDefinitions();
        $fieldNames = array_keys($fieldDefinitions);
        array_shift($fieldNames); //remove id
        $valuesList = array();
        $lastField = sizeof($fieldNames);
        for ($i = 0; $i < $lastField; $i++) {
            $valuesList[] = ':' . $fieldNames[$i];
        }

        $sql = 'INSERT ' . 'INTO ' . $this->getTableName() . '(  '
            . join(',', $fieldNames)
            . ")\n VALUES ( "
            . join(',', $valuesList)
            . ')';

        $today = new \DateTime();
        $date = $today->format('Y-m-d H:i:s');
        $bound = [];

        /**
         * @var PDOStatement
         */
        $stmt = $dbh->prepare($sql);
        foreach ($dto as $name => $value) {
            switch ($name) {
                case 'id':
                    //ignore
                    break;
                case 'uid' :
                    $uid = TIdentifier::NewId();
                    $stmt->bindValue(":$name", $uid, PDO::PARAM_STR);
                    break;
                case 'createdon':
                    $stmt->bindValue(":$name", $date, PDO::PARAM_STR);
                    break;
                case 'createdby':
                    $stmt->bindValue(":$name", $userName, PDO::PARAM_STR);
                    break;
                case 'changedby':
                    $stmt->bindValue(":$name", $userName, $fieldDefinitions[$name]);
                    break;
                case 'changedon':
                    $stmt->bindValue(":$name", $date, $fieldDefinitions[$name]);
                    break;
                default:
                    // ignore dto properties not in field list
                    if (in_array($name, $fieldNames)) {
                        $bound[] = $name;
                        $stmt->bindValue(":$name", $value, $fieldDefinitions[$name]);
                    }
                    break;
            }
        }

        // $count =
        $stmt->execute();
        $this->lastErrorCode = $stmt->errorCode();
        if ($this->lastErrorCode == PDO::ERR_NONE) {
            return $dbh->lastInsertId();
        }
        return false;
    }

    public function getAll($includeInactive = false)
    {
        $dbh = $this->getConnection();
        $sql = 'SELECT * FROM ' . $this->getTableName();
        if (!$includeInactive && array_key_exists('active', $this->getFieldDefinitionList())) {
            $sql .= ' WHERE active=1';
        }
        /**
         * @var PDOStatement
         */
        $stmt = $dbh->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->getClassName());
        return $result;
    }

    public function deleteByForeignKey($key,$value,$filterCondition = null) {
        $dbh = $this->getConnection();
        $sql = "DELETE FROM ".$this->getTableName()." WHERE $key = ?";
        if ($filterCondition) {
            $sql .= " AND ($filterCondition)";
        }
        $stmt = $this->executeStatement($sql,[$value]);
        $this->lastErrorCode = $stmt->errorCode();
        if ($this->lastErrorCode == PDO::ERR_NONE) {
            return $stmt->rowCount();
        }
        return false;
    }

    public function delete($id)
    {
        $dbh = $this->getConnection();
        $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE id = ?';
        /**
         * @var PDOStatement
         */
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute(array($id));
        $this->lastErrorCode = $stmt->errorCode();
        if ($this->lastErrorCode == PDO::ERR_NONE) {
            return $stmt->rowCount();
        }
        return false;
    }

    public function remove($id)
    {
        $dbh = $this->getConnection();
        return $this->updateValues($id, array('active' => 0));
    }

    public function restore($id)
    {
        $dbh = $this->getConnection();
        return $this->updateValues($id, array('active' => 1));
    }

    public function getEntity($value, $includeInactive = false, $fieldName = null)
    {
        if ($fieldName === null) {
            $fieldName = $this->getLookupField();
        }
        return $this->getSingleEntity("$fieldName = ?", [$value], $includeInactive);
    }

    public function getEntityByUid($value, $includeInactive = false)
    {
        $fieldDefinitions = $this->getFieldDefinitions();
        if (array_key_exists('uid',$fieldDefinitions) && TIdentifier::IsValid($value)) {
            return $this->getSingleEntity("uid = ?", [$value], $includeInactive);
        }
        return false;
    }

    public function getIdForUid($value)
    {
        if (TIdentifier::IsValid($value)) {
            return $this->getIdForFieldValue('uid',$value);
        }
        return false;
    }

    public function getIdForCode($value)
    {
        return $this->getIdForFieldValue('code', $value);
    }

    public function getIdForFieldValue($fieldName,$value) {
        $fieldDefinitions = $this->getFieldDefinitions();
        if (array_key_exists($fieldName,$fieldDefinitions)) {
            $sql = 'SELECT id FROM '.$this->getTableName()." WHERE $fieldName = ?";
            return $this->getValue($sql, [$value]);
        }
        return false;
    }

    public function getFieldValue($fieldName,$id) {
        $sql = "SELECT $fieldName FROM ".$this->getTableName().' WHERE id=?';
        $stmt = $this->executeStatement($sql,[$id]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getEntityClassName() {
        return $this->getClassName();
    }

    public function getColumnValues($columName,$condition=null,$params = [],$activeOnly = true) {
        $sql = "SELECT $columName FROM ".$this->getTableName();

        if ($condition) {
            $sql .= " WHERE ($condition) ";
        }
        if ($activeOnly) {
            $sql .= ($condition ? ' AND ' : ' WHERE ').' active=1';
        }
        $stmt = $this->executeStatement($sql,$params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param $condition
     * @param $params
     * @param $activeOnly
     * @return \stdClass (not entity object)
     */
    public function getData($condition,$params,$activeOnly=true) {
        $sql = "SELECT * FROM ".$this->getTableName();
        if ($condition) {
            $sql .= " WHERE ($condition) ";
        }
        if ($activeOnly) {
            $sql .= ($condition ? ' AND ' : ' WHERE ').' active=1';
        }
        $stmt = $this->executeStatement($sql,$params);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getEmptyDTO() {
        $result = new \stdClass();
        $fields = $this->getFieldDefinitionList();
        foreach ($fields as $name => $type) {
            if ($name == 'id') {
                $value = 0;
            }
            else {
                $value = $type == PDO::PARAM_STR ? '' : null;;
            }
            $result->$name = $type == PDO::PARAM_STR ? '' : null;

        }
        return $result;
    }

    public function newEntity() {
        $className = $this->getClassName();
        return new $className();
    }
}