<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2024-09-28 20:51:51
 */

namespace Peanut\mailings\db\model\entity;
class Subscription  extends \Tops\db\TAbstractEntity 
{ 
    public $id;
    public $emailid;
    public $listid;
    public $status;
    public $uid;

}
