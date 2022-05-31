<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/21/2019
 * Time: 6:18 PM
 */

namespace PeanutTest\scripts;


use Tops\services\IMessageContainer;

class ConsoleMessageContainer implements IMessageContainer
{

    public function AddMessage($messageType, $text, $arg1 = null, $arg2 = null)
    {
        switch($messageType) {
            case 1 :
                print 'ERROR: ';
                break;
            case 2 :
                print 'WARNING: ';
                break;
            default:
                print 'Message: ';
        }
        if ($messageType == 1) {
            exit($text);
        }
        print "$text/n";
    }

    public function AddInfoMessage($text, $arg1 = null, $arg2 = null)
    {
        $this->AddMessage(0,$text);
    }

    public function AddWarningMessage($text, $arg1 = null, $arg2 = null)
    {
        $this->AddMessage(2,$text);
    }

    public function AddErrorMessage($text, $arg1 = null, $arg2 = null)
    {
        $this->AddMessage(1,$text);
    }

    public function GetResult()
    {
        return null;
    }
}