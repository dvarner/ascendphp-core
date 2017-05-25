<?php namespace Ascend;

use Ascend\Bootstrap as BS;
use Ascend\Request;
use Ascend\Feature\Validation;

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
        $this->db = BS::getDBPDO();

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

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` ({$sqlFields}) ENGINE=InnoDB";

        $this->db->query($sql);
        $this->db->execute();

        $this->loadSeed();
    }

    public function loadSeed()
    {
        if (isset($this->seed) && is_array($this->seed) && count($this->seed) > 0) {
            foreach ($this->seed AS $k => $fields) {
                BS::getDB()->insert($this->table, $fields);
            }
        }
    }

    public function getStructure()
    {
        $fields = array();
        foreach ($this->structure as $field => $type) {
            $fields[$field] = '`' . $field . '` ' . $type;
        }

        if (isset($this->timestamps)) {
            $fields['created_at'] = 'created_at timestamp not null';
            $fields['updated_at'] = 'updated_at timestamp not null';
            $fields['deleted_at'] = 'deleted_at timestamp not null';
        }
        /*
        use SoftDeletes; <--- find this in model
        if (isset($this->dates['deleted_at'])) {

        }
        */
        return $fields;
    }

    public static function insert($fields)
    {
        BS::getDB()->insert(self::getTableName(), $fields);
    }

    /**
     * Inserts or Updates
     */
    public function save()
    {

        $modelNamespace = '\\' . get_called_class();
        $e = explode('\\', $modelNamespace);
        $modelNameSingular = strtolower($e[count($e) - 1]);
        $modelName = $modelNameSingular . 's';

        $allowed = array_merge($this->fillable, $this->guarded, array('id'));

        $fields = array();
        foreach ($allowed AS $field) {
            if (isset($this->{$field})) {
                $fields[$field] = $this->{$field};
            }
        }

        // @todo Switch DB::tables to [model]
        if (!isset($fields['id'])) {
            $insertId = BS::getDB()->insert($modelName, $fields);
            $id = $insertId;
            /*
            $redirectUri = '/' . $modelNameSingular . '/' . $id . '/edit';
            header('location: ' . $redirectUri);
            exit;
            */
        } else {
            $exist = Database::table($modelName)->where($modelName, 'id', '=', $fields['id'])->first();
            $wheres['id'] = $fields['id'];
            $id = $fields['id'];
            unset($fields['id']);
            BS::getDB()->update($modelName, $fields, $wheres);
            // BS::getDB() // @todo update above to this
        }

        return $id;

        /*
        $url = $_SERVER['HTTP_REFERER'];
        $uri = parse_url($url, PHP_URL_PATH);
        $uri = str_replace('create', 'edit', $uri);

        header("location: {$uri}?id={$insertId}");
        */
    }

    public function delete($id)
    {

        $modelNamespace = '\\' . get_called_class();
        $e = explode('\\', $modelNamespace);
        $modelNameSingular = strtolower($e[count($e) - 1]);
        $modelName = $modelNameSingular . 's';

        BS::getDB()->delete($modelName, $id);
        // BS::getDB() // @todo update above to this

        return true;
    }

    public function valid($addValidations)
    {
        $validClass = new Validation;

        // $validations = array();
        foreach ($addValidations AS $field => $newValidations) {
            foreach ($newValidations AS $special => $newValidation) {
                if (isset($this->validation[$field])) {
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
        // @todo eventually store this in config to reuse; make a singleton ...
        $model = get_called_class();
        // echo 'model: '.$model.'<br />';
        $n = new $model;
        return $n->getTable();
    }

    public static function all()
    {
        // @todo Find a way to use Database class
        $table = self::getTableName();

        $sql = "SELECT * FROM {$table}";

        /*
        $request = new Request;
        if ($request->input('search')) {

        }
        */

        $db = BS::getDBPDO();
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
        self::$dbStatic = new Database;
        self::$dbStatic->table = $table;
        self::$dbStatic->where[] = $first . ' ' . $expression . " '{$second}'";
        return self::$dbStatic;

        /*
        $this->inc = (isset($this->inc) ? $this->inc + 1 : 1);
        if($expression == 'is' && $value == 'null'){
            $this->where[] = $table . '.' . $id . ' is null';
        }elseif($expression == 'is' && $value == 'not null'){
            $this->where[] = $table . '.' . $id . ' is not null';
        }else{
            $this->where[] = $table . '.' . $id . ' ' . $expression . ' :' . $this->inc;
            $this->bindStore[] = array(':' . $this->inc, $value);
        }
        return $this;

        return $db;
        */
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