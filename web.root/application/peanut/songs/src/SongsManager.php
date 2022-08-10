<?php
namespace Peanut\songs;

use Peanut\songs\db\model\entity\Song;
use Peanut\songs\db\model\entity\Songpage;
use Peanut\songs\db\model\entity\Songset;
use Peanut\songs\db\model\repository\SongindexRepository;
use Peanut\songs\db\model\repository\SongpagesRepository;
use Peanut\songs\db\model\repository\SongsetsRepository;
use Peanut\songs\db\model\repository\SongsRepository;
use Peanut\songs\db\model\repository\SongtagsRepository;
use Peanut\songs\db\model\repository\TagsRepository;
use Tops\cache\TAbstractCache;
use Tops\cache\TSessionCache;
use Tops\sys\TNameValuePair;
use Tops\sys\TPath;


class SongsManager
{
    /**
     * @var TAbstractCache
     */
    private $sessionCache;
    private function getSessionCache() {
        if (!isset($this->sessionCache)) {
            $this->sessionCache = new TSessionCache();
        }
        return $this->sessionCache;
    }

    private $songsRepository;
    private function getSongsRepository() : SongsRepository {
        if (!isset($this->songsRepository)) {
            $this->songsRepository = new SongsRepository();
        }
        return $this->songsRepository;
    }

    private $songsetsRepository;
    private function getSongsetsRepository() : SongsetsRepository {
        if (!isset($this->songsRepository)) {
            $this->songsetsRepository = new SongsetsRepository();
        }
        return $this->songsetsRepository;
    }

    private $songpagesRepository;
    private function getSongpagesRepository() : SongpagesRepository {
        if (!isset($this->songpagesRepository)) {
            $this->songpagesRepository = new SongpagesRepository();
        }
        return $this->songpagesRepository;
    }

    private $songTagsRepository;
    private function getSongTagsRepository() : SongtagsRepository {
        if (!isset($this->tagsRepository)) {
            $this->songTagsRepository = new SongtagsRepository();
        }
        return $this->songTagsRepository;
    }

    private $tagsRepository;
    private function getTagsRepository() : TagsRepository {
        if (!isset($this->tagsRepository)) {
            $this->tagsRepository = new TagsRepository();
        }
        return $this->tagsRepository;
    }

    private $songIndexRepository;
    private function getSongIndexRepository() : SongindexRepository {
        if (!isset($this->songIndexRepository)) {
            $this->songIndexRepository = new SongindexRepository();
        }
        return $this->songIndexRepository;
    }

    public function getAllSongsSet()
    {
        $set = new Songset();
        $set->setname = 'All';
        $set->id = 0;
        return $set;
    }
    public function getDefaultSongSet() {
        $set = new Songset();
        $set->setname = 'Default';
        $set->id = 1;
        return $set;
    }

    public function getSongSetList() {
        $result = [
            $this->getAllSongsSet(),
            $this->getDefaultSongSet(),
        ];

        $additional = $this->getSongsetsRepository()->getEntityCollection('id <> ?',[1],false,'ORDER BY setname ASC');
        if (empty($additional)) {
            return $result;
        }
        return array_merge($result,$additional);
    }

    public function getFeaturedSongsList($expires='1D')
    {
        $cache = $this->getSessionCache();
        $list = $cache->Get('featured-songs');
        if (!$list) {
            $list = $this->getSongpagesRepository()->getRandomSongIds();
            $cache->Set('featured-songs',$list,$expires);
        }
        return $this->getSongpagesRepository()->selectSongsFromList($list);
    }

    public function getLatestSongs($limit=6) {
        return $this->getSongpagesRepository()->getLatestSongs($limit);
    }

    public function getSongPages($request=null) {
        return $this->getSongpagesRepository()->getSongPageList($request);
    }

    public function getSongCount($setId=0) {
        if ($setId === 'all' || !$setId) {
            return $this->getSongsRepository()->getCount();
        }

        return $this->getSongsetsRepository()->getSongCount($setId);
    }

    public function getSongPageCount($request=null) {
        return $this->getSongpagesRepository()->getSongPageCount($request);
    }
    public function getSongTypesLookup() {
        return $this->getTagsRepository()->getLookupList('type');
    }

    public function getInstrumentsLookup() {
        return $this->getTagsRepository()->getLookupList('instrument');
    }

    public function removeSong($songId,$pageId=0) {
        if (!$pageId) {
            $page = $this->getSongpagesRepository()->getPageBySongId($songId);
            if ($page) {
                $pageId = $page->id;
            }
        }
        if (!empty($pageId)) {
            $this->getSongpagesRepository()->remove($pageId);
        }
        $this->getSongsRepository()->remove($songId);
    }

    public function getSongPage($songPageId) {
        $repo = $this->getSongpagesRepository();

        if (is_numeric($songPageId)) {
            $page = $repo->get($songPageId);
        }
        else {
            $page = $repo->getSingleEntity('contentId=?',[$songPageId]);
        }
        if (!$page) {
            return false;
        }
        $songId = $page->songId;
        $song = $this->getSongsRepository()->get($songId);
        $page->song = $song;
        $songTagsRepo = $this->getSongTagsRepository();
        $page->types = $songTagsRepo->getTagValues($songId, 'type');
        return $page;
    }

    public function updateSongTags($songId, array $tags) {
        $songTags = $this->getSongTagsRepository();
        $current = $songTags->getTagValues($songId);
        $delete = [];
        $add = [];
        foreach ($current as $tag) {
            if (!in_array($tag,$tags)) {
                $delete[] = $tag;
            }
        }
        foreach ($tags as $tag) {
            if (!in_array($tag,$current)) {
               $add[] = $tag;
            }
        }
        if (!empty($delete)) {
            $songTags->deleteTags($songId,$delete);
        }
        if (!empty($add)) {
            $songTags->addTags($songId,$add);
        }
    }


    public function updateSongPage($pageDto) {
        $response = new \stdClass();

        $songDto = $pageDto->song ?? null;
        if (!$songDto) {
            $response->error = 'No song';
            return $response;
        }
        unset($pageDto->song);
        $songId = $songDto->id ?? 0;
        $pageId = $pageDto->id ?? 0;

        $types = $pageDto->types ?? null;
        if ($types !== null) {
            unset($pageDto->types);
        }
        $newSong = empty($songDto->id);
        $newPage = empty($pageDto->id);

        $songRepo = $this->getSongsRepository();

        if ($newSong) {
            $song = new Song();
        }
        else {
            $song = $songRepo->get($songId);
            if (!$song) {
                $response->error = 'Song #'.$songId.' not found.';
                return $response;
            }
        }

        $pageRepo = $this->getSongpagesRepository();

        if ($newPage) {
            $page = new Songpage();
        }
        else {
            $page = $pageRepo->get($pageId);
            if (!$page) {
                $response->error = 'Page #'.$pageId.' not found.';
                return $response;
            }
        }

        $song->assignFromObject($songDto);
        if ($newSong) {
            $songId = $songRepo->insert($song);
            $song->id = $songId;
        }
        else {
            $songRepo->update($song);
        }

        $hasicon = $this->hasSongImage('icons',$pageDto->contentId);

        $page->assignFromObject($pageDto);
        $page->hasicon = $hasicon ? 1 : 0;


        if ($newPage) {
            $page->songId = $song->id;
            $pageId = $pageRepo->insert($page);
            $page->id = $pageId;
        }
        else {
            $pageRepo->update($page);
        }

        $this->updateSongIndex($page,$song);
        
        if ($types !== null) {
            $this->updateSongTags($pageDto->songId,$types);
        }

        $response->pageId = $pageId;
        $response->songId = $songId;
        $response->hasicon = $hasicon;

        return $response;
    }

    public function updateSongIndex(Songpage $songpage, $song=null)
    {
        if (!$song) {
            $song = $this->getSongsRepository()->get($songpage->songId);
            if ($song === false) {
                return false;
            }
        }

        $textArray = [$song->title, $songpage->description];
        if (!empty($song->lyrics)) {
            $textArray[] = strip_tags($song->lyrics);
        }
        if (!empty($songpage->imagecaption)) {
            $textArray[] = $songpage->imagecaption;
        }

        if (!empty($songpage->introduction)) {
            $textArray[] = strip_tags($songpage->introduction);
        }
        if (!empty($songpage->commentary)) {
            $textArray[] = strip_tags($songpage->commentary);
        }

        $text = str_replace(["\t","\r\n","\n"],' ',implode(' ',$textArray));
            
        return $this->getSongIndexRepository()->updateIndex($songpage->songId,
            implode(' ',$textArray));
        
    }

    public function getLatestSongLinks($limit=6) {
        return $this->getSongpagesRepository()->getLatestSongsLinkList('/song/',$limit);
    }

    public function getSongTypeLinks()
    {
        return $this->getTagsRepository()->getLinkList('/songs/','type');
    }

    public function hasSongImage($imgType,$contentId) {
        $songImgPath = "assets/img/songs/$imgType/$contentId.jpg";
        $path = TPath::fromFileRoot($songImgPath);
        $result = @file_exists($path);
        return ($result === true);
    }

    public function updateSong($song)
    {
        return $song->id;
    }

    public function getFrontPageItems($type) {
        if ($type = 'featured') {
            return $this->getFeaturedSongsList(12);
        }
        else {
            return $this->getSongpagesRepository()->getLatestSongs(12);
        }
    }

    /** @noinspection HtmlUnknownTarget */
    public static function renderFrontPageCarousel($type) {
        $manager = new SongsManager();
        $songs =  ($type == 'featured') ?
            $manager->getFeaturedSongsList(12) :
            $manager->getLatestSongs(12);

        $template =
            '   <div class="col-md-4">'."\n".
            '       <h5><a href="%s">%s</a></h5>'."\n".
            '       <p>%s</p>'."\n".
            '       <a href="%s"><img class="img img-fluid img-thumbnail" src="/assets/img/songs/icons/%s.jpg" '."\n".
            '            style="float:left;margin-right: 1rem" alt="song icon"></a>'."\n".
            '       %s'."\n".
            '   </div>';

        $i = 0;
        $h = '<div class="carousel-item active">';
        for ($r=0;$r<4;$r++) {
            print("$h\n");
            $h = '<div class="carousel-item">';
            print '<div class="row">'."\n";
            for($c=0;$c<3;$c++) {
                $song = $songs[$i];
                $i++;
                $test = sprintf($template,
                    $song->songUrl,
                    $song->name,
                    $song->description,
                    $song->songUrl,
                    $song->code,
                    $song->introduction
                );
                printf($template,
                    $song->songUrl,
                    $song->name,
                    $song->description,
                    $song->songUrl,
                    $song->code,
                    $song->introduction
                );
            }
            print "    </div>\n";
            print "</div>\n";
        }
    }

    public function validateNewSong($request)
    {
        $contentId = trim($request->contentId ?? '');
        if (!$contentId) {
            return 'Content Id is required';
        }
        $title = trim($request->title ?? '');
        if (!$title) {
            return 'Title is required';
        }

        $repo = $this->getSongpagesRepository();
        $existing = $repo->getPageBycontentId($contentId);
        if ($existing) {
            return "Content Id '$contentId' is already used.";
        }
        $repo = $this->getSongsRepository();
        $existing = $repo->getSongByTitle($title);
        if ($existing) {
            return "Title '$title' is already used.";
        }

        return 'ok';
    }

    /**
     * @param $songId
     * @return bool| Song
     */
    public function getSong($songId)
    {
        return $this->getSongsRepository()->get($songId);
    }

    public function getUnassignedSongsList()
    {
        return $this->getSongsRepository()->getUnassignedSongsList();
    }

    public function getSongLyrics($id)
    {
        $song = $this->getSong($id);
        return $song->lyrics;
    }

    public function getSongInfoInSet($setid=0)
    {
        return $this->getSongsetsRepository()->getSongInfoList($setid);
    }

    public function getSongInfoNotInSet($setid)
    {
        return $this->getSongsetsRepository()->getSongInfoListNotInSet($setid);
    }

    public function getSetById(int $setId)
    {
        return $this->getSongsetsRepository()->get($setId);
    }

    public function checkUniqueSetName($setName, int $setId, $username)
    {
        if (!$setId) {
            $setId = 0;
        }
        $count = $this->getSongsetsRepository()->countUniqueSetNames($setName,  $setId, $username);
        return empty($count);
    }

    public function createSet($setName, $username)
    {
        return $this->getSongsetsRepository()->newSongSet($setName,$username);
    }

    public function updateSetSongs($setId,$songIds) {
        $this->getSongsetsRepository()->updateSetSongs($setId,$songIds);
    }

    public function changeSetName($setId,$setName) {
        $this->getSongsetsRepository()->changeSetName($setId,$setName);
    }

    public function removeSet($setId)
    {
        $this->getSongsetsRepository()->removeSet($setId);
    }


}