<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:04
 */ 

namespace Peanut\songs\db\model\repository;

use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class SongpagesRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'tls_songpages';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Peanut\songs\db\model\entity\Songpage';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'songId'=>PDO::PARAM_INT,
        'introduction'=>PDO::PARAM_STR,
        'commentary'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR,
        'postedDate'=>PDO::PARAM_STR,
        'contenttype'=>PDO::PARAM_STR,
        'pageimage'=>PDO::PARAM_STR,
        'imagecaption'=>PDO::PARAM_STR);
    }
}