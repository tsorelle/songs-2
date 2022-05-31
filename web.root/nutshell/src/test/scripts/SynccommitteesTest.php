<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 6/22/2019
 * Time: 1:54 PM
 */

namespace PeanutTest\scripts;


use Peanut\QnutCommittees\CommitteeManager;
use Peanut\QnutCommittees\db\model\repository\AppointeesRepository;
use Tops\mail\TMailgunListManager;
use Tops\sys\TTracer;
use Tops\sys\TWebSite;

class SynccommitteesTest extends TestScript
{

    public function execute()
    {
/*        $code = 'nominating';
        $domain = TWebSite::GetDomain();
        $manager = new TMailgunListManager();
        $repository = new CommitteeMembersRepository();;
        $memberList = $repository->getEmailAddressList($code);
        $response = $manager->sychronizeList(sprintf('%s@%s',$code,$domain),$memberList);

        $response->code = $code;
        $response->domain = $domain;
        $response->members = $memberList;
        var_dump($response);
*/
        $manager = new CommitteeManager();
        $manager->synchronizeForwarders();


    }
}