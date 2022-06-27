<?php
namespace Peanut\songs;

use Peanut\songs\db\model\entity\Song;
use Peanut\songs\db\model\entity\Songpage;
use Peanut\songs\db\model\repository\SongindexRepository;
use Peanut\songs\db\model\repository\SongpagesRepository;
use Peanut\songs\db\model\repository\SongsRepository;
use Peanut\songs\db\model\repository\SongtagsRepository;
use Peanut\songs\db\model\repository\TagsRepository;
use Tops\cache\TAbstractCache;
use Tops\cache\TSessionCache;
use Tops\db\TQuery;
use Tops\sys\TDates;
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

    public function getAllSongsCount() {
        return $this->getSongPagesRepository()->getAllSongsCount();
    }
    public function getSongCount($request=null) {
        return $this->getSongpagesRepository()->getSongCount($request);
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

    public function getSongPage($songId) {
        if (is_numeric($songId)) {
            $song = $this->getSongsRepository()->get($songId);
        }
        else {
            $song = $this->getSongsRepository()->getSingleEntity('contentid=?',[$songId]);
        }
        if (!$song) {
            return false;
        }
        $repo = $this->getSongpagesRepository();
        $page = $repo->getPageBySongId($song->id);
        if ($page) {
            $page->song = $song;
            $songTagsRepo = $this->getSongTagsRepository();
            $page->types = $songTagsRepo->getTagValues($song->id, 'type');
            // $page->instruments = $songTagsRepo->getTagValues($songId, 'instrument');
        }

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
            $page->SongId = $songId;
        }
        else {
            $songRepo->update($song);
        }

        $page->assignFromObject($pageDto);
        if ($newPage) {
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
        $response->hasicon = $this->hasSongImage('icons',$song->contentid);
        $response->hasthumbnail = $this->hasSongImage('thumbnails',$song->contentid);

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
        $textArray = [$song->title, $song->description];
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

}