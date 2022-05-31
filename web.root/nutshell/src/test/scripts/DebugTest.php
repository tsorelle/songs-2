<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Concrete\Core\Export\Item\AttributeKey;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Tops\concrete5\TConcrete5UserFactory;
use Tops\services\TMessageContainer;
use Tops\sys\TUser;

;

class DebugTest extends TestScript
{

    public function execute()
    {
        // if (!TUser::getCurrent()->isAdmin()) {}
/*        $u = new User();
        $test = $u->isRegistered();
        $this->assert(!$test,'Not logged in');*/
         TUser::SignIn('admin','Ge0rge@F0x');
//        print "Signed in as ".TUser::getCurrent()->getUserName()."\n";

//        $role = 'Administrators';
//
//        $group = \Concrete\Core\User\Group\Group::getByName($role);
//        $u = new User();
//        print 'Signed in: '.$u->getUserName()."\n";
//        $test =  $u->isSuperUser() || $u->inGroup($group);
//        $this->assert($test,'Not admin');

        $currentUser = new User();
        print "Start user name ".$currentUser->getUserName()."\n";
        $username = 'admin';
        $userInfo =  \Core::make('Concrete\Core\User\UserInfoRepository')->getByName($username);
        if (empty($userInfo)) {
            exit('getByName failed.');
        }
        $user = $userInfo->getUserObject();
        $userId = $user->getUserID();
        $result = User::loginByUserID($userId);
        if (!$result) {
            exit('Login failed.');
        }
        $currentUser = new User();
        if ($currentUser->getUserID() != $user->getUserId()) {
            print 'Current user is not correct.'."\n";
        }
        print "Current user name ".$currentUser->getUserName();


    }
}