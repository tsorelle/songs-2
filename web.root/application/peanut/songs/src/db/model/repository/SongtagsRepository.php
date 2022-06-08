<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:32
 */ 
 // Deployment NS: namespace Peanut\songs\db\model\repository;

namespace Peanut\ORM\repository;


use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class SongtagsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'tls_songtags';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
        return 'Peanut\songs\db\model\entity\Songtag';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'songId'=>PDO::PARAM_INT,
        'tagId'=>PDO::PARAM_INT);
    }
}