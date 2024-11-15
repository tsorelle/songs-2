<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2024-09-28 18:32:03
 */ 
namespace Peanut\mailings\db\model\repository;


use \PDO;
use PDOStatement;
use Peanut\mailings\db\model\entity\Emailaddress;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class EmailaddressesRepository extends \Tops\db\TEntityRepository
{
    const status_active = 1;

    protected function getDatabaseId() {
        return 'twoq';
    }

    public function updateName($emailId, $fullname)
    {
        $this->executeStatement('UPDATE '.$this->getTableName().' SET fullname =? WhERE id=?',[$fullname,$emailId]);
    }

    protected function getTableName() {
        return 'twoq_emailaddresses';
    }

    public function addAddress($fullname, $email)
    {
        $dto = new Emailaddress();
        $dto->email = $email;
        $dto->fullname = $fullname;
        $dto->status = self::status_active;
        return $this->insert($dto);
    }

    public function getByAddress($emailAddress)
    {
        return $this->getSingleEntity('email=?',[$emailAddress]);
    }

    public function newEmailAddress($email, $fullname)
    {
        $dto = $this->getByAddress($email);
        if ($dto) {
            // can't make duplicate
            return false;
        }
        if (!$fullname) {
            $fullname = $email;
        }
        $dto = new Emailaddress();
        $dto->email = $email;
        $dto->fullname = $fullname;
        $dto->status = 1;
        $dto->id = $this->insert($dto);
        return $dto;
    }


    protected function getClassName() {
        return 'Peanut\mailings\db\model\entity\Emailaddress';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_STR,
        'email'=>PDO::PARAM_STR,
        'fullname'=>PDO::PARAM_STR,
        'status'=>PDO::PARAM_STR);
    }

    public function getAddressList($listId, $subscriptionStatus = 1, $emailStatus= 1)
    {
        $sql = 'SELECT e.email AS address , e.fullname AS `name` '.
            'FROM `twoq_emailaddresses`  e '.
            'JOIN `twoq_subscriptions` s ON e.id = s.`emailid` '.
            'WHERE s.`listid` = ? '.
            'AND s.`status` = ? '.
            'AND e.`status` = ? ';

        $stmt = $this->executeStatement($sql, [$listId,$subscriptionStatus,$emailStatus]);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (empty($result)) {
            return false;
        }
        return $result;
    }

    public function getEmailAddress($address)
    {
        return $this->getSingleEntity('email=?',[$address]);

    }
}