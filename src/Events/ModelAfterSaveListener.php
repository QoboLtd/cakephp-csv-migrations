<?php
namespace CsvMigrations\Events;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\Utility\Inflector;

class ModelAfterSaveListener implements EventListenerInterface
{
    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.Model.afterSave' => 'sendCalendarReminder'
        ];
    }

    /**
     * sendCalendarReminder method
     * Notification about the reminder is sent only
     * when the record belonds to anyone.
     *
     * @param Cake\Event $event from the afterSave
     * @param Cake\Datasource\EntityInterface $entity from the afterSave
     * @return array|bool $sent on whether the email was sent
     */
    public function sendCalendarReminder(Event $event, EntityInterface $entity)
    {
        $sent = false;
        $attendees = [];

        //get applications's timezone
        $timezone = Time::now()->format('e');
        $dtz = new \DateTimeZone($timezone);

        $table = $event->subject();
        $subject = sprintf("Reminder for %s", $table->alias());

        if (!$entity->isNew()) {
            $subject = sprintf("Reminder for %s was modified", $table->alias());
        }

        //get attendees for the event
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $config = $table->getConfig();
            $remindersTo = $table->getTableAllowRemindersField();
        }

        if (empty($remindersTo)) {
            return $sent;
        }

        $emails = $this->_getAttendees($table, $entity, $remindersTo);

        if (empty($emails)) {
            return $sent;
        }

        foreach ($emails as $email) {
            $vCalendar = new \Eluceo\iCal\Component\Calendar('//EN//');

            $vTimezone = $this->_getTimezone($timezone, $dtz);
            $vCalendar->setTimezone($vTimezone);

            $vAttendees = $this->_getEventAttendees($emails);

            $vEvent = $this->_getCalendarEvent($entity, [
                'dtz' => $dtz,
                'organizer' => $email,
                'attendees' => $vAttendees
            ]);

            $vEvent->setAttendees($vAttendees);
            $vCalendar->addComponent($vEvent);

            $headers = "Content-Type: text/calendar; charset=utf-8";
            $headers .= 'Content-Disposition: attachment; filename="event.ics"';
            $emailer = new Email('default');

            $emailer->to($email)
                ->setHeaders([$headers])
                ->subject($subject)
                ->attachments(['event.ics' => [
                    'contentDisposition' => true,
                    'mimetype' => 'text/calendar',
                    'data' => $vCalendar->render()
                ]]);
            $sent = $emailer->send();
        }

        return $sent;
    }

    /**
     * getAssignedAssociations
     * gets all Entities associated with the record
     * @param EntityInterface $entity of the record
     * @param ArrayObject $options extra options
     * @return array $entities
     */
    public function getAssignedAssociations($table, $entity, $options = [])
    {
        $entities = [];
        $associations = [];

        $tables = empty($options['tables']) ? [] : $options['tables'];

        if (!empty($tables)) {
            foreach ($table->associations() as $association) {
                if (in_array(Inflector::humanize($association->target()->table()), $tables)) {
                    array_push($associations, $association);
                }
            }
        } else {
            $associations = $table->associations();
        }

        foreach ($associations as $association) {
            $query = $association->target()->find('all', [
                'conditions' => [$association->primaryKey() => $entity->{$association->foreignKey()} ]
            ]);
            $result = $query->first();
            if ($result) {
                $entities[] = $result;
            }
        }

        return $entities;
    }

    /**
     * _getAttendees
     * @param Cake\ORM\Table $table passed
     * @param Cake\Entity $entity of the record
     * @param array $remindersTo listing related tables
     * @return array
     */
    protected function _getAttendees($table, $entity, $remindersTo)
    {
        $attendees = [];
        $assignedEntities = $this->getAssignedAssociations($table, $entity, ['tables' => $remindersTo]);

        if (!empty($assignedEntities)) {
            $attendees = array_map(function ($item) {
                if (isset($item['email'])) {
                    return $item['email'];
                }
            }, $assignedEntities);
        }

        return $attendees;
    }

    /**
     * _getEventAttendees
     * @param array $attendees pass
     * @return \Eluceo\iCal\Property\Event\Attendees
     */
    protected function _getEventAttendees($attendees)
    {
        $vAttendees = new \Eluceo\iCal\Property\Event\Attendees();
        foreach ($attendees as $email) {
            $vAttendees->add("MAILTO:$email", [
                'ROLE' => 'REQ-PARTICIPANT',
                'PARTSTAT' => 'NEEDS-ACTION',
                'RSVP' => 'TRUE',
            ]);
        }

        return $vAttendees;
    }

    /**
     * _getCalendarEvent
     * @param Cake\Entity $entity passed
     * @param array $options with extra settings
     * @return \Eluceo\iCal\Component\Event $vEvent
     */
    protected function _getCalendarEvent($entity, $options = [])
    {
        $vEvent = new \Eluceo\iCal\Component\Event();
        $vOrganizer = new \Eluceo\iCal\Property\Event\Organizer($options['organizer'], ['MAILTO' => $options['organizer']]);

        $vEvent->setOrganizer($vOrganizer);
        $vEvent->setUseTimezone(true);
        $vEvent->setSummary($entity->subject);

        $dates = $this->_getEventTime($entity, $options['dtz']);
        $vEvent->setDtStart($dates['start']);
        $vEvent->setDtEnd($dates['end']);


        return $vEvent;
    }

    /**
     * _getEventTime
     * Identify the start/end combination of the event.
     * We either use duration or any of the end fields
     * that might be used in the system.
     *
     * @param Cake\Entity $entity passed
     * @param DateTimeZone $dtz datetimezone object
     * @return array
     */
    protected function _getEventTime($entity, $dtz)
    {
        $start = $end = null;
        $due = null;
        $durationMinutes = 0;

        $start = new \DateTime($entity->start_date->format('Y-m-d H:i:s'), $dtz);

        // calculate the duration of an event
        if (!empty($entity->duration)) {
            $durationParts = date_parse($entity->duration);
            $durationMinutes = $durationParts['hour'] * 60 + $durationParts['minute'];

            $end = new Time($entity->start_date->format('Y-m-d H:i:s'));
            $end->modify("+ {$durationMinutes} minutes");
        } else {
            //if no duration is present use end_date
            foreach (['end_date', 'due_date'] as $endField) {
                if (!empty($entity->{$endField})) {
                    $due = $entity->{$endField};
                    break;
                }
            }

            if (!empty($due)) {
                $end = new \DateTime($due->format('Y-m-d H:i:s'), $dtz);
            }
        }

        return compact('start', 'end');
    }


    /**
     * _getTimezone
     * returning vTimezone object with defined rules
     * @param string $tz of DateTime timezone
     * @param \DateTimeZone $dtz passed
     * @return \Eluceo\iCal\Component\Timezone $vTimezone returned
     */
    protected function _getTimezone($tz, $dtz)
    {
        $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);

        $vTimezoneRuleDst = $this->_setDaylightSavingRule($dtz);
        $vTimezoneRuleStd = $this->_setStandardTimeRule($dtz);

        $vTimezone->addComponent($vTimezoneRuleDst);
        $vTimezone->addComponent($vTimezoneRuleStd);

        return $vTimezone;
    }

    /**
     * Setting DST switch rule
     * @param \DateTimeZone $dtz passed
     * @return \Eluceo\iCal\Component\TimezoneRule $vTimezoneRuleDst
     */
    protected function _setDaylightSavingRule($dtz)
    {
        $vTimezoneRuleDst = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
        $vTimezoneRuleDst->setTzName('CEST');
        $vTimezoneRuleDst->setDtStart(new \DateTime('1981-03-27 02:00:00', $dtz));
        $vTimezoneRuleDst->setTzOffsetFrom('+0100');
        $vTimezoneRuleDst->setTzOffsetTo('+0200');
        $dstRecurrenceRule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
        $dstRecurrenceRule->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY);
        $dstRecurrenceRule->setByMonth(3);
        $dstRecurrenceRule->setByDay('-1SU');

        $vTimezoneRuleDst->setRecurrenceRule($dstRecurrenceRule);

        return $vTimezoneRuleDst;
    }

    /**
     * _setStandardTimeRule method
     * Setting Standard time switching
     * @param \DateTimeZone $dtz passed from the app
     * @return \Eluceo\iCal\Component\TimezoneRule $vTimezoneRuleStd
     */
    protected function _setStandardTimeRule($dtz)
    {
        $vTimezoneRuleStd = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
        $vTimezoneRuleStd->setTzName('CET');
        $vTimezoneRuleStd->setDtStart(new \DateTime('1996-10-30 03:00:00', $dtz));
        $vTimezoneRuleStd->setTzOffsetFrom('+0200');
        $vTimezoneRuleStd->setTzOffsetTo('+0100');
        $stdRecurrenceRule = new \Eluceo\iCal\Property\Event\RecurrenceRule();
        $stdRecurrenceRule->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY);
        $stdRecurrenceRule->setByMonth(10);
        $stdRecurrenceRule->setByDay('-1SU');

        $vTimezoneRuleStd->setRecurrenceRule($stdRecurrenceRule);

        return $vTimezoneRuleStd;
    }
}
