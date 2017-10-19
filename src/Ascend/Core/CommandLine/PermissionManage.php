<?php namespace Ascend\Core\CommandLine;

use \Ascend\Core\CommandLine\_CommandLineAbstract;
use \Ascend\Core\CommandLineArguments;
use \App\Model\Permission;
use \App\Model\Role;
use \App\Model\RolePermission;

class PermissionManage extends _CommandLineAbstract
{

    protected $command = 'permission:manage';
    protected $name = 'Permission Manage';
    protected $detail = 'Manage permissions';

    public function run()
    {
        $this->manage();
    }

    private function manage() {
        $roles = Role::all();
        $permissions = Permission::all();
        foreach($roles AS $role) {
            foreach($permissions AS $permission) {
                $setPermission = ($role['id'] == 1 ? 1 : 0);

                $exists = RolePermission::where('role_id','=', $role['id'])
                    ->where('permission_id','=', $permission['id'])
                    ->first();
                if (is_null($exists['id'])) {
                    $rp = new RolePermission;
                    $rp->role_id = $role['id'];
                    $rp->permission_id = $permission['id'];
                    $rp->method_get = $setPermission;
                    $rp->method_post = $setPermission;
                    $rp->method_put = $setPermission;
                    $rp->method_delete = $setPermission;
                    $rp->save();
                    echo 'Created Role ID "' . $role['id'] . '" at Permission "' . $permission['id'] . '"' . PHP_EOL;
                }
            }
        }
        echo '-- Done --' . PHP_EOL;
    }
}