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
namespace CsvMigrations\FieldHandlers;

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
