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
    // Field Handlers events
    const FIELD_HANDLER_DEFAULT_VALUE = 'CsvMigrations.FieldHandler.DefaultValue';
    // CsvMigrations Table events
    const MODEL_AFTER_SAVE = 'CsvMigrations.Model.afterSave';
    const MODEL_AFTER_SAVE_COMMIT = 'CsvMigrations.Model.afterSaveCommit';
    // Thumbnails
    const CREATE_THUMBNAILS = 'CsvMigrations.FileStorage.createThumbnails';
    const REMOVE_THUMBNAILS = 'CsvMigrations.FileStorage.deleteThumbnails';
}
