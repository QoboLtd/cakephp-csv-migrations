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
namespace CsvMigrations\Swagger;

use Cake\Core\App;
use Swagger\Context;
use Swagger\StaticAnalyser;

class Analyser extends StaticAnalyser
{
    /**
     * Extract and process all doc-comments from an
     * auto-generated swagger annotations content.
     *
     * @param string $filename Path to a php file.
     * @return Analysis
     */
    public function fromFile($filename)
    {
        $className = basename($filename, '.php');
        $className = App::className($className, 'Controller/Api');

        $tokens = [];
        if ($className) {
            $annotations = $className::generateSwaggerAnnotations($filename);
            $tokens = token_get_all($annotations);
        }

        return $this->fromTokens($tokens, new Context(['filename' => $filename]));
    }
}
