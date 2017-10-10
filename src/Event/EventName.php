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
    // API controller events
    const API_ADD_AFTER_SAVE = 'CsvMigrations.Add.afterSave';
    const API_ADD_BEFORE_SAVE = 'CsvMigrations.Add.beforeSave';
    const API_EDIT_AFTER_FIND = 'CsvMigrations.Edit.afterFind';
    const API_EDIT_AFTER_SAVE = 'CsvMigrations.Edit.afterSave';
    const API_EDIT_BEFORE_FIND = 'CsvMigrations.Edit.beforeFind';
    const API_EDIT_BEFORE_SAVE = 'CsvMigrations.Edit.beforeSave';
    const API_INDEX_AFTER_PAGINATE = 'CsvMigrations.Index.afterPaginate';
    const API_INDEX_BEFORE_PAGINATE = 'CsvMigrations.Index.beforePaginate';
    const API_INDEX_BEFORE_RENDER = 'CsvMigrations.Index.beforeRender';
    const API_LOOKUP_AFTER_FIND = 'CsvMigrations.afterLookup';
    const API_LOOKUP_BEFORE_FIND = 'CsvMigrations.beforeLookup';
    const API_VIEW_AFTER_FIND = 'CsvMigrations.View.afterFind';
    const API_VIEW_BEFORE_FIND = 'CsvMigrations.View.beforeFind';
    // Controller events
    const BATCH_IDS = 'CsvMigrations.Batch.ids';
    // Field Handlers events
    const FIELD_HANDLER_DEFAULT_VALUE = 'CsvMigrations.FieldHandler.DefaultValue';
    // CsvMigrations Table events
    const MODEL_AFTER_SAVE = 'CsvMigrations.Model.afterSave';
    // CsvMigrations Views events
    const VIEW_BODY_BOTTOM = 'View.View.Body.Bottom';
    const VIEW_TABS_LIST = 'CsvMigrations.View.View.TabsList';
    const VIEW_TAB_AFTER_CONTENT = 'CsvMigrations.View.View.TabContent.afterContent';
    const VIEW_TAB_BEFORE_CONTENT = 'CsvMigrations.View.View.TabContent.beforeContent';
    const VIEW_TAB_CONTENT = 'CsvMigrations.View.View.TabContent';
    const VIEW_TRANSLATION_BUTTON = 'CsvMigrations.View.View.TranslationButton';
}
