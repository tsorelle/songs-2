<?php

namespace Peanut\songs\db\model\repository;

use PHPUnit\Framework\TestCase;

class SongpagesRepositoryTest extends TestCase
{

    public function testGetLatestSongs() {
        $repo = new SongpagesRepository();
        $songs = $repo->getLatestSongs();
        $expected = 8;
        $actual = count($songs);
        $this->assertEquals($expected,$actual);

    }
    public function testGetRandomSongIds() {
        $repo = new SongpagesRepository();
        $a = $repo->getRandomSongIds();
        $actual = count($a);
        $expected = 12;
        $this->assertEquals($expected,$actual);
    }

    public function testGetPageBySongId()
    {
        $repo = new SongpagesRepository();
        $page = $repo->getPageBySongId(2);
        $expected = 1;
        $actual = $page->id;
        $this->assertEquals($expected,$actual);
    }

    public function testGetSongPageList() {
        $repo = new SongpagesRepository();
        $actual = $repo->getSongPageList();
        $this->assertNotEmpty($actual);;
        $song = $actual[0];
        $this->assertNotEmpty($song);
    }

    public function testSongListPaging() {
        $repo = new SongpagesRepository();
        $actual = $repo->getSongPageList();
        $this->assertNotEmpty($actual);;
        $total = count($actual);
        $items = 20;
        $pageCount = ceil($total / $items);
        $request = new \stdClass();
        $request->pageSize = 20;
        $itemsCount = 0;
        for ($i=1; $i<= $pageCount; $i++) {
            $request->page = $i;
            $page = $repo->getSongPageList($request);
            $count = count($page);
            if ($i==$pageCount) {
                $this->assertTrue($count < 20);
            }
            else {
                $this->assertTrue($count == 20);
            }
            $itemsCount += $count;
        }
        self::assertEquals($total,$itemsCount);

    }

    public function testFullTextSearch() {
        $repo = new SongpagesRepository();
        $request = new \stdClass();
        // $request->searchType = 'text';
        $request->searchType = SongpagesRepository::searchTypeText;
        $request->searchTerms = 'boggs';
        $actual = $repo->getSongPageList($request);
        $this->assertNotEmpty($actual);

    }

    public function testGetCowboySongs() {
        $repo = new SongpagesRepository();
        $request = new \stdClass();
        $request->filter = 'cowboy';
        $actual = $repo->getSongPageList($request);
        $this->assertNotEmpty($actual);
        $count = count($actual);

        $request->filter = 1;
        $actual = $repo->getSongPageList($request);
        $this->assertNotEmpty($actual);
        $this->assertEquals($count, count($actual));
        $this->assertNotEmpty($actual);

    }

    public function testSongCount() {
        $repo = new SongpagesRepository();
        $request = null;
        $actual = $repo->getSongPageList($request);
        $expected = count($actual);
        $actual = $repo->getSongCount($request);
        $this->assertEquals($expected,$actual);


        $request = new \stdClass();
        $request->filter = 'cowboy';
        $actual = $repo->getSongPageList($request);
        $expected = count($actual);
        $actual = $repo->getSongCount($request);
        $this->assertEquals($expected,$actual);

        $request = new \stdClass();
        $request->filter = 'empty';
        $actual = $repo->getSongPageList($request);
        $expected = count($actual);
        $actual = $repo->getSongCount($request);
        $this->assertEquals($expected,$actual);


    }

    public function testParseSearchTerms()
    {
        $repo = new SongpagesRepository();
        // $s = 'This is "a test of" parsing.';
        $s = "This is 'a test of' parsing.";
        $actual = $repo->parseSearchTerms($s);
        $this->assertNotEmpty($actual);

        $s = "This is 'a test of parsing.";
        $actual = $repo->parseSearchTerms($s);
        $this->assertNotEmpty($actual);
    }
}
