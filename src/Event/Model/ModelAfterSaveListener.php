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

use BadMethodCallException;
use Cake\Database\Type;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\LogTrait;
use Cake\Network\Exception\SocketException;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CsvMigrations\Event\EventName;
use CsvMigrations\Exception\UnsupportedForeignKeyException;
use CsvMigrations\Exception\UnsupportedPrimaryKeyException;
use CsvMigrations\Table as CsvTable;
use CsvMigrations\Utility\DTZone;
use CsvMigrations\Utility\ICal\IcEmail;
use DateTimeZone;
use InvalidArgumentException;
use Psr\Log\LogLevel;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Webmozart\Assert\Assert;

class ModelAfterSaveListener implements EventListenerInterface
{
    use LogTrait;

    /**
     * Skip attendees in fields
     *
     * @todo This should move into the configuration
     * @var string[]
     */
    protected $skipAttendeesIn = ['created_by', 'modified_by'];

    /**
     * End date fields
     *
     * @var string[]
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
     * @return mixed[] Associative array with tried emails as keys and results as values
     */
    public function sendCalendarReminder(Event $event, EntityInterface $entity): array
    {
        /**
         * Get Table instance from the event.
         *
         * @var \Cake\Datasource\RepositoryInterface&\Cake\ORM\Table
         */
        $table = $event->getSubject();

        // make sure it is a \CsvMigrations\Event\Model\CsvTable
        if (! $table instanceof CsvTable) {
            $this->log('Table is not an intance of \CsvMigrations\Event\Model\CsvTable', LogLevel::ERROR);

            return [];
        }

        // get attendees Table for the event (example: Users)
        $remindersTo = $this->getRemindersToModules($table);
        if (empty($remindersTo)) {
            return [];
        }

        // figure out which field is a reminder one (example: start_date)
        $reminderField = $this->getReminderField($table);
        if ('' === $reminderField) {
            return [];
        }

        // skip sending email if reminder field is empty
        if (empty($entity->get($reminderField))) {
            return [];
        }

        // find attendee fields (example: assigned_to)
        $attendeesFields = $this->getAttendeesFields($table, $remindersTo);
        if (empty($attendeesFields)) {
            $this->log('Failed to find attendee fields', LogLevel::NOTICE);

            return [];
        }

        $requiredFields = array_merge((array)$reminderField, $attendeesFields);

        // skip if none of the required fields was modified
        if (! $this->isRequiredModified($entity, $requiredFields, $table)) {
            return [];
        }

        // get attendee emails
        $emails = $this->getAttendees($table, $entity, $attendeesFields);
        if (empty($emails)) {
            $this->log('Failed to find attendee emails', LogLevel::NOTICE);

            return [];
        }

        // get email subject and content
        $mailer = new IcEmail($table, $entity);
        $emailSubject = $mailer->getEmailSubject();
        $emailContent = $mailer->getEmailContent();

        // get common event options
        $eventOptions = $this->getEventOptions($table, $entity, $reminderField);
        // Set same attendees for all
        $eventOptions['attendees'] = $emails;

        $result = [];
        foreach ($emails as $email) {
            // New event with current attendee as organizer (WTF?)
            $eventOptions['organizer'] = $email;

            try {
                $sent = $mailer->sendCalendarEmail($email, $emailSubject, $emailContent, $eventOptions);
            } catch (BadMethodCallException $e) {
                $sent = $e;
                $this->log(sprintf('Failed to send email: %s', $e->getMessage()), LogLevel::ERROR);
            } catch (SocketException $e) {
                $sent = $e;
                $this->log(sprintf('Failed to send email: %s', $e->getMessage()), LogLevel::ERROR);
            }

            $result[$email] = $sent;
        }

        return $result;
    }

    /**
     * Get a list of reminder modules
     *
     * Check if the given table has reminders configured,
     * and if so, return the list of modules to which
     * reminders should be sent (Users, Contacts, etc).
     *
     * @param \Cake\Datasource\RepositoryInterface $table Table to check
     * @return string[] List of modules
     */
    protected function getRemindersToModules(RepositoryInterface $table): array
    {
        $config = (new ModuleConfig(ConfigType::MODULE(), $table->getRegistryAlias()))->parseToArray();
        if (empty($config['table']['allow_reminders'])) {
            return [];
        }

        return $config['table']['allow_reminders'];
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
     * @param \Cake\Datasource\RepositoryInterface $table Table to use
     * @return string First reminder field name
     */
    protected function getReminderField(RepositoryInterface $table): string
    {
        $config = (new ModuleConfig(ConfigType::MIGRATION(), $table->getRegistryAlias()))->parse();

        $fields = array_filter((array)$config, function ($field) {
            if ('reminder' === $field->type) {
                return $field;
            }
        });

        if (empty($fields)) {
            return '';
        }

        // FIXME: What should happen when there is more than 1 reminder field on the table?
        reset($fields);

        return current($fields)->name;
    }

    /**
     * Retrieve attendees fields from current Table's associations.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string[] $modules Reminder to modules
     * @return string[]
     */
    protected function getAttendeesFields(Table $table, array $modules): array
    {
        $associations = [];
        foreach ($table->associations() as $association) {
            if (in_array(Inflector::humanize($association->getTarget()->getTable()), $modules)) {
                $associations[] = $association;
            }
        }

        if (empty($associations)) {
            return [];
        }

        $result = [];
        foreach ($associations as $association) {
            $foreignKey = $association->getForeignKey();
            if (!is_string($foreignKey)) {
                throw new UnsupportedForeignKeyException();
            }
            if (in_array($foreignKey, $this->skipAttendeesIn)) {
                continue;
            }
            $result[] = $foreignKey;
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
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param string[] $requiredFields Required fields list
     * @param \Cake\ORM\Table $table Table instance
     * @return bool
     */
    protected function isRequiredModified(EntityInterface $entity, array $requiredFields, Table $table): bool
    {
        Assert::isInstanceOf($entity, \Cake\ORM\Entity::class);

        foreach ($requiredFields as $field) {
            if (! $entity->isDirty($field)) {
                continue;
            }

            $columnType = $table->getSchema()->getColumnType($field);
            if (null === $columnType) {
                continue;
            }

            $toPHP = Type::build($columnType)->toPHP($entity->get($field), $table->getConnection()->getDriver());

            // loose comparison on purpose
            if ($toPHP == $entity->getOriginal($field)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * getAttendees
     *
     * Get the list of attendee emails
     *
     * @throws \InvalidArgumentException when no attendees found
     * @param \CsvMigrations\Table $table passed
     * @param \Cake\Datasource\EntityInterface $entity of the record
     * @param string[] $fields Attendees fields
     * @return string[]
     */
    protected function getAttendees(CsvTable $table, EntityInterface $entity, array $fields): array
    {
        try {
            $assignedEntities = $this->getAssignedAssociations($table, $entity, $fields);
        } catch (InvalidArgumentException $e) {
            $assignedEntities = [];
        }

        if (empty($assignedEntities)) {
            $this->log('Failed to find attendee entities', LogLevel::ERROR);

            return [];
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

        return $result;
    }

    /**
     * getAssignedAssociations
     *
     * gets all Entities associated with the record
     *
     * @param \Cake\ORM\Table $table of the record
     * @param \Cake\Datasource\EntityInterface $entity extra options
     * @param string[] $fields Attendees fields
     * @return \Cake\Datasource\EntityInterface[] $entities
     */
    public function getAssignedAssociations(Table $table, EntityInterface $entity, array $fields): array
    {
        $result = [];
        foreach ($table->associations() as $association) {
            if (! in_array($association->getForeignKey(), $fields)) {
                continue;
            }

            $primaryKey = $association->getTarget()->getPrimaryKey();
            if (! is_string($primaryKey)) {
                throw new UnsupportedPrimaryKeyException();
            }

            $foreignKey = $association->getForeignKey();
            if (!is_string($foreignKey)) {
                throw new UnsupportedForeignKeyException();
            }

            try {
                $relatedEntity = $association->getTarget()
                    ->find('all')
                    ->where([$primaryKey => $entity->get($foreignKey)])
                    ->enableHydration(true)
                    ->firstOrFail();
                $result[] = $relatedEntity;
            } catch (RecordNotFoundException $e) {
                // @ignoreException
            }
        }

        Assert::allIsInstanceOf($result, EntityInterface::class);

        return $result;
    }

    /**
     * Get options for the new event
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param string $startField Entity field to use for event start time
     * @return mixed[]
     */
    protected function getEventOptions(CsvTable $table, EntityInterface $entity, string $startField): array
    {
        // Event start and end times
        $eventTimes = $this->getEventTime($entity, $startField);

        $mailer = new IcEmail($table, $entity);

        $result = [
            'id' => $entity->get('id'),
            'sequence' => $entity->isNew() ? 0 : time(),
            'summary' => $mailer->getEventSubject(),
            'description' => $mailer->getEventContent(),
            'location' => (string)$entity->get('location'),
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
     * @return mixed[] Associative array of DateTimeZone instances for start and end
     */
    protected function getEventTime(EntityInterface $entity, string $startField): array
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
