<?php
namespace CsvMigrations\FieldHandlers;

use CsvMigrations\FieldHandlers\DatetimeFieldHandler;

/**
 * ReminderFieldHandler
 *
 * For all intensive purposes, Reminder field is
 * exactly like the Datetime field.  A separate
 * class in necessary for the functional separation
 * related to reminders, calendars, and the like.
 */
class ReminderFieldHandler extends DatetimeFieldHandler
{
}
