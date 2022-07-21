<?php

namespace Peanut\songs\services;



use Peanut\songs\db\model\entity\Songset;
use Peanut\songs\SongsManager;
use Tops\sys\TUser;

/**
 * Service contract
 *    Request: setId
 *    Response:
 *       interface IGetSongsResponse extends IGetVersesResponse {
 *            set: ISongSet;
 *            sets: ISongSet[];
 *            songs: ISongInfo[];
 *            catalogSize: number;
 *            canedit: any;
 *        }
 *       interface ISongInfo {
 *           id: any;
 *           title: string;
 *           notes: string;
 *       }
 *       interface ISongSet {
 *           id: any;
 *           setname: string;
 *       }
 */
class GetSongsCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $setId = $this->getRequest() ?? 0;
        $manager = new SongsManager();
        $response = new \stdClass();
        $response->sets = $manager->getSongSetList();

        $selectedSet = $this->getSelectedSet($setId,$response->sets);
        if (!$selectedSet) {
            $this->addErrorMessage("Set %setId not found.");
            return;
        }

        $response->set = $selectedSet;

        $response->songs = $manager->getSongInfoInSet($selectedSet->id);
        if (empty($response->songs)) {
            $this->addErrorMessage("No songs found in set '$selectedSet->title'");
            return;
        }

        $song = $response->songs[0];

        $user = TUser::getCurrent();
        $response->canedit = $user->isAuthorized('edit-songs');
        $songDetail = $manager->getSong($song->id);
        $response->lyrics = $songDetail->lyrics;
        $response->notes = $songDetail->notes;
        $response->catalogSize = $manager->getSongCount();
        $this->setReturnValue($response);
    }

    private function getSelectedSet($setId,array $sets) {
        foreach ($sets as $set) {
            if ($set->id == $setId) {
                return $set;
            }
        }
        return false;
    }
}