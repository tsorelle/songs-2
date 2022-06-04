<?php

namespace Peanut\content\services;

use Peanut\content\db\ContentManager;

class GetContentCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        $response = (new ContentManager())->getContent($id);
        $this->setReturnValue($response);
    }
}