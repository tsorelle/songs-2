<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Tops\mail\TMailgunListManager;
use Tops\sys\TUser;

class GetmailgunmailinglistsTest extends TestScript
{

    public function execute()
    {
        if (!TUser::getCurrent()->isAuthenticated()) {
            exit ('You must sign in.');
        }
        $manager = new TMailgunListManager();

        $lists = $manager->getMailingLists();
        // var_dump($lists[0]);

        $outputDir = '/home1/austinqu/testdata';
        if (!is_dir($outputDir)) {
            exit ("No output");
        }
        $lines = [];

        print "----------- Lists ------------------------\n\n";

        $lines[] = "Name,Address,memberCount,desctiption";
        foreach ($lists as $item) {
            $name = trim($item->name);
            $address = trim($item->address);
            if (!$name) {
                $name = explode('@',$address)[0];
            }
            $lines[] = "$name,$address,$item->members,$item->description";
        }

        $data = implode("\n",$lines);
        $ok = file_put_contents("$outputDir/mailgun-lists.csv", $data);
        if ($ok === false) {
            print "File not written\n";
        }
        print "$data\n";

        print "\n\n--------Members --------------------\n\n";

        $lines = [];

        $lines[] = "list,address,name";
        foreach ($lists as $list) {
            $members = $manager->getListMembers($list->address);
            foreach ($members as $member) {
                $lines[] = "$list->address,$member->address,$member->name";
            }
        }
        $data = implode("\n",$lines);
        $ok = file_put_contents("$outputDir/mailgun-members.csv", $data);
        if ($ok === false) {
            print "File not written\n";
        }
        print "$data\n";

        print "ok\n";


    }
}