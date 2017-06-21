<?php namespace Ascend;

use Ascend\BootStrap as BS;

class Database
{

    // private static $db = null;
    private $bindStore = array();

    public function __construct()
    {
        try {
            $this->db = BS::getDBPDO(); // new DBPDO(); //$this->_config, $this->_class);
        } catch (Exception $e) {
            die('DB Error Connect: ' . $e->getMessage() . RET);
        }
    }

    public static function table($table)
    {
        $db = BS::getDB();
        $db->table = $table;
        return $db;
    }

    public function get($keyAsId = true)
    {
        $this->build();
        $row = $this->db->resultset($keyAsId);
        return $row;
    }

    public function first($keyAsId = true)
    {
        $this->limit = 'LIMIT 1';
        $this->build();
        $row = $this->db->resultset($keyAsId);
        foreach ($row AS $k => $v) {
            if (is_numeric($k)) {
                return $row[$k];
            } else {
                return $row;
            }
            exit;
        }
    }

    private function build()
    {

        $sql = "";
        if (!isset($this->select)) {
            $this->select = 'SELECT * FROM ';
        }
        if (isset($this->select)) {
            $sql .= $this->select;
            unset($this->select);
        }
        if (isset($this->table)) {
            $sql .= $this->table;
            unset($this->table);
        }
        // if (isset($this->join)) { foreach ($this->join AS $v) { $sql.= " " . $v; unset($v); } unset($this->join); }
        if (isset($this->where)) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
            unset($this->where);
        }
        // if (isset($this->extra)) { $sql.= ' ' . implode(' ', $this->extra); unset($this->extra); }
        if (isset($this->orderby)) {
            $sql .= ' ' . $this->orderby;
            unset($this->orderby);
        }
        if (isset($this->limit)) {
            $sql .= ' ' . $this->limit;
            unset($this->limit);
        }

        // echo $sql . ' :line 206' . RET; exit;
        if (!isset($this->bindStore)) {
            $this->bindStore = [];
        }
        $this->db->query($sql);
        $this->lastSQL['sql'] = $sql;
        $this->lastSQL['bind'] = $this->bindStore;
        if (isset($this->bindStore) && is_array($this->bindStore) && count($this->bindStore) > 0) {
            foreach ($this->bindStore AS $v) {
                // echo 'bind: '.$v[0].' | '.$v[1].'<br />'.RET;
                $this->db->bind($v[0], $v[1]);
                unset($v);
            }
            unset($this->bindStore, $this->inc);
        }
    }

    public function insert($table, $arr)
    {
        $this->table = $table;
        if (!isset($this->table)) {
            trigger_error('table() function not set!', E_USER_ERROR);
        }
        $insert_id_arr = array();
        $fields = '';
        $values = '';

        // *** Build insert
        foreach ($arr AS $name => $value) {
            $fields .= ($fields == '' ? '' : ', ') . '`' . $name . '`';
            $values .= ($values == '' ? '' : ', ') . ':' . $name;
            unset($name, $value);
        }
        $sql = 'INSERT INTO ' . $this->table .
            ' (' . $fields . ') VALUES (' . $values . ')';
        $this->db->query($sql);

        // *** Bind fields/values to query
        foreach ($arr AS $name => $value) {
            $this->db->bind(':' . $name, $value);
            unset($name, $value);
        }

        $this->db->execute();
        // *** Get inserted id
        $insert_id = $this->db->lastInsertId();
        unset($v);
        // }
        return $insert_id;
    }

    public function where($table, $id, $expression, $value = null)
    {
        if (!is_null($value)) {
            $this->table = $table;
            $this->inc = (isset($this->inc) ? $this->inc + 1 : 1);
            if ($expression == 'is' && $value == 'null') {
                $this->where[] = $table . '.' . $id . ' is null';
            } elseif ($expression == 'is' && $value == 'not null') {
                $this->where[] = $table . '.' . $id . ' is not null';
            } else {
                $this->where[] = $table . '.' . $id . ' ' . $expression . ' :' . $this->inc;
                $this->bindStore[] = array(':' . $this->inc, $value);
            }
        } else {
            $value = $expression;
            $expression = $id;
            $id = $table;
            $this->inc = (isset($this->inc) ? $this->inc + 1 : 1);
            if ($expression == 'is' && $value == 'null') {
                $this->where[] = $id . ' is null';
            } elseif ($expression == 'is' && $value == 'not null') {
                $this->where[] = $id . ' is not null';
            } else {
                $this->where[] = $id . ' ' . $expression . ' :' . $this->inc;
                $this->bindStore[] = array(':' . $this->inc, $value);
            }
        }
            
        return $this;
    }

    public function update($table, $update, $where)
    {
        $this->table = $table;

        $sql = 'UPDATE ' . $this->table . ' SET ';
        $sqlu = "";
        foreach ($update AS $k => $v) {
            $sqlu .= ($sqlu == '' ? '' : ',') . $k . ' = :' . $k;
        }
        // $sqlu.= ',updated_at = "' . \Carbon\Carbon::now() . '"';
        $sql .= $sqlu;
        $sqlw = "";
        foreach ($where AS $k => $v) {
            $sqlw .= ($sqlw == '' ? '' : ' && ') . $k . ' = :' . $k;
        }
        $sql .= " WHERE " . $sqlw;
        // var_dump($sql,$update, $where); exit;
        $this->db->query($sql);

        // *** Bind fields/values to query
        foreach ($update AS $name => $value) {
            $this->db->bind(':' . $name, $value);
            unset($name, $value);
        }
        foreach ($where AS $name => $value) {
            $this->db->bind(':' . $name, $value);
            unset($name, $value);
        }
        
        // echo $this->interpolateQuery($sql, array_merge($update, $where));exit;

        $this->db->execute();
        // $this->db->debugDumpParams();
    }

    public function delete($table, $id)
    {
        // return $this->deleteSoft($table, $id);

        $sql = 'DELETE FROM ' . $table .
            ' WHERE id = :id';
        $this->db->query($sql);

        // *** Bind fields/values to query
        $this->db->bind(':id', $id);
        $this->db->execute();

        return true;
    }
    
    public function deleteSoft($table, $id) {
        $this->table = $table;
        $update['deleted_at'] = \Carbon\Carbon::now();
        $where['id'] = $id;
        $this->update($table, $update, $where);
    }

    
    public function orderBy($id, $by = 'asc') {
        // die('orderBy Incomplete!');

        if(!isset($this->orderby)){ $this->orderby = ''; }

        $sql = ($this->orderby != '' ? ',' : ' ORDER BY ');
        $sql.= '' .
                $id . ' ' . ($by == 'asc' ? 'asc' : 'desc');
        $this->orderby.= $sql;
        return $this;
    }
    
    public function getLastSQL()
    {
        echo '<pre>';
        var_dump($this->lastSQL);
    }
    public function interpolateQuery($query, $params) {
    $keys = array();
    $values = $params;

    # build a regular expression for each parameter
    foreach ($params as $key => $value) {
        if (is_string($key)) {
            $keys[] = '/:'.$key.'/';
        } else {
            $keys[] = '/[?]/';
        }

        if (is_string($value))
            $values[$key] = "'" . $value . "'";

        if (is_array($value))
            $values[$key] = "'" . implode("','", $value) . "'";

        if (is_null($value))
            $values[$key] = 'NULL';
    }

    $query = preg_replace($keys, $values, $query);

    return $query;
}
    /*
    public function tableExists($table){
        // @todo -user:dvarner -date:12/9/2015 Need to update table to use $this->getPre()
        $sql = "SHOW TABLES LIKE '{$table}'";
        $this->db->query($sql);
        $result = $this->db->resultset();
        return (count($result) > 0?true:false);
    }

    public static function select($sql, $bind = false) {
        self::init();
        self::$db->prepare($sql);
        // $this->lastSQL['sql'] = $sql;
        // $this->lastSQL['bind'] = $bind;

        if(is_array($bind) && count($bind) > 0) {
            foreach ($bind AS $v) {
                self::$db->bind($v[0], $v[1]);
                unset($v);
            }
        }

        $rows = self::$db->execute();

        return $rows;
    }

    /*
    public function getPre() {
        return $this->db->getPre();
    }

    // @todo Make $select accept string or array
    public function select($table, $select = '*') {
        $this->table = $this->getPre() . $table;
        $this->select = "SELECT {$select} FROM " . $this->getPre() . $table;
        return $this;
    }

    public function whereIsNull($table, $id) {
        $this->where[] = $this->getPre() . $table . '.' . $id . ' IS NULL';
        return $this;
    }

    public function whereOr() {
        die('whereOr Incomplete!');
    }

// OR where

    public function whereBetween() {
        die('whereBetween Incomplete!');
    }

// Between these #

    public function whereIn() {
        die('whereIn Incomplete!');
    }

// Within this array

    public function whereExist() {
        die('whereExist Incomplete!');
    }

    public function groupBy($field, $id) {
        // die('groupBy Incomplete!');
        $this->extra[] = 'GROUP BY ' . $this->getPre() . $field . '.' . $id;
        return $this;
    }

    public function having() {
        die('having Incomplete!');
    }

    public function limit($start, $per = false) {
        $this->limit = 'LIMIT ' . $start . (is_numeric($per) ? ',' . $per : '');
        // die('limit Incomplete!');
        return $this;
    }

    public function join($table, $id, $by, $join_id, $join_table = false) {
        if ($join_table === false) {
            $join_table = $this->table;
        }else{
            $exp = explode(' ',$join_table);
            if(count($exp) == 2){
                $join_alias = $exp[1];
            }else{
                $join_alias = $this->getPre().$join_table;
            }
        }
        $exp = explode(' ',$table);
        if(count($exp) == 2){
            $alias = $exp[1];
        }else{
            $alias = $this->getPre().$table;
        }
        // if(!isset($this->join)){ $this->join = array(); }
        $this->join[] = 'JOIN ' . $this->getPre() . $table . ' ON ' .
            $alias . '.' . $id . ' ' . $by . ' ' . $join_id;
           // $alias . '.' . $id . ' ' . $by . ' ' . $join_alias . '.' . $join_id;
        $last = count($this->join) - 1;
// echo 'join: '.$this->join[$last]."\r\n";
        //$this->table = $table;
        return $this;
    }

    public function leftJoin($table, $id, $by, $join_id, $join_table) {
        return $this->joinLeft($table, $id, $by, $join_id, $join_table);
    }
    public function joinLeft($table, $id, $by, $join_id, $join_table) {
        $this->join[] = 'LEFT JOIN ' .
                $this->getPre() . $table . ' ON ' . $this->getPre() . $table . '.' . $id . ' ' .
                $by . ' ' . $this->getPre() . $join_table . '.' . $join_id;
        return $this;
    }

    public function count() {
        die('count Incomplete!');
    }

    public function min() {
        die('min Incomplete!');
    }

    public function max() {
        die('max Incomplete!');
    }

    public function avg() {
        die('avg Incomplete!');
    }

    public function sum() {
        die('sum Incomplete!');
    }

    public function insert($table, $arr) {
        $this->table = $table;
        if (!isset($this->table)) {
            die('table() function not set!');
        }
        $insert_id_arr = array();
        $fields = '';
        $values = '';

        // *** Build insert
        foreach ($arr AS $name => $value) {
            $fields.= ($fields == '' ? '' : ', ') . $name;
            $values.= ($values == '' ? '' : ', ') . ':' . $name;
            unset($name, $value);
        }
        $sql = 'INSERT INTO ' . $this->getPre() . $this->table .
            ' (' . $fields . ') VALUES (' . $values . ')';
        $this->db->query($sql);

        // *** Bind fields/values to query
        foreach ($arr AS $name => $value) {
            $this->db->bind(':' . $name, $value);
            unset($name, $value);
        }

        $this->db->execute();
        // *** Get inserted id
        $insert_id = $this->db->lastInsertId();
        unset($v);
        // }
        return $insert_id;
    }

    public function update($table, $update, $where) {
        $this->table = $table;

        $sql = 'UPDATE ' . $this->getPre() . $this->table . ' SET ';
        $sqlu = "";
        foreach($update AS $k => $v){
            $sqlu.= ($sqlu == ''?'':',') . $k.' = :'.$k;
        }
        $sqlu.= ',updated_at = "' . \Carbon\Carbon::now() . '"';
        $sql.= $sqlu;
        $sqlw = "";
        foreach($where AS $k => $v){
            $sqlw.= ($sqlw == ''?'':' && ') . $k.' = :'.$k;
        }
        $sql.= " WHERE ".$sqlw;
        // var_dump($sql,$update, $where);
        // exit;
        $this->db->query($sql);

        // *** Bind fields/values to query
        foreach ($update AS $name => $value) {
            $this->db->bind(':' . $name, $value);
            unset($name, $value);
        }
        foreach ($where AS $name => $value) {
            $this->db->bind(':' . $name, $value);
            unset($name, $value);
        }

        $this->db->execute();
    }
    public function deleteHard($table, $id) {
        $this->table = $table;
        if (!isset($this->table)) {
            die('table() function not set!');
        }

        $sql = 'DELETE FROM ' . $this->getPre() . $this->table .
            ' WHERE id = :id';
        $this->db->query($sql);

        // *** Bind fields/values to query
        $this->db->bind(':id', $id);
        $this->db->execute();

        // @todo -user:dvarner -date:01/11/2016 Add a return for success or failed
    }

    public function truncate() {
        // *** @todo Copy get() but do truncate
    }

    public function getTables() {
        $sql = "SHOW TABLES";
        $this->db->query($sql);
        $arr = $this->db->resultset();
        if (isset($arr) && is_array($arr) && count($arr) > 0) {
            return $arr;
        } else {
            return false; // $arr[] = 'No Tables';
        }
    }

    public function getFieldsColumns($table) {
        $sql = "SHOW COLUMNS from " . $this->getPre() . '_' . $table;
        $this->db->query($sql);
        return $this->db->resultset();
    }

    public function drop($table) {
        $this->db->query("DROP TABLE :tbl");
        $this->db->bind(':tbl', $this->getPre() . $table);
        $this->db->execute();
    }

    public function create($file, $model){

        // @todo -user:dvarner -date:3/27/2016 Setup a way to pass in foreign keys
        require_once PATH_MODULES . $file . '/models/' . $model . '.php';
        $dyn = '\\Ascend\\Modules\\' . $file . '\\Model\\' . $model;
        $class = new $dyn;

        $pre = $this->getPre();

        if($model != 'Install'){
            $this->db->query("SELECT id FROM {$pre}install WHERE model = '{$model}'");
            $result = $this->db->resultset();
        }else{
            $result = [];
        }

        if(count($result) == 0){
            $table = $class->getTable();
            echo 'Install: ' . $table . '<br />';

            $sql = "CREATE TABLE IF NOT EXISTS `{$pre}{$table}` (";
                //"`id` int(10) unsigned NOT NULL AUTO_INCREMENT,";

                $fields = $class->getFields();
                foreach($fields AS $field => $attr){
                    $sql.= $field . ' ' . $attr . ',';
                }

                $sql.= "created_at timestamp null default current_timestamp,";
                $sql.= "updated_at timestamp null default null,";
                $sql.= "deleted_at timestamp null default null,";
                $sql.= " PRIMARY KEY (`id`)";

                $foreign = $class->getForeign();
                foreach($foreign AS $local => $join){
                    $sql.= ', FOREIGN KEY (' . $local . ') REFERENCES ' . $join;
//                    $e = explode('(',$join);
//                    $tbl = substr($e[1],0,-1);
                }

            $sql.= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";

            // echo $sql.'<br />';

            $this->db->query($sql);
            $this->db->execute();

            if(method_exists($class, 'getSeeder')){
                $setSeeder = 0;

                $seeder = $class->getSeeder();
                if(count($seeder) > 0){
                    if(count($result) == 0){
                        // echo 'Run Seeder<br />';
                        foreach($seeder AS $rows){
                            $this->insert($table, $rows);
                        }
                        $setSeeder = 1;
                    }
                }
                $sql = "INSERT INTO {$pre}install";
                $sql.= " SET model='{$model}'";
                $sql.= ", seeder={$setSeeder}";
                $this->db->query($sql);
                $this->db->execute();
            }
        }
    }

    public function install() {
    }

    public function uninstall() {
    }

    public function checkConnection() {
        // @todo -user:dvarner -date:11/26/2015 Update this to allow different languages and better error
        $data = array(
            'success' => true,
            'msg' => 'Database connection successful'
        );
        return json_encode($data,true);
    }

    private function zcache($sec) {
        // *** @todo setup a way to cache queries for x seconds.
        // Instead of remember call cache.
        // Make cache use memcache.
        // $users = DB::table('users')->remember(10)->get();
        // $users = DB::table('users')->cacheTags(array('people', 'authors'))->remember(10)->get();
    }

    public function zinteger($size = 10) {
        // part of the chain building idea for creating tables
        $this->fields_arr[].= "int({$size})";
        // die('limit Incomplete!');
        return $this;
    }

    private function zunsigned($field,$size=10) {
        // part of the chain building idea for creating tables
        $cnt = count($this->fields_arr);
        $this->fields_arr[$cnt-1].= " unsigned";
        // die('limit Incomplete!');
        return $this;
    }

    private function zautoIncrement($field,$size=10) {
        // part of the chain building idea for creating tables
        $cnt = count($this->fields_arr);
        $this->fields_arr[$cnt-1].= " auto_increment";
        // die('limit Incomplete!');
        return $this;
    }

    private function zcreateOld($table, $arr) {
        // old concept remove for chaining
        $sql = "CREATE TABLE IF NOT EXIST";
        $sql.= $table;
        $sql.= "(";
        $sqlf = "";
        foreach ($arr AS $k => $v) {
            $u = (isset($v['unsigned']) ? ' unsigned' : '');
            $n = (isset($v['null']) ? ' not null' : '');
            $field = $k . ' ' . $v['type'] . $u . $n;
            $sqlf.= ($sqlf == '' ? '' : ',');
            $sqlf.= ($v['type'] != '' ? $field : '');
            unset($k, $v);
        }
        $sql.= $sqlf;
        $sql.= ")";
        // $this->db->query("DROP TABLE :tbl");
        // $this->db->bind(':tbl',$this->getPre().$table);
        // $this->db->execute();
    }


    public function zalter($table) {
        $sql = "ALTER TABLE " . $this->getPre() . $table;
    }

    public function zattr($attr, $field, $unsigned = false, $null) {
        $arr = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'float',
            'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'mediumblob', 'blob', 'longblog', 'enum', 'set');
        // $set: if number then 1 or 0. 1 = unsigned
        // $set: if string then length
        $sql = $field . ' ' . $attr;
        if (false !== array_search($attr, array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'float'))) {
            $sql.= ' ' . ($unsigned == false ? '' : ' unsigned') . ' not null,';
        } else {
            if ($set > 0) {
                $sql.= '(' . $set . ')';
            } else {
                die('Failed to get # length for creating field in table. Table: "' . $this->table . '" set field: "' . $field . '"');
            }
        }
        $this->attr[] = $sql;
        return $this;
    }

    public function zcreate($table) {
        $this->table = $this->getPre() . $table;
        $this->fields_arr = array();
        $this->create = "CREATE TABLE IF NOT EXIST :tbl(";
        $this->bindstore[] = array(':tbl', $this->table);
        return $this;
    }

    public function zcreateExecute() {
        $this->create .= ' id int unsigned auto_increment, PRIMARY KEY (`id`)';
        $this->create .= ')';
        $sql = $this->create;
        echo 'Exec: '.$sql.'<br />';
        try {
            $this->db->query($sql);
            if (isset($this->bindStore) && is_array($this->bindStore) && count($this->bindStore) > 0) {
                foreach ($this->bindStore AS $v) {
                    // echo 'bind: '.$v[0].' | '.$v[1].'<br />'.RET;
                    $this->db->bind($v[0], $v[1]);
                    unset($v);
                }
                unset($this->bindStore);
            }
            $this->db->execute();
        } catch (Exception $e) {
            die('PDO > Create error: ' . $e->error());
        }
    }

    public function zincrement($field){
        $this->fields_arr[] = "`{$field}` int(10) unsigned NOT NULL AUTO_INCREMENT";
        return $this;
    }
    */
}