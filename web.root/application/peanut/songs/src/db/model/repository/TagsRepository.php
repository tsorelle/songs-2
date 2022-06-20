<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:32
 */ 
namespace Peanut\songs\db\model\repository;

use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class TagsRepository extends \Tops\db\TEntityRepository
{
    public function getLinkList(string $url,string $typeCode)
    {
        $sql = 'select id, name, description, type, concat(?,code) as url'.
            ' FROM '.$this->getTableName().' WHERE `type` = ?'.
            ' ORDER BY `name`';
        $stmt = $this->executeStatement($sql,[$url, $typeCode]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    protected function getTableName() {
        return 'tls_tags';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Peanut\songs\db\model\entity\Tag';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'name'=>PDO::PARAM_STR,
        'description'=>PDO::PARAM_STR,
        'type'=>PDO::PARAM_STR,
        'code'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR
        );
    }

    public function getLookupList($type,$includeInactive = false) {
        return $this->getEntityCollection('type=?',[$type],$includeInactive,'order by name');
    }
}