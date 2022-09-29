<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:04
 */ 

namespace Peanut\songs\db\model\repository;

use \PDO;
use PDOStatement;
use Peanut\songs\db\model\entity\Songpage;
use phpDocumentor\Reflection\Types\Boolean;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class SongpagesRepository extends \Tops\db\TEntityRepository
{
    const searchTypeNone = 0;
    const searchTypeTitle = 1;
    const searchTypeText = 2;
    const searchTypeInactive = 3;
    const orderTitle = 1;
    const orderDate = 2;
    const orderDateDesc = 3;

    const songSearchHeader =
        'select s.id, s.title as `name`, p.description, p.contentId as `code`,p.youtubeId, p.introduction, p.lyricsformatted, s.notes, '.
        "if(p.hasicon = 1,concat('/assets/img/songs/icons/',p.contentId,'.jpg'),'/assets/img/songs/icons/default.jpg') as iconsrc, ".
        "if(p.hasicon = 1,concat('/assets/img/songs/thumbnails/',p.contentId,'.jpg'),'/assets/img/songs/thumbnails/default.jpg') as thumbnailsrc, ".
        "concat('/song/',p.contentId) as songUrl, p.active ";

    protected function getTableName() {
        return 'tls_songpages';
    }
    protected function getSongTableName() {
        return 'tls_songs';
    }

    protected function getIndexTableName() {
        return 'tls_songsearchindex';
    }

    protected function getDatabaseId() {
        return null;
    }

    protected function getClassName() {
       return 'Peanut\songs\db\model\entity\Songpage';
    }

    public function getPageBycontentId($contentId)
    {
        $result = $this->getSingleEntity("contentId=?",[$contentId]);
        return $result;
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'contentId'=>PDO::PARAM_STR,
        'songId'=>PDO::PARAM_INT,
        'description'=>PDO::PARAM_STR,
        'introduction'=>PDO::PARAM_STR,
        'commentary'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR,
        'postedDate'=>PDO::PARAM_STR,
        'pageimage'=>PDO::PARAM_STR,
        'imagecaption'=>PDO::PARAM_STR,
        'youtubeId'=>PDO::PARAM_STR,
        'hasicon'=>PDO::PARAM_STR,
        'lyricsformatted'=>PDO::PARAM_STR
        );
    }

    public function getPageBySongId(int $songId, $indludeInactive = false)
    {
        return $this->getEntity($songId, $indludeInactive, 'songid');
    }

    public function getAllSongsCount() {
        $stmt = $this->executeStatement('SELECT COUNT(*) FROM '.$this->getTableName());
        return $stmt->fetchColumn();
    }

    public function getSongCount() {

    }
    public function getSongPageCount($request = null) {
        $countReq = is_object($request) ? clone $request : $request;
        if ($countReq !== null) {
            unset($countReq->pageSize);
            unset($countReq->page);
            unset($countReq->order);
        }
        $stmt = $this->executeSearch('SELECT COUNT(*) ',$countReq);
        return (int)$stmt->fetchColumn();
    }

    public function getSongPageList($request = null) {
        $stmt = $this->executeSearch(self::songSearchHeader,$request);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getRandomSongIds($type='favorite',$count=12)
    {
        $sql = 'SELECT p.id FROM tls_songpages p JOIN tls_songtags st on st.songId = p.songId'.
            " JOIN tls_tags tt on tt.id = st.tagId WHERE tt.code = ? AND hasicon=1 ORDER BY RAND() LIMIT ".$count;
        $stmt = $this->executeStatement($sql,[$type]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function selectSongsFromList($idList) {
        $sql = self::songSearchHeader.
            ' FROM '.$this->getTableName().' p JOIN '.
                $this->getSongTableName().' s ON s.id = p.songId '
               .' WHERE p.id IN ('.implode(',',$idList).' and active = 1)';
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getLatestSongs($limit = 6) {
        $sql = self::songSearchHeader.
            ' FROM '.$this->getTableName().' p JOIN '.
            $this->getSongTableName().' s ON s.id = p.songId '.
                ' WHERE active=1 '
            .' ORDER BY p.postedDate DESC LIMIT '.$limit;
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);

    }

    public function getLatestSongsList($limit = 6) {
        $sql = self::songSearchHeader.
            ' FROM '.$this->getTableName().' p JOIN '.
            $this->getSongTableName().' s ON s.id = p.songId '.
            ' WHERE active=1 '
            .' ORDER BY p.postedDate DESC LIMIT '.$limit;
        $stmt = $this->executeStatement($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getLatestSongsLinkList(string $url,$limit=6)
    {
        $sql = 'select s.id, s.title as `name`, p.contentId as `code`,p.description, '.
	    "concat('$url',p.contentId) as url, p.active ".
            ' FROM '.$this->getTableName().' p JOIN '.
            $this->getSongTableName().' s ON s.id = p.songId '.
            ' WHERE active=1 '
            .' ORDER BY p.postedDate DESC LIMIT '.$limit;

        $stmt = $this->executeStatement($sql,[$url]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function executeSearch($sql, $request = null) {
        $request = $request ?? new \stdClass();
        $searchType = $request->searchType ?? self::searchTypeNone;
        $searchTerms = $request->searchTerms ?? null;
        $filter = $request->filter ?? null;
        $order = $request->order ?? self::orderTitle;
        switch ($order) {
            case self::orderDate:
                $order = 'p.postedDate ASC';
                break;
            case self::orderDateDesc:
                $order = 'p.postedDate DESC';
                break;
            default:
                $order = 's.title';
                break;
        }
        $pageNo = $request->page ?? 1;
        $pageSize =  $request->pageSize ?? 0;

        $sql .= 'FROM '.$this->getTableName().' p JOIN '.$this->getSongTableName().' s ON s.id = p.songId ';

        $conditions = [];
        $params = [];

        if ($filter) {
            $sql .= ' join tls_songtags st on st.songId = s.id ';
            if (is_numeric($filter)) {
                $conditions[] =  'st.tagId = ?';
            }
            else {
                $sql .= ' join tls_tags tg on tg.id = st.tagId ';
                $conditions[] =  'tg.code = ?';
            }
            $params = [$filter];
        }

        $active = 1;
        if ($searchType == self::searchTypeInactive) {
            if ($searchTerms) {
                $conditions[] = 'title like ?';
                $params[] = "%$searchTerms%";
            }
            $active = 0;
        }
        else if ($searchType == self::searchTypeTitle) {
            $conditions[] = 'title like ?';
            $params[] = "%$searchTerms%";
        }
        else if ($searchType == self::searchTypeText) {
            $terms = $this->parseSearchTerms($searchTerms);
            if (count($terms) > 0) {
                $sql .= ' join tls_songindex idx on p.songId = idx.id ';
                $clause = '(idx.songText LIKE ? ';
                for ($i = 0; $i<count($terms)-1; $i++) {
                    $clause .= 'OR idx.songText LIKE ? ';
                }
                $clause.= ')';
                $conditions[] = $clause;
                $params = array_merge($params,$terms);
            }
        }

        if (empty($conditions)) {
            $sql .= ' WHERE p.active = ?';
        }
        else {
            $sql .= ' WHERE '. implode(' AND ',$conditions).' AND p.active = ?';
        }

        $sql .= " ORDER BY $order ";

        if ($pageSize) {
            $offset = ($pageNo > 0) ? ($pageNo - 1) * $pageSize : 0;
            $sql .=  sprintf('LIMIT %d,%d',$offset,$pageSize);
        }

        $params[] = $active;
        return $this->executeStatement($sql,$params);

    }

    public function parseSearchTerms($searchTerms)
    {
        $result = [];
        $searchTerms = str_replace("'",'"',$searchTerms);
        $terms = explode(' ',$searchTerms);
        $quote = '';
        foreach ($terms as $term) {
            $term = trim($term);
            if ($quote) {
                $end = substr($term,-1) == '"';
                if ($end) {
                    $term = trim(substr($term,0, -1));
                    // $term = trim(substr($term,0,strlen($term) -1));
                }
                if ($term) {
                    $quote .= ' ' . $term;
                }
                if ($end) {
                    $result[] = "%$quote%";
                    $quote = '';
                }
            }
            else {
                if (substr($term,0,1) == '"') {
                    $quote = substr($term,1);
                }
                else {
                    $result[] = "%$term%";
                }
            }
        }
        if ($quote) {
            $result[] = $quote;
        }
        return $result;
    }

}