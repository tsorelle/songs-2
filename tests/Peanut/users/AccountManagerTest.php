<?php

namespace Peanut\users;

use PHPUnit\Framework\TestCase;

class AccountManagerTest extends TestCase
{

    public function testSetAdminAccount()
    {
        $password = 'Banj0Boy';
        $manager = new AccountManager();
        $manager->setAdminAccount($password);
        $account = $manager->getUserData('admin');
        $this->assertNotEmpty($account);
        $actual = password_verify($password,$account->password);
        $this->assertTrue($actual);

    }

    /**
     * @return void
     *
     * Tests addRole, getRoleIdForName, removeRole
     */
    public function testAddRole()
    {
        $manager = new AccountManager();
        $manager->addRole('clowns','Payasos','test');
        $roleId = $manager->getRoleIdForName('clowns');
        $this->assertNotEmpty($roleId);
        $manager->removeRole('clowns');
        $roleId = $manager->getRoleIdForName('clowns');
        $this->assertEmpty($roleId);

    }


    /**
     * @return void
     * Tests addAccount, getUserData,addRole, addUserRoles, getUserRoles,
     *  getUserRoleNames, getUsersInRole and removeAccount
     */
    public function testAddAccount()
    {
        $password = 'password';
        $manager = new AccountManager();
        $addResult = $manager->addAccount('testuser',$password);
        $account = $manager->getUserData('testuser');
        $this->assertNotEmpty($account);
        $this->assertEquals($account->id,$addResult->accountId);
        $expected = 'testuser';
        $actual = $account->username;
        $this->assertEquals($expected,$actual);
        $actual = password_verify($password,$account->password);
        $this->assertTrue($actual);
        $manager->addRole('Clowns','test');
        $manager->addRole('Jugglers','test');

        $manager->addUserRoles($account->id,['Clowns','Jugglers']);
        $uroles = $manager->getUserRoles($account->id);
        $urolecount = count($uroles);
        $uroles = $manager->getUserRoleNames($account->id);
        $this->assertEquals($urolecount,count($uroles));
        $clowns = in_array('Clowns',$uroles);
        $this->assertTrue($clowns);
        $jugs = in_array('Jugglers',$uroles);
        $this->assertTrue($jugs);

        $users = $manager->getUsersInRole('Clowns');
        $this->assertNotEmpty($users);
        $user = $users[0];
        $this->assertEquals($account->id,$user->id);

        $manager->removeAccount($account->id);
        $account = $manager->getUserData('testuser');
        $this->assertEmpty($account);
    }

    public function testAddRoleForUserId()
    {
        $password = 'password';
        $username = 'testuser';
        $manager = new AccountManager();
        $manager->addAccount('testuser',$password);
        $account = $manager->getUserData('testuser');
        $manager->addRole('Clowns','test');
        $manager->addRoleForUserId($account->id,'Clowns');
        $roles = $manager->getUserRoleNames('testuser');
        $actual = in_array('Clowns',$roles);
        $this->assertTrue($actual);
        $manager->removeAccount($username);
        $manager->removeRole('Clowns');


    }

    public function testChangePassword()
    {
        $password = 'password';
        $newpwd = 'newpassword';

        $manager = new AccountManager();
        $manager->addAccount('testuser',$password);
        $manager->changePassword('testuser',$newpwd);
        $user = $manager->getUserData('testuser');
        $result = password_verify($newpwd,$user->password);
        $this->assertTrue($result);
        $manager->removeAccount($user->id);

    }

    public function testSignIn()
    {
        $password = 'password';
        $username = 'testuser';
        $manager = new AccountManager();
        $manager->addAccount('testuser',$password);
        $account = $manager->getUserData('testuser');

        $signinResult = $manager->signIn($username,$password);
        $success = $signinResult !== false;
        $result = $success ? '' : $signinResult;
        $this->assertTrue($success,$result);

        $current = $manager->getCurrentSignedInUser();
        $success = $current !== false;
        $this->assertTrue($success);
        $this->assertEquals($current->id,$account->id);
        $manager->signOut();
        $current = $manager->getCurrentSignedInUser();
        $this->assertEmpty($current);

        $manager->removeAccount($username);
    }

    public function testGetProfileValues()
    {

        $manager = new AccountManager();
        $name = 'Test User';
        $username = 'testuser';
        $email = 'test@user.com';
        $addResult = $manager->registerSiteUser($username,'password',$name,$email);
        $errorCode = isset($addResult->errorCode) ? $addResult->errorCode : 'no error code';
        $this->assertTrue($errorCode === false);
        $userId = $addResult->accountId;
        $user = $manager->getCmsUserIdByEmail($email);
        $this->assertNotEmpty($user);
        $profile = $manager->getProfileValues($userId);
        $manager->removeAccount($userId);
        $this->assertIsArray($profile);
        $actual = $profile['full-name'];
        $expected = $name;
        $this->assertEquals($expected,$actual);
        $actual = $profile['email'];
        $expected = $email;
        $this->assertEquals($expected,$actual);



    }

    public function testChangeUserName()
    {
        $password = 'password';
        $username = 'testuser';
        $manager = new AccountManager();
        $manager->addAccount('testuser',$password);
        $id = $manager->getAccountIdForUsername($username);
        $expected = 'newusername';
        $manager->changeUserName($id,$expected);
        $user = $manager->getUserData($id);
        $actual = $user->username;
        $manager->removeAccount($id);
        $this->assertEquals($expected,$user->username);

    }

}
