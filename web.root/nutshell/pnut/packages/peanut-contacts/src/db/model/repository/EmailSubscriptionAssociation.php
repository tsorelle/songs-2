<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 11/27/2017
 * Time: 5:18 PM
 */

namespace Peanut\contacts\db\model\repository;


class EmailSubscriptionAssociation extends SubscriptionAssociation
{
    public function __construct()
    {
        parent::__construct(
            'qnut_email_subscriptions',
            'pnut_contacts',
            'qnut_email_lists',
            'personId',
            'listId',
            'Peanut\contacts\db\model\entity\Contact',
            'Peanut\contacts\db\model\entity\EmailList');
    }
}