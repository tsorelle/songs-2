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
    const songPageTableName = 'tls_songpages';
    public function getSongByTitle($title)
    {
        return $this->getSingleEntity('title=?',[$title]);
    }

    public function getUnassignedSongsList()
    {
        $sql = 'SELECT s.id, s.title as name FROM '.$this->getTableName().' s '.
                'WHERE s.id not in (select songId from '.self::songPageTableName.')';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll();
    }

    protected function getTableName() {
        return 'tls_songs';
    }

    public function deleteSong($id)
    {

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
        'publicdomain'=>PDO::PARAM_STR,
        'notes'=>PDO::PARAM_STR
        );
    }
}