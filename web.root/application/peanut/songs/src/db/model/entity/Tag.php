<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:32
 */ 

namespace Peanut\songs\db\model\entity;

class Tag  extends \Tops\db\TAbstractEntity 
{ 
    public $id;
    public $name;
    public $description;
    public $type;
    public $code;
    public $active;
}
