<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


require_once "/usr/local/cpanel/php/cpanel.php";


class CpanelTest extends TestScript
{

    public function execute()
    {


        // see: https://documentation.cpanel.net/display/DD/Guide+to+the+LiveAPI+System+-+PHP+Class

        $installed = file_exists( "/usr/local/cpanel/php/cpanel.php");
        print "Installed: ";
        print $installed ? 'Yes' : 'No';
        print("\n");

        $classExists = class_exists('CPANEL');
        print "Class loaded: ";
        print $classExists ? 'Yes' : 'No';
        print("\n");

        $panel = new \CPANEL();
        print "Instantiated: ";
        print is_object($panel) ? 'Yes' : 'No';
        print("\n");


        $this->assert(true,'Testing works');

    }
}