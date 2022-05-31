<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/14/2019
 * Time: 5:48 PM
 */

namespace PeanutTest\scripts;

use Application\mailgun\FakeForwardingListManager;
use Mailgun\Mailgun;
use PDOStatement;
use Tops\db\TDatabase;
use Tops\db\TPdoQueryManager;
use Tops\db\TQuery;
use Tops\mail\IForwardingListManager;
use Tops\mail\TEmailAddress;
use Tops\mail\TMailgunConfiguration;
use Tops\mail\TMailgunListManager;
use Tops\sys\TWebSite;


class MailinglistsupdateTest extends TestScript
{

    /**
     * @var IForwardingListManager
     */
    private $manager;
    private function getManager()
    {
        if (!isset($this->manager)) {
            if (TWebSite::GetDomain() === 'local.austinquakers.org') {
                // cannot access mailgun api from dev enviornment so use fake
                $this->manager = new FakeForwardingListManager();
            }
            else {
                $this->manager = new TMailgunListManager();
            }
        }
        return $this->manager;
    }


    public function execute()
    {
        $manager = $this->getManager();
        $lists = ['webproject@austinquakers.org',
            'websupport@austinquakers.org',
            'youngfriends@austinquakers.org'];

        $del = ['worship@austinquakers.org', 'worshipclerk@austinquakers.org'];

        foreach ($lists as $address) {

            $newname = substr($address, 0, strlen($address) - 3) . 'net';
            print "copy to $newname\n";
            $members = $manager->getListMembers($address);
            $manager->removeList($address);
            $manager->addList($newname);
            $manager->addUpdateMembers($newname, $members, false);
        }

        foreach ($del as $address) {
            $manager->removeList($address);
            print "$address deleted\n";
        }
    }
}