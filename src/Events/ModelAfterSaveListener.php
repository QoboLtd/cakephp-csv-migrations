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

        //get attendees for the event
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $config = $table->getConfig();
            $remindersTo = $table->getTableAllowRemindersField();
        }

        // Figure out which field is a reminder one
        $reminderField = $table->getReminderFields();
        if (empty($reminderField) || !is_array($reminderField)) {
            return $sent;
        }
        $reminderField = $reminderField[0];
        if (!is_array($reminderField) || empty($reminderField['name'])) {
            return $sent;
        }
        $reminderField = $reminderField['name'];
        // Skip sending email if reminder field is empty
        if (empty($entity->$reminderField)) {
            return $sent;
        }

        if (empty($remindersTo)) {
            return $sent;
        }

        /*
         * Figure out the subject of the email
         *
         * This should happen AFTER the `$table->getConfig()` call,  just
         * in case the display field of the table is changed from the
         * configuration.
         *
         * Use singular of the table name and the value of the entity's display field.
         * For example: "Call: Payment remind" or "Lead: Qobo Ltd".
         */
        $eventSubject = $entity->{ $table->displayField() } ?: 'reminder';
        $emailSubject = Inflector::singularize($table->alias()) . ": " . $eventSubject;
        // If the record is being updated, prefix the above subject with "(Updated) ".
        if (!$entity->isNew()) {
            $emailSubject = '(Updated) ' . $emailSubject;
        }


        $emails = $this->_getAttendees($table, $entity, $remindersTo);

        if (empty($emails)) {
            return $sent;
        }

        foreach ($emails as $email) {
            $vCalendar = new \Eluceo\iCal\Component\Calendar('//EN//');

            $vAttendees = $this->_getEventAttendees($emails);

            $vEvent = $this->_getCalendarEvent($entity, [
                'dtz' => $dtz,
                'organizer' => $email,
                'subject' => $eventSubject,
                'attendees' => $vAttendees,
                'field' => $reminderField,
                'timezone' => $timezone,
            ]);

            $vEvent->setAttendees($vAttendees);
            $vCalendar->addComponent($vEvent);

            $headers = "Content-Type: text/calendar; charset=utf-8";
            $headers .= 'Content-Disposition: attachment; filename="event.ics"';
            $emailer = new Email('default');

            $emailer->to($email)
                ->setHeaders([$headers])
                ->subject($emailSubject)
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
        $vEvent->setSummary($options['subject']);

        $dates = $this->_getEventTime($entity, $options);
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
     * @todo Check that entity fields are objects, before calling format() on them
     * @param Cake\Entity $entity passed
     * @param array $options Options
     * @return array
     */
    protected function _getEventTime($entity, $options)
    {
        $start = $end = null;
        $due = null;
        $durationMinutes = 0;
        $dtz = $options['dtz'];
        $field = $options['field'];

        $start = new \DateTime($entity->$field->format('Y-m-d H:i:s'), $dtz);

        // calculate the duration of an event
        if (!empty($entity->duration)) {
            $durationParts = date_parse($entity->duration);
            $durationMinutes = $durationParts['hour'] * 60 + $durationParts['minute'];

            $end = new \DateTime($entity->$field->format('Y-m-d H:i:s'));
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

        // If all else fails, assume 1 hour duration
        if (empty($end)) {
            $end = new \DateTime($entity->$field->format('Y-m-d H:i:s'));
            $end->modify("+ 60 minutes");
        }

        // falling back to UTC in case custom timezone is used for an app.
        if (!empty($options['timezone']) && $options['timezone'] !== 'UTC') {
            $epoch = time();
            $tz = new \DateTimeZone($options['timezone']);
            $transitions = $tz->getTransitions($epoch, $epoch);

            $offset = $transitions[0]['offset'];

            if (!empty($start)) {
                $start->modify("-$offset seconds");
            }

            if (!empty($end)) {
                $end->modify("-$offset seconds");
            }
        }

        return compact('start', 'end');
    }
}
