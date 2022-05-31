<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/9/2019
 * Time: 8:39 AM
 */

namespace PeanutTest\scripts;


use Tops\mail\TEmailValidator;
use Tops\mail\TMailgunEmailValidator;

class MailgunvalidatorTest extends TestScript
{

    public function execute()
    {

        print "\n\nemail: badd address\n";
        $actual = TEmailValidator::Validate('badd address');
        $this->assertNotEmpty($actual,'Validation result');
        $this->assert(isset($actual->usedExternal),'used external not set');
        $this->assert($actual->usedExternal == false,'used external');
        $this->assert(!$actual->isValid,'validated invalid address');
        var_dump($actual);

        print "\n\nemail :terrysorelledoesntlivehereanymore@gmail.com";
        $actual = TEmailValidator::Validate('terrysorelledoesntlivehereanymore@gmail.com\n');
        $this->assertNotEmpty($actual,'Validation result');
        $this->assert(isset($actual->usedExternal),'used external not set');
        $this->assert($actual->usedExternal !== true,'used external');
        $this->assert(!$actual->isValid,'validated invalid address');
        var_dump($actual);

        $email = 'noterrysorelle@gmail.com';
        print "\n\nemail: $email\n";
        $actual = TEmailValidator::Validate($email);
        $this->assertNotEmpty($actual,'validation result');
        $this->assert(isset($actual->usedExternal),'used external not set');
        $this->assert($actual->usedExternal == true,'used external');
        $this->assert(!$actual->isValid,'should be invalid');
        var_dump($actual);


        $email = 'terry.sorelle@outlook.com';
        print "\n\nemail: $email\n";
        $actual = TEmailValidator::Validate($email);
        $this->assertNotEmpty($actual,'validation result');
        $this->assert(isset($actual->usedExternal),'used external not set');
        $this->assert($actual->usedExternal == true,'used external');
        $this->assert($actual->isValid,'refuse valid address');
        var_dump($actual);

        $email = 'lizyeats@gmail.com';
        print "\n\nemail: $email\n";
        $actual = TEmailValidator::Validate($email);
        $this->assertNotEmpty($actual,'validation result');
        $this->assert(isset($actual->usedExternal),'used external not set');
        $this->assert($actual->usedExternal == true,'used external');
        $this->assert($actual->isValid,'refuse valid address');
        var_dump($actual);


        /*


        $validator = new TMailgunEmailValidator();
        if ($validator->enabled) {
//            $result = $validator->validate('terry.sorelle@outlook.com');
//            $this->assertNotEmpty($result,'Validation resule');
//            $this->assert($result === true,'Valid address failed to validate');
//
            $result = $validator->validate('terrysorelledoesntlivehereanymore@gmail.com');
            $this->assertNotEmpty($result,'Validation result');
            $this->assert($result !== true,'Invalid address validated');
            var_dump($result);


        }
        else {
            print "Validator not enabled.";
        }
*/
    }

}