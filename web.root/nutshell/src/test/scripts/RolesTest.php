<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/17/2017
 * Time: 9:06 PM
 */

namespace PeanutTest\scripts;


use Concrete\Core\User\Group\GroupList;
use Tops\concrete5\Concrete5PermissionsManager;
use Tops\sys\TUser;

class RolesTest extends TestScript
{

    public function execute()
    {
        if (!TUser::getCurrent()->isAdmin()) {
            exit('Not authorized to run this test.');
        }
        $list = new GroupList();;
        $list->includeAllGroups();
        foreach ($list->getResults() as $group) {
            print $group->gName."\n";
            //var_dump($group);
            print "\n";
        }
        $manager = new Concrete5PermissionsManager();
        print_r($manager->getRoles());
    }
}