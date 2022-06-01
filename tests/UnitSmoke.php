<?php


use PHPUnit\Framework\TestCase;

class UnitSmoke extends TestCase
{
    function testFirst () {
        $actual = class_exists('Peanut\sys\ViewModelManager');
        $actual = class_exists('Peanut\songs\services\HelloMarsCommand');
        $this->assertTrue($actual,'class not found');
    }
}
