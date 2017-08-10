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
use CsvMigrations\Utility\ICal\IcCalendar;
use CsvMigrations\Utility\ICal\IcEvent;
use DateTime;
use DateTimeZone;
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
     * @return array|bool $sent on whether the email was sent
     */
    public function sendCalendarReminder(Event $event, EntityInterface $entity)
    {
        $sent = false;
        $currentUser = null;

        $table = $event->subject();

        //get attendees Table for the event
        $remindersTo = null;
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $remindersTo = $table->getConfig(ConfigurationTrait::$CONFIG_OPTION_ALLOW_REMINDERS);
        }

        // skip if attendees Table is not defined
        if (empty($remindersTo)) {
            return $sent;
        }

        // Figure out which field is a reminder one
        $reminderField = null;
        if (method_exists($table, 'getReminderFields') && is_callable([$table, 'getReminderFields'])) {
            $reminderField = $table->getReminderFields();
        }
        if (empty($reminderField) || !is_array($reminderField)) {
            return $sent;
        }

        // FIXME : What happens when there is more than 1 reminder field on the table?
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
        $emailContent = Inflector::singularize($table->alias()) . ' ' . $emailSubjectValue . " information was ";
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
            $emailContent .= " by " . $currentUser['name'];
        }
        // append changelog if entity is not new
        if (!$entity->isNew()) {
            $emailContent .= ":\n\n" . $this->_getChangelog($entity);
        }

        // append link
        $entityUrl = Router::url(['prefix' => false, 'controller' => $table->table(), 'action' => 'view', $entity->id], true);
        $emailContent .= "\n\nSee more: " . $entityUrl;

        // Application timezone
        $dtz = new DateTimeZone($this->getAppTimeZone());

        $eventTimes = $this->_getEventTime($entity, $reminderField, $dtz);

        $eventDescription = '';
        if ($entity->description) {
            $eventDescription .= $entity->description . "\n\n";
        }
        $eventDescription .= $entityUrl;

        $eventOptions = [
            'id' => $entity->id,
            'sequence' => $entity->isNew() ? 0 : time(),
            'summary' => $emailSubject,
            'description' => $eventDescription,
            'location' => $entity->location,
            'startTime' => $eventTimes['start'],
            'endTime' => $eventTimes['end'],
            'attendees' => $emails,
        ];

        foreach ($emails as $email) {
            // New event with current attendee as organizer (WTF?)
            $eventOptions['organizer'] = $email;
            $icEvent = new IcEvent($eventOptions);
            $icEvent = $icEvent->getEvent();

            // New calendar
            $icCalendar = new IcCalendar();
            $icCalendar->addEvent($icEvent);
            $icCalendar = $icCalendar->getCalendar();

            // FIXME: WTF happened to new lines???
            $headers = "Content-Type: text/calendar; charset=utf-8";
            $headers .= 'Content-Disposition: attachment; filename="event.ics"';

            try {
                $emailer = new Email('default');
                $emailer->to($email)
                    ->setHeaders([$headers])
                    ->subject($emailSubject)
                    ->attachments(['event.ics' => [
                        'contentDisposition' => true,
                        'mimetype' => 'text/calendar',
                        'data' => $icCalendar->render()
                    ]]);
                $sent = $emailer->send($emailContent);
            } catch (\Exception $e) {
                // TODO : Add logging here
            }
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

        $associations = $table->associations();
        if (!empty($options['tables'])) {
            $associations = [];
            foreach ($table->associations() as $association) {
                if (!in_array(Inflector::humanize($association->target()->table()), $options['tables'])) {
                    $associations[] = $association;
                }
            }
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
            if ($entity->dirty($field)) {
                $result = true;
                break;
            }
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
     *
     * @param Cake\ORM\Table $table passed
     * @param Cake\Entity $entity of the record
     * @param array $fields Attendees fields
     * @return array
     */
    protected function _getAttendees($table, $entity, $fields)
    {
        $result = [];
        $assignedEntities = $this->getAssignedAssociations($table, $entity, $fields);

        if (empty($assignedEntities)) {
            return $result;
        }

        $result = array_map(function ($item) {
            if (isset($item['email'])) {
                return $item['email'];
            }
        }, $assignedEntities);

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
     * _getEventTime
     *
     * Identify the start/end combination of the event.
     * We either use duration or any of the end fields
     * that might be used in the system.
     *
     * @param \Cake\Datasource\EntityInterface $entity Saved entity instance
     * @param string $startField Entity field to use for event start time
     * @param \DateTimeZone $dtz DateTimeZone instance to use for times
     * @return array Associative array of DateTimeZone instances for start and end
     */
    protected function _getEventTime(EntityInterface $entity, $startField, DateTimeZone $dtz)
    {
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
        $result = Time::now()->format('e');
        if (empty($result)) {
            $result = 'UTC';
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

            return new DateTime($value, $timezone);
        }

        if ($value instanceof Time) {
            $value = $value->format($format);

            return new DateTime($value, $timezone);
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
