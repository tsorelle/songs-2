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

class AddaccountTest extends TestScript
{

    public function execute()
    {
        if (!TUser::getCurrent()->isAuthenticated()) {
           // TUser::SignIn('emittag','Ge0rge@F0x');
            // TUser::SignIn('admin','Ge0rge@F0x');
            // exit ('Signed in, please refresh');
            exit ('Sign in, please');
        }
        $cu = TUser::getCurrent()->getUserName();
        print "Signed in as ".$cu."\n";

        $i = 6;
        $testUser = 'testuser'.$i;
        $fullname = 'Test User';
        $result = TUser::addAccount($testUser,'p@ssw0rd',$testUser.'@mail.com',['Bloggers','Froggers'],[
            TUser::profileKeyFullName => $fullname,
            'no-such-thing' => 'No good'
        ]);

        $result = TUser::addAccount($testUser,'p@ssw0rd',$testUser.'@mail.com',[],[
            TUser::profileKeyFullName => $fullname
           // ,'no-such-thing' => 'No good'
        ]);


        $result->user = empty($result->user) ? 'no user' : 'OK';
        print_r($result);

        $user = TUser::getByUserName('testuser4');
        $result = $user->getAccountPageUrl();
        $this->assertNotEmpty($result,'url');
        print ("\n<a href='$result' target='_blank'>Account page</a>\n");
        echo \Core::make('helper/navigation')->getLogInOutLink();
    }
}