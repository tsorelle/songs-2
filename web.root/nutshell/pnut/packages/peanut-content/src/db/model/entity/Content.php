<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-05-31 17:51:59
 */ 

namespace Peanut\content\db\model\entity;

class Content  extends \Tops\db\TimeStampedEntity 
{ 
    public $id;
    public $description;
    public $format;
    public $content;
    public $active;

}
