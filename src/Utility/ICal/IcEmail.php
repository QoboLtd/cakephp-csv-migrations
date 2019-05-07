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
use Eluceo\iCal\Component\Calendar;
use InvalidArgumentException;
use Qobo\Utils\Utility\User;

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
     * @param mixed[] $eventOptions Event options for calendar attachment
     * @param string $config Email config to use ('default' if omitted)
     * @return mixed[] Result of \Cake\Mailer\Email::send()
     */
    public function sendCalendarEmail(string $to, string $subject, string $content, array $eventOptions, string $config = 'default') : array
    {
        // Get iCal calendar
        $calendar = $this->getEventCalendar($eventOptions);

        // FIXME: WTF happened to new lines???
        $headers = "Content-Type: text/calendar; charset=utf-8";
        $headers .= 'Content-Disposition: attachment; filename="event.ics"';

        $emailer = new Email($config);
        $emailer->setTo($to)
            ->setHeaders([$headers])
            ->setSubject($subject)
            ->addAttachments(['event.ics' => [
                'contentDisposition' => true,
                'mimetype' => 'text/calendar',
                'data' => $calendar->render()
            ]]);

        return $emailer->send($content);
    }

    /**
     * Get iCal calendar with given event
     *
     * @param mixed[] $eventOptions Options for event creation
     * @return \Eluceo\iCal\Component\Calendar Whatever IcCalendar::getCalendar() returns
     */
    protected function getEventCalendar(array $eventOptions) : Calendar
    {
        // New iCal event
        $event = (new IcEvent($eventOptions))->getEvent();

        // New iCal calendar
        $calendar = new IcCalendar();
        $calendar->addEvent($event);

        return $calendar->getCalendar();
    }

    /**
     * Get plain value of the entity display field
     *
     * @return string
     */
    protected function getDisplayValue() : string
    {
        try {
            $displayField = $this->table->getDisplayField();
            $displayValue = $this->entity->get($displayField);

            $factory = new FieldHandlerFactory();

            $renderAs = 'plain';
            if (!empty($this->table->getFieldsDefinitions()[$displayField]['type']) && preg_match("/^related\(/", $this->table->getFieldsDefinitions()[$displayField]['type'])) {
                $renderAs = 'related';
            }

            $result = $factory->renderValue($this->table, $displayField, $displayValue, [ 'renderAs' => $renderAs]);
            $result = trim(strip_tags(html_entity_decode($result, ENT_QUOTES)), " \t\n\r\0\x0B\xC2\xA0");
        } catch (InvalidArgumentException $e) {
            $result = 'reminder';
        }

        return $result;
    }

    /**
     * Get email subject
     *
     * @return string
     */
    public function getEmailSubject() : string
    {
        $result = sprintf(
            '%s: %s',
            Inflector::singularize($this->table->getAlias()),
            $this->getDisplayValue()
        );

        if (! $this->entity->isNew()) {
            $result = sprintf('(Updated) %s', $result);
        }

        return $result;
    }

    /**
     * Get email content
     *
     * @return string
     */
    public function getEmailContent() : string
    {
        $result = '';

        $module = Inflector::singularize($this->table->getAlias());
        $displayValue = $this->getDisplayValue();
        $user = $this->getUserString();
        $action = $this->entity->isNew() ? 'created' : 'updated';

        // Example: Lead "Foobar" created by System
        $result = sprintf("%s \"%s\" %s by %s", $module, $displayValue, $action, $user);

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
    public function getEventSubject() : string
    {
        return $this->getEmailSubject();
    }

    /**
     * Get event content/description
     *
     * @return string
     */
    public function getEventContent() : string
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
    public function getEntityUrl() : string
    {
        $result = Router::url(
            [
                'prefix' => false,
                'controller' => $this->table->getTable(),
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
    protected function getUserString() : string
    {
        $result = 'System';

        $userFields = [
            'name',
            'username',
            'email',
        ];

        $currentUser = User::getCurrentUser();
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
    protected function getChangelog() : string
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
