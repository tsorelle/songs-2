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
        'imagecaption'=>PDO::PARAM_STR,
        'youtubeId'=>PDO::PARAM_STR,
        'hasicon'=>PDO::PARAM_STR,
        'hasthumbnail'=>PDO::PARAM_STR);
    }

    public function getPageBySongId(int $songId, $indludeInactive = false)
    {
        return $this->getEntity($songId, $indludeInactive, 'songid');
    }

    public function getSongPageList($request = null) {
        $searchType = $request->searchType ?? 'basic';
        $searchTerms = $request->searchTerms ?? null;
        $filter = $request->filter ?? null;
        $order = $request->order ?? 'title';
        $pageNo = $request->page ?? 1;
        $pageSize =  $request->pageSize ?? 0;

        $sql =
            'select s.id, s.title, s.description,s.contentid as `code`,p.youtubeId,p.introduction, '.
            "if(p.hasicon = 1,concat('/assets/img/songs/icons/',s.contentId,'.jpg'),'/assets/img/songs/icons/default.jpg') as iconsrc, ".
            "if(p.hasicon = 1,concat('/assets/img/songs/thumbnails/',s.contentId,'.jpg'),'/assets/img/songs/thumbnails/default.jpg') as thumbnailsrc, ".
            "concat('/songs/',s.contentid) as songUrl, p.active ".

            'FROM '.$this->getTableName().' p JOIN '.$this->getSongTableName().' s ON s.id = p.songId ';

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

        if ($searchType == 'title') {
            $conditions[] = 'title like ?';
            $params[] = "%$searchTerms%";
        }
        else if ($searchType == 'text') {
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

        if (!empty($conditions)) {
            $sql .= ' WHERE '. implode(' AND ',$conditions);
        }

        $sql .= ' ORDER BY s.'.$order.' ';

        if ($pageSize) {
            $offset = ($pageNo > 0) ? ($pageNo - 1) * $pageSize : 0;
            $sql .=  sprintf('LIMIT %d,%d',$offset,$pageSize);
        }

        $stmt = $this->executeStatement($sql,$params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // for test only
    public function findByTitle($title) {
        $sql = 'SELECT * FROM tls_songs where title like ?';
        $params = [];
        $params[] = "%$title%";

        $stmt = $this->executeStatement($sql,$params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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