<?php

namespace PeanutTest\scripts;

use Application\scym\ui\UserMenu;

class UseruiTest extends TestScript
{

    public function execute()
    {
        $markup = UserMenu::getMarkup();
        $this->assert($markup,'No markup generated');
        $markupText = join("\n",$markup);
        $this->assertNotEmpty($markupText,'Markup output');
    }
}