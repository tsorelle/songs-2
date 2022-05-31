<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-03-02 17:24:44
 */ 

namespace Peanut\contacts\db\model;

class Emailbounce  extends \Tops\db\TAbstractEntity
{ 
    public $bounceId;
    public $personId;
    public $email;
}
