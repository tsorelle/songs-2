<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-05-11 14:51:59
 */ 

namespace Peanut\contacts\db\model\entity;

class Contact  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $fullname;
    public $email;
    public $phone;
    public $listingtypeId;
    public $sortkey;
    public $notes;
    public $uid;
    public $accountId;
    public $active;

}
