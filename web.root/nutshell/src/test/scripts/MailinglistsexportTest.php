<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/14/2019
 * Time: 5:48 PM
 */

namespace PeanutTest\scripts;

use Tops\mail\IForwardingListManager;
use Tops\mail\TEmailAddress;
use Tops\mail\TMailgunListManager;


class MailinglistsexportTest extends TestScript
{

    public function execute()
    {
        $manager = new TMailgunListManager();
        $lists  = $manager->getMailingLists();
        // var_dump($lists);
        print "List count: ".count($lists)."\n------------------------\n";

        foreach ($lists as $list) {
            print "\nlist:$list->address";
            if (!empty($list->name)) {
                print ",$list->name";
            }
            print "\n";
            $members = $manager->getListMembers($list->address);
            foreach($members as $member) {
                print $member->address;
                if (!empty($member->name)) {
                    print ",$member->name";
                }
                print "\n";
            }
        }
        print "\n------------------------\n";

    }
}