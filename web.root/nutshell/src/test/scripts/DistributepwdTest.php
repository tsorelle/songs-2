<?php

namespace PeanutTest\scripts;

use Laminas\Validator\EmailAddress;
use Peanut\QnutDirectory\sys\MailTemplateManager;
use Peanut\QnutMigration\db\model\repository\MigrationUsersRepository;
use Tops\sys\TTemplateManager;

class DistributepwdTest extends TestScript
{

    public function execute()
    {
        $templateManager = new MailTemplateManager();
        $template = $templateManager->getTemplateContent('SiteAnnouncement.html');
        $repository = new MigrationUsersRepository();
        /**
         * @var $users \Peanut\QnutMigration\db\model\entity\MigrationUser[]
         */
        $users = $repository->getAll();
        $count = 0;
        $errors = 0;
        foreach ($users as $user) {
            if ($user->processed == 1) {
                continue;
            }
            try {

                $recipient = new \Tops\mail\TEmailAddress($user->email,$user->fullname);
                $bodyText = TTemplateManager::ReplaceContentTokens($template, [
                    'fullname' => $user->fullname,
                    'username' => $user->username,
                    'pwd' => $user->pwd]);
                \Tops\mail\TPostOffice::SendMessageFromUs(
                    $recipient,
                    'New Website for SCYM',
                    $bodyText
                );
                $user->processed = 1;
                print "\nSent to: $user->fullname";
                $count++;
            } catch (\Exception $ex) {
                $errors++;
                $user->error = $ex->getMessage() . "\n\n" . $ex->getTraceAsString();
            }
            $repository->update($user);
        }
        print "\n\n\n$count messages sent\n";
        print "$errors errors\n";

    }
}