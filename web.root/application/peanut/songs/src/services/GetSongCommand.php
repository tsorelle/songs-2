<?php
/**
 * Created by PhpStorm.
 * User: terry
 * Date: 5/15/2017
 * Time: 5:02 PM
 */

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;
use Tops\sys\TLanguage;

class GetSongCommand extends \Tops\services\TServiceCommand
{
    protected function run()
    {
        $songId = $this->getRequest();
        if (empty($songId)) {
            $this->addErrorMessage('Song ID not received.');
            return;
        }
        $manager = new SongsManager();
        $song = $manager->getSong($songId);
        if (!$song) {
            $this->addErrorMessage("Song not found for #$songId");
            return;
        }
        $this->setReturnValue($song);
    }
}