<?php namespace Ascend;

use Ascend\BootStrap as BS;
use \PDO as PDO;

class DatabasePDO
{

    public $storeQueries = array();
    public $storeConnections = array();
    public $storeConnectionName = 'default';
    public $dbh = null;

    public function __construct()
    {
        if (BS::getConfig('db')) {
            foreach (BS::getConfig('db') AS $k => $v) {
                $this->connect($v);
                unset($k, $v);
            }
            $this->setConnection('default');
        } else {
            trigger_error('Failed to find database connections in config!', E_USER_ERROR);
        }
    }

    public function connect($arr)
    {
        if (false !== strpos($arr['hostname'], ':')) {
            list($host, $port) = explode(':', $arr['hostname']);
            $arr['hostname'] = $host . ';port=' . $port;
            unset($host, $port);
        }
        $dsn = $arr['type'] . ':host=' . $arr['hostname'] . ';dbname=' . $arr['database'];
        if (!isset($arr['options'])) {
            $arr['options'] = array(
                PDO::ATTR_PERSISTENT => true, // Sets to persistent. Increase performance by checking if already connected.
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION // This allows handling errors gracefully
            );
        }
        try {
            $this->storeConnections[$arr['name']] = new PDO($dsn, $arr['username'], $arr['password'], $arr['options']);
        } catch (PDOException $e) {
            die('pdo connection error: ' . $e->getMessage());
        }
    }

    public function setConnection($name)
    {
        if (isset($this->storeConnections[$name])) {
            $this->dbh = $this->storeConnections[$name];
        } else {
            $name = current(array_keys($this->storeConnections));
            $this->dbh = $this->storeConnections[$name];
        }
        $this->storeConnectionName = $name;
    }

    public function query($query)
    {
        $this->query = $query;
        // echo 'sql: '.$query.'<br />'.RET;
        $this->storeQueries[] = $query;
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null)
    {
        // echo 'bind: '.$param.' = '.$value.'<br />'.RET;
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->error_store['bind'][] = array('param' => $param, 'value' => $value);
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        try {
            $r = $this->stmt->execute();
            unset($this->error_store);
        } catch (Exception $ex) {
            echo '<h3>Error:</h3>';
            echo 'SQL: ' . $this->query . '<br />';
            echo '<pre style="white-space: pre-wrap;">';
            echo $ex;
            echo '<hr />';
            print_r($this->error_store);
            echo '</pre>';
            exit;
        }
        return $r;
    }

    public function resultset($keyAsId = true)
    {
        $this->execute();
        $arr = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($keyAsId === true) {
            if (isset($arr[0]['id'])) {
                foreach ($arr AS $k => $v) {
                    $narr[$v['id']] = $v;
                }
            } else {
                $narr = $arr;
            }
        } else {
            $narr = $arr;
        }
        return $narr;
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function transactionStart()
    {
        return $this->dbh->beginTransaction();
    }

    public function transactionEnd()
    {
        return $this->dbh->commit();
    }

    public function transactionCancel()
    {
        return $this->dbh->rollBack();
    }

    public function debugDumpParams()
    {
        return $this->stmt->debugDumpParams();
    }

    public function lastQuery()
    {
        $cnt = count($this->storeQueries);
        return $this->storeQueries[$cnt - 1];
    }

    public function tableExists($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $this->query($sql);
        $result = $this->resultset();
        return count($result) > 0 ? true : false;
    }
}