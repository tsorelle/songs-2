<?php

namespace Peanut\contacts\db\model\repository;

use Peanut\mailings\db\model\repository\EmailaddressesRepository;
use PHPUnit\Framework\TestCase;

class EmailMessagesRepositoryTest extends TestCase
{

    public function testGetMessageRecipients()
    {
        $repo = new EmailaddressesRepository();
        $actual = $repo->getAll();
        $this->assertNotEmpty($actual);
    }
}
