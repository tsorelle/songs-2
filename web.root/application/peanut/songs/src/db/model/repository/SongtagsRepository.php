<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:32
 */ 
namespace Peanut\songs\db\model\repository;

use \PDO;
use PDOStatement;
use Peanut\songs\db\model\entity\Songtag;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class SongtagsRepository extends \Tops\db\TEntityRepository
{
    public function addTags($songId, array $add)
    {
        foreach ($add as $tagId) {
            $songTag = new Songtag();
            $songTag->tagId = $tagId;
            $songTag->songId = $songId;
            $this->insert($songTag);
        }
    }

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

    public function getTagValues($songId,$type= null) {
        $sql = 'SELECT st.tagId FROM tls_songtags st JOIN tls_tags t on t.id = st.tagId '.
                'WHERE st.songId = ?';
        $params = [$songId];
        if ($type !== null) {
            $sql .= ' AND t.type = ? ';
            $params[] = $type;
        }
        $sql .=  ' ORDER BY t.name';
        $stmt = $this->executeStatement($sql,$params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function deleteTag($songId,$tagId) {
        $sql = 'DELETE FROM tls_songtags WHERE songId = ? and tagId = ?';
        $this->executeStatement($sql,[$songId,$tagId]);
    }

    public function deleteTags($songId, array $tagIds) {
        $list = implode(',',$tagIds);
        $sql =  sprintf('DELETE FROM tls_songtags WHERE songId = ? and tagId in (%s)',$list);
        $this->executeStatement($sql,[$songId]);
    }


}