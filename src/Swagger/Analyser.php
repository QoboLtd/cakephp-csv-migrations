<?php
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
