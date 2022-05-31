<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/14/2019
 * Time: 5:48 PM
 */

namespace PeanutTest\scripts;

use PDOStatement;
use Tops\db\TDatabase;
use Tops\db\TPdoQueryManager;
use Tops\db\TQuery;


class ForwardersTest extends TestScript
{
    public function execute()
    {
        // $this->processScrape();
        $this->printNewList();
    }


    /**
     * @var TQuery
     */
    private $query;
    private function fullAddress($email)
    {

        $sql =
            "SELECT CONCAT(firstName,' ',lastName) AS `name` , ".
            "CASE   ".
            "    WHEN (active <> 1) THEN 'INACTIVE'   ".
            "    WHEN (deceased IS NOT NULL) THEN 'DECEASED'  ".
            "    ELSE ''  ".
            "END AS STATUS FROM qnut_persons   ".
            "WHERE email = ?";

        $result = $this->query->get($sql,[$email]);
        if ($result) {
            if ($result->status) {
                return $result->status;
            }
            return sprintf('%s <%s>',$result->name,$email);
        }
        return "NOT FOUND: ".$email;
        return $email;
    }


    private function processScrape() {
        // file prep:
        // Scrren copy bluehost list
        // Delete top and bottom so that:
        //       First list name on top line
        //       Blank line follows last list member

        $lines = file('D:\dev\fma\process-mail\scrape.txt');

        $previous = trim(array_shift($lines));
        $listName = '';
        $isListname = false;
        foreach ($lines as $line) {
            $line = trim($line);
            $isAddress = strpos($line,'@') !== false;
            if ($isAddress) {
                if ($isListname) {
                    if ($line != $listName) {
                        print "\n$line\n-----------------------------------\n";
                        $listName = $line;
                    }
                    $isListname = false;
                }
                else {
                    print "$line\n";
                    $isListname = true;
                }

            }
/*

            if (empty($line)) {
                if (($previous != $listName)) {
                    print "\n$previous\n-----------------------------------\n";
                    $listName = $previous;
                }
            }
            else if ($line === 'edit') {
                print "$previous\n";
            }
            $previous = $line;*/
        }
    }

    private function printNewList() {

          $_ignoreList =
            ['dpac@austinquakers.org',
            'jimshelpers@austinquakers.net',
            'jimshelpers@austinquakers.org',
            'earthcare@austinquakers.org',
            'fdsmsfamily@austinquakers.org',
            'dpac@austinquakers.org',
            'dpacclerk@austinquakers.org'
        ];
        $ignoreList = $_ignoreList;
        $this->query = new TQuery();
        // $lines = file('D:\dev\fma\clean-mail\from-bluehost-2019-03-16.txt');
        $lines = file('D:\dev\fma\process-mail\forwarders-raw.txt');
        $s = 'waiting';
        $notFound = [];
        $ignore = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if(empty($line)) {
                if ($s == 'emails') {
                    print "$line\n";
                    $s = 'waiting';
                }
            }
            else {
                switch($s) {
                    case 'waiting' :
                        print "$line\n";
                        $s ='header';
                        break;
                    case 'header' :
                        $ignore = in_array($line,$ignoreList);
                        if (!$ignore) {
                            print "$line\n";
                        }
                        $s = 'emails';
                        break;
                    case 'emails' :
                        if ($ignore || (substr($line,0,1) == '*')) {
                            break;
                        }
                        if ($line == 'siobhans70@gmail.com' || $line == 'siobhan70@gmail.com') {
                            $address = 'Siobhan Florek <siobhan70@gmail.com>';
                        }
                        else if ($line == 'janet@mccracken.name' || $line == 'janetmccracken@me.com') {
                            $address = 'Janet McCracken <janetmccracken@me.com>';
                        }
                        else if ($line == 'djohnson@texascbar.org' || $line == 'dajohnson081954@gmail.com') {
                            $address = 'Diann Johnson <dajohnson081954@gmail.com>';
                        }
                        else if ($line == 'johnnybgood@ckla.net' || $line == 'johnnybgoode1369@gmail.com') {
                            $address = 'Jon Buterbaugh <johnnybgoode1369@gmail.com>';
                        }
                        else if ($line == 'melissa.ruof@verizon.net' || $line == 'ruofmelissa@gmail.com ') {
                            $address = 'Melissa Ruof <ruofmelissa@gmail.com >';
                        }
                        else if ($line == 'jeanne_stern@mail.utexas.edu' || $line == 'jeanne.stern@gmail.com') {
                            $address = 'Jeanne Stern <jeanne.stern@gmail.com>';
                        }
                        else if ($line == 'EdHelenIngram@AOL.com' || $line == 'eingram01@att.net') {
                            $address = 'Ed Ingram <eingram01@att.net>';
                        }
                        else if ($line == 'pauljohnpearce@gmail.com' || $line == 'johnpearce@gmail.com') {
                            $address = 'Paul Pearce <johnpearce@gmail.com>';
                        }
                        else if ($line == 'jason@briggeman.org' || $line == 'jbriggem@gmail.com' || $line == 'jbriggem@gmu.edu') {
                            $address = 'Jason Briggeman <jason@briggeman.org>';
                        }
                        else if ($line == 'steeli77@hotmail.com' || $line == 'Elizabeth_Stehl@yahoo.com' ) {
                            $address = 'Elizabeth Stehl <steeli77@hotmail.com>';
                        }
                        else if ($line == 'howardhawhee@gmail.com' || $line == 'hhawhee@hotmail.com' || strtolower($line) =='hhawhee@austincc.edu') {
                            $address = 'Howard Hawhee <howardhawhee@gmail.com>';
                        }
                        else if ($line == 'jpolache@gmail.com' || $line == 'jonpol@aol.com' || $line == 'jonpol@grandecom.net' || $line == 'JonPol@grandecom.net') {
                            $address = 'Jon Polacheck <jpolache@gmail.com>';
                        }
                        else if ($line == 'terrys@2quakers.net' || $line == 'tsorelle@outlook.com' || $line == 'terry.sorelle@outlook.com') {
                            $address = 'Terry SoRelle <terry.sorelle@outlook.com>';
                        }
                        else if ($line == 'liz.yeats@outlook.com' || $line == 'lizy@2quakers.net') {
                            $address = 'Liz Yeats <liz.yeats@outlook.com>';
                        }
//                        else  if (array_pop(explode('@',$line)) == 'austinquakers.org') {
//                            $address = $line;
//                        }
                        else {
                            $domain = array_pop(explode('@',$line));
                            if ($domain == 'austinquakers.org' || $domain == 'austinquakers.net') {
                                $address = $line;
                            }
                            else {
                                $address = $this->fullAddress($line);
                            }
                            // $address = strtolower($address);
                            if (substr($address,0,10) == 'NOT FOUND:') {
                                $address = substr($address,11);
                                $notFound[] = $address;
                            }
                        }
                        print htmlentities($address)."\n";
                        break;
                }
            }
        }
        print "\n=====================================================================\n";
        print "\nAddresses not found in Directory:\n\n";
        foreach ($notFound as $address) {
            print "$address\n";
        }


//        print htmlentities( $this->fullAddress('terry.sorelle@outlook.com'))."\n";
//        print htmlentities( $this->fullAddress('mariaclauss@hotmail.com'))."\n";
//        print htmlentities( $this->fullAddress('benmung77@gmail.com'))."\n";


    }
}