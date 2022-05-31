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


class MailinglistsTest extends TestScript
{
    // private $mailgun;
    // private $testMode = true;
    private $testMode = true;
    private $errors = [];
    private $processed = [];
    private $lineNo = 0;
    private $valid = true;
    /**
     * @var IForwardingListManager
     */
    private $manager;

    public function execute()
    {
        $this->manager = new TMailgunListManager();
        // $apikey = TMailgunConfiguration::GetSettings()->apikey;
        // $this->mailgun = new Mailgun($apikey);
        $this->addLists();
    }

    private function addError($message,$line) {
        $this->errors[] = "ERROR: $message; LINE: $this->lineNo; '$line'";
        $this->valid = false;
    }

    private $currentGroup;
    private function addLists() {

        $inputFile = 'D:\dev\fma\clean-mail\2019-06-14\maillists.txt';
        // $inputFile = '/home1/austinqu/deploy/tmp/maillists.txt';
        if (!file_exists($inputFile) ) {
          exit('No input file');
        }

        $lines = file($inputFile);

        foreach ($lines as $line) {
            $this->lineNo++;
            $line = trim($line);
            $first = @substr($line,1,1);
            if (empty($line) || $first == '#' || $first=='-') {
                continue; // ignore comment
            }
            @list($line,$remainder) = explode(':',$line);
            if ($line == 'list') {
                $line = $remainder;
                $this->addGroup($line);
            }
            else {
                $this->addMember($line);
            }
        }
        if (is_object($this->currentGroup)) {
            $this->processed[] = $this->currentGroup;
        }

        if (count($this->errors)) {
            print("\nERRORS:\n");
            print_r($this->errors);
            $this->assert(true,'Processing errors occured');
        }
        else {
            print("\nPROCESSED:\n");
            // var_dump($this->processed);
            $this->createLists();
        }

        print "=== done ===\n";
    }

    private function addGroup($line) {
        if (is_object($this->currentGroup)) {
            $this->processed[] = $this->currentGroup;
        }
        $this->currentGroup = null;
        @list($address,$name) = explode(',',$line);
        $name = trim($name);
        $address = trim($address);
        @list($account,$domain) = explode('@',$address);
        if (empty($name) || empty($domain) || empty($account)) {
            $this->addError("Invalid group",$line);
            return;
        }

        if (array_key_exists($address,$this->processed)) {
            $this->addError('Duplicate group',$line);
            return;
        }

        $group = new \stdClass();
        $group->address = $address;
        $group->name = $name;
        $this->currentGroup = $group;

    }
    private function addMember($line) {
        if ($this->currentGroup) {
            $email = TEmailAddress::FromString($line);
            if (!$email) {
                $this->addError('Invalid email address',$line);
                return;
            }

            $member = new \stdClass();
            $member->address = $email->getAddress();
            $name = $email->getName();
            if ($name) {
                $member->name = $name;
            }
            $member->subscribed = true;
            $this->currentGroup->members[] = $member;
        }
    }

    private function createLists()
    {
        foreach ($this->processed as $key => $group) {
            if ($this->testMode === true) {
                print "list:$group->address,$group->name\n";
                continue;
                print "-----------------------------------\n";
                foreach ($group->members as  $member) {
                    $name = @$member->name;
                    $address = $member->address;
                    if ($name) {
                        print "$name&lt;$address&gt;\n";
                    } else {
                        print "$address\n";
                    }
                }
                print "\n";

            }
            else {
                print "Adding: $group->name, $group->address\n";
                // $this->manager->removeList($group->address);
                $this->manager->addList($group->address,$group->name);
                $this->manager->addUpdateMembers($group->address, $group->members);
                if ($this->testMode === 1) {
                    return;
                }
            }

        }
    }
}