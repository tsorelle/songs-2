<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2017-12-15 18:44:53
 */ 
namespace Tops\db\model\repository;


use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class ProcessLogRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'tops_process_log';
    }

    protected function getDatabaseId() {
        return 'tops-db';
    }

    protected function getClassName() {
        return 'Tops\db\model\entity\ProcessLogEntry';
    }

    protected function getFieldDefinitionList()
    {
        return array(
            'id'=>PDO::PARAM_INT,
            'processCode'=>PDO::PARAM_STR,
            'posted'=>PDO::PARAM_STR,
            'event'=>PDO::PARAM_STR,
            'message'=>PDO::PARAM_STR,
            'messageType'=>PDO::PARAM_INT,
            'detail'=>PDO::PARAM_STR
        );
    }
}