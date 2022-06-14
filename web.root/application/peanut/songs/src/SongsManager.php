<?php
namespace Peanut\songs;

use Peanut\songs\db\model\entity\Song;
use Peanut\songs\db\model\entity\Songpage;
use Peanut\songs\db\model\repository\SongindexRepository;
use Peanut\songs\db\model\repository\SongpagesRepository;
use Peanut\songs\db\model\repository\SongsRepository;
use Peanut\songs\db\model\repository\SongtagsRepository;
use Peanut\songs\db\model\repository\TagsRepository;
use Tops\db\TQuery;
use Tops\sys\TDates;

class SongsManager
{
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

    public function getSongPages($request=null) {
        return $this->getSongpagesRepository()->getSongPageList($request);
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
        $page = $this->getSongpagesRepository()->getPageBySongId($songId);
        $page->song = $this->getSongsRepository()->get($songId);
        $songTagsRepo = $this->getSongTagsRepository();
        $page->types = $songTagsRepo->getTagValues($songId,'type');
        $page->instruments = $songTagsRepo->getTagValues($songId,'instrument');

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
        $songDto = null;
        if (isset($pageDto->song)) {
            $songDto = $pageDto->song;
            $pageDto->songId = $songDto->id;
            unset($pageDto->song);
        }
        
        $types = $pageDto->types ?? null;
        if ($types !== null) {
            unset($pageDto->types);
        }

        $instruments = $pageDto->instruments ?? null;
        if ($instruments !== null) {
            unset($pageDto->instruments);
        }

        $pageRepo = $this->getSongpagesRepository();
        $songRepo = $this->getSongsRepository();

        $newPage = empty($pageDto->id);
        if ($newPage) {
            $page = new Songpage();
            $song = new Song();
        }
        else {
            $page = $pageRepo->get($pageDto->id);
            if (!$page) {
                return 'Page not found';
            }
            $song = $pageDto->get($pageDto->songId);
            if (!$song) {
                return 'Song not found';
            }
        }
        
        $song->assignFromObject($songDto);
        if ($newPage) {
            $songId = $songRepo->insert($song);
            if ($songId === false) {
                return 'Cannot insert song';
            }
            $pageDto->songId = $songId;
            $pageId = $pageRepo->insert($pageDto);
            if ($pageId === false) {
                return 'Cannot insert page';
            }
        }
        else {
            $songRepo->update($song);
            $pageRepo->update($page);
        }
        
        $this->updateSongIndex($page,$song);
        
        if ($types && $instruments) {
            $tags = array_merge($types,$instruments);
            $this->updateSongTags($pageDto->songId,$tags);

        }

        return true;
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
    

}