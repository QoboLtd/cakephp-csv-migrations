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
        $remindersTo = [];

        //get applications's timezone
        $tz = Time::now()->format('e');
        $dtz = new \DateTimeZone($tz);

        $table = $event->subject();
        $subject = sprintf("Reminder for %s", $table->alias());

        //@TODO: sending only newly created events,
        // not editing
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

        $assignedEntities = $this->getAssignedAssociations($table, $entity, ['tables' => $remindersTo]);

        if (!empty($assignedEntities)) {
            $attendees = array_map(function ($item) {
                if (isset($item['email'])) {
                    return $item['email'];
                }
            }, $assignedEntities);
        }

        if (!empty($attendees)) {
            $durationMinutes = 0;

            $to = implode(',', $attendees);

            $vCalendar = new \Eluceo\iCal\Component\Calendar('//EN//');
            $vEvent = new \Eluceo\iCal\Component\Event();
            $vOrganizer = new \Eluceo\iCal\Property\Event\Organizer($to, ['MAILTO' => $to]);
            $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);

            foreach ($attendees as $email) {
                $vAttendees = new \Eluceo\iCal\Property\Event\Attendees();

                $vAttendees->add("MAILTO:$email", [
                    'ROLE' => 'REQ-PARTICIPANT',
                    'PARTSTAT' => 'NEEDS-ACTION',
                    'RSVP' => 'TRUE',
                ]);
            }

            // calculate the duration of an event
            if (!empty($entity->duration)) {
                $durationParts = date_parse($entity->duration);
                $durationMinutes = $durationParts['hour'] * 60 + $durationParts['minute'];
            }

            //initial reminder event
            $vEvent->setAttendees($vAttendees)
                ->setOrganizer($vOrganizer)
                ->setSummary($entity->subject);

            $endDate = new Time($entity->start_date->format('Y-m-d H:i:s'));
            $endDate->modify("+ {$durationMinutes} minutes");

            //setting the rule on switching hours for Daylight Saving Time
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

            //setting the dule for Standard time switching hours
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

            $vTimezone->addComponent($vTimezoneRuleDst);
            $vTimezone->addComponent($vTimezoneRuleStd);

            $vEvent->setDtStart(new \DateTime($entity->start_date->format('Y-m-d H:i:s'), $dtz));

            if (!empty($endDate) && ($endDate instanceof Time)) {
                $vEvent->setDtEnd(new \DateTime($endDate->format('Y-m-d H:i:s'), $dtz));
            }

            $vCalendar->setTimezone($vTimezone);
            $vEvent->setSummary($entity->subject);
            $vEvent->setUseTimezone(true);
            $vCalendar->addComponent($vEvent);

            $headers = "Content-Type: text/calendar; charset=utf-8";
            $headers .= 'Content-Disposition: attachment; filename="event.ics"';

            $email = new Email('default');
            $email->to($to)
                ->setHeaders([$headers])
                ->subject($subject)
                ->attachments(['event.ics' => [
                    'contentDisposition' => true,
                    'mimetype' => 'text/calendar',
                    'data' => $vCalendar->render()
                ]]);
            $sent = $email->send();
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
}
