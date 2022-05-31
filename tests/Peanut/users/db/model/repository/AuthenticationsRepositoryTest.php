<?php

namespace Peanut\users\db\model\repository;

use PHPUnit\Framework\TestCase;
use Tops\db\TQuery;

class AuthenticationsRepositoryTest extends TestCase
{

    const testIp = '100.200.300.400';

    private function cleanup() {
        $query = new TQuery();
        $query->executeStatement('DELETE FROM pnut_authentications WHERE ip = ?',[self::testIp]);
    }

    private function insertTestRecord($attempts=1,$timeOffset = 0) {
        $query = new TQuery();
        $sql = sprintf(
            'INSERT INTO pnut_authentications (ip,`last`,attempts) '.
            'VALUES (?,DATE_ADD(NOW(), INTERVAL %s MINUTE), ?)'
            ,$timeOffset);
        $query->executeStatement($sql,[self::testIp,$attempts]);
    }

    public function checkSuccessRecord() {
        $query = new TQuery();
        $sql = 'SELECT COUNT(*) FROM pnut_authentications WHERE ip = ? AND success IS NOT NULL';
        $count = $query->getValue($sql,[self::testIp]);
        return $count > 0;
    }

    public function testUpdateForFailure()
    {
        $this->cleanup();
        $repo = new AuthenticationsRepository();
        $actual = $repo->updateForFailure(self::testIp,2);
        $this->assertTrue($actual);
        $current = $repo->getCurrent(self::testIp);
        $this->assertEquals(1,$current->attempts);

        $actual = $repo->updateForFailure(self::testIp,2);
        $this->assertTrue($actual);
        $current = $repo->getCurrent(self::testIp);
        $this->assertEquals(2,$current->attempts);

        $actual = $repo->updateForFailure(self::testIp,2);
        $this->assertFalse($actual);

        $this->cleanup();



    }

    public function testGetCurrent()
    {
        $this->cleanup();
        $repo = new AuthenticationsRepository();
        $actual = $repo->getCurrent(self::testIp);
        $this->assertEmpty($actual);

        $this->insertTestRecord();
        $actual = $repo->getCurrent(self::testIp);
        $this->assertNotEmpty($actual);

        $this->cleanup();
        $this->insertTestRecord(1,'-15');
        $actual = $repo->getCurrent(self::testIp);
        $this->assertEmpty($actual);

        $this->cleanup();
    }

    public function testUpdateForSuccess()
    {
        $this->cleanup();
        $repo = new AuthenticationsRepository();
        $repo->updateForSuccess(self::testIp);
        $actual = $this->checkSuccessRecord();
        $this->assertTrue($actual);;

        $this->cleanup();
        $this->insertTestRecord();
        $repo->updateForSuccess(self::testIp);
        $actual = $this->checkSuccessRecord();
        $this->assertTrue($actual);;
        $this->cleanup();

    }
}
