<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/14/2019
 * Time: 5:48 PM
 */

namespace PeanutTest\scripts;

use PDOStatement;
use Peanut\QnutCommittees\CommitteeManager;
use Tops\db\TDatabase;
use Tops\db\TPdoQueryManager;
use Tops\db\TQuery;


class CompareforwardersTest extends TestScript
{
    public function execute()
    {
        $forwarders = $this->processScrape();
        // $lists = $this->processExport();
        $lists = (new CommitteeManager())->exportCommitteeLists();
       // print_r($lists);
        // return;
        $forwarderAddresses = array_keys($forwarders);
        $listAddresses = array_keys($lists);
        //print_r($forwarderAddresses);
        // print_r($listAddresses);
        // return;

        foreach ($listAddresses as $listAddress) {
            $exists = array_key_exists($listAddress,$forwarders);
            if ($exists) {
                $forwarderMembers = $forwarders[$listAddress];
                $listMembers = $lists[$listAddress];
                $remove = array_diff($forwarderMembers,$listMembers);
                $add = array_diff($listMembers,$forwarderMembers);;
                $removeCount = count($remove);
            }
            else {
                $add = $lists[$listAddress];
                $remove = [];
                $removeCount = 0;

            }
            $addCount = count($add);

            if ($addCount + $removeCount > 0) {
                print("\n---------\n");
                print("$listAddress");
                if (!$exists) {
                    print " (new)";
                }
                print("\n---------\n");

                if ($addCount) {
                    print("Add:\n");
                    foreach ($add as $addAddress) {
                        print "    $addAddress\n";
                    }
                }
                if ($removeCount) {
                    print("Remove:\n");

                    foreach ($remove as $removeAddress) {
                        print "    $removeAddress\n";
                    }
                }
            }
        }
        /*
                $diff = array_diff($listAddresses,$forwarderAddresses);
                if ($diff) {
                    print "Lists to add:\n";
                    print_r($diff);
                }

                $diff = array_diff($forwarderAddresses,$listAddresses);
                if ($diff) {
                    print "\nLists to delete:\n";
                    print_r($diff);
                }
        */

    }


    /**
     * @var TQuery
     */
    private $query;

    private function processScrape() {
        $scrape = [];
        // file prep:
        // Scrren copy bluehost list
        // Delete top and bottom so that:
        //       First list name on top line
        //       Blank line follows last list member

        $lines = file('D:\dev\fma\process-mail\scrape.txt');

        $previous = trim(array_shift($lines));
        $listName = '';
        $isListname = false;
        $skip = false;
        foreach ($lines as $line) {
            $line = trim(strtolower($line));
            $isAddress = strpos($line,'@') !== false;
            if ($isAddress) {
                $ext = substr($line,-4);
                if ($isListname) {
                    $skip = $ext == '.net';
                    if ($line != $listName) {
                        $listName = $line;
                        if (!$skip) {
                            $scrape[$listName] = [];
                        }
                    }
                    $isListname = false;
                }
                else {
                    if (!$skip) {
                        $scrape[$listName][] = strtolower($line);
                    }
                    $isListname = true;
                }
            }
        }
        return $scrape;
        // print_r($scrape);
    }

    private function processExport() {
        $exports = [];
        $lines = file('D:\dev\fma\clean-mail\2019-06-27\mglists-export.txt');
        $listName = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                @list($skip,$list) = explode(':',$line);
                if ($list) {
                    @list($address,$name) = explode(',',$list);
                    $listName = strtolower($address);
                    $exports[$listName] = [];
                }
                else {
                    @list($address,$name) = explode(',',$line);
                    $exports[$listName][] = strtolower($address);
                }
            }
        }
        return $exports;
        // print_r($exports);
    }

}