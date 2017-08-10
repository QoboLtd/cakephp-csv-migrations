<?php
namespace CsvMigrations\Utility\ICal;

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

/**
 * Calendar class
 *
 * This is a wrapper/helper class for creating
 * iCal calendars.
 */
class IcCalendar
{
    /**
     * @var \Eluceo\iCal\Component\Calendar $calendar Instance of the calendar
     */
    protected $calendar;

    /**
     * Constructor
     *
     * @param mixed $calendar (null or Calendar instance)
     */
    public function __construct($calendar = null)
    {
        if (empty($calendar)) {
            $calendar = new Calendar('-//Calendar Events//EN//');
            $calendar->setCalendarScale('GREGORIAN');
        }

        $this->setCalendar($calendar);
    }

    /**
     * Set calendar instance
     *
     * @param \Eluceo\iCal\Component\Calendar $calendar Calendar instance to set
     * @return void
     */
    public function setCalendar(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * Get calendar instance
     *
     * @return \Eluceo\iCal\Component\Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Add event to calendar
     *
     * @param \Eluceo\iCal\Component\Event $event Event to add
     * @return void
     */
    public function addEvent(Event $event)
    {
        $this->calendar->addComponent($event);
    }
}
