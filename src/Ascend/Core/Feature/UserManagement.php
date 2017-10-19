<?php namespace Ascend\Core\Feature;

class UserManagement
{

    /**
     * Non Static Functions
     */

    public function __construct()
    {

        $this->install();
    }

    private function install()
    {
        $requiredModels = array(
            'Permission',        // p # p.id = rp.perm_id
            'Role',                // r # r.id = rp.role_id
            'RolePermission',    // rp # rp.role_id = r.id (get, post put, delete)
            'User'                // u # u.role_id = r.id
        );

        $error = array();
        foreach ($requiredModels AS $modelName) {
            $modelPath = '\\' . 'Acend' . '\\' . $modelName;
            if (!class_exists($modelPath)) {
                $error[] = 'Model Required: ' . $modelName;
            }
        }

        if (count($error) > 0) {
            trigger_error(implode('<br />' . RET, $error));
            exit;
        }
    }

    /**
     * Non Static Functions
     */
}