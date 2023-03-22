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
        $setId = 0;
        $songId = 0;
        $songIndex = 0;
        $initializing = false;
        $request = $this->getRequest();
        if ($request) {
            $setId = $request->setId ?? 0;
            $songId = $request->songId ?? 0;
            $initializing = isset($request->initializing);
        }
        $manager = new SongsManager();
        $response = new \stdClass();
        if ($initializing) {
            $user = TUser::getCurrent();
            $response->username = $user->getUserName();
            $response->canedit = $user->isAuthorized('edit-songs');
            $response->sets = $manager->getSongSetList();
            $selectedSet = $this->getSelectedSet($setId, $response->sets);
            if (!$selectedSet) {
                $this->addErrorMessage("Set %setId not found.");
                return;
            }
            $response->set = $selectedSet;
        }
        else {
            $selectedSet = $manager->getSetById($setId);
        }

        $response->songs = $manager->getSongInfoInSet($selectedSet->id);
        if (empty($response->songs)) {
            $this->addErrorMessage("No songs found in set '$selectedSet->title'");
            return;
        }

        if (!$songId) {
            $songId = $response->songs[0]->id;
            $songIndex = 0;
        }
        else {
            $count = count($response->songs);
            for($i=0;$i<$count;$i++) {
                $song = $response->songs[$i];
                if ($song->id == $songId) {
                    $songIndex = $i;
                    break;
                }
            }
        }

        $songDetail = $manager->getSong($songId);
        $response->lyrics = $songDetail->lyrics;
        $response->notes = $songDetail->notes;
        $response->songIndex = $songIndex;
        $response->catalogSize = $manager->getSongCount($setId);
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