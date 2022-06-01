<?php


use PHPUnit\Framework\TestCase;

class UnitSmoke extends TestCase
{
    function testFirst () {
        $actual = class_exists('Peanut\sys\ViewModelManager');
        $this->assertTrue($actual,'class not found');
        $actual = class_exists('Peanut\songs\services\GetSongCommand');
        $this->assertTrue($actual,'class not found');
    }
}
