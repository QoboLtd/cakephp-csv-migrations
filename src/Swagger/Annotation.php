<?php
namespace CsvMigrations\Swagger;

use Cake\Core\App;
use Cake\Database\Exception;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class Annotation
{
    /**
     * Annotation content.
     *
     * @var string
     */
    protected $_content = null;

    /**
     * Mapping of database column types to swagger types.
     *
     * @var array
     */
    protected $_db2swagger = [
        'datetime' => 'dateTime',
        'decimal' => 'float'
    ];

    /**
     * Class name to generate annotations for.
     *
     * @var string
     */
    protected $_className = '';

    /**
     * Full path name of the file to generate annotations for.
     *
     * @var string
     */
    protected $_pathName = '';

    /**
     * Swagger annotations.
     *
     * @var array
     */
    protected $_annotations = [
        'info' => '/**
            @SWG\Swagger(
                @SWG\Info(
                    title="API Documentation",
                    description="Interactive API documentation powered by Swagger.io",
                    termsOfService="http://swagger.io/terms/",
                    version="1.0.0"
                )
            )
         */',
        'definition' => '/**
            @SWG\Definition(
                definition="{{definition}}",
                required={"{{required}}"},
                {{properties}}
            )
         */',
        'property' => '
            @SWG\Property(
                property="{{property}}",
                type="{{type}}"
            )',
            // example="3af32bdd-86d5-4bfa-80dd-889ba9be1e64",
            // description="Trading account CRM id"
        'paths' => '/**
            @SWG\Get(
                path="/api/{{module_url}}",
                summary="Retrieve a list of {{module_human_plural}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="limit",
                    description="Results limit",
                    in="query",
                    required=false,
                    type="integer",
                    default=""
                ),
                @SWG\Parameter(
                    name="sort",
                    description="Sort results by field",
                    in="query",
                    required=false,
                    type="string",
                    enum={ {{sort_fields}} }
                ),
                @SWG\Parameter(
                    name="direction",
                    description="Sorting direction",
                    in="query",
                    required=false,
                    type="string",
                    enum={"asc", "desc"}
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation",
                    @SWG\Schema(
                        type="array",
                        ref="#/definitions/{{module_singular}}"
                    )
                )
            )

            @SWG\Get(
                path="/api/{{module_url}}/view/{id}",
                summary="Retrieve a {{module_human_singular}} by ID",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="id",
                    description="{{module_human_singular}} ID",
                    in="path",
                    required=true,
                    type="string",
                    default=""
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation",
                    @SWG\Schema(
                        type="array",
                        ref="#/definitions/{{module_singular}}"
                    )
                ),
                @SWG\Response(
                    response="404",
                    description="Not found"
                )
            )

            @SWG\Post(
                path="/api/{{module_url}}/add",
                summary="Add new {{module_human_singular}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="body",
                    in="body",
                    description="{{module_human_singular}} object to be added to the system",
                    required=true,
                    @SWG\Schema(ref="#/definitions/{{module_singular}}")
                ),
                @SWG\Response(
                    response="201",
                    description="Successful operation"
                )
            )

            @SWG\Put(
                path="/api/{{module_url}}/edit/{id}",
                summary="Edit an existing {{module_human_singular}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="id",
                    description="{{module_human_singular}} ID",
                    in="path",
                    required=true,
                    type="string",
                    default=""
                ),
                @SWG\Parameter(
                    name="body",
                    in="body",
                    description="{{module_human_singular}} name",
                    required=true,
                    @SWG\Schema(ref="#/definitions/{{module_singular}}")
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation"
                ),
                @SWG\Response(
                    response="404",
                    description="Not found"
                )
            )
        */'
    ];

    /**
     * Constructor method.
     *
     * @param string $className Class name
     * @param string $path File path
     */
    public function __construct($className, $path)
    {
        $this->_className = App::shortName($className, 'Controller/Api', 'Controller');

        $this->_path = $path;
    }

    /**
     * Swagger annotation content getter.
     *
     * @return string
     */
    public function getContent()
    {
        if (empty($this->_content)) {
            $this->_generateContent();
        }

        return $this->_content;
    }

    /**
     * Swagger annotation content setter.
     *
     * @param string $content The content
     * @return void
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Method that generates and sets swagger annotation content.
     *
     * @return void
     */
    protected function _generateContent()
    {
        $result = file_get_contents($this->_path);

        // if App class then use info annotation and exit
        if ('App' === $this->_className) {
            $result = preg_replace('/(^class\s)/im', $this->_annotations['info'] . "\n$1", $result);
            $this->setContent(trim($result));

            return;
        }

        $properties = $this->_getProperties();

        $definition = $this->_getDefinition($properties);

        $paths = $this->_getPaths();

        $result = preg_replace('/(^class\s)/im', implode("\n", [$definition, $paths]) . "\n$1", $result);

        $this->setContent(trim($result));
    }

    /**
     * Generates and returns swagger properties annotation.
     *
     * It uses current table's column definitions to generate
     * swagger property annotation on the fly.
     *
     * @return string
     */
    protected function _getProperties()
    {
        $result = null;
        $table = TableRegistry::get($this->_className);

        $entity = $table->newEntity();
        $hiddenProperties = $entity->hiddenProperties();
        try {
            $columns = $table->schema()->columns();
            $columns = array_diff($columns, $hiddenProperties);
        } catch (Exception $e) {
            return $result;
        }

        foreach ($columns as $column) {
            $data = $table->schema()->column($column);
            $placeholders = [
                '{{property}}' => $column,
                '{{type}}' => array_key_exists($data['type'], $this->_db2swagger) ?
                    $this->_db2swagger[$data['type']] :
                    $data['type']
            ];
            $result[] = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                $this->_annotations['property']
            );
        }

        $result = implode(',', $result);

        return $result;
    }

    /**
     * Generates and returns swagger definition (model) annotation.
     *
     * It uses current table's column definitions to construct a list
     * of required columns and uses properties argument to generate
     * definition annotation.
     *
     * @param  string $properties Swagger properties annotations
     * @return string
     */
    protected function _getDefinition($properties)
    {
        $result = null;
        $table = TableRegistry::get($this->_className);

        $entity = $table->newEntity();
        $hiddenProperties = $entity->hiddenProperties();
        try {
            $columns = $table->schema()->columns();
            $columns = array_diff($columns, $hiddenProperties);
        } catch (Exception $e) {
            return $result;
        }

        $required = [];
        foreach ($columns as $column) {
            $data = $table->schema()->column($column);
            if ($data['null']) {
                continue;
            }
            $required[] = $column;
        }

        $placeholders = [
            '{{definition}}' => Inflector::singularize($this->_className),
            '{{required}}' => implode(',', $required),
            '{{properties}}' => (string)$properties
        ];

        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->_annotations['definition']
        );

        return $result;
    }

    /**
     * Generates and returns swagger paths (controller) annotation.
     *
     * It uses current table's column definitions to construct a list
     * of all visible columns to be used as sorting fields and generates
     * paths annotations on the fly.
     *
     * @return array
     */
    protected function _getPaths()
    {
        $result = null;
        $table = TableRegistry::get($this->_className);

        $entity = $table->newEntity();
        $hiddenProperties = $entity->hiddenProperties();
        try {
            $fields = $table->schema()->columns();
            $fields = array_diff($fields, $hiddenProperties);
            sort($fields);
        } catch (Exception $e) {
            return $result;
        }

        $placeholders = [
            '{{module_human_singular}}' => Inflector::singularize(Inflector::humanize(Inflector::underscore($this->_className))),
            '{{module_human_plural}}' => Inflector::pluralize(Inflector::humanize(Inflector::underscore($this->_className))),
            '{{module_singular}}' => Inflector::singularize($this->_className),
            '{{module_url}}' => Inflector::dasherize($this->_className),
            '{{sort_fields}}' => '"' . implode('", "', $fields) . '"'
        ];

        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->_annotations['paths']
        );

        return $result;
    }
}
