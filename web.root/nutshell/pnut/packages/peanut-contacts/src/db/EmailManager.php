<?php

namespace Peanut\contacts\db;

use Peanut\contacts\db\model\repository\ContactsRepository;

class EmailManager
{
    private $contactsRepository;
    private function getContactsRepository()
    {
        if (!isset($this->contactsRepository)) {
            $this->contactsRepository = new ContactsRepository();
        }
        return $this->contactsRepository;
    }
    public function getSubscriberList(float $listId)
    {
        return $this->getContactsRepository()->getEmailSubscribersList($listId);
    }

    public function unsubscribeMultiple($listId,array $contactList) {
        return $this->getContactsRepository()->removeEmailSubscriptions($listId,$contactList);
    }
}