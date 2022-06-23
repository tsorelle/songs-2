<?php

namespace PeanutTest\scripts;

use Peanut\songs\db\model\entity\Song;
use Peanut\songs\db\model\entity\Songpage;
use Peanut\songs\db\model\repository\SongpagesRepository;
use Peanut\songs\db\model\repository\SongsRepository;
use Peanut\songs\SongsManager;
use Tops\db\TQuery;

class PrepdataTest extends TestScript
{

    public function execute()
    {
        $repo = new SongpagesRepository();
        $this->convertIntro($repo);
        // $this->updateIndex($repo);
        // $this->updateIcons($repo);
        // $this->tempFix($repo);
        // $this->parseSongData($repo);
    }

    private function updateIcons(SongpagesRepository $repo) {
        $songRepo = new SongsRepository();
        /**
         * @var $songs Songpage[];
         */
        $songpages = $repo->getAll();
        $imgpath = 'D:\dev\twoquakers\songs-2\web.root\assets\img\songs\icons';
        /**
         * @var $page Songpage
         */
        foreach ($songpages as $page) {
            /**
             * @var $song Song
             */
            $song = $songRepo->get($page->songId);
            if (!$song) {
                print "$page->songId - Song not found\n";
                continue;
            }
            $path = sprintf('%s\%s.jpg', $imgpath,$song->contentid);
            if (file_exists($path)) {
                $page->hasicon = 1;
            }
            else {
                print "needs icon: $song->contentid\n";
            }

            $content = trim($page->commentary);
            if ($content) {
                $updated++;
                $lines = explode("\n",$content);
                $intro = strip_tags($lines[0]);
                // $page->introduction = strip_tags($lines[0]);
                if (strlen($intro > 2056)) {
                    print( $page->id.": Truncated \n");
                }
            }
            $repo->update($page);


        }
    }



    private function tempFix($repo) {
        $query = new TQuery();
        $sql =
            'SELECT b.id, b.`commentary` FROM backup_songpages b '.
            "WHERE contenttype = 'html' AND b.id IN ( ".
            '16, '.
            '22, '.
            '22, '.
            '22, '.
            '23, '.
            '23, '.
            '26, '.
            '27, '.
            '34, '.
            '37, '.
            '37, '.
            '41, '.
            '56, '.
            '58, '.
            '59, '.
            '60, '.
            '61, '.
            '76, '.
            '77, '.
            '80, '.
            '81, '.
            '85, '.
            '89, '.
            '92, '.
            '93, '.
            '103,'.
            '105,'.
            '107,'.
            '108,'.
            '111,'.
            '114,'.
            '115,'.
            '118,'.
            '120,'.
            '125,'.
            '132,'.
            '141,'.
            '156,'.
            '158,'.
            '159,'.
            '188,'.
            '189,'.
            '190,'.
            '193,'.
            '194,'.
            '195,'.
            '197,'.
            '198,'.
            '198,'.
            '200,'.
            '201,'.
            '202,'.
            '203,'.
            '204,'.
            '206,'.
            '207)';

        $items = $query->executeStatement($sql)->fetchAll(\PDO::FETCH_OBJ);
        foreach ($items as $item) {
            $id = $item->id;
            $songpage = $repo->get($id);
            if (!$songpage) {
                exit ("No song page $id");
            }
            $lines = explode("\n",$items);
            if (!empty($lines)) {
                if ($this->parseImage($lines[0],$songpage)) {
                    $repo->update($songpage);
                }
            }
        }
    }

    /**
     * @param $line
     * @return string[]
     */
    protected function parseImage($line, $songpage) : bool
    {
        // Assumes images aleady converted
        $line = str_ireplace('/assets/images/', '/assets/img/songs/', $line);

        $p = stripos($line, '/images/');
        // $parts = explode('/',substr($line,$p));
        $image = '';
        $caption = '';
        if ($p !== false) {
            $line = substr($line, $p + 8);
            $p = strpos($line, '"');
            if ($p !== false) {
                $image = substr($line, 0, $p);
                $p = strpos($line, '<p>');
                if ($p !== false) {
                    $line = substr($line, $p + 3);
                    $p = strpos($line, '</p>');
                    if ($p !== false) {
                        $caption = substr($line, 0, $p);
                    }
                }
            }
        }
        $ok = false;
        $id = $songpage->id;
        if ($image) {
            $songpage->pageimage = $image;
            $ok = true;
        }
        else {
            print "$id: Image not found\n";
        }
        if ($caption) {
            $songpage->imagecaption = $caption;
            $ok = true;
        }
        else {
            print "$id: Caption not found\n";
        }
        return $ok;
    }

    /**
     * @param SongpagesRepository $repo
     * @param $songpage
     * @return void
     */
    protected function parseSongData(SongpagesRepository $repo, $songpage): void
    {
        $repo = new SongpagesRepository();
        $songs = $repo->getAll();
        $count = 0;
        foreach ($songs as $songpage) {
            $count++;
            $content = $songpage->commentary;
            $id = $songpage->id;
            $lines = explode("\n", $content);
            $newContent = [];

            $first = true;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($first) {
                    if (strpos($line, '<div') === 0) {
                        $this->parseImage($line, $songpage);
                    }
                    else {
                        $newContent[] = $line;
                    }
                    $first = false;
                }
                $newContent[] = $line;
            }
            $content = implode("\n", $newContent);
            $content = str_ireplace('/assets/images/', '/assets/img/songs/', $content);
            $songpage->commentary = $content;
            $repo->update($songpage);
        }
        print "Count: $count\n";
    }

    private function updateIntros(SongpagesRepository $repo)
    {
        /**
         * @var $pages Songpage[];
         */
        $pages = $repo->getAll();
        $count = count( $pages);
        $updated = 0;
        foreach ($pages as $page) {
            $content = trim($page->commentary);
            if ($content) {
                $updated++;
                $lines = explode("\n",$content);
                $page->introduction = strip_tags($lines[0]);
                $repo->update($page);
            }
        }
        print "Updated $updated of $count\n";
    }

    private function updateIndex(SongpagesRepository $repo)
    {
        $pages = $repo->getAll();
        $manager = new SongsManager();
        foreach ($pages as $page) {
            $manager->updateSongIndex($page);
        }
    }

    private function convertIntro(SongpagesRepository $repo)
    {
        $pages = $repo->getEntityCollection('contentType=?',['text']);
        $count = count($pages);
        foreach ($pages as $page) {
            $content = trim($page->commentary);
            if (substr($content,0,1) !== '<') {
                $page->commentary = '<p>'.$content.'</p>';
                print "Updated page# $page->id\n";
            }
            // $page->introduction = '<p>'.$page->introduction.'</p>';
            $repo->update($page);
        }
        print "Updated $count\n";

    }
}