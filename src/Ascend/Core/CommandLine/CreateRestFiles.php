<?php namespace Ascend\Core\CommandLine;

use \Ascend\Core\CommandLine\_CommandLineAbstract;
use \Ascend\Core\CommandLineArguments;
use \App\Model\User;

class CreateRestFiles extends _CommandLineAbstract {
	
	protected $command = 'create:rest:files';
	protected $name = 'Create Rest API file structure; Route, Controller, Model';
	protected $detail = '';
	
	public function run() {
		
		$argv = CommandLineArguments::getArgv();

		if (isset($argv[2])) {

		    $modelName = ucfirst($argv[2]);

		    $pathController = PATH_CONTROLLERS . $modelName . '.php';
            $pathModel = PATH_MODELS . $modelName . '.php';

		    if (!file_exists($pathController) && !file_exists($pathModel)) {

		        $this->createController($modelName);
                $this->createModel($modelName);

                $this->outputSuccess("Add Route::rest('{$modelName}'); to app/route.php");
            } else {
		        $this->outputError('Model / Controller already exist with name "' . $modelName . '"');
            }



		} else {
			echo 'Please provide parameter for rest name' . RET;
		}
	}

    protected function createController($modelName) {
        $code = <<<EOF
<?php namespace App\Controller;

class {$modelName}Controller extends Controller {
	
	public function __construct() {
		// By doing this; rest api is setup for this controller to defaults set by BaseController
		\$this->setModel('{$modelName}');
	}
}
EOF;
        $path = PATH_CONTROLLERS . $modelName . 'Controller.php';
        file_put_contents($path, $code);
    }

	protected function createModel($modelName) {
	    $lowerModelName = strtolower($modelName);
	    $code = <<<EOF
<?php namespace App\Model;

use Ascend\Model;

class {$modelName} extends Model
{
    protected \$table = '{$lowerModelName}s';
	
	public \$timestamps = true;
	protected \$fillable = array();
	protected \$guarded = array();
	
	protected \$structure = array(
		'id'		=> 'int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
	);
	
	protected \$validation = array(
		'id'		=> array('int'),
	);
}
EOF;
        $path = PATH_MODELS . $modelName . '.php';
        file_put_contents($path, $code);
    }
}