<?php
namespace CsvMigrations\Event;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Table as CsvTable;
use CsvMigrations\Utility\ICal\IcCalendar;
use CsvMigrations\Utility\ICal\IcEvent;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

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
    protected $ignoredFields = ['created', 'modified'];

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
            'CsvMigrations.Model.afterSave' => 'sendCalendarReminder'
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
     * @param Cake\Event $event from the afterSave
     * @param Cake\Datasource\EntityInterface $entity from the afterSave
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
            $emailSubject = $this->getEmailSubject($table, $entity);
            $emailContent = $this->getEmailContent($table, $entity);
            // get common event options
            $eventOptions = $this->getEventOptions($table, $entity, $reminderField);
            // Set same attendees for all
            $eventOptions['attendees'] = $emails;
        } catch (Exception $e) {
            debug($e->getMessage());

            return $result;
        }

        foreach ($emails as $email) {
            // New event with current attendee as organizer (WTF?)
            $eventOptions['organizer'] = $email;

            try {
                $sent = $this->sendCalendarEmail($email, $emailSubject, $emailContent, $eventOptions);
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
            $result[] = $association->foreignKey();
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
     * @param \Cake\ORM\EntityInterface $entity of the record
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

        if (empty($result)) {
            throw new InvalidArgumentException("Failed to find attendee emails");
        }

        return $result;
    }

    /**
     * Get email subject
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @return string
     */
    protected function getEmailSubject(CsvTable $table, EntityInterface $entity)
    {
        $module = Inflector::singularize($table->alias());
        $displayValue = $this->getDisplayValue($table, $entity);
        $result = $module . ": " . $displayValue;

        if (!$entity->isNew()) {
            $result = "(Updated) " . $result;
        }

        return $result;
    }

    /**
     * Get email content
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @return string
     */
    protected function getEmailContent(CsvTable $table, EntityInterface $entity)
    {
        $result = '';

        $module = Inflector::singularize($table->alias());
        $displayValue = $this->getDisplayValue($table, $entity);
        $user = $this->getUserString($table);
        $action = $entity->isNew() ? 'created' : 'updated';

        // Example: Lead Foobar created by System
        $result = sprintf("%s %s %s by %s", $module, $displayValue, $action, $user);

        $changeLog = $this->getChangelog($entity);
        if (!empty($changeLog)) {
            $result .= "\n\n";
            $result .= $changeLog;
            $result .= "\n";
        }

        $result .= "\n\n";
        $result .= "See more: ";
        $result .= $this->getEntityUrl($table, $entity);

        return $result;
    }

    /**
     * Get event subject/summary
     *
     * For now this is just an alias for getEmailSubject().
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @return string
     */
    protected function getEventSubject(CsvTable $table, EntityInterface $entity)
    {
        return $this->getEmailSubject($table, $entity);
    }

    /**
     * Get event content/description
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @return string
     */
    protected function getEventContent(CsvTable $table, EntityInterface $entity)
    {
        $result = '';

        $entityFields = [
            'description',
            'agenda',
            'comments',
            'comment',
            'notes',
        ];

        foreach ($entityFields as $field) {
            if (!empty($entity->$field)) {
                $result = $entity->$field;
                break;
            }
        }

        $result .= "\n\n";
        $result .= "See more: ";
        $result .= $this->getEntityUrl($table, $entity);

        return $result;
    }

    /**
     * Get plain value of the entity display field
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @return string
     */
    protected function getDisplayValue(CsvTable $table, EntityInterface $entity)
    {
        try {
            $displayField = $table->displayField();
            $displayValue = $entity->{$displayField};

            $fhf = new FieldHandlerFactory();
            $result = $fhf->renderValue($table, $displayField, $displayValue, [ 'renderAs' => 'plain']);
            if (empty($result) || !is_string($result)) {
                throw new InvalidArgumentException("Failed to get entity display value");
            }
        } catch (Exception $e) {
            $result = 'reminder';
        }

        return $result;
    }

    /**
     * Get full URL to the entity view
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @return string
     */
    protected function getEntityUrl(CsvTable $table, EntityInterface $entity)
    {
        $result = Router::url(
            [
                'prefix' => false,
                'controller' => $table->table(),
                'action' => 'view',
                $entity->id
            ],
            true
        );

        return $result;
    }

    /**
     * Get plain value of the current user
     *
     * @param \CsvMigrations\Table $table Table instance
     * @return string
     */
    protected function getUserString(CsvTable $table)
    {
        $result = 'System';

        $userFields = [
            'name',
            'username',
            'email',
        ];

        $currentUser = $table->getCurrentUser();
        if (empty($currentUser) || !is_array($currentUser)) {
            return $result;
        }

        foreach ($userFields as $field) {
            if (!empty($currentUser[$field])) {
                return $currentUser[$field];
            }
        }

        return $result;
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
    protected function getChangelog(EntityInterface $entity)
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
        foreach ($this->ignoredFields as $field) {
            if (array_key_exists($field, $fields)) {
                unset($fields[$field]);
            }
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
     * Get options for the new event
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\ORM\EntityInterface $entity Entity instance
     * @param string $startField Entity field to use for event start time
     * @return array
     */
    protected function getEventOptions(CsvTable $table, EntityInterface $entity, $startField)
    {
        // Event start and end times
        $eventTimes = $this->getEventTime($entity, $startField);

        $result = [
            'id' => $entity->id,
            'sequence' => $entity->isNew() ? 0 : time(),
            'summary' => $this->getEventSubject($table, $entity),
            'description' => $this->getEventContent($table, $entity),
            'location' => $entity->location,
            'startTime' => $eventTimes['start'],
            'endTime' => $eventTimes['end'],
        ];

        return $result;
    }

    /**
     * Get iCal calendar with given event
     *
     * @param array $eventOptions Options for event creation
     * @return object Whatever IcCalendar::getCalendar() returns
     */
    protected function getEventCalendar(array $eventOptions)
    {
        // New iCal event
        $event = new IcEvent($eventOptions);
        $event = $event->getEvent();

        // New iCal calendar
        $calendar = new IcCalendar();
        $calendar->addEvent($event);
        $calendar = $calendar->getCalendar();

        return $calendar;
    }

    /**
     * Send email with calendar attachment
     *
     * @param string $to Destination email address
     * @param string $subject Email subject
     * @param string $content Email message content
     * @param array $eventOptions Event options for calendar attachment
     * @param string $config Email config to use ('default' if omitted)
     * @return array Result of Email::send()
     */
    protected function sendCalendarEmail($to, $subject, $content, array $eventOptions, $config = 'default')
    {
        // Get iCal calendar
        $calendar = $this->getEventCalendar($eventOptions);

        // FIXME: WTF happened to new lines???
        $headers = "Content-Type: text/calendar; charset=utf-8";
        $headers .= 'Content-Disposition: attachment; filename="event.ics"';

        $emailer = new Email($config);
        $emailer->to($to)
            ->setHeaders([$headers])
            ->subject($subject)
            ->attachments(['event.ics' => [
                'contentDisposition' => true,
                'mimetype' => 'text/calendar',
                'data' => $calendar->render()
            ]]);
        $result = $emailer->send($content);

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
        $dtz = new DateTimeZone($this->getAppTimeZone());

        // We check that the value is always there in sendCalendarReminder()
        $start = $this->toDateTime($entity->$startField, $dtz);

        // Default end time is 1 hour from start
        $end = $start;
        $end->modify("+ 60 minutes");

        // If no duration given, check end fields and use value if found
        if (empty($entity->duration)) {
            foreach ($this->endDateFields as $endField) {
                if (!empty($entity->{$endField})) {
                    $end = $this->toDateTime($entity->{$endField}, $dtz);
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
        $start = $this->offsetToUtc($start);
        $end = $this->offsetToUtc($end);

        $result = [
            'start' => $start,
            'end' => $end,
        ];

        return $result;
    }

    /**
     * Get application timezone
     *
     * If application timezone is not configured,
     * fallback on UTC.
     *
     * @todo Move to Qobo/Utils
     * @return string Timezone string, like UTC
     */
    protected function getAppTimeZone()
    {
        $result = 'UTC';
        $appTimezone = Time::now()->format('e');
        if (!empty($appTimezone)) {
            $result = $appTimezone;
        }

        return $result;
    }

    /**
     * Convert a given value to DateTime instance
     *
     * @throws \InvalidArgumentException when cannot convert to \DateTime
     * @param mixed $value Value to convert (string, Time, DateTime, etc)
     * @param \DateTimeZone $dtz DateTimeZone instance
     * @return \DateTime
     */
    protected function toDateTime($value, DateTimeZone $dtz)
    {
        // TODO : Figure out where to move. Can vary for different source objects
        $format = 'Y-m-d H:i:s';

        if (is_string($value)) {
            $value = strtotime($value);
            $value = date($format, $value);

            return new DateTime($value, $dtz);
        }

        if ($value instanceof Time) {
            $value = $value->format($format);

            return new DateTime($value, $dtz);
        }

        if ($value instanceof DateTime) {
            return $value;
        }

        throw new InvalidArgumentException("Type [" . gettype($value) . "] is not supported for date/time");
    }

    /**
     * Offset DateTime value to UTC
     *
     * NOTE: This is a temporary work around until we fix our handling of
     *       the application timezones.  Database values should always be
     *       stored in UTC no matter what.  Otherwise, you will be riding
     *       a bike which is on fire, while you are on fire, and everything
     *       around you is on fire.  See Redmine ticket #4336 for details.
     *
     * @param \DateTime $value DateTime value to offset
     * @return \DateTime
     */
    protected function offsetToUtc(DateTime $value)
    {
        $result = $value;

        $dtz = $value->getTimezone();
        if ($dtz->getName() === 'UTC') {
            return $result;
        }

        $epoch = time();
        $transitions = $dtz->getTransitions($epoch, $epoch);

        $offset = $transitions[0]['offset'];
        $result = $result->modify("-$offset seconds");

        return $result;
    }
}
