<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2019-03-14 17:29:18
 */
namespace Peanut\contacts\db\model\repository;


use \PDO;
use PDOStatement;
use Peanut\contacts\db\model\entity\EmailCorrection;
use PharIo\Manifest\Email;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class EmailCorrectionsRepository extends \Tops\db\TEntityRepository
{
    protected function getTableName() {
        return 'qnut_email_corrections';
    }

    public function refreshCorrections()
    {
        // disabled pending testing of new approache
        return 0;
        /*
        $sql = 'UPDATE qnut_email_corrections c '.
            'JOIN pnut_contacts p ON c.personId = p.id  '.
            "SET c.active = 0, c.changedon = CURRENT_TIMESTAMP , c.changedby = 'system' ".
            'WHERE c.errorLevel = 1 AND p.email IS NOT NULL AND c.address <> p.email ';
        $stmt = $this->executeStatement($sql);
        return $stmt->rowCount();*/
    }

    public function unsubscribeAll($id)
    {
        $sql = 'DELETE from qnut_email_subscriptions WHERE personId = ?';
        $this->executeStatement($sql,[$id]);
    }

    protected function getDatabaseId() {
        return 'tops-db';
    }

    protected function getClassName() {
        return 'Peanut\contacts\db\model\entity\EmailCorrection';
    }

    protected function getFieldDefinitionList()
    {
        return array(
        'id'=>PDO::PARAM_INT,
        'address'=>PDO::PARAM_STR,
        'name'=>PDO::PARAM_STR,
        'personId'=>PDO::PARAM_INT,
        'accountId'=>PDO::PARAM_INT,
        'reportedDate'=>PDO::PARAM_STR,
        'errorLevel'=>PDO::PARAM_INT,
        'errorMessage'=>PDO::PARAM_STR,
        'retriesLeft'=>PDO::PARAM_STR,
        'createdby'=>PDO::PARAM_STR,
        'createdon'=>PDO::PARAM_STR,
        'changedby'=>PDO::PARAM_STR,
        'changedon'=>PDO::PARAM_STR,
        'active'=>PDO::PARAM_STR);
    }

    public function getPendingCorrectionsCount() {
        $sql = 'SELECT COUNT(*) FROM `qnut_email_corrections` WHERE active = 1';
        return $this->getValue($sql);
    }

    public function getPendingCorrections($personFormUrl) {
        $sql = "SELECT id, personId, reportedDate, address,  ".
            "IFNULL(`name`,'(unknown)') AS `name`, '' AS correction, null as remove, null as invalid, ".
            "IF(personId IS NULL,'0', CONCAT( '%s',personId)) AS personLink ".
            "FROM qnut_email_corrections WHERE active=?";

        $sql = sprintf($sql,$personFormUrl);

        $stmt = $this->executeStatement($sql,[1]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param $email
     * @return EmailCorrection | boolean
     */
    public function getCorrection($email) {
        return $this->getSingleEntity('address = ? AND active=1',[$email]);
    }

    public function resolve($emailAddress)
    {
        $sql = 'UPDATE '.$this->getTableName().'SET active = 0 WHERE address = ?';
        return $this->executeStatement($sql,[$emailAddress]);
    }
}