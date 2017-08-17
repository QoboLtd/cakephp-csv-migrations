<?php
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
    // Field Handlers events
    const FIELD_HANDLER_DEFAULT_VALUE = 'CsvMigrations.FieldHandler.DefaultValue';
    // Menu elements events
    const MENU_ACTIONS_ASSOCIATED = 'CsvMigrations.Associated.actionsMenu.beforeRender';
    const MENU_ACTIONS_DB_LISTS_INDEX = 'CsvMigrations.Dblists.Index.actionsMenu.beforeRender';
    const MENU_ACTIONS_DB_LIST_ITEMS_INDEX = 'CsvMigrations.DblistItems.Index.actionsMenu.beforeRender';
    const MENU_ACTIONS_INDEX = 'CsvMigrations.Index.actionsMenu.beforeRender';
    const MENU_TOP_DB_LISTS_INDEX = 'CsvMigrations.Dblists.Index.topMenu.beforeRender';
    const MENU_TOP_DB_LIST_ITEMS_INDEX = 'CsvMigrations.DblistItems.Index.topMenu.beforeRender';
    const MENU_TOP_INDEX = 'CsvMigrations.Index.topMenu.beforeRender';
    const MENU_TOP_VIEW = 'CsvMigrations.View.topMenu.beforeRender';
    // CsvMigrations Table events
    const MODEL_BEFORE_SAVE = 'CsvMigrations.Model.afterSave';
    // CsvMigrations Views events
    const VIEW_BODY_BOTTOM = 'View.View.Body.Bottom';
    const VIEW_TABS_LIST = 'CsvMigrations.View.View.TabsList';
    const VIEW_TAB_AFTER_CONTENT = 'CsvMigrations.View.View.TabContent.afterContent';
    const VIEW_TAB_BEFORE_CONTENT = 'CsvMigrations.View.View.TabContent.beforeContent';
    const VIEW_TAB_CONTENT = 'CsvMigrations.View.View.TabContent';
    const VIEW_TRANSLATION_BUTTON = 'CsvMigrations.View.View.TranslationButton';
}
