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
namespace CsvMigrations;

/**
 * @deprecated 28.0.2 Added BC alias.
 */
class_alias('CsvMigrations\Utility\FileUpload', 'CsvMigrations\FileUploadsUtils');

trigger_error(
    'Use CsvMigrations\Utility\FileUpload instead of CsvMigrations\FileUploadsUtils.',
    E_USER_DEPRECATED
);
