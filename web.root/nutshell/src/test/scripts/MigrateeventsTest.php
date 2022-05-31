<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use mysql_xdevapi\Exception;
use Peanut\QnutCalendar\db\model\CalendarEventManager;
use Peanut\QnutCalendar\db\model\entity\CalendarEvent;
use Tops\db\TQuery;
use Tops\sys\TDateRepeater;

class MigrateeventsTest extends TestScript
{
    // private $testmode = true;

    /**
     * @var TQuery
     */
    private $query;
    private $added = [];
    private $extra = [];
    private $skipped = [];
    private $parsed = [];
    private $today;
    private $now;
    private $recurEndMinimum;
    private $cancelations = [];
    /**
     * @var CalendarEventManager
     */
    private $manager;

    private $committeeIndex =  [
        // tid => committeeId
        1 =>   5,   // First Day School
        2 =>   1,   // Worship and Ministry
        18 =>  10,   // Adult Religious Education
        19 =>  31,   // Colombia Ministry Support
        20 =>  27,   // Friendly Communications
        21 =>   8,   // Community Life
        22 =>  37,   // Community Relations
        23 =>  40,   // Earthcare
        24 =>   6,   // Finance and Administration
        25 =>  38,   // House and Grounds
        26 =>  15,   // Library
        27 =>  16,   // Care and Counsel
        28 =>  24,   // Peace and Social Concerns
        29 =>  26,   // Stewardship
        30 =>  41,   // Death Penalty Abolition Committee
        39 =>  25,   // Nominating
    ];

    private $resourceIndex = [
        // tid => resource
        12 =>  1, // Worship room
        13 =>  2, // Social Hall
        14 => 11, // Library Meeting Room
        15 => 10, // Portable building
        16 =>  9, // Little house main room
        17 =>  6, // Projector
        33 =>  5, // Little house back room
        36 =>  4, // Garden House
        37 =>  3, // Library - Small room
        38 => 12, // Office
    ];

    private $dowIndex = [
        'SU' => 1,
        'MO' => 2,
        'TU' => 3,
        'WE' => 4,
        'TH' => 5,
        'FR' => 6,
        'SA' => 7,
    ];



    private function parseDrupalRepeat($drupalRepeat)
    {
        $parsed = new \stdClass();
        $parsed->exceptions = explode("\n", $drupalRepeat);
        $rrule = array_shift($parsed->exceptions);
        $rules = explode(';', substr($rrule, 11));
        $parsed->freq = array_shift($rules);
        $next = array_shift($rules);
        list($key, $parsed->interval) = explode('=', $next);
        $next = array_shift($rules);
        list($key, $value) = explode('=', $next);
        if ($key == 'BYDAY') {
            $parsed->byDay = $value;
            $next = array_shift($rules);
            list($key, $parsed->until) = explode('=', $next);
        } else {
            $parsed->until = $value;
        }

        return $parsed;
    }

    private function toQnutRepeat($startDate,$parsed) {
        /*
         * $parsed->freq
            $parsed->interval
            $parsed->byDay
            $parsed->until
         */
        $startDate = new \DateTime($startDate);
        switch ($parsed->freq) {
            case 'WEEKLY' :
                $dow = $startDate->format('w') + 1;
                $result = 'wk'.$parsed->interval.",$dow";
                break;
            case 'MONTHLY':
                if (empty($parsed->byDay)) {
                    throw new \Exception('Only monthly by dow is supported');
                }
                $days = explode(',',$parsed->byDay);

                $i = 0;
                $ord = '';
                $dow = '';
                foreach ($days as $day) {
                    $ord  .= substr($day,1,1);
                    $i = substr($day,2);
                    $d =  $this->dowIndex[$i];
                    if ($dow == '') {
                        $dow = $d;
                    }
                    else if ($d != $dow) {
                        throw new \Exception('Multiple dow not supported.');
                    }
                }
                $result = 'mo'.$parsed->interval.",$ord,$dow";
                break;
            default :
                throw new \Exception('Frequency not supported at this time: '.$parsed->freq);
        }

        return $result;
    }

    private function toUpdateRequest($drupalEvent)
    {
        $result = new \stdClass();
        $result->cancelDates = [];
        $result->addDates = [];
        $result->event = null;
        $result->resources = [];
        $result->committees = [];

        $result->event = new CalendarEvent();
        $result->event->id =  0;
        $result->event->allDay = 0;
        $result->event->active = 1;
        $result->event->title =  $drupalEvent->title ;
        $result->event->start =  $drupalEvent->startTime;
        $result->event->end =  $drupalEvent->endTime;
        $result->event->location =  $drupalEvent->location;
        $result->event->url =  NULL;
        $result->event->notes =  '';
        $result->event->description =  $drupalEvent->bodyText;
        $result->event->recurPattern =  '';
        $result->event->recurEnd =  NULL;
        $result->event->recurId =  NULL;
        $result->event->recurInstance =  NULL;

        if ($drupalEvent->forOutsideGroup == 'Yes') {
            $result->event->eventTypeId =  '3'; // outside
        }
        else if ($drupalEvent->isPublic == 'yes') {
            $result->event->eventTypeId =  '1'; // public
        }
        else {
            $result->event->eventTypeId =  '2'; // private
        }

        if ($drupalEvent->repeatRule) {
            $parsed = $this->parseDrupalRepeat($drupalEvent->repeatRule);
            $this->parsed[] = $parsed;
            $result->event->recurPattern = $this->toQnutRepeat($drupalEvent->startDate,$parsed);
            if (!empty($parsed->until)) {
                $until = new \DateTime($parsed->until);
                if ($until <= $this->recurEndMinimum) {
                   $result->event->recurEnd =  $until->format('Y-m-d');
                }
            }
        }

        if (!empty($parsed->exceptions)) {
            foreach ($parsed->exceptions as $exception) {
                list($type,$dates) = explode(':',$exception);
                $dates = explode(',',$dates);
                foreach ($dates as $date) {
                    $date = new \DateTime($date);
                    if ($date >= $this->today) {
                        if ($type == 'EXDATE') {
                            $this->cancelations = $drupalEvent->id . ": ".$date->format('Y-m-d');
                            // cancelations not supported yet.
                            //$result->cancelDates[] = $date->format('Y-m-d');
                        }
                        else {
                            $result->addDates[] = $date->format('Y-m-d');
                        }
                    }
                }
            }
        }

        $stmt = $this->query->executeStatement('SELECT * FROM migrate_events_taxonomy WHERE eventId = ?',[$drupalEvent->eventId]);
        $taxonomies = $stmt->fetchAll(\PDO::FETCH_OBJ);
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->vid == 1) {
                $id = @$this->committeeIndex[$taxonomy->tid];
                if ($id) {
                    $result->committees[] = $id;
                }
            }
            else if ($taxonomy->vid == 3) {
                $id = @$this->resourceIndex[$taxonomy->tid];
                if ($id) {
                    $result->resources[] = $id;
                }
            }
        }


        return $result;
    }


    private function addEvent($event,$committees,$resources) {
        if (!empty($this->testmode)) {
            return $event;
        }
        $id = $this->manager->addEvent($event,'migration');
        $this->manager->updateEventAssociations($id,$committees,$resources);
        return $this->manager->getEvent($id);
    }

    private function postEvent($request)
    {
        $committees = empty($request->committees) ? null : $request->committees;
        $resources = empty($request->resources) ? null : $request->resources;
        $newEvent = $this->addEvent($request->event, $committees, $resources);
        foreach ($request->addDates as $addedDate) {
            $event = clone $newEvent;
            list($date, $starttime) = explode(' ', $event->start);
            $event->start = "$addedDate $starttime";
            if ($event->end) {
                list($date, $endtime) = explode(' ', $event->end);
                $event->end = "$addedDate $endtime";
            }
            $event->id = 0;
            $event->recurId = null;
            $event->recurInstance = null;
            $event->recurEnd = null;
            $event->recurPattern = null;
            $this->addEvent($event, $committees, $resources);
            $this->added[] = $request;
            $this->extra[] = $event;
        }
        $this->added[] = $request;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->manager = new CalendarEventManager();
        $this->now= new \DateTime();
        $this->today = new \DateTime($this->now->format('Y-m-d'));
        $this->recurEndMinimum = clone $this->today;
        $this->recurEndMinimum->modify('3 months');
        $this->query = new TQuery();
        $stmt = $this->query->executeStatement('SELECT * FROM migrate_events');
        $drupalEvents = $stmt->fetchAll(\PDO::FETCH_OBJ);
        foreach ($drupalEvents as $event) {
            try {
                $request = $this->toUpdateRequest($event);
                $this->postEvent($request);
            }
            catch (\Exception $ex) {
                $this->skipped[] = "$event->id: ".$ex->getMessage();
                throw $ex;
            }
        }

        print count($this->added)." events added.\n";
        print count($this->skipped)." events skipped.\n";
        print count($this->cancelations)." dates cancelled.\n";
        if (!empty($this->skipped)) {
            print implode("\n",$this->skipped);
        }
        print "\n";
    }
}