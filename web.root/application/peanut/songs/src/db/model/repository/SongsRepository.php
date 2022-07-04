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

class SongsRepository extends \Tops\db\TEntityRepository
{
    public function getSongByTitle($title)
    {
        return $this->getSingleEntity('title=?',[$title]);
    }

    protected function getTableName() {
        return 'tls_songs';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Peanut\songs\db\model\entity\Song';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'title'=>PDO::PARAM_STR,
        'lyrics'=>PDO::PARAM_STR,
        'publicdomain'=>PDO::PARAM_STR);
    }
}