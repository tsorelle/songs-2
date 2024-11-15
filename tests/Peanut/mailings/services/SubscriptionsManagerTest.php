<?php

namespace Peanut\mailings\services;

use Peanut\mailings\db\model\entity\Subscription;
use Peanut\mailings\db\model\repository\SubscriptionsRepository;
use PHPUnit\Framework\TestCase;

class SubscriptionsManagerTest extends TestCase
{
    /**
     * @var $_repository SubscriptionsRepository
     */
    private $repository;
    private function getSubscription($id)
    {
        if (!isset($this->repository)) {
            $this->repository = new SubscriptionsRepository();
        }
        $sub = $this->repository->get($id);
        $this->assertNotEmpty($sub);
        return $sub;
    }

    public function testResubscribe()
    {

    }

    public function testUnsubscribeEmail()
    {
        $testId = 1;
        // $uid = '64030030-c52a-4b05-80c7-a36534a9ade8'
        $manager = new SubscriptionsManager();

        /**
         * @var $sub Subscription
         */
        $sub = $this->getSubscription($testId);
        $uid = $sub->uid;

        $result = $manager->unsubscribe($uid);
        $expected = 2;
        $this->confirmSubStatus($testId,$expected,$result->removed);
        /**
         * @var $actual Subscription
         */
        $result = $manager->resubscribe($uid);
        $expected = 1;
        $this->confirmSubStatus($testId,$expected,$result->removed);
    }

    /**
     * @param SubscriptionsManager $manager
     * @param int $testId
     * @param int $expected
     * @return Subscription
     */
    public function confirmSubStatus(int $testId, int $expected, $removed)
    {
        /**
         * @var $sub Subscription
         */
        $actual = $this->getSubscription($testId);
        $this->assertEquals($expected, $actual->status);
        $this->assertEquals($removed,($actual->status == 2));
    }

}
