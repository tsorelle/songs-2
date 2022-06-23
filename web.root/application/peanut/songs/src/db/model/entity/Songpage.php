<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2022-06-07 13:27:04
 */ 

namespace Peanut\songs\db\model\entity;

class Songpage  extends \Tops\db\TAbstractEntity
{ 
    public $id;
    public $songId;
    public $introduction;
    public $commentary;
    public $active;
    public $postedDate;
    public $pageimage;
    public $imagecaption;
    public $youtubeId;
    public $hasicon;
    public $hasthumbnail;

    public function getDtoDataTypes()
    {
        $types = parent::getDtoDataTypes();
        $types['postedDate'] = \Tops\sys\TDataTransfer::dataTypeDate;
        return $types;
    }
}
