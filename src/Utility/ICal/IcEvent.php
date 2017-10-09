<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\Utility\ICal;

use DateTime;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Attendees;
use Eluceo\iCal\Property\Event\Organizer;

/**
 * Event class
 *
 * This is a wrapper/helper class for creating
 * iCal events.
 */
class IcEvent
{
    /**
     * @var \Eluceo\iCal\Component\Event $event Instance of the event
     */
    protected $event;

    /**
     * @var array $defaultParams Default event configuration parameters
     */
    protected $defaultParams = [
        'id' => null,
        'sequence' => 0,
        'summary' => 'Reminder',
        'description' => '',
        'attendees' => [],
    ];

    /**
     * Constructor
     *
     * @param mixed $event Event (null, or array of settings, or Event instane)
     */
    public function __construct($event = null)
    {
        $params = [];
        if (is_array($event) && !empty($event)) {
            $params = $event;
            $event = new Event();
        }

        if (empty($event)) {
            $event = new Event();
        }

        $this->setEvent($event);
        $this->configureEvent($params);
    }

    /**
     * Configure Event parameters
     *
     * @param array $params Event parameters
     * @return void
     */
    public function configureEvent(array $params = [])
    {
        $params = array_merge($this->defaultParams, $params);
        foreach ($params as $name => $value) {
            $method = 'set' . ucfirst($name);
            if (method_exists($this, $method) && is_callable([$this, $method])) {
                $this->$method($value);
            }
        }
    }

    /**
     * Set Event instance
     *
     * Overwrite the instance of the iCal event with
     * a given one.
     *
     * @param \Eluceo\iCal\Component\Event $event Instance of Event to use
     * @return void
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get Event instance
     *
     * @return \Eluceo\iCal\Component\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set Event ID
     *
     * @param string $id Unique ID for event
     * @return void
     */
    public function setId($id)
    {
        $id = (string)$id;
        if (!empty($id)) {
            $this->event->setUniqueId($id);
        }
    }

    /**
     * Set Event sequence
     *
     * Use 0 for new events and time() or similar for the
     * updated events.
     *
     * @param int $sequence Event sequence
     * @return void
     */
    public function setSequence($sequence)
    {
        $sequence = (int)$sequence;
        $this->event->setSequence($sequence);
    }

    /**
     * Set Event organizer
     *
     * @param string $organizer Organizer email
     * @return void
     */
    public function setOrganizer($organizer)
    {
        $organizer = (string)$organizer;
        $params = ['MAILTO' => $organizer];

        if (!empty($organizer)) {
            $organizer = new Organizer($organizer, $params);
            $this->event->setOrganizer($organizer);
        }
    }

    /**
     * Set Event summary
     *
     * @param string $summary Event subject/summary
     * @return void
     */
    public function setSummary($summary)
    {
        $summary = (string)$summary;
        $this->event->setSummary($summary);
    }

    /**
     * Set Event description
     *
     * @param string $description Event description
     * @return void
     */
    public function setDescription($description)
    {
        $description = (string)$description;
        $this->event->setDescription($description);
    }

    /**
     * Set Event start time
     *
     * @param \DateTime $time Start time in UTC
     * @return void
     */
    public function setStartTime(DateTime $time)
    {
        $this->event->setDtStart($time);
    }

    /**
     * Set Event end time
     *
     * @param \DateTime $time End time in UTC
     * @return void
     */
    public function setEndTime(DateTime $time)
    {
        $this->event->SetDtEnd($time);
    }

    /**
     * Set Event location
     *
     * @param string $location Event location
     * @return void
     */
    public function setLocation($location)
    {
        $location = (string)$location;
        if (!empty($location)) {
            $this->event->setLocation($location, "Location:");
        }
    }

    /**
     * Set Event attendees
     *
     * @param array $attendees A list of attendees' emails
     * @return void
     */
    public function setAttendees(array $attendees = [])
    {
        $iCalAttendees = new Attendees();
        foreach ($attendees as $email) {
            $iCalAttendees->add("MAILTO:$email", [
                'ROLE' => 'REQ-PARTICIPANT',
                'PARTSTAT' => 'NEEDS-ACTION',
                'RSVP' => 'TRUE',
            ]);
        }
        $this->event->setAttendees($iCalAttendees);
    }
}
