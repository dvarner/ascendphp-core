<?php namespace Ascend\Core\CommandLine;

use Ascend\Core\Bootstrap;
use Ascend\Core\CommandLine\_CommandLineAbstract;

// use Ascend\Database as DB;

class DatabaseMigrate extends _CommandLineAbstract
{

    protected $command = 'db:migrate';
    protected $name = 'Database Migrate';
    protected $detail = 'Migrate tables';

    public function run()
    {

        $output = '';
        $db = Bootstrap::getDBPDO();

        $this->migrationsModel();
        $models = $this->getModelsList();

        $output .= 'Migration Started!' . RET;

        $batchId = $this->getLastBatchId();

        foreach ($models AS $model) {

            require_once PATH_MODELS . $model . '.php';
            $class = '\\App\\Model\\' . $model;
            $n = new $class;

            $tableName = $n->getTable();

            $migration = self::getModel($model);

            if (is_null($migration)) {
                try {
                    $output .= 'Run Model "' . $model . '"' . RET;

                    $n->createTable();

                    $this->saveMigrationRow($batchId, $model, $n->getStructure());
                } catch (Exception $e) {
                    echo 'DatabaseMigrate@run: Caught exception: ' . $e->getMessage() . RET;
                    exit;
                }
            } else {
                $structure = json_encode($n->getStructure());
                if ($migration['structure'] != $structure) {
                    // $output.= 'Update "' . $model . '" structure!' . RET;
                    $before = json_decode($migration['structure'], true);
                    $after = json_decode($structure, true);
                    $beforeOrig = $before;
                    $afterOrig = $after;

                    // Check to see if schema has changed
                    foreach ($before AS $bk => $bv) {
                        foreach ($after AS $ak => $av) {
                            if ($bv == $av) {
                                unset($before[$bk], $after[$ak]);
                            }
                            unset($ak, $av);
                        }
                        unset($bk, $bv);
                    }

                    // var_dump($before, $after);

                    // Do alter's if changes exist
                    if (count($before) > 0 || count($after) > 0) {
                        $output .= 'Alter table "' . $tableName . '"' . RET;
                        // $output.= dump($before) . dump($after);

                        $renameMap = $n->getRenameMap();
                        if (isset($renameMap)) {
                            foreach ($after AS $ak => $av) {
                                $rCnt = count($renameMap);
                                // Go backwards for newest to oldest and find last occurance
                                for ($r = $rCnt-1; $r >= 0; $r--) {
                                    // $output.= 'Rename Check: ' . $renameMap[$r]['to'] .' == ' . $ak . RET;
                                    if ($renameMap[$r]['to'] == $ak) {
                                        $sql = 'ALTER TABLE ' . $tableName . ' CHANGE ';
                                        $sql.= $renameMap[$r]['from'];
                                        $sql.= ' ';
                                        $sql.= $after[$renameMap[$r]['to']];
                                        $output .=  $renameMap[$r]['from'] . ': ' . $sql . RET;
                                        $db->query($sql);
                                        $db->execute();
                                        $this->saveMigrationRow($batchId, $model, $n->getStructure());
                                        unset($before[$renameMap[$r]['from']], $after[$renameMap[$r]['to']]);
                                        break;
                                    }
                                }
                            }
                        }

                        if (count($before) > 0) {
                            // Things to remove
                            foreach ($before AS $bk => $bv) {
                                if (!isset($after[$bk])) {
                                    $sql = 'ALTER TABLE ' . $tableName . ' DROP COLUMN ' . $bk;
                                    $output .= $bk . ': ' . $sql . RET;
                                    $db->query($sql);
                                    $db->execute();
                                    $this->saveMigrationRow($batchId, $model, $n->getStructure());
                                }
                            }
                        }
                        if (count($after) > 0) {
                            // Things to add or update
                            foreach ($after AS $ak => $av) {
                                if (!isset($before[$ak])) {

                                    reset($afterOrig);
                                    while (key($afterOrig) !== $ak) next($afterOrig);
                                    prev($afterOrig);
                                    $prevKey = key($afterOrig);
                                    // $output.= 'prevKey: ' . $prevKey . RET;

                                    // Add field
                                    $sql = 'ALTER TABLE ' . $tableName . ' ADD ' . $av . ' AFTER ' . $prevKey;
                                    $output .= $ak . ': ' . $sql . RET;
                                    $db->query($sql);
                                    $db->execute();
                                    $this->saveMigrationRow($batchId, $model, $n->getStructure());
                                } else {
                                    // Change field
                                    $sql = 'ALTER TABLE ' . $tableName . ' MODIFY COLUMN ' . $av;
                                    $output .= $ak . ': ' . $sql . RET;
                                    $db->query($sql);
                                    $db->execute();
                                    $this->saveMigrationRow($batchId, $model, $n->getStructure());
                                }
                                unset($ak, $av);
                            }
                        }
                    }
                }
            }

            // unset($model, $n);
        }

        $output .= 'Migration Complete!' . RET;
        echo $output;
        exit;
    }

    private function migrationsModel()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
		  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  `batch_id` int unsigned NOT NULL,
		  `model` varchar(255) NOT NULL,
		  `structure` text NOT NULL,
		  `created_at` timestamp NOT NULL
		) ENGINE=InnoDB";

        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        $db->execute();
    }

    private function getModelsList()
    {

        $models = array();
        $path = PATH_MODELS;
        $cdir = scandir($path);

        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (
                    !is_dir($path . DIRECTORY_SEPARATOR . $value)
                    &&
                    '_' != substr($value, 0, 1)
                ) {
                    $models[] = str_replace('.php', '', $value);
                }
            }
        }

        return $models;
    }

    private function getLastBatchId()
    {
        $sql = "SELECT batch_id FROM migrations ORDER BY batch_id DESC LIMIT 1";

        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        $db->execute();
        $row = $db->single();

        return count($row) == 0 ? 1 : $row['batch_id'] + 1;
    }

    private function getModel($model)
    {
        $sql = "SELECT * FROM migrations WHERE model = '{$model}' ORDER BY id DESC LIMIT 1";

        $db = Bootstrap::getDBPDO();
        $db->query($sql);
        $db->execute();
        $row = $db->single();

        return is_array($row) && count($row) > 0 ? $row : null;
    }

    private function saveMigrationRow($batchId, $model, $structure)
    {

        $structure = json_encode($structure);

        $db = Bootstrap::getDBPDO();
        $db->query("INSERT INTO migrations SET batch_id = {$batchId}, model = '{$model}', structure = '{$structure}'");
        $db->execute();
    }
}








