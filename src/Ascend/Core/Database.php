<?php namespace Ascend\Core;

use Exception;
use PDO;
use PDOException;

class Database
{
    protected ?PDO $pdo = null;
    protected bool $sql_log_to_file = false;

    public function __construct()
    {
        if ('mysql' === Environment::get('DB_CONNECTION')) {
            $this->instance();
        }
    }

    protected function instance()
    {
        if (!$this->pdo instanceof PDO) {
            $this->sql_log_to_file = Config::get('database.connections.mysql.sql_log_to_file');
            $database_host = Config::get('database.connections.mysql.host');
            $database_name = Config::get('database.connections.mysql.name');
            $database_user = Config::get('database.connections.mysql.user');
            $database_password = Config::get('database.connections.mysql.password');
            $charset = Config::get('database.connections.mysql.charset');

            $dsn = 'mysql:host=' . $database_host . ';dbname=' . $database_name . ';charset=' . $charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $database_user, $database_password, $options);
            } catch (PDOException $e) {
                // todo 20190729 Log this instead of throw error if fails
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
    }

    protected function log()
    {
        if ($this->sql_log_to_file) {
//            $arg_list = func_get_args();
//            $file = Config::get('path.storage.log') . 'sql.log';
//            $datetime = Timezone::databaseDateFormat();
//            $line = $datetime . TAB . $sql . $save_binds . RET;
//            file_put_contents($file, , FILE_APPEND | LOCK_EX);
        }
    }

    public function prepare($sql, $bind = [])
    {
//        $this->log($sql, $bind);
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt;
    }

    public function count($sql, $bind = []): int
    {
        $stmt = $this->prepare($sql, $bind);
        return $stmt->rowCount();
    }

    public function getRow($sql, $bind = [])
    {
        $stmt = $this->prepare($sql, $bind);
        return $stmt->fetch();
    }

    public function getAll($sql, $bind = [])
    {
        $stmt = $this->prepare($sql, $bind);
        return $stmt->fetchAll();
    }

    /**
     * @throws Exception
     */
    public function insert(string $table, array $binds)
    {
//        if (count($binds) === 0) {
//            throw new Exception('Binds empty for insert');
//        }
//
//        $sql = 'INSERT INTO ' . $table . ' SET ';
//        $sql_binds = [];
//
//        foreach ($binds as $field => $value) {
//            $sql_binds[] = $field . ' = :' . $field;
//        }
//
//        // todo 4/2/22 add this back in for created_at, updated_at, deleted_at if soft_delete set
////        $tm = time();
////        $name = 'created_at';
////        $bind[$name] = TimeZone::dateFormatDB($tm);
//
//        $sql .= implode(',', $sql_binds);
//
//        try {
//            $this->getRow($sql);
//            return (int)self::$this->pdo->lastInsertId();
//        } catch (PDOException $e) {
//            // @todo 20190729 do more with this sql error
//            dd($e);
//        }
    }

    public function update()
    {

    }

    public function restore()
    {

    }

    public function delete()
    {

    }

    public function permanentDelete()
    {

    }

    public function tableExists($table): bool
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $count = $this->count($sql);
        return (bool)$count;
    }
}