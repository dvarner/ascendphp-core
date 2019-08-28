<?php namespace Ascend\Core;

class Model extends ModelChain
{
    // protected static $table = '';
    // protected static $fields = [];
    public $sql = [];

    public static function getTableName()
    {
        return static::$table;
    }

    public static function getFields()
    {
        return static::$fields;
    }

    public static function getSeedsSkip()
    {
        return isset(static::$seeds_skip);
    }

    public static function getSeeds()
    {
        return isset(static::$seeds) ? static::$seeds : null;
    }

    public static function create($fields = [], $add_dates = true)
    {

        $sql_fields = '';
        foreach ($fields AS $k => $v) {
            $sql_fields .= ',' . $k . ' ' . $v;
        }
        $ignore_dates_sql = '';
        if ($add_dates) {
            $add_dates_sql = '
              ,`created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `deleted_at` timestamp NULL DEFAULT NULL
            ';
        }
        $sql = "
        CREATE TABLE IF NOT EXISTS `" . self::getTableName() . "` (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY{$sql_fields}{$add_dates_sql})
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci
        ";
        self::$pdo->prepare($sql)->execute();
        /*
        $sql = "ALTER TABLE `".self::getTableName()."` ADD PRIMARY KEY (`id`);";
        self::$pdo->prepare($sql)->execute();

        $sql = "ALTER TABLE `".self::getTableName()."` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT;";
        self::$pdo->prepare($sql)->execute();
        */
    }

    // ** Extend Functions

    public static function insert($binds, $empty = null)
    {
        $table = self::getTableName();
        return parent::insert($table, $binds);
    }

    public static function update($binds, $where, $empty = null)
    {
        $table = self::getTableName();
        return parent::update($table, $binds, $where);
    }

    public static function delete($where, $empty = null)
    {
        $table = self::getTableName();
        if (is_numeric($where)) {
            $id = $where;
            unset($where);
            $where['id'] = $id;
        }
        return parent::delete($table, $where);
    }

    public static function getById($id)//, $is_deleted = false)
    {
        $sql = "SELECT * FROM " . self::getTableName() . " WHERE id = :id";
        // if ($is_deleted) $sql .= " AND deleted_at is null";
        return self::one($sql, ['id' => $id]);
    }

    public static function getAll($is_deleted = false)
    {
        $sql = "SELECT * FROM " . self::getTableName();
        if ($is_deleted) $sql .= " WHERE deleted_at is null";
        return self::many($sql, [], 'id');
    }
}