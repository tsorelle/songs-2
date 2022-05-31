<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 10/2/2017
 * Time: 6:13 AM
 */

namespace PeanutTest\scripts;

use Core;
use Concrete\Core\Attribute\Category;
use Concrete\Core\Attribute\Key\UserKey;
use Tops\concrete5\TConcrete5User;
use Tops\sys\TUser;
use UserAttributeKey;
use AttributeType;
use Concrete\Core\Attribute\Category\CategoryService;

class UserattrTest extends TestScript
{
    private $userController;

    private function getUserController() {
        if (!isset($this->userController)) {
            /**
             * @var $service CategoryService;
             */
            $service = \Core::make(CategoryService::class);
            $this->userController = $service->getByHandle('user')->getController();
        }
        return $this->userController;
    }

    private function createAttribute($args,$type='text',$pkg=false) {
        $handle = $args['akHandle'];
        if (UserKey::getByHandle($handle) === null) {
            $this->getUserController()->add($type, $args, $pkg);
        }
    }

    public function execute()
    {
        if (!TUser::getCurrent()->isAdmin()) {
            exit('Not authorized to run this test.');
        }
        $list = TConcrete5User::getAttributeList();
        print_r($list);
        /*
        foreach  ($list as $key => $args ) {
            $this->createAttribute($args);
        }*/

    }
}