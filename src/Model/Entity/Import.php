<?php
namespace CsvMigrations\Model\Entity;

use Cake\ORM\Entity;

/**
 * Import Entity
 *
 * @property string $id
 * @property string $filename
 * @property $options
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \Cake\I18n\Time $trashed
 * @property string $model_name
 * @property int $attempts
 * @property \Cake\I18n\Time $attempted_date
 * @property string $status
 *
 * @property \CsvMigrations\Model\Entity\ImportResult[] $import_results
 */
class Import extends Entity
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