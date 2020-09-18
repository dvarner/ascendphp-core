<?php namespace Ascend\Core\CommandLine;

use App\Model\Migration;
use Ascend\Core\CommandLineWrapper;
use Ascend\Core\Database;

class SQLMigrateCommandLine extends CommandLineWrapper
{
    protected static string $command = 'sql:migrate';
    protected static string $name = 'Migrate SQL modules';
    protected static string $help = 'sql:migrate                    Runs next set of Model changes' . PHP_EOL .
    '             sql:migrate rollback                  Revert tables back 1 migration' . PHP_EOL .
    '             sql:migrate destroy:YYYYMMDD          Removes all tables';
    protected static $next_batch_id = 0;
    private static $current_migration_tables = [];

    public static function run($arguments = null)
    {
        $found = false;
        if (is_null($arguments)) {
            $found = true;
            self::out('');
            self::out(' >> Start << ');
            Database::one('set foreign_key_checks=0');
            self::migrate();
            Database::one('set foreign_key_checks=1');
            self::out(' >> Complete <<');
        } else {
            if ($arguments == 'rollback') {
                $found = true;
                Database::one('set foreign_key_checks=0');
                self::rollback();
                Database::one('set foreign_key_checks=1');
            }
            if ($arguments == 'destroy:' . date('Ymd', time())) {
                $found = true;
                Database::one('set foreign_key_checks=0');
                self::destroy();
                Database::one('set foreign_key_checks=1');
            }
        }

        if (!$found) {
            self::out('');
            self::out('"sql:migrate" command incorrectly formatted');
        }
    }

    private static function migrate()
    {
        $dir = PATH_MODELS;
        if (is_dir($dir)) {
            // Run Migrations table first; is required for all other models
            $model = 'Migration';
            $table = call_user_func('\\App\\Model\\' . $model . '::getTableName');
            if (!Database::table_exists($table)) {
                self::createAndSeedModel($model);
            }
            self::$next_batch_id = Migration::getNextBatchId();

            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (filetype($dir . $file) == 'file' && $file != '.' && $file != '..') {
                        list($model, $ext) = explode('.', $file);
                        if ($ext == 'php' && $model != 'Migration') {
                            $table = call_user_func('\\App\\Model\\' . $model . '::getTableName');
                            // $current_migration_tables
                            if (Database::table_exists($table) && in_array($table, self::$current_migration_tables)) {
                                self::alterModel($model);
                            } else {
                                self::createAndSeedModel($model);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    private static function rollback()
    {
        self::out('CURRENTLY DISABLED! JUST REVERSE YOUR LAST CHANGES!'); exit;

        $last_batch_id = Migration::getLastBatchId();
        self::out('Last Batch ID: '.$last_batch_id);
        if ($last_batch_id == 1) {
            self::out('We do not allow rollback on batch 1 because that would delete the whole db! This HAS to be a manual process.');
            exit;
        }
        $migrations = Migration::getByBatchID($last_batch_id);
        foreach ($migrations AS $migration) {
            self::out('Model: '.$migration['model']);
            $migration_previous = Migration::getByModelAndBatchID($migration['model'],$last_batch_id);
            // self::alterModel($migration['model']);
        }
    }

    private static function destroy()
    {
        $dir = PATH_MODELS;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                self::out('');
                self::out('Run destroy -> DROP THE FOLLOWING TABLES');
                while (($file = readdir($dh)) !== false) {
                    if (filetype($dir . $file) == 'file' && $file != '.' && $file != '..') {
                        list($model, $ext) = explode('.', $file);
                        if ($ext == 'php') {
                            $table = call_user_func('\\App\\Model\\' . $model . '::getTableName');
                            if (Database::table_exists($table)) {
                                Database::one('DROP TABLE ' . $table);
                                self::out('Model: '.$model);
                            }
                        }
                    }
                }
                self::out('Process Complete');
                closedir($dh);
            }
        }
    }

    private static function createAndSeedModel($model)
    {
        $create_table = false;

        $table_name = call_user_func('\\App\\Model\\' . $model . '::getTableName');
        if (!Database::table_exists($table_name)) {
            $create_table = true;
            self::$current_migration_tables[] = $model;
            self::out('Create Model: ' . $model);
            $fields = call_user_func('\\App\\Model\\' . $model . '::getFields');
            call_user_func('\\App\\Model\\' . $model . '::create', $fields);
            self::saveMigrationRow(self::$next_batch_id, $model, $fields);
        }

        $foreign_keys = call_user_func('\\App\\Model\\' . $model . '::getForeignKeys');
        if (is_array($foreign_keys) && count($foreign_keys) > 0) {
            $table_name = call_user_func('\\App\\Model\\' . $model . '::getTableName');
            foreach ($foreign_keys AS $foreign_key => $v) {
                $foreign_table_name = call_user_func('\\App\\Model\\' . $v['model'] . '::getTableName');
                if (!Database::table_exists($foreign_table_name)) {
                    // $foreign_fields = call_user_func('\\App\\Model\\' . $v['model'] . '::getFields');
                    // call_user_func('\\App\\Model\\' . $v['model'] . '::create', $foreign_fields);
                    self::createAndSeedModel($v['model']);
                }
                $sql = 'ALTER TABLE ' . $table_name . ' ADD FOREIGN KEY (' . $foreign_key . ') REFERENCES ' . $foreign_table_name . '(' . $v['field'] . ')';
                self::out($sql);
                Database::query($sql);
                unset($foreign_key, $v);
            }
        }

        $seeds = call_user_func('\\App\\Model\\' . $model . '::getSeeds');
        $seeds_skip = call_user_func('\\App\\Model\\' . $model . '::getSeedsSkip');
        if ($create_table && !is_null($seeds) && is_array($seeds) && count($seeds) > 0 && $seeds_skip === false) {
            self::out('Seed Model: ' . $model);
            if (isset($seeds['file'])) {
                $csv_content = file_get_contents(PATH_STORAGE . $seeds['file']);
                $csv_content = str_replace("\r",'',$csv_content);
                $csv = str_getcsv($csv_content, "\n");
                array_shift($csv); # remove column header
                foreach ($csv AS $row) {
                    $row_array = explode(',',$row);
                    $insert = [];
                    foreach ($seeds['map'] AS $field => $col_number) {
                        $insert[$field] = $row_array[$col_number];
                    }
                    call_user_func('\\App\\Model\\' . $model . '::insert', $insert);
                }
            } else {
                foreach ($seeds AS $seed) {
                    $insert = call_user_func('\\App\\Model\\' . $model . '::insert', $seed);
                }
            }
        }
    }

    private static function saveMigrationRow($batch_id, $model, $fields)
    {
        $structure = json_encode($fields);
        \App\Model\Migration::insert(['batch_id' => $batch_id, 'model' => $model, 'structure' => $structure]);
    }

    private static function alterModel($model)
    {
        $batch_id = self::$next_batch_id;
        $migration = \App\Model\Migration::getByModelAndBatchID(self::$next_batch_id - 1, $model);
        $fields = call_user_func('\\App\\Model\\' . $model . '::getFields');
        $structure_current_json = json_encode($fields);
        // self::out('### COMPARE ###');
        // self::out($migration['structure']);
        // self::out($structure_current_json);
        if (isset($migration['structure']) && $migration['structure'] === $structure_current_json) {
        } else {
            self::out('Alter Model: ' . $model);
            self::out('Next Batch ID: ' . self::$next_batch_id);
            $structure_current = isset($migration['structure']) ? json_decode($migration['structure'], true) : [];
            $structure_new = json_decode($structure_current_json, true);
            // var_dump('current:',$structure_current,'new:',$structure_new);
            if (is_null($structure_current)) $structure_current = [];
            if (is_null($structure_new)) $structure_new = [];
            $fields_remove = $structure_current;
            $fields_add = $structure_new;
            $fields_alter = $structure_new;
            foreach ($fields_remove AS $k => $v) {
                if (isset($structure_new[$k])) {
                    unset($fields_remove[$k]);
                }
                unset($k, $v);
            }
            foreach ($fields_add AS $k => $v) {
                if (isset($structure_current[$k])) {
                    unset($fields_add[$k]);
                } else {
                    unset($fields_alter[$k]);
                }
                unset($k, $v);
            }
            foreach ($fields_alter AS $k => $v) {
                if (trim(strtolower($structure_current[$k])) == trim(strtolower($v))) {
                    unset($fields_alter[$k]);
                }
                unset($k, $v);
            }
            // var_dump($fields_remove, $fields_add, $fields_alter);
            $table_name = call_user_func('\\App\\Model\\' . $model . '::getTableName');
            if (is_array($fields_remove) && count($fields_remove) > 0) {
                foreach ($fields_remove AS $rk => $rv) {
                    $sql = 'ALTER TABLE ' . $table_name . ' DROP COLUMN ' . $rk;
                    self::out($sql);
                    Database::query($sql);
                    // self::saveMigrationRow($batch_id, $model, $structure_current);
                    unset($rk, $rv);
                }
            }
            if (is_array($fields_add) && count($fields_add) > 0) {
                foreach ($fields_add AS $k => $v) {
                    // First get previous field
                    $reverse = array_reverse($structure_new, true);
                    $next = false;
                    $previous_field = 'id';
                    foreach ($reverse AS $kk => $vv) {
                        if ($next) {
                            $previous_field = $kk;
                            break;
                        }
                        if ($kk == $k) {
                            $next = true;
                        }
                    }

                    // Add field
                    $sql = 'ALTER TABLE ' . $table_name . ' ADD ' . $k . ' ' . $v . ' AFTER ' . $previous_field;
                    self::out($sql);
                    Database::query($sql);
                    // self::saveMigrationRow($batch_id, $model, $structure_current);
                    unset($k, $v);
                }
            }
            if (is_array($fields_alter) && count($fields_alter) > 0) {
                foreach ($fields_alter AS $k => $v) {
                    // Change field
                    $sql = 'ALTER TABLE ' . $table_name . ' MODIFY COLUMN ' . $k . ' ' . $v;
                    self::out($sql);
                    Database::query($sql);
                    // self::saveMigrationRow($batch_id, $model, $structure_current);
                }
            }
            self::saveMigrationRow($batch_id, $model, $structure_new);
        }
    }
}