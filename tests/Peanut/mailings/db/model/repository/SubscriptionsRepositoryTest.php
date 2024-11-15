<?php

namespace Peanut\mailings\db\model\repository;

use Peanut\mailings\db\model\repository\SubscriptionsRepository;
use PHPUnit\Framework\TestCase;

class SubscriptionsRepositoryTest extends TestCase
{
    function testGetListSubscriptions()
    {
        $repository = new SubscriptionsRepository();
        $actual = $repository->getListSubscriptions(17);
        $this->assertNotEmpty($actual);;
   }

   function testUidUpdate()
   {
       $repository = new SubscriptionsRepository();
       $actual = $repository->getSubscriptionByEmail(213,17);
       $this->assertNotEmpty($actual);
       $uid = $actual->uid;
//       $uid2 = $actual['uid'];
//       $this->assertEquals($uid,$uid2);
       $this->assertNotEmpty($uid);
        unset($actual->uid);

       $repository->update($actual);

       $updated = $repository->getSubscriptionByEmail(213,17);
       $this->assertNotEmpty($updated);
       $this->assertEquals($uid, $updated->uid);

   }

   function testSubUpdate()
   {
       $repository = new SubscriptionsRepository();
       $subscriptionDto = $repository->getSubscriptionByEmail(213,17);
       $this->assertNotEmpty($subscriptionDto);

   }

}
