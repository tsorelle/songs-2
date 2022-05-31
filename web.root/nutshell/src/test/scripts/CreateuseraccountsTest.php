<?php

namespace PeanutTest\scripts;

use Peanut\QnutDirectory\db\model\entity\Person;
use Peanut\QnutDirectory\db\model\repository\PersonsRepository;
use Peanut\QnutMigration\db\model\repository\MigrationUsersRepository;
use \Peanut\QnutMigration\db\model\entity\MigrationUser;
use Concrete\Core\Export\Item\AttributeKey;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Tops\concrete5\TConcrete5UserFactory;
use Tops\services\TMessageContainer;
use Tops\sys\TAddUserAccountResponse;
use Tops\sys\TUser;

class CreateuseraccountsTest extends TestScript
{

    public function execute()
    {
        $unprocessedOnly = true;
        $this->assert(class_exists('\Peanut\QnutMigration\db\model\entity\MigrationUser'),'No user class');
        $this->assert(class_exists('\Peanut\QnutMigration\db\model\repository\MigrationUsersRepository'),'No repo class');
        $repository = new MigrationUsersRepository();
        $personRepo = new PersonsRepository();
        $count = 0;
        $errors = 0;
        /**
         * @var $users \Peanut\QnutMigration\db\model\entity\MigrationUser[]
         */
        $users = $repository->getAll();
        foreach ($users as $user) {
            if ($unprocessedOnly && $user->processed == 1) {
                continue;
            }

            try {
                /**
                 * @var $person Person
                 */
                $person = $personRepo->get($user->id);
                if (!$person) {
                    $user->error = 'Person not found for id '.$user->id;
                }
                else {
                    $pwd = 'George@Fox1652'.$user->id;
                    /**
                     * @var $result TAddUserAccountResponse
                     */
                    $result = TUser::addAccount(
                        $user->username,
                        $pwd,
                        $person->email ? $person->email : $user->email,
                        [], // roles
                        [
                            TUser::profileKeyFullName => $person->fullname
                        ]);
                    if (isset($result->errorCode) && $result->errorCode) {
                        $user->error = 'Add user failed '.$result->errorCode;
                    }
                    else {
                        $person->accountId = $result->userId;
                        $personRepo->update($person);
                        $count++;
                        $user->processed = 1;
                        $user->pwd = $pwd;
                        print "Processed: $user->fullname\n";
                    }
                }
            }
            catch (\Exception $ex) {
                $errors++;
                $user->error = $ex->getMessage()."\n\n".$ex->getTraceAsString();
            }

            $repository->update($user);

        }
        print "\n$count accounts added";
    }
}