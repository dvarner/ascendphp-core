<?php namespace Ascend;

use Ascend\BootStrap as BS;

class Database
{

    // private static $db = null;
    // private $bindStore = [];
    private $db = null;
    private $sql = [
        'binds' => [],
        'select' => '',
        'table' => '',
        'join' => [],
        'where' => [],
        'string' => '',
    ];
    private $lastSQL = [];

    public function __construct()
    {
        try {
            $this->db = BS::getDBPDO(); // new DBPDO(); //$this->_config, $this->_class);
        } catch (Exception $e) {
            die('DB Error Connect: ' . $e->getMessage() . PHP_EOL);
        }
    }

    // *** static starts for chaining *** //
    public static function table($table)
    {
        $db = BS::getDB();
        $db->clearSQLStore();
        // $db->table = $table; // remove? no longer used?
        $db->sql['table'] = $table;
        return $db;
    }

    // *** Middle chain-rz
    public function select($select = "*") {
        $this->sql['select'] = 'SELECT ' . $select . ' FROM ';
        return $this;
    }
    public function join($tableA, $fieldA, $tableB, $fieldB) {
        $this->sql['join'][] = ' JOIN ' . $tableB . ' ON ' . $tableA . '.' . $fieldA . ' = ' . $tableB . '.' . $fieldB;
        return $this;
    }
    public function leftJoin($tableA, $fieldA, $tableB, $fieldB) {
        $this->sql['join'][] = ' LEFT JOIN ' . $tableB . ' ON ' . $tableA . '.' . $fieldA . ' = ' . $tableB . '.' . $fieldB;
        return $this;
    }
    public function where($id, $expression, $value = null)
    {
        // $id = 'deleted_at'; $exp = 'is'; $v = 'null';
        // $id = 'deleted_at'; $exp = 'is not'; $v = 'null';
        // @todo setup = null to change to is null and != null to is not null; idk if i want to; make cod'rs lazy
        if ($value == 'null' && !in_array($expression, ['is', 'is not'])) {
            var_dump(debug_backtrace());
            trigger_error('SQL Warning: Query has a null set to something other than is or is not!', E_USER_ERROR);
        }
        if (is_numeric($value) || $value == 'null') {
            $this->sql['where'][] = $id . ' ' . $expression . ' ' . $value;
        } else {
            $this->sql['where'][] = $id . ' ' . $expression . " '" . $value . "'";
        }

        return $this;
    }
    public function groupBy($field)
    {
        $this->sql['groupBy'][] = ' GROUP BY ' . $field;
        return $this;
    }
    public function orderBy($field, $direction = 'asc')
    {
        $this->sql['orderBy'][] = ' ORDER BY ' . $field . ' ' . $direction;
        return $this;
    }

    // *** end of chaining *** //
    public function get($keyAsId = true)
    {
        $this->runQueryAndBind();
        $row = $this->db->resultset($keyAsId);
        return $row;
    }
    public function first()
    {
        $this->setSQLLimit(1);
        $this->runQueryAndBind();
        $row = $this->db->resultset(false);
        $row = isset($row[0]) ? $row[0] : [];
        return $row;
    }

    // *** other useful functions *** //
    public function insert($table, $fieldValues)
    {
        $this->clearSQLStore();

        $fields = '';
        $values = '';

        // *** Build insert
        foreach ($fieldValues AS $name => $value) {
            $fields .= ($fields == '' ? '' : ', ') . '`' . $name . '`';
            $values .= ($values == '' ? '' : ', ') . ':' . $name;
            unset($name, $value);
        }
        $this->sql['string'] = 'INSERT INTO ' . $table .
            ' (' . $fields . ') VALUES (' . $values . ')';

        $this->sql['table'] = $table;
        $this->sql['binds'] = $fieldValues;

        // $this->setLastSQL();

        // $this->combineSQLIntoSQLString();
        $this->runQuery();
        $this->setLastSQL();
        $this->loadBinds();
        $this->db->execute();

        // *** Get inserted id
        $insert_id = $this->db->lastInsertId();
        return $insert_id;
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

    // *** helper functions for cleaner code *** //
    protected function resetSQLString() {
        $this->sql['string'] = '';
    }
    protected function setSQLStringSelectDefaultIfDoesNotExist() {
        if (!isset($this->sql['select'])) {
            $this->sql['select'] = 'SELECT * FROM';
        }
    }
    protected function appendSQLSelectToSQLString() {
        $this->sql['string'].= $this->sql['select'];
    }
    protected function appendSQLTableToSQLString() {
        $this->sql['string'].= ' ' . $this->sql['table'];
    }
    protected function appendSQLJoinToSQLString() {
        if (isset($this->sql['join'])) {
            $this->sql['string'].= implode(' ', $this->sql['join']);
        }
    }
    protected function appendSQLWhereToSQLString() {
        if (isset($this->sql['where'])) {
            $whereSQL = '';
            foreach ($this->sql['where'] AS $where) {
                $whereSQL .= ($whereSQL == '' ? '' : ' && ') . $where;
            }
            $this->sql['string'].= ' WHERE ' . $whereSQL;
        }
    }
    protected function appendSQLGroupByToSQLString() {
        if (isset($this->sql['groupBy'])) {
            $sql = '';
            foreach ($this->sql['groupBy'] AS $v) {
                $sql .= ($sql == '' ? '' : ', ') . $v;
            }
            $this->sql['string'].= ' ' . $sql;
        }
    }
    protected function appendSQLOrderByToSQLString() {
        if (isset($this->sql['orderBy'])) {
            $sql = '';
            foreach ($this->sql['orderBy'] AS $v) {
                $sql .= ($sql == '' ? '' : ', ') . $v;
            }
            $this->sql['string'].= ' ' . $sql;
        }
    }
    protected function setSQLLimit($limit)
    {
        $this->sql['limit'] = $limit;
    }
    protected function appendSQLLimitToSQLString() {
        $this->sql['string'].= ' ' . (isset($this->sql['limit']) ? 'LIMIT ' . $this->sql['limit'] : '');
    }
    protected function combineSQLIntoSQLString() {
        $this->resetSQLString();
        $this->setSQLStringSelectDefaultIfDoesNotExist();
        $this->appendSQLSelectToSQLString();
        $this->appendSQLTableToSQLString();
        $this->appendSQLJoinToSQLString();
        $this->appendSQLWhereToSQLString();
        $this->appendSQLGroupByToSQLString();
        $this->appendSQLOrderByToSQLString();
        $this->appendSQLLimitToSQLString();
    }
    protected function runQueryAndBind()
    {
        $this->combineSQLIntoSQLString();
        $this->runQuery();
        $this->setLastSQL();
        $this->loadBinds();
    }
    protected function clearSQLStore() {
        $this->sql = [];
    }
    protected function runQuery() {
        $this->db->query($this->sql['string']);
    }
    protected function setLastSQL()
    {
        $this->lastSQL = $this->sql;
    }
    protected function loadBinds() {
        if (isset($this->sql['binds']) && is_array($this->sql['binds']) && count($this->sql['binds']) > 0){
            foreach ($this->sql['binds'] AS $field => $value) {
                $this->db->bind(':' . $field, $value);
            }
        }
    }

    // *** displays out last sql statement *** //
    public static function getLastSQL()
    {
        $db = BS::getDB();
        // return $db->lastSQL['string'];
        return $db->interpolateQuery($db->lastSQL['string'], isset($db->lastSQL['bind']) ? $db->lastSQL['bind'] : []);
    }
    public function getLastSQLString() {
        // return $this->lastSQL['string'];
        return $this->interpolateQuery($this->lastSQL['string'], isset($this->lastSQL['bind']) ? $this->lastSQL['bind'] : []);

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

    ///////////////////////////////
    /*
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
    */
}
/*

// comment out __construct() to test the below way without the framework
BS::initDBPDO(); // <-- user/pass in here
BS::initDB();

$rows = Database::table('users')->get();
echo count($rows) . ': ' . Database::getLastSQL() . PHP_EOL; // var_dump($rows);

$row = Database::table('users')->first();
echo count($row) . ': ' . Database::getLastSQL() . PHP_EOL; // var_dump($row);

$rows = Database::table('users')->where('id', '=', 8)->get();
echo count($rows) . ': ' . Database::getLastSQL() . PHP_EOL; // var_dump($rows);
$d = new Database;
$d->insert('users', ['user' => 'tetest2st']);
echo 'insert: ' . $d->getLastSQLString() . PHP_EOL; // var_dump($rows);

$rows = Database::table('users')->where('created_at', 'is', 'null')->get();
echo count($rows) . ': ' . Database::getLastSQL() . PHP_EOL; // var_dump($rows);

$rows = Database::table('users')->where('created_at', 'is not', 'null')->get();
echo count($rows) . ': ' . Database::getLastSQL() . PHP_EOL; // var_dump($rows);

$rows = Database::table('users')
    ->select('users_data.*')
    ->join('users', 'id', 'users_data', 'user_id')
    ->where('users.created_at', 'is not', 'null')
    ->get();
echo count($rows) . ': ' . Database::getLastSQL() . PHP_EOL; // var_dump($rows);
*/