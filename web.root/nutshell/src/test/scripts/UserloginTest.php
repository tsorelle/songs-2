<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Tops\sys\TUser;
use Concrete\Core\User\User;


class UserloginTest extends TestScript
{

    public function execute()
    {
        if (!TUser::getCurrent()->isAdmin()) {
            exit('Not authorized to run this test.');
        }
        $username = 'ScriptMaster';

        $current = TUser::getCurrent();
        $isAdmin = $current->isAdmin();

        if (TUser::getCurrent()->isAuthenticated()) {
            exit('Log out before running test');
        }
        /*
                password ignored in C5
                $actual = TUser::SignIn($username,'bad password');
                $this->assert($actual === false,'Sign in result should have been false');
        */

        $actual = TUser::SignIn('unknownuser','bad password');
        $this->assert($actual === false,'Sign in result should have been false');

        $expected = !TUser::getCurrent()->isAuthenticated();
        $this->assert($expected,'Expected anonymous');

        $actual = TUser::SignIn($username,'M6tJb@1*yoIf97cFCQlDFmwJ');
        $this->assert($actual,'Not logged in');
        $current = TUser::getCurrent();
        $c5user = new User();
        $actual = $c5user->uID;
        $expected = $current->getId();
        $this->assertEquals($expected,$actual,'Current user mismatch');
        $actual = $current->getUserName();
        $this->assertEquals($username,$actual,'Wrong user is current');
        $actual = $current->isAdmin();
        $this->assert($actual,'Not admin');

    }
}