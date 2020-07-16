<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Rating Entity
 *
 * @property int $id
 * @property int $bidinfo_id
 * @property int $ratings_for
 * @property int $rated_by
 * @property int $ratings
 * @property string $comments
 * @property \Cake\I18n\Time $created_at
 * @property \Cake\I18n\Time $updated_at
 *
 * @property \App\Model\Entity\Bidinfo $bidinfo
 */
class Rating extends Entity
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
        'bidinfo_id' => true,
        'ratings_for' => true,
        'rated_by' => true,
        'ratings' => true,
        'comments' => true,
        'created' => true,
        'modified' => true,
        'bidinfo' => true,
    ];
}
