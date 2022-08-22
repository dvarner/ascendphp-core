<?php namespace Ascend\Core;

class ModelChain extends ModelChainAbstract
{
//    protected array $sql;

    public function getTableName(): string
    {
        return $this->table;
    }

    public function getSQL(array $fields): string
    {
        $sql_fields = (count($fields) === 0 ? '*' : implode(', ', $fields));
        return 'SELECT ' . $sql_fields . ' FROM ' . $this->getTableName();
    }

    public static function all(array $fields = []): string
    {
        $sql = (new ModelChain())->getSQL($fields);
        return (new Database())->getAll($sql);
    }

//    public static function where($id, $expression, $value = null): ModelChain
//    {
//        $sql = [];
//        if ($value == 'null' && !in_array($expression, ['is', 'is not'])) {
//            var_dump(debug_backtrace());
//            trigger_error('SQL Warning: Query has a null set to something other than is or is not!', E_USER_ERROR);
//        }
//        if (is_numeric($value) || $value == 'null') {
//            $sql['where'][] = $id . ' ' . $expression . ' ' . $value;
//        } else {
//            $sql['where'][] = $id . ' ' . $expression . " '" . $value . "'";
//        }
//        $chain = new self;
//        $chain->init();
//        return $chain;
//    }
//
//    public function orderBy($field, $direction = 'asc')
//    {
//        $this->sql['order_by'][] = ' ORDER BY ' . $field . ' ' . $direction;
//        // var_dump($this->sql);
//        return $this;
//    }
//
//    public function first()
//    {
//        $table = $this->sql['table'];
//        $sql = "SELECT * FROM {$table} WHERE " . $this->getSQL() . " LIMIT 1";
//        $this->sql = [];
//        return self::one($sql);
//    }
//
//    public function all()
//    {
//        $table = $this->sql['table'];
//        $sql = "SELECT * FROM {$table} WHERE " . $this->getSQL();
//        // var_dump($this->sql);
//        $this->sql = [];
//        $r = self::many($sql);
//        return $r;
//
//    }
//
//    public function getSQL()
//    {
//        $sql = '';
//        if (isset($this->sql['where']) && is_array($this->sql['where']) && count($this->sql['where']) > 0) {
//            foreach ($this->sql['where'] AS $where) {
//                if ($sql != '')  $sql .= " AND ";
//                $sql .= $where;
//            }
//        }
//        if ($sql != '')  $sql .= " AND ";
//        $sql.= "deleted_at IS NULL";
//        $sql_order_by = '';
//        if (isset($this->sql['order_by']) && is_array($this->sql['order_by']) && count($this->sql['order_by']) > 0) {
//            foreach ($this->sql['order_by'] AS $order) {
//                if ($sql_order_by != '')  $sql .= ", ";
//                $sql_order_by .= $order;
//            }
//        }
//        $sql.= $sql_order_by;
//        return $sql;
//    }
}