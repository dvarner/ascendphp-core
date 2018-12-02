<?php namespace Ascend\Core;

use Ascend\Core\Bootstrap;
use Ascend\Core\Request;
use Ascend\Core\Feature\Validation;

class Model
{

    protected $db;
    protected $table;
    protected static $dbStatic;

    /**
     * Non static functions below
     */

    public function __construct()
    {
        $this->db = Bootstrap::getDBPDO();

        if (isset($this->fillable) && is_array($this->fillable) && count($this->fillable) > 0) {
            foreach ($this->fillable AS $field) {
                $this->{$field} = null;
            }
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    public function createTable()
    {

        $fields = $this->getStructure();

        $sqlFields = implode(', ', $fields);

        // @todo update this in framework; added default and COLLATE
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$this->table}` ({$sqlFields})
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci
        ";

        $this->db->query($sql);
        $this->db->execute();

        $this->loadSeed();
    }

    public function loadSeed()
    {
        if (isset($this->seed) && is_array($this->seed) && count($this->seed) > 0) {
            foreach ($this->seed AS $k => $fields) {
                Bootstrap::getDB()->insert($this->table, $fields);
            }
        }
    }

    public function getFillable() {
        return $this->fillable;
    }

    public function getStructure()
    {
        $fields = array();
        foreach ($this->structure as $field => $type) {
            $fields[$field] = '`' . $field . '` ' . $type;
        }

        if (!isset($this->timestamps)){ $this->timestamps = true; }
        if ($this->timestamps === true) {
            $fields['created_at'] = 'created_at timestamp null default null';
            $fields['updated_at'] = 'updated_at timestamp null default null';
        }

        if (!isset($this->softDelete)){ $this->softDelete = true; }
        if ($this->softDelete === true) {
            $fields['deleted_at'] = 'deleted_at timestamp null default null';
        }

        return $fields;
    }

    public function getRenameMap() {
        return (isset($this->renameMap)?$this->renameMap:[]);
    }

    public static function insert($fields)
    {
        $fields['created_at'] = date('Y-m-d H:i:s', time());
        return Bootstrap::getDB()->insert(self::getTableName(), $fields);
    }

    // @todo move into framework
    public static function update($fields, $where) {
        $fields['updated_at'] = date('Y-m-d H:i:s', time());
        return Bootstrap::getDB()->update(self::getTableName(), $fields, $where);
    }

    /**
     * Inserts or Updates
     */
    public function save()
    {
        $modelName = $this->getCalledModelName();
        $tableName = $this->getTable();
        /*
        $modelNamespace = '\\' . get_called_class();
        $e = explode('\\', $modelNamespace);
        $modelName = strtolower($e[count($e) - 1]);
        $tableName = $this->getTable();
        */

        if (!isset($this->guarded)){ $this->guarded = []; }
        $allowed = array_merge($this->fillable, $this->guarded, array('id'));

        $fields = array();
        foreach ($allowed AS $field) {
            if (isset($this->{$field})) {
                $fields[$field] = $this->{$field};
            }
        }

        // @todo Switch Database::tables to [model]
        if (!isset($fields['id'])) {
            $fields['created_at'] = date('Y-m-d H:i:s', time());
            $insertId = Bootstrap::getDB()->insert($tableName, $fields);
            $id = $insertId;
            /*
            $redirectUri = '/' . $modelNameSingular . '/' . $id . '/edit';
            header('location: ' . $redirectUri);
            exit;
            */
        } else {
            // $exist = Database::table($tableName)->where($tableName, 'id', '=', $fields['id'])->first();
            $modelName = '\\App\\Model\\'.$modelName;
            $exist = $modelName::where('id', '=', $fields['id'])->first();
            $wheres['id'] = $fields['id'];
            $id = $fields['id'];
            unset($fields['id']);
            $fields['updated_at'] = date('Y-m-d H:i:s', time());
            Bootstrap::getDB()->update($tableName, $fields, $wheres);
            // Bootstrap::getDB() // @todo update above to this
        }

        return $id;

        /*
        $url = $_SERVER['HTTP_REFERER'];
        $uri = parse_url($url, PHP_URL_PATH);
        $uri = str_replace('create', 'edit', $uri);

        header("location: {$uri}?id={$insertId}");
        */
    }

    public function methodDelete($id) {
        $this->delete($id);
    }

    public function delete($id)
    {

        $modelNamespace = '\\' . get_called_class();
        $e = explode('\\', $modelNamespace);
        $modelName = strtolower($e[count($e) - 1]);
        $tableName = $this->getTable();

        Bootstrap::getDB()->deleteSoft($tableName, $id);
        // Bootstrap::getDB() // @todo update above to softDelete

        return true;
    }

    public function valid($addValidations)
    {
        // $validations = array();
        foreach ($addValidations AS $field => $newValidations) {
            foreach ($newValidations AS $special => $newValidation) {
                if (isset($this->validation[$field]) && is_array($newValidation)) {
                    if (!in_array($newValidation, $this->validation[$field])) {
                        $validations[$field] = $this->validation[$field];
                        $validations[$field][$special] = $newValidation;
                    }
                } else {
                    $validations[$field][$special] = $newValidation;
                }
            }
        }

        $valid = new Validation;
        $r = $valid->valid($validations);
        return $r;
    }

    /**
     * Static functions below
     */

    public static function getTableName()
    {
        // This function is required for static functions to work
        $model = get_called_class();
        // echo 'model: '.$model.'<br />';
        $n = new $model;
        return $n->getTable();
    }

    public static function all()
    {
        $table = self::getTableName();

        $sql = "SELECT * FROM {$table} WHERE deleted_at IS NULL";

        /*
        $request = new Request;
        if ($request->input('search')) {

        }
        */

        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        $db->execute();
        return $db->resultset();
    }

    public static function get()
    {
        $dbStatic = self::$dbStatic->get();
        self::$dbStatic = null;
        return $dbStatic;
    }

    public static function first()
    {
        $dbStatic = self::$dbStatic->first();
        self::$dbStatic = null;
        return $dbStatic;
    }

    public static function where($first, $expression, $second)
    {
        $table = self::getTableName();
        return Database::table($table)->where($first, $expression, $second);
    }

    public function andWhere($first, $expression, $second)
    {
        $table = self::getTableName();
        return Database::table($table)->where($first, $expression, $second);
    }

    public function getCalledModelName() {
        $modelNamespace = '\\' . get_called_class();
        $e = explode('\\', $modelNamespace);
        // $modelName = strtolower($e[count($e) - 1]);
        $modelName = $e[count($e) - 1];
        return $modelName;
    }

    public static function getById($id) {
        $table = self::getTableName();

        $sql = "SELECT * FROM {$table}";
        $sql.= " WHERE ";
        $sql.= "id = {$id}";
        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        $db->execute();
        $row = $db->resultset(false);
        $row = isset($row[0]) ? $row[0] : null;

        return $row;
    }

    public static function initStaticDB($db)
    {
        self::$staticDBh = $db;
    }

    public static function queryOne($sql, $bind) {
        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        foreach ($bind AS $name => $value) {
            $db->bind(':' . $name, $value);
            unset($name, $value);
        }
        $db->execute();
        return $db->single();
    }

    public static function queryMany($sql, $bind) {
        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        foreach ($bind AS $name => $value) {
            $db->bind(':' . $name, $value);
            unset($name, $value);
        }
        $db->execute();
        return $db->resultset();
    }

    public static function transactionStart()
    {
        $db = Bootstrap::getDBPDO();
        return $db->transactionStart();
    }

    public static function transactionCommit()
    {
        $db = Bootstrap::getDBPDO();
        return $db->transactionCommit();
    }

    public static function transactionCancel()
    {
        $db = Bootstrap::getDBPDO();
        return $db->transactionCancel();
    }

    /*
    public function getFields() {
        return $this->fields;
    }

    public function getForeign() {
        return (isset($this->foreign)?$this->foreign:array());
    }

    public function getValidations() {
        return $this->validations;
    }

    public function getSeeder() {
        if(isset($this->seeder)) {
            return $this->seeder;
        }else{
            return array();
        }
    }
	*/
}