<?php

namespace Peanut\Contacts\db\model;

interface ISubscriptionsManager
{
    public function getSubscriberByUid($uid);
    public function unsubscribeEmail($uid,$listId);

}