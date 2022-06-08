<?php

namespace PeanutTest\scripts;

use Peanut\songs\db\model\entity\Songpage;
use Peanut\songs\db\model\repository\SongpagesRepository;
use Peanut\songs\db\model\repository\SongsRepository;
use Tops\db\TQuery;

class UpdateurlsTest extends TestScript
{

    public function execute()
    {
        $repo = new SongpagesRepository();
        // $this->tempFix($repo);
        // $this->parseSongData($repo);
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
}