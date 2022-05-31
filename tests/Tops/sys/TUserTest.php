<?php

namespace Tops\sys;

use Peanut\users\AccountManager;
use PHPUnit\Framework\TestCase;

class TUserTest extends TestCase
{

    public function testSignIn()
    {
        $password = 'password';
        $username = 'testuser';
        $manager = new AccountManager();
        $account = $manager->addAccount('testuser',$password);

        $signedIn = TUser::SignIn($username,$password);
        $this->assertTrue($signedIn === true);

        $current = TUser::getCurrent();
        $success = $current !== false;
        $this->assertTrue($success);
        $id = $current->getId();
        $this->assertEquals($current->getUserName(),$username);
        $manager->signOut();
        $manager->removeAccount($username);

    }

    public function testGetCurrentAnonymous()
    {
        $manager = new AccountManager();
        $manager->signOut();
        $current = TUser::getCurrent();
        $actual = $current->isAdmin();
        $this->assertFalse($actual);
        $actual = $current->isAuthenticated();
        $this->assertFalse($actual,'Should be not autnenticated');
        $actual = $current->getUserName();
        $this->assertEquals('guest',$actual);
    }

    public function testGetCurrentSignedIn()
    {
        // todo: test use case alrady signed in
        // todo: test use case non-admin user
        $manager = new AccountManager();
        $manager->signOut();
        $password = 'password';
        $username = 'testuser';
        $manager->addRole('Clowns','circus test','test');
        // $manager->addRole('administrators','test');
        $manager->addAccount($username,$password);
        $account = $manager->getUserData($username);
        $manager->addRoleForUserId($account->id,'Clowns');
        $manager->addRoleForUserId($account->id,'administrator');
        $manager->signIn($username,$password);

        $current = TUser::getCurrent();
        $this->assertEquals($username,$current->getUserName());
        $this->assertTrue($current->isMemberOf('Clowns'));
        $this->assertTrue($current->isAuthenticated());
        $this->assertTrue($current->isAdmin());
        $current->signOut();
        $manager->removeAccount($username);
        $manager->removeRole('Clowns');
        $manager->removeRole('Jugglers');

        $current = TUser::getCurrent();
        $actual = $current->isAdmin();
        $this->assertFalse($actual);
        $actual = $current->isAuthenticated();
        $this->assertFalse($actual,'Should be not autnenticated');
        $actual = $current->getUserName();
        $this->assertEquals('guest',$actual);
    }


    public function testGetByUserName()
    {
        $manager = new AccountManager();
        $manager->signOut();
        $password = 'password';
        $username = 'testuser';

        $manager->addAccount($username,$password);

        $user = TUser::getByUserName($username);

        $this->assertNotEmpty($user);
        $actual = $user->getUserName();
        $this->assertEquals($actual, $username);
        $id = $user->getId();
        $manager->removeAccount($id);
    }

    public function testGetById()
    {
        $user = TUser::getById(1);
        $this->assertNotEmpty($user);
        $actual = $user->getUserName();
        $this->assertEquals($actual, 'admin');
        $id = $user->getId();
        $this->assertEquals(1,$id);;
    }

    public function testAddAccount()
    {
        TUser::SignIn('admin','Banj0Boy');
        $password = 'password';
        $username = 'testuser';
        $fullname = 'Test User';
        $email = 'user@test.com';

        TUser::addAccount($username,$password,$fullname,$email,['manager']);

        $user = TUser::getByUserName($username);
        $id = $user->getId();
        $actual = $user->getUserName();
        self::assertEquals($username,$actual);
        $actual = $user->getEmail();
        $this->assertEquals($email,$actual);
        $actual = $user->isMemberOf('manager');
        $this->assertTrue($actual);
        TUser::SignOut();
        $manager = new AccountManager();
        $manager->removeAccount($id,true);
    }

}
