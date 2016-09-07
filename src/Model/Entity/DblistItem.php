<?php
namespace CsvMigrations\Model\Entity;

use Cake\ORM\Entity;

/**
 * DblistItem Entity
 *
 * @property string $id
 * @property string $dblist_id
 * @property string $name
 * @property string $value
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \CsvMigrations\Model\Entity\Dblist $dblist
 */
class DblistItem extends Entity
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
