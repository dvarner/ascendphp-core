<?php namespace Ascend\Core;

class Database
{
    protected static $pdo = null;
    private static $log_queries = false;

    public static function logQueries()
    {
        self::$log_queries = true;
    }

    public static function connect()
    {
        if (DB_LOG_QUERIES) self::logQueries();
        $charset = 'utf8mb4';
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            self::$pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // @todo 20190729 Log this instead of throw error if fails in prod
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function count($sql, $bind = [])
    {
        self::log($sql, $bind);
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->rowCount();
    }

    public static function query($sql)
    {
        self::log($sql);
        return self::$pdo->query($sql);
    }

    public static function one($sql, $bind = [])
    {
        self::log($sql, $bind);
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetch();
    }

    // $key = id field from the table will be the key of the array
    //        , if you do this make sure there are no dups... it will override
    public static function many($sql, $bind = [], $key = false)
    {
        self::log($sql, $bind);
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($bind);
        if ($key === false) {
            return $stmt->fetchAll();
        } else {
            $result = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $result[$row[$key]] = $row;
            }
            return $result;
        }
    }

    public static function insert($table, $bind)
    {
        $fields = '';
        $values = '';

        // *** Build insert
        foreach ($bind AS $name => $value) {
            $fields .= ($fields == '' ? '' : ', ') . '`' . $name . '`';
            $values .= ($values == '' ? '' : ', ') . ':' . $name;
            unset($name, $value);
        }

        $tm = time();
        $name = 'created_at';
        $bind[$name] = date('Y-m-d H:i:s', $tm);
        $fields .= ($fields == '' ? '' : ', ') . '`' . $name . '`';
        $values .= ($values == '' ? '' : ', ') . ':' . $name;

        $name = 'updated_at';
        $bind[$name] = date('Y-m-d H:i:s', $tm);
        $fields .= ($fields == '' ? '' : ', ') . '`' . $name . '`';
        $values .= ($values == '' ? '' : ', ') . ':' . $name;

        $sql = 'INSERT INTO ' . $table . ' (' . $fields . ') VALUES (' . $values . ')';
        // echo $sql.'<br />';
        // var_dump($bind);

        try {
            self::log($sql, $bind);
            self::$pdo->prepare($sql)->execute($bind);
            return (int)self::$pdo->lastInsertId();
        } catch (\PDOException $e) {
            // @todo 20190729 do more with this sql error
            throw $e;
            /*
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {

                // Take some action if there is a key constraint violation, i.e. duplicate name
            } else {
                throw $e;
            }
            */
        }
    }

    public static function update($table, $bind, $where)
    {

        $sql = 'UPDATE ' . $table . ' SET ';
        $sqlu = '';
        foreach ($bind AS $name => $value) {
            $sqlu .= ($sqlu == '' ? '' : ', ');
            $sqlu .= $name . ' = :' . $name;
            unset($name, $value);
        }
        $sqlu .= ' WHERE ';
        $sqlw = '';
        foreach ($where AS $name => $value) {
            $sqlw .= ($sqlw == '' ? '' : ' AND ');
            $sqlw .= $name . ' = :' . $name;
            unset($name, $value);
        }
        $sqlu .= $sqlw;
        $sql .= $sqlu;
        $bind = array_merge($bind, $where);

        try {
            self::log($sql, $bind);
            self::$pdo->prepare($sql)->execute($bind);
        } catch (\PDOException $e) {
            // @todo 20190729 do more with this sql error
            throw $e;
        }
    }

    public static function delete($table, $where)
    {

        $deleted_at = date('Y-m-d H:i:s', time());
        $sql = "UPDATE " . $table . " SET deleted_at = :deleted_at WHERE ";
        $sqlw = '';
        foreach ($where AS $name => $value) {
            $sqlw .= ($sqlw == '' ? '' : ' AND ');
            $sqlw .= $name . ' = :' . $name;
            unset($name, $value);
        }
        $sql .= $sqlw;
        $where['deleted_at'] = $deleted_at;

        try {
            self::log($sql, $where);
            self::$pdo->prepare($sql)->execute($where);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public static function deletePermanently($table, $where)
    {
        $sql = 'DELETE FROM ' . $table . ' WHERE ';
        $sqlw = '';
        foreach ($where AS $name => $value) {
            $sqlw .= ($sqlw == '' ? '' : ' AND ');
            $sqlw .= $name . ' = :' . $name;
            unset($name, $value);
        }
        $sql .= $sqlw;

        if (count($where) > 0) {
            try {
                self::log($sql, $where);
                self::$pdo->prepare($sql)->execute($where);
            } catch (\PDOException $e) {
                throw $e;
            }
        } else {
            // @todo 20190724 Log this or do something different
            die('Cant do this, deletePermanently() without a $where');
        }
    }

    public static function table_exists($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        self::log($sql, []);
        $results = self::$pdo->query($sql);
        return $results->rowCount() > 0 ? true : false;
    }

    public static function log($sql, $binds = [])
    {
        if (self::$log_queries) {
            $save_binds = '';
            if (is_array($binds) && count($binds) > 0) {
                $save_binds = RET . 'BINDS: ' . TAB . json_encode($binds);
            }
            $file = PATH_LOG . 'sql.log';
            $datetime = date('Y-m-d H:i:s');
            file_put_contents($file, $datetime . TAB . $sql . $save_binds . RET, FILE_APPEND | LOCK_EX);

            // Log queries which dont have deleted_at b/c we might need to add it to them
            $pattern = '#^INSERT|UPDATE|DELETE#i';
            preg_match($pattern, $sql, $matches);
            if (isset($matches) && is_array($matches) && count($matches) == 0) {
                $pattern = '#^SELECT.*WHERE.*deleted_at.*$#i';
                preg_match($pattern, $sql, $matches);
                if (isset($matches) && is_array($matches) && count($matches) == 0) {
                    if (false === ($a = strpos($sql, ' id ='))) {
                        $file = PATH_LOG . 'sql.no-deleted-at.log';
                        $datetime = date('Y-m-d H:i:s');
                        file_put_contents($file, $datetime . TAB . $sql . $save_binds . RET, FILE_APPEND | LOCK_EX);
                    }
                }
            }
        }
    }
}

class DB extends Database {}

DB::connect();