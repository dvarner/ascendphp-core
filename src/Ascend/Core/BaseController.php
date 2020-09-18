<?php namespace Ascend\Core;

// use Ascend\Core\Bootstrap;
// use Ascend\Core\Feature\Permission;

// @todo Setup way to lockdown with api key

class BaseController
{
    protected $model = null;
    protected $pathSub = '';

    protected function setModel($model)
    {
        // $this->model = strtolower($model);
        $this->model = $model;
        $this->modelPermission = $model;
    }

    protected function setModelPermission($model)
    {
        $this->modelPermission = $model;
    }

    protected function setPathSub($pathSub)
    {
        $this->pathSub = $pathSub;
    }

    protected function isModelSet()
    {
        $r = is_null($this->model) ? false : true;
        if (!$r) {
            die('Model is not set for in "' . get_called_class() . '" controllers!');
        }
    }

    /**
     * Calls for REST API
     */

    // viewList = view
    public function viewList()
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'get');

        // GET /photos

        // Two different ways; send data below or ajax on page; ajax is on page.
        $r = new Request;
        // $a[$this->model . 's'] = $this->index($r);
        return Route::getView($this->pathSub . strtolower($this->model) . '/index'); // , $a);
    }

    // get = index
    public function methodGet()
    { // Request $request) {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'get');

        // GET /api/photos

        $modelNamespace = '\\App\\Model\\' . $this->model;

        return $modelNamespace::all();
    }

    // viewCreate = create
    public function viewCreate()
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'post');

        // GET photos/create

        // return Route::getView(strtolower($this->model) . '/create');
        return Route::getView($this->pathSub . strtolower($this->model) . '/create');
    }

    // post = store
    public function methodPost()//$injectedVariables = [])
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'post');

        // POST /api/photos

        $modelNamespace = '\\App\\Model\\' . $this->model;

        $model = new $modelNamespace;

        $r = new Request;
        $r->setMethod = 'POST';
        $a = $r->all();

        // Pass variables through which might be set on backend and not on fe.
        // Example: user_id set by session or timestamp of action.
        // @todo took out causing issues; so need to figure out why it was done and if needed
        /*
        foreach ($injectedVariables AS $k => $v) {
            $a[$k] = $v;
        }
        */

        if (is_array($a) && count($a) > 0) {
            foreach ($a AS $k => $v) {
                if (is_null($model->$k)) {
                    $model->$k = $v;
                }
            }
        }

        $id = $model->save();

        $db = Bootstrap::getDB();
        // var_dump($db->getlastSQL());
        // echo \Ascend\Database::getLastSQL();
        // echo $db->getlastSQLString();
        // die;

        $data = [];
        $data['data'] = $this->methodGetOne($id);
        $data['status'] = 'success';
        return $data;
    }

    // getOne = show
    public function methodGetOne($id)
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'get');

        // GET /api/photos/{id}

        $modelNamespace = '\\App\\Model\\' . $this->model;
        $r = $modelNamespace::where('id', '=', $id)->first();
        unset($r['password']);
        return $r;
    }

    // viewEdit = edit
    public function viewEdit($id)
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'put');

        // GET /api/photos/{id}/edit

        $a = $this->methodGetOne($id);
        // return Route::getView(strtolower($this->model) . '/edit', $a);
        return Route::getView($this->pathSub . strtolower($this->model) . '/edit', $a);
    }

    // put = update
    public function methodPut($id)
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'put');

        $modelNamespace = '\\App\\Model\\' . $this->model;

        $model = new $modelNamespace;
        $model->id = $id;

        $r = new Request;
        $a = $r->all();
        // echo '<pre>'; var_dump($a); exit;

        if (isset($a['password']) && $a['password'] != '') {
            $a['password'] = password_hash($a['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        } else {
            unset($a['password']);
        }

        if (is_array($a) && count($a) > 0) {
            foreach ($a AS $k => $v) {
                if ($k == 'confirmation_at'){
                    $v = date('Y-m-d H:i:s', strtotime($v));
                }
                if (is_null($model->$k)) {
                    $model->$k = $v;
                }
            }
        }

        $id = $model->save();

        $data = [];
        $data['data'] = $this->methodGetOne($id);
        $data['status'] = 'success';
        return $data;
    }

    // delete = destroy
    public function methodDelete($id)
    {
        $this->isModelSet();
        // Permission::get($this->modelPermission, 'delete');

        // DELETE /api/photos/{id}

        $modelNamespace = '\\App\\Model\\' . $this->model;

        $model = new $modelNamespace;
        $model->id = $id;

        $r = new Request;
        $a = $r->all();

        if (is_array($a) && count($a) > 0) {
            foreach ($a AS $k => $v) {
                if (is_null($model->$k)) {
                    $model->$k = $v;
                }
            }
        }

        $model->methodDelete($id);

        $data = array();
        $data['status'] = 'success';
        return $data;
    }
}