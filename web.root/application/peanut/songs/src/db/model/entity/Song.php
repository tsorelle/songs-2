<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:32
 */ 

namespace Peanut\songs\db\model\entity;

class Song  extends \Tops\db\TAbstractEntity 
{ 
    public $id;
    public $title;
    public $lyrics;
    public $publicdomain;
    public $notes;
}
