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
namespace CsvMigrations\Event\Model;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\Table as CsvTable;
use CsvMigrations\Utility\DTZone;
use CsvMigrations\Utility\ICal\IcEmail;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

class ModelAfterSaveListener implements EventListenerInterface
{
    /**
     * Skip attendees in fields
     *
     * @todo This should move into the configuration
     * @var array
     */
    protected $skipAttendeesIn = ['created_by', 'modified_by'];

    /**
     * End date fields
     *
     * @var array
     */
    protected $endDateFields = ['end_date', 'due_date'];

    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::MODEL_AFTER_SAVE() => 'sendCalendarReminder'
        ];
    }

    /**
     * sendCalendarReminder method
     *
     * Send reminder notification email for the saved record.  The email
     * is only sent out, when:
     *
     * * There are some changes on the record, in fields except of those in the
     *   ignore list.
     * * The notifications are enabled and configured for the module, in which
     *   the record is being saved.
     * * The record is assigned to somebody who can be used as a target of the
     *   notification.
     *
     * @param \Cake\Event\Event $event from the afterSave
     * @param \Cake\Datasource\EntityInterface $entity from the afterSave
     * @return array Associative array with tried emails as keys and results as values
     */
    public function sendCalendarReminder(Event $event, EntityInterface $entity)
    {
        $result = [];

        try {
            // Get Table instance from the event
            $table = $event->subject();
            // Make sure it's a CsvTable
            $this->checkCsvTable($table);
            //get attendees Table for the event (example: Users)
            $remindersTo = $this->getRemindersToModules($table);
            // Figure out which field is a reminder one (example: start_date)
            $reminderField = $this->getReminderField($table);
            // Skip sending email if reminder field is empty
            if (empty($entity->$reminderField)) {
                throw new InvalidArgumentException("Reminder field has no value");
            }
            // Find attendee fields (example: assigned_to)
            $attendeesFields = $this->getAttendeesFields($table, ['tables' => $remindersTo]);
            // skip if none of the required fields was modified
            $requiredFields = array_merge((array)$reminderField, $attendeesFields);
            $this->checkRequiredModified($entity, $requiredFields);
            // get attendee emails
            $emails = $this->getAttendees($table, $entity, $attendeesFields);
            // get email subject and content
            $mailer = new IcEmail($table, $entity);
            $emailSubject = $mailer->getEmailSubject();
            $emailContent = $mailer->getEmailContent();
            // get common event options
            $eventOptions = $this->getEventOptions($table, $entity, $reminderField);
            // Set same attendees for all
            $eventOptions['attendees'] = $emails;
        } catch (Exception $e) {
            return $result;
        }

        foreach ($emails as $email) {
            // New event with current attendee as organizer (WTF?)
            $eventOptions['organizer'] = $email;

            try {
                $sent = $mailer->sendCalendarEmail($email, $emailSubject, $emailContent, $eventOptions);
            } catch (\Exception $e) {
                $sent = $e;
            }
            $result[$email] = $sent;
        }

        return $result;
    }

    /**
     * Check if given table is an instance of CsvTable
     *
     * Reminder functionality relies on a number of
     * methods which are defined in \CsvMigrations\Table.
     * So instead of checking all those methods one by
     * one, we simply check if the given table instance
     * inherits from the \CsvMigrations\Table.
     *
     * @throws \InvalidArgumentException If $table is not an instance of CsvTable
     * @param object $table Instance of a table class
     * @return void
     */
    protected function checkCsvTable($table)
    {
        if (!$table instanceof CsvTable) {
            throw new InvalidArgumentException("Table is not an intance of CsvTable");
        }
    }

    /**
     * Get a list of reminder modules
     *
     * Check if the given table has reminders configured,
     * and if so, return the list of modules to which
     * reminders should be sent (Users, Contacts, etc).
     *
     * @throws \InvalidArgumentException when no reminder modules found
     * @param \CsvTable\Table $table Table to check
     * @return array List of modules
     */
    protected function getRemindersToModules(CsvTable $table)
    {
        $result = $table->getConfig(ConfigurationTrait::$CONFIG_OPTION_ALLOW_REMINDERS);
        if (empty($result) || !is_array($result)) {
            throw new InvalidArgumentException("Failed to find reminder modules");
        }

        return $result;
    }

    /**
     * Get the fist reminder field of the given table
     *
     * NOTE: It is not very common to have more than one
     *       reminder field per table, but it is not
     *       impossible.  We need to figure out what
     *       should happen.  Possible scenarios:
     *
     *       * Forbid more than one field with validation.
     *       * Send reminder only to the first/non-empty.
     *       * Send separate reminder for each.
     *       * Have more flexible configuration rules.
     *
     * @throws \InvalidArgumentException when failed to find reminder field
     * @param \CsvMigrations\Table $table Table to use
     * @return string First reminder field name
     */
    protected function getReminderField(CsvTable $table)
    {
        $fields = $table->getReminderFields();
        if (empty($fields) || !is_array($fields)) {
            throw new InvalidArgumentException("Failed to find reminder fields");
        }

        // FIXME : What should happen when there is more than 1 reminder field on the table?
        foreach ($fields as $field) {
            if (!empty($field['name'])) {
                // Return the first field found
                return $field['name'];
            }
        }

        throw new InvalidArgumentException("Failed to find a reminder field with 'name' key");
    }

    /**
     * Retrieve attendees fields from current Table's associations.
     *
     * @throws \InvalidArgumentException when failed to find attendee fields
     * @param \CsvMigrations\Table $table Table instance
     * @param array $options Options
     * @return array
     */
    protected function getAttendeesFields(CsvTable $table, $options = [])
    {
        $result = [];

        $associations = $table->associations();
        if (!empty($options['tables'])) {
            $associations = [];
            foreach ($table->associations() as $association) {
                if (in_array(Inflector::humanize($association->target()->table()), $options['tables'])) {
                    $associations[] = $association;
                }
            }
        }

        foreach ($associations as $association) {
            $field = $association->foreignKey();
            if (in_array($field, $this->skipAttendeesIn)) {
                continue;
            }
            $result[] = $field;
        }

        if (empty($result)) {
            throw new InvalidArgumentException("Failed to find attendee fields");
        }

        return $result;
    }

    /**
     * Check that required entity fields are modified
     *
     * Entities are modified all the time and we don't always want
     * to send a notification about these changes.  Instead, here
     * we check that particular fields were modified (usually the
     * reminder datetime field or the attendees of the event).
     *
     * @throws \InvalidArgumentException when required fields are not modified
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param array $requiredFields Required fields list
     * @return void
     */
    protected function checkRequiredModified(EntityInterface $entity, array $requiredFields)
    {
        if (empty($requiredFields)) {
            throw new InvalidArgumentException("Required fields not specified");
        }

        // check if any of the required fields was modified and set modified flag to true
        $modified = false;
        foreach ($requiredFields as $field) {
            if ($entity->dirty($field)) {
                $modified = true;
                break;
            }
        }

        if (!$modified) {
            throw new InvalidArgumentException("None of the required fields were modified");
        }
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
     * getAttendees
     *
     * Get the list of attendee emails
     *
     * @throws \InvalidArgumentException when no attendees found
     * @param \CsvMigrations\Table $table passed
     * @param \Cake\Datasource\EntityInterface $entity of the record
     * @param array $fields Attendees fields
     * @return array
     */
    protected function getAttendees(CsvTable $table, EntityInterface $entity, array $fields)
    {
        $assignedEntities = $this->getAssignedAssociations($table, $entity, $fields);

        if (empty($assignedEntities)) {
            throw new InvalidArgumentException("Failed to find attendee entities");
        }

        $result = array_map(function ($item) {
            if (isset($item['email'])) {
                return $item['email'];
            }
        }, $assignedEntities);
        // Filter out empty items
        $result = array_filter($result);
        // Filter out duplicates
        $result = array_unique($result, SORT_STRING);

        if (empty($result)) {
            throw new InvalidArgumentException("Failed to find attendee emails");
        }

        return $result;
    }

    /**
     * Get options for the new event
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param string $startField Entity field to use for event start time
     * @return array
     */
    protected function getEventOptions(CsvTable $table, EntityInterface $entity, $startField)
    {
        // Event start and end times
        $eventTimes = $this->getEventTime($entity, $startField);

        $mailer = new IcEmail($table, $entity);

        $result = [
            'id' => $entity->id,
            'sequence' => $entity->isNew() ? 0 : time(),
            'summary' => $mailer->getEventSubject(),
            'description' => $mailer->getEventContent(),
            'location' => $entity->location,
            'startTime' => $eventTimes['start'],
            'endTime' => $eventTimes['end'],
        ];

        return $result;
    }

    /**
     * getEventTime
     *
     * Identify the start/end combination of the event.
     * We either use duration or any of the end fields
     * that might be used in the system.
     *
     * @param \Cake\Datasource\EntityInterface $entity Saved entity instance
     * @param string $startField Entity field to use for event start time
     * @return array Associative array of DateTimeZone instances for start and end
     */
    protected function getEventTime(EntityInterface $entity, $startField)
    {
        // Application timezone
        $dtz = new DateTimeZone(DTZone::getAppTimeZone());

        // We check that the value is always there in sendCalendarReminder()
        $start = DTZone::toDateTime($entity->$startField, $dtz);

        // Default end time is 1 hour from start
        $end = $start;
        $end->modify("+ 60 minutes");

        // If no duration given, check end fields and use value if found
        if (empty($entity->duration)) {
            foreach ($this->endDateFields as $endField) {
                if (!empty($entity->{$endField})) {
                    $end = DTZone::toDateTime($entity->{$endField}, $dtz);
                    break;
                }
            }
        }

        // If duration is given, then calculate the end date
        if (!empty($entity->duration)) {
            $durationParts = date_parse($entity->duration);
            $durationMinutes = $durationParts['hour'] * 60 + $durationParts['minute'];

            $end = $start;
            $end->modify("+ {$durationMinutes} minutes");
        }

        // Adjust to UTC in case custom timezone is used for an app.
        $start = DTZone::offsetToUtc($start);
        $end = DTZone::offsetToUtc($end);

        $result = [
            'start' => $start,
            'end' => $end,
        ];

        return $result;
    }
}
