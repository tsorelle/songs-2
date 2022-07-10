<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-07-08 12:40:00
 */ 
namespace Peanut\songs\db\model\repository;

use \PDO;
use PDOStatement;
use Peanut\songs\db\model\entity\Songset;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class SongsetsRepository extends \Tops\db\TEntityRepository
{
    public function GetAllSet() {
        $set = new Songset();
        $set->id = 0;
        $set->setname = 'All';
        $set->user = '';
        return $set;
    }
    public function GetDefaultSet() {
        $set = new SongSet();
        $set->id = 1;
        $set->setname = 'Default';
        $set->user = '';
        return $set;
    }


    public static function CreateSongSet($request)
    {
        $set = new Songset();
        $set->id = -1;
        $set->setname = '';
        $set->user = '';
        return $set;
    }

    public function getSongInfoList($setid)
    {
        $params = [];
        $sql = 'SELECT s.id,s.title FROM tls_songs s';
        if ($setid > 0) {
            $sql .= ' JOIN tls_songsetmembers m on m.songid = s.id WHERE m.setid = ?';
            $params = [$setid];
        }
        $sql .= ' ORDER BY s.title';
        $stmt = $this->executeStatement($sql,$params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    protected function getTableName() {
        return 'tls_songsets';
    }

    private function getAssociationTableName() {
        return 'tls_songsetmembers';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Peanut\songs\db\model\entity\Songset';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'setname'=>PDO::PARAM_INT);
    }
    private function addMember($setId, $songId) {

    }

    private function removeMember($setId,$songId) {

    }

    private function setMembers($setId,array $songIdList) {

    }

    private function getMembers($setId) {

    }

}