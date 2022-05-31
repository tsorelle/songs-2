<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2019-03-14 17:29:18
 */ 

namespace Peanut\contacts\db\model\entity;

class EmailCorrection  extends \Tops\db\TEntity
{ 
    public $id;
    public $address;
    public $name;
    public $personId;
    public $accountId;
    public $reportedDate;
    public $errorLevel;
    public $errorMessage;
    public $retriesLeft;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['reportedDate'] = \Tops\sys\TDataTransfer::dataTypeDateTime;
        return $types;
    }
}
