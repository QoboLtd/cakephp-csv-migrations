<?php
namespace CsvMigrations\Events;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

class ModelAfterSaveListener implements EventListenerInterface
{
    /**
     * Changelog template
     */
    const CHANGELOG = '* %s: changed from "%s" to "%s".' . "\n";

    /**
     * Ingored modified fields
     *
     * @var array
     */
    protected $_ignoredFields = ['created', 'modified'];

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
        $currentUser = null;

        //get applications's timezone
        $timezone = Time::now()->format('e');
        $dtz = new \DateTimeZone($timezone);

        $table = $event->subject();

        //get attendees Table for the event
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $config = $table->getConfig();
            $remindersTo = $table->getTableAllowRemindersField();
        }

        // skip if attendees Table is not defined
        if (empty($remindersTo)) {
            return $sent;
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

        $attendeesFields = $this->_getAttendeesFields($table, ['tables' => $remindersTo]);
        // skip if no attendees fields found
        if (empty($attendeesFields)) {
            return $sent;
        }

        // skip if none of the required fields was modified
        $requiredFields = array_merge((array)$reminderField, $attendeesFields);
        if (!$this->_requiredFieldsModified($entity, $requiredFields)) {
            return $sent;
        }

        /*
         * Figure out the subject of the email
         *
         * This should happen AFTER the `$table->getConfig()` call,
         * in case the display field of the table is changed from the
         * configuration.
         *
         * Use singular of the table name and the value of the entity's display field.
         * For example: "Call: Payment remind" or "Lead: Qobo Ltd".
         */
        $fhf = new FieldHandlerFactory();

        $emailSubjectValue = $fhf->renderValue($table, $table->displayField(), $entity->{$table->displayField()}, [ 'renderAs' => 'plain']);

        $eventSubject = $emailSubjectValue ?: 'reminder';
        $emailSubject = Inflector::singularize($table->alias()) . ": " . $eventSubject;
        $emailContent = Inflector::singularize($table->alias()) . " information was ";
        // If the record is being updated, prefix the above subject with "(Updated) ".
        if (!$entity->isNew()) {
            $emailSubject = '(Updated) ' . $emailSubject;
            $emailContent .= "updated";
        } else {
            $emailContent .= "created";
        }

        $emails = $this->_getAttendees($table, $entity, $attendeesFields);

        if (empty($emails)) {
            return $sent;
        }


        if (method_exists($table, 'getCurrentUser') && is_callable([$table, 'getCurrentUser'])) {
            $currentUser = $table->getCurrentUser();
            $emailContent .= " by " . $currentUser['email'];
        }
        // append changelog if entity is not new
        if (!$entity->isNew()) {
            $emailContent .= "\n\n" . $this->_getChangelog($entity);
        }

        foreach ($emails as $email) {
            $vCalendar = new \Eluceo\iCal\Component\Calendar('-//Calendar Events//EN//');
            $vCalendar->setCalendarScale('GREGORIAN');

            $vAttendees = $this->_getEventAttendees($emails);

            $vEvent = $this->_getCalendarEvent($entity, [
                'dtz' => $dtz,
                'organizer' => $email,
                'subject' => $emailSubject,
                'attendees' => $vAttendees,
                'field' => $reminderField,
                'timezone' => $timezone,
            ]);

            if (!$entity->isNew()) {
                $vEvent->setSequence(time());
            } else {
                $vEvent->setSequence(0);
            }

            $vEvent->setUniqueId($entity->id);
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
            $sent = $emailer->send($emailContent);
        }

        return $sent;
    }

    /**
     * Retrieve attendees fields from current Table's associations.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param array $options Options
     * @return array
     */
    protected function _getAttendeesFields(Table $table, $options = [])
    {
        $result = [];

        $associations = [];
        if (!empty($options['tables'])) {
            foreach ($table->associations() as $association) {
                if (!in_array(Inflector::humanize($association->target()->table()), $options['tables'])) {
                    continue;
                }

                $associations[] = $association;
            }
        } else {
            $associations = $table->associations();
        }

        if (empty($associations)) {
            return $result;
        }

        foreach ($associations as $association) {
            $result[] = $association->foreignKey();
        }

        return $result;
    }

    /**
     * Checks if required fields have been modified. Returns true
     * if any of the fields has been modified, otherwise false.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param array $requiredFields Required fields list
     * @return bool
     */
    protected function _requiredFieldsModified(EntityInterface $entity, array $requiredFields)
    {
        $result = false;

        if (empty($requiredFields)) {
            return $result;
        }

        // check if any of the required fields was modified and set modified flag to true
        foreach ($requiredFields as $field) {
            if (!$entity->dirty($field)) {
                continue;
            }
            $result = true;
            break;
        }

        return $result;
    }

    /**
     * getAssignedAssociations
     *
     * gets all Entities associated with the record
     *
     * @param \Cake\ORM\Table $table of the record
     * @param \Cake\Datasource\EntityInterface $entity extra options
     * @param array $fields Attendees fields
     * @return array $entities
     */
    public function getAssignedAssociations(Table $table, EntityInterface $entity, array $fields)
    {
        $entities = [];

        foreach ($table->associations() as $association) {
            if (!in_array($association->foreignKey(), $fields)) {
                continue;
            }
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
     * @param array $fields Attendees fields
     * @return array
     */
    protected function _getAttendees($table, $entity, $fields)
    {
        $attendees = [];
        $assignedEntities = $this->getAssignedAssociations($table, $entity, $fields);

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
     * Creates changelog report in string format.
     *
     * Example:
     *
     * Subject: changed from 'Foo' to 'Bar'.
     * Content: changed from 'Hello world' to 'Hi there'.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @return string
     */
    protected function _getChangelog(EntityInterface $entity)
    {
        $result = '';

        // plain changelog if entity is new
        if ($entity->isNew()) {
            return $result;
        }

        // get entity's modified fields
        $fields = $entity->extractOriginalChanged($entity->visibleProperties());

        if (empty($fields)) {
            return $result;
        }

        // remove ignored fields
        foreach ($this->_ignoredFields as $field) {
            if (!array_key_exists($field, $fields)) {
                continue;
            }
            unset($fields[$field]);
        }

        if (empty($fields)) {
            return $result;
        }

        foreach ($fields as $k => $v) {
            $result .= sprintf(static::CHANGELOG, Inflector::humanize($k), $v, $entity->{$k});
        }

        return $result;
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

        if ($entity->description) {
            $vEvent->setDescription($entity->description);
        }

        $dates = $this->_getEventTime($entity, $options);
        $vEvent->setDtStart($dates['start']);
        $vEvent->setDtEnd($dates['end']);


        if ($entity->location) {
            $vEvent->setLocation($entity->location, "Location:");
        }

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

        if ($entity->$field instanceof Time) {
            $start = new \DateTime($entity->$field->format('Y-m-d H:i:s'), $dtz);
        } else {
            $start = new \DateTime(date('Y-m-d H:i:s', strtotime($entity->$field)), $dtz);
        }

        // calculate the duration of an event
        if (!empty($entity->duration)) {
            $durationParts = date_parse($entity->duration);
            $durationMinutes = $durationParts['hour'] * 60 + $durationParts['minute'];

            $end = new \DateTime($start->format('Y-m-d H:i:s'));
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
            $end = new \DateTime($start->format('Y-m-d H:i:s'));
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
