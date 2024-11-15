<?php

namespace Peanut\mailings\services;


// use Peanut\Mailings\db\model\entity\Subscription;
use Peanut\mailings\db\model\repository\EmailaddressesRepository;
use Peanut\mailings\db\model\repository\SubscriptionsRepository;
// use Peanut\Contacts\db\model\ISubscriptionsManager;
// use Peanut\Contacts\db\model\repository\EmailListsRepository;

class SubscriptionsManager // implements ISubscriptionsManager
{

    private static $_subscriptionRepository;
    private function getSubscriptionsRepository()
    {
        if (!isset(self::$_subscriptionRepository)) {
            self::$_subscriptionRepository = new SubscriptionsRepository();
        }
        return self::$_subscriptionRepository;
    }
    private static $_emailAddressesRepository;
    private function getemailAddressesRepository()
    {
        if (!isset(self::$_emailAddressesRepository)) {
            self::$_emailAddressesRepository = new EmailaddressesRepository();
        }
        return self::$_emailAddressesRepository;
    }

    public function getSubscriptionList ($listId)
    {
        $repository = $this->getSubscriptionsRepository();
        return $repository->getListSubscriptions($listId);

    }

    public function getEmailAddress($email)
    {
        $addressRepository =$this->getemailAddressesRepository();
        return $addressRepository->getEmailAddress($email);
    }

    public function isSubscribed($email,$listId) : bool
    {
        $repository = $this->getSubscriptionsRepository();
        $existingEmail =  $this->getEmailAddress($email);
        if ($existingEmail) {
            $subscription = $repository->getSubscriptionByEmail($existingEmail->id,$listId);
            return ($subscription->status == 1);
        }
        return false;
    }

    public function subscribe($fullname,$email,$listId): \stdClass
    {
        $result = new \stdClass();
        $addressRepository = $this->getemailAddressesRepository();
        $subscriptionsRepository = $this->getSubscriptionsRepository();
        $currentemail = $addressRepository->getEmailAddress($email);
        if ($currentemail) {
            $currentemail->status = EmailaddressesRepository::status_active;
            $emailId = $currentemail->id;
            $currentemail->fullname = $fullname;
            $addressRepository->update($currentemail);
            $result->emailaddress = $currentemail;
        }
        else {
            $emailId = $addressRepository->addAddress($fullname,$email);
            $result->emailaddress = $addressRepository->get($emailId);
        }
        $result->subscription = $subscriptionsRepository->addNew($emailId,$listId);
        return $result;
    }

    public function getSubscription($id)
    {
        $result = new \stdClass();
        $subrepository = $this->getSubscriptionsRepository();
        $result->subscription = $subrepository->get($id);
        if (!$result->subscription) {
            return false;
        }
        $erepository = $this->getemailAddressesRepository();
        $result->emailaddress = $erepository->get($result->subscription->emailid);
        return $result;
    }

    public function deleteSubscription($emailAddress,$listid)
    {
        // for test only
        $subrepository = $this->getSubscriptionsRepository();
        $subscription = $subrepository->getSubscriptionByEmail($emailAddress,$listid);
    }

    public function getSubscriber($emailAddress,$listId)
    {
        $result = new \stdClass();
        $subrepository = new SubscriptionsRepository();
        $erepository = new EmailaddressesRepository();
        $result->email = $erepository->getByAddress($emailAddress);
        if (!$result->email) {
            $result->subscription = false;
        }
        $result->subscription = $subrepository->getSubscriptionByEmail($result->email->id,$listId);

        return $result;
    }

    public function updateSubscription($subscriptionDto)
    {
        $subrepository = new SubscriptionsRepository();
        $subscriptionDto->emailid = $subscriptionDto->emailaddress->id;
        $subrepository->update($subscriptionDto->subscription);
        $erepository = new EmailaddressesRepository();
        $erepository->update($subscriptionDto->emailaddress);
    }

    public function deleteOrphandedEmailAddresses()
    {
        $subrepository = $this->getSubscriptionsRepository();
        $subrepository->deleteOrphans();

    }

    public function getSubscribers($listid)
    {
        return $this->getSubscriptionsRepository()->getActiveSubscribers($listid);
    }

    public function createEmailAddress($email, $fullname)
    {
        $erepository = new EmailaddressesRepository();
        return $erepository->newEmailAddress($email,$fullname);
    }

    public function unsubscribe($uid)
    {
        $repository = $this->getSubscriptionsRepository();
        $result = new \stdClass();
        $result->removed = false;
        $subscription = $repository->getEntityByUid($uid);
        if ($subscription) {
            if ($repository->unsubscribe($uid)) {
                $result = $repository->getSubscriptionInfoByUid($uid);
                $result->removed = true;
            }
        }
        return $result;
    }

    public function resubscribe($uid)
    {
        $repository = $this->getSubscriptionsRepository();
        $result = new \stdClass();
        $result->subscribed = true;
        $subscription = $repository->getEntityByUid($uid);
        if ($subscription) {
            if ($repository->resubscribe($uid)) {
                $result = $repository->getSubscriptionInfoByUid($uid);
                $result->subscribed = true;
            }
        }
        return $result;
    }

    public function cleanupTest()
    {
        $subrep = $this->getSubscriptionsRepository();
        $subrep->deleteTestRecords();
        $subrep->deleteOrphans();
    }


    public function getSubscriberByUid($uid)
    {
        return $this->getSubscriptionsRepository()->getEntityByUid($uid);
    }

    public function unsubscribeEmail($uid, $listId)
    {
        return $this->unsubscribe($uid);
        // listId not needed since $uid is unique
    }
}