<?php
namespace CsvMigrations;

use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table as BaseTable;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldTrait;
use CsvMigrations\ListTrait;
use CsvMigrations\MigrationTrait;
use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event as vEvent;
use \Eluceo\iCal\Property\Event\Organizer as vOrganizer;
use \Eluceo\iCal\Property\Event\Attendees as vAttendees;

/**
 * Accounts Model
 *
 */
class Table extends BaseTable
{
    use ConfigurationTrait;
    use FieldTrait;
    use ListTrait;
    use MigrationTrait;

    /**
     * Searchable parameter name
     */
    const PARAM_NON_SEARCHABLE = 'non-searchable';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Muffin/Trash.Trash');

        // set table/module configuration
        $this->_setConfiguration($this->table());

        //Set the current module
        $config['table'] = $this->_currentTable();

        $this->_setAssociations($config);
    }

    public function beforeSave($event, $entity, $options = [])
    {
        $config = $this->getConfig();

        if (!empty($config['table']['allow_reminders'])) {
            $assignedEntities = $this->getAssignedAssociations($entity, ['tables' => $config['table']['allow_reminders']]);

            if (!empty($assignedEntities)) {
                $attendees = array_map(function($item) {
                    if (isset($item['email'])) {
                        return $item['email'];
                    }
                }, $assignedEntities);

                $attendees = array_filter($attendees);

                $this->_sendCalendarReminder($entity, ['attendees' => $attendees]);
            }
        }
    }

    protected function _sendCalendarReminder($entity, $options = [])
    {
        $sent = false;

        if (!empty($options['attendees'])) {

            $to = implode(',' , $options['attendees']);

            $subject = sprintf("%s - %s", $this->alias(),"Reminder");

            $headers = "\r\nMIME-version: 1.0\r\nContent-Type: text/calendar; method=REQUEST; charset=\"iso-8859-1\"";
            $headers .= "\r\nContent-Transfer-Encoding: 7bit\r\nX-Mailer: Microsoft Office Outlook 12.0";

            $vCalendar = new Calendar('//EN//');
            $vEvent = new vEvent();
            $vOrganizer = new vOrganizer($to, ['MAILTO' => $to]);

            foreach ($options['attendees'] as $email) {
                $vAttendees = new vAttendees();

                $vAttendees->add('MAILTO:'.$email,[
                    'ROLE' => 'REQ-PARTICIPANT',
                    'PARTSTAT'=>'NEEDS-ACTION',
                    'RSVP'=>'TRUE',
                ]);
            }

            //@NOTE: its '02:30' string object,
            $duration_parts = date_parse($entity->duration);
            $duration_minutes = $duration_parts['hour'] * 60 + $duration_parts['minute'];

            $end_date = new Time($entity->start_date->format('Y-m-d H:i:s'));
            $end_date->modify("+ {$duration_minutes} minutes");

            $vEvent->setDtStart(new \DateTime($entity->start_date->format('Y-m-d H:i:s')))
                ->setDtEnd(new \DateTime($end_date->format('Y-m-d H:i:s')))
                ->setNoTime(false)
                ->setStatus('CONFIRMED')
                ->setAttendees($vAttendees)
                ->setOrganizer($vOrganizer)
                ->setSummary($entity->subject);

            $vCalendar->addComponent($vEvent);

            $email = new Email('default');

            $sent = $email->to($to)
                ->setHeaders([$headers])
                ->subject($subject)
                ->attachments(['event.ics' =>[
                    'contentDisposition' => true,
                    'mimetype' => 'text/calendar',
                    'data' => $vCalendar->render()
                ]])
                ->send();

        }

        return $sent;
    }


    public function getAssignedAssociations($entity, $options = [])
    {
        $entities = [];

        $tables = empty($options['tables']) ? [] : explode(',', $options['tables']);
        $associations = [];

        if (!empty($tables)) {
            foreach ($this->associations() as $association) {
                if (in_array(Inflector::humanize($association->target()->table()), $tables) ) {
                    array_push($associations, $association);
                }
            }
        } else {
            $associations = $this->associations();
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
     * Get searchable fields
     *
     * @return array field names
     */
    public function getSearchableFields()
    {
        $result = [];
        foreach ($this->getFieldsDefinitions($this->alias()) as $field) {
            if (!$field[static::PARAM_NON_SEARCHABLE]) {
                $result[] = $field['name'];
            }
        }

        return $result;
    }

    /**
     * getReminderTypeFields
     * @return array $result containing reminder fieldnames
     */
    public function getReminderFields()
    {
        $result = [];
        foreach ($this->getFieldsDefinitions($this->alias()) as $field) {
            if ($field['type'] == 'reminder') {
                $result[] = $field;
            }
        }

        return $result;
    }

    /**
     * Returns searchable fields properties.
     *
     * @param  array $fields searchable fields
     * @return array
     */
    public function getSearchableFieldProperties(array $fields)
    {
        $result = [];

        if (empty($fields)) {
            return $result;
        }
        foreach ($this->getFieldsDefinitions($this->alias()) as $field => $definitions) {
            if (in_array($field, $fields)) {
                $csvField = new CsvField($definitions);
                $type = $csvField->getType();
                $result[$field] = [
                    'type' => $type
                ];
                if ('list' === $type) {
                    $result[$field]['fieldOptions'] = $this->_getSelectOptions($csvField->getLimit());
                }
            }
        }

        return $result;
    }

    /**
     * Enable accessibility to associations primary key. Useful for
     * patching entities with associated data during updating process.
     *
     * @return array
     */
    public function enablePrimaryKeyAccess()
    {
        $result = [];
        foreach ($this->associations() as $association) {
            $result['associated'][$association->name()] = [
                'accessibleFields' => [$association->primaryKey() => true]
            ];
        }

        return $result;
    }

    /**
     * Method that adds lookup fields with the id value to the Where clause in ORM Query
     *
     * @param  \Cake\ORM\Query $query Query instance
     * @param  string          $id    Record id
     * @return \Cake\ORM\Query
     */
    public function findByLookupFields(Query $query, $id)
    {
        $lookupFields = $this->lookupFields();

        if (empty($lookupFields)) {
            return $query;
        }

        $tableName = $this->alias();
        // check for record by table's lookup fields
        foreach ($lookupFields as $lookupField) {
            // prepend table name to avoid CakePHP ORM's ambiguous column errors
            if (false === strpos($lookupField, '.')) {
                $lookupField = $tableName . '.' . $lookupField;
            }
            $query->orWhere([$lookupField => $id]);
        }

        return $query;
    }

    /**
     * Method that checks Entity's association fields (foreign keys) values and query's the database to find
     * the associated record. If the record is not found, it query's the database again to find it by its
     * display field. If found it replaces the associated field's value with the records id.
     *
     * This is useful for cases where the display field value is used on the associated field. For example
     * a new post is created and in the 'owner' field the username of the user is used instead of its uuid.
     *
     * BEFORE:
     * {
     *    'title' => 'Lorem Ipsum',
     *    'content' => '.....',
     *    'owner' => 'admin',
     * }
     *
     * AFTER:
     * {
     *    'title' => 'Lorem Ipsum',
     *    'content' => '.....',
     *    'owner' => '77dd9203-3f21-4571-8843-0264ae1cfa48',
     * }
     *
     * @param  \Cake\ORM\Entity $entity Entity instance
     * @return \Cake\ORM\Entity
     */
    public function setAssociatedByLookupFields(Entity $entity, $options = [])
    {
        foreach ($this->associations() as $association) {
            $lookupFields = $association->target()->lookupFields();

            if (empty($lookupFields)) {
                continue;
            }

            $value = $entity->{$association->foreignKey()};
            // skip if association's foreign key is NOT set in the entity
            if (is_null($value)) {
                continue;
            }

            // check if record can be fetched by primary key
            $found = (bool)$association->target()->find('all', [
                'conditions' => [$association->primaryKey() => $value]
            ])->count();

            // skip if record found by primary key
            if ($found) {
                continue;
            }

            // check if record can be fetched by display field
            $query = $association->target()->find()
                // select associated record's primary key (usually id)
                ->select($association->primaryKey());

            // check for record by table's lookup fields
            foreach ($lookupFields as $lookupField) {
                $query->orWhere([$lookupField => $value]);
            }

            $associatedEntity = $query->first();

            // skip if record cannot be found by display field
            if (is_null($associatedEntity)) {
                continue;
            }

            $entity->{$association->foreignKey()} = $associatedEntity->{$association->primaryKey()};
        }

        return $entity;
    }

    /**
     * Method that adds lookup fields with the matching values to the Where clause in ORM Query
     *
     * @param  \Cake\ORM\Query $query  Query instance
     * @param  array           $values Entity lookup-fields values
     * @return \Cake\ORM\Query
     */
    public function findByLookupFieldsWithValues(Query $query, array $values)
    {
        $lookupFields = $this->lookupFields();

        if (empty($lookupFields) || empty($values)) {
            return $query;
        }

        // check for record by table's lookup fields
        foreach ($lookupFields as $lookupField) {
            if (!isset($values[$lookupField])) {
                continue;
            }
            $query->orWhere([$lookupField => $values[$lookupField]]);
        }

        return $query;
    }

    /**
     * Return current table in camelCase form.
     * It adds plugin name as a prefix.
     *
     * @return string Table Name along with its prefix if found.
     */
    protected function _currentTable()
    {
        list($namespace, $alias) = namespaceSplit(get_class($this));
        $alias = substr($alias, 0, -5);
        list($plugin) = explode('\\', $namespace);
        if ($plugin === 'App') {
            return Inflector::camelize($alias);
        }

        return Inflector::camelize($plugin . '.' . $alias);
    }
}
