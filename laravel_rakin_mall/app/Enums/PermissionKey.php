<?php
namespace App\Enums;
/**
 * Created by Spatie.
 * User: rithea.saret
 * Date: 12/07/2021
 * Time: 4:50 PM
 */
use \Spatie\Enum\Enum;

/**
 * @method static self create()
 * @method static self list()
 * @method static self view()
 * @method static self access()
 * @method static self edit()
 * @method static self delete()
 */
class PermissionKey extends Enum
{
    protected static function labels(): array
    {
        return [
            'create' => 1,
            'list' => 2,
            'view' => 3,
            'access' => 4,
            'edit' => 5,
            'delete' => 6
        ];
    }
    protected static function values(): array
    {
        return [
            'create' => 'create',
            'list' => 'list',
            'view' => 'view',
            'access' => 'access',
            'edit' => 'edit',
            'delete' => 'delete'
        ];
    }
}