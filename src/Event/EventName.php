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
namespace CsvMigrations\Event;

use MyCLabs\Enum\Enum;

/**
 * Event Name enum
 */
class EventName extends Enum
{
    // Controller events
    const BATCH_IDS = 'CsvMigrations.Batch.ids';
    // Field Handlers events
    const FIELD_HANDLER_DEFAULT_VALUE = 'CsvMigrations.FieldHandler.DefaultValue';
    // CsvMigrations Table events
    const MODEL_AFTER_SAVE = 'CsvMigrations.Model.afterSave';
}
