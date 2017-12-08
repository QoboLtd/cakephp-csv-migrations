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

use Cake\Datasource\EntityInterface;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Table as Table;
use Exception;
use InvalidArgumentException;

/**
 * Email class
 *
 * This class helps with sending emails
 * that contain iCal calendars/events
 */
class IcEmail
{
    /**
     * Changelog template
     */
    const CHANGELOG = '* %s: changed from "%s" to "%s".' . "\n";

    protected $table;
    protected $entity;

    /**
     * Ingored modified fields
     *
     * @var array
     */
    protected $ignoredFields = ['created', 'modified'];

    /**
     * Constructor
     *
     * @param \CsvMigrations\Table $table Table instance
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @return void
     */
    public function __construct(Table $table, EntityInterface $entity)
    {
        $this->table = $table;
        $this->entity = $entity;
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
    public function sendCalendarEmail($to, $subject, $content, array $eventOptions, $config = 'default')
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
     * Get plain value of the entity display field
     *
     * @return string
     */
    protected function getDisplayValue()
    {
        try {
            $displayField = $this->table->displayField();
            $displayValue = $this->entity->{$displayField};

            $fhf = new FieldHandlerFactory();
            $result = $fhf->renderValue($this->table, $displayField, $displayValue, [ 'renderAs' => 'plain']);
            if (empty($result) || !is_string($result)) {
                throw new InvalidArgumentException("Failed to get entity display value");
            }
        } catch (Exception $e) {
            $result = 'reminder';
        }

        return $result;
    }

    /**
     * Get email subject
     *
     * @return string
     */
    public function getEmailSubject()
    {
        $module = Inflector::singularize($this->table->alias());
        $displayValue = $this->getDisplayValue();
        $result = $module . ": " . $displayValue;

        if (!$this->entity->isNew()) {
            $result = "(Updated) " . $result;
        }

        return $result;
    }

    /**
     * Get email content
     *
     * @return string
     */
    public function getEmailContent()
    {
        $result = '';

        $module = Inflector::singularize($this->table->alias());
        $displayValue = $this->getDisplayValue();
        $user = $this->getUserString();
        $action = $this->entity->isNew() ? 'created' : 'updated';

        // Example: Lead Foobar created by System
        $result = sprintf("%s %s %s by %s", $module, $displayValue, $action, $user);

        $changeLog = $this->getChangelog();
        if (!empty($changeLog)) {
            $result .= "\n\n";
            $result .= $changeLog;
            $result .= "\n";
        }

        $result .= "\n\n";
        $result .= "See more: ";
        $result .= $this->getEntityUrl();

        return $result;
    }

    /**
     * Get event subject/summary
     *
     * For now this is just an alias for getEmailSubject().
     *
     * @return string
     */
    public function getEventSubject()
    {
        return $this->getEmailSubject();
    }

    /**
     * Get event content/description
     *
     * @return string
     */
    public function getEventContent()
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
            if (!empty($this->entity->$field)) {
                $result = $this->entity->$field;
                break;
            }
        }

        $result .= "\n\n";
        $result .= "See more: ";
        $result .= $this->getEntityUrl();

        return $result;
    }

    /**
     * Get full URL to the entity view
     *
     * @return string
     */
    public function getEntityUrl()
    {
        $result = Router::url(
            [
                'prefix' => false,
                'controller' => $this->table->table(),
                'action' => 'view',
                $this->entity->id
            ],
            true
        );

        return $result;
    }

    /**
     * Get plain value of the current user
     *
     * @return string
     */
    protected function getUserString()
    {
        $result = 'System';

        $userFields = [
            'name',
            'username',
            'email',
        ];

        $currentUser = $this->table->getCurrentUser();
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
     * @return string
     */
    protected function getChangelog()
    {
        $result = '';

        // plain changelog if entity is new
        if ($this->entity->isNew()) {
            return $result;
        }

        // get entity's modified fields
        $fields = $this->entity->extractOriginalChanged($this->entity->visibleProperties());

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
            $result .= sprintf(static::CHANGELOG, Inflector::humanize($k), $v, $this->entity->{$k});
        }

        return $result;
    }
}
