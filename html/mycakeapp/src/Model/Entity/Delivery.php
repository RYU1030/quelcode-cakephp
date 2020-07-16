<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Delivery Entity
 *
 * @property int $id
 * @property int $bidinfo_id
 * @property string $receiver_name
 * @property string $address
 * @property string $mobile_number
 * @property bool $is_shipped
 * @property bool $is_received
 * @property \Cake\I18n\Time $created_at
 * @property \Cake\I18n\Time $updated_at
 *
 * @property \App\Model\Entity\Bidinfo $bidinfo
 */
class Delivery extends Entity
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
        'receiver_name' => true,
        'address' => true,
        'mobile_number' => true,
        'is_shipped' => true,
        'is_received' => true,
        'created' => true,
        'modified' => true,
        'bidinfo' => true,
    ];
}
