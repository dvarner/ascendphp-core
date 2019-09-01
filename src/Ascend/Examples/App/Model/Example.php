<?php namespace Ascend\Examples\App\Model;

use Ascend\Core\Model;

class Example extends Model
{
    protected static $table = 'examples';
    protected static $fields = [
        // 'id' => 'int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY', // this is automatically created for every model
        'sub_id' => 'INT UNSIGNED NOT NULL',
        'name' => 'VARCHAR(255) NOT NULL',
        'is_active' => 'TINYINT UNSIGNED NOT NULL',
    ];

    protected static $seeds = [
        ['sub_id' => 0, 'name' => 'Example 1'], // 1
        ['sub_id' => 1, 'name' => 'Example 2'], // 2
    ];
    /*
    protected static $seeds_skip = true;
    protected static $seeds = [
        'file' => 'baby-names.csv',
        'map' => [
            'name' => 1,
        ]
    ];
    */

    public static function getAllActive()
    {
        $table = self::getTableName();

        // $sql = "SELECT * FROM {$table} WHERE is_active = :is_active AND deleted_at IS NULL";
        // return self::many($sql,['is_active' => 1]);

        return self::where('is_active', '=', 1)->orderBy('created_at', 'desc')->all();
    }
}
