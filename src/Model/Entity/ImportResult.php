<?php
namespace CsvMigrations\Model\Entity;

use Cake\ORM\Entity;

/**
 * ImportResult Entity
 *
 * @property string $id
 * @property string $import_id
 * @property int $row_number
 * @property string $model_name
 * @property string $model_id
 * @property string $status
 * @property string $status_message
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \Cake\I18n\Time $trashed
 *
 * @property \CsvMigrations\Model\Entity\Import $import
 * @property \CsvMigrations\Model\Entity\Model $model
 */
class ImportResult extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
