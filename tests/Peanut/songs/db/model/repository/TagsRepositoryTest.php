<?php

namespace Peanut\songs\db\model\repository;

use PHPUnit\Framework\TestCase;

class TagsRepositoryTest extends TestCase
{

    public function testGetAllTags()
    {
        $repo = new TagsRepository();
        $actual = $repo->getAll();
        $this->assertNotEmpty($actual);

    }

    public function testGetLookupList() {
        $repo = new TagsRepository();
        $actual = $repo->getLookupList('type');
        $this->assertNotEmpty($actual);

        $actual = $repo->getLookupList('instrument');
        $this->assertNotEmpty($actual);


    }
}
