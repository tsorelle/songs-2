<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/15/2019
 * Time: 3:38 PM
 */

namespace Peanut\contacts\services\messaging;


use Peanut\contacts\db\DirectoryManager;
use Peanut\contacts\db\EmailManager;
use Tops\services\TServiceCommand;
use Tops\sys\TLanguage;

/**
 * Class GetPendingEmailCorrectionsCommand
 * @package Peanut\QnutDirectory\services\messaging
 *
 *  service contract
 *      response {
 *          translations: string[]
 *          corrections: IEmailCorrection[]
 *      }
 *
 *     interface IEmailCorrection
 *     {
 *         id : any;
 *         name: string;
 *         address : string;
 *         reportedDate : string;
 *         correction: string;
 *         personLink: string;
 *         remove: any;
 *     }
 */

class GetPendingEmailCorrectionsCommand extends TServiceCommand
{

    protected function run()
    {
        $response = new \stdClass();
        $manager = new EmailManager();
        $response->corrections = $manager->getUnresolvedEmailProblems();
        $response->translations = TLanguage::getTranslations(array(
            'error-invalid-emails',
            'email-apply-corrections',
            'label-add',
            'label-cancel',
            'label-date',
            'label-address',
            'label-name',
            'label-correction',
            'label-remove'
        ));

        $this->setReturnValue($response);
    }
}