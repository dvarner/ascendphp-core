<?php namespace Ascend\Feature;

use Ascend\Bootstrap as BS;

class Permission
{
    public static function get($slug, $type = 'get')
    {
        if ($type == 'get') {
            $type = 'method_get';
        }
        if ($type == 'post') {
            $type = 'method_post';
        }
        if ($type == 'put') {
            $type = 'method_put';
        }
        if ($type == 'delete') {
            $type = 'method_delete';
        }

        if (Session::exist('user.id')) {

            $userId = Session::get('user.id');

            $sql = "
				SELECT rp.method_get, rp.method_post, rp.method_put, rp.method_delete
				FROM permissions p
				JOIN rolepermissions rp ON rp.permission_id = p.id
				JOIN users u ON u.role_id = rp.role_id
				WHERE p.slug = '{$slug}'
				AND u.id = '{$userId}'
			";
        } else {
            $roleId = BS::getConfig('role.default');

            $sql = "
				SELECT rp.method_get, rp.method_post, rp.method_put, rp.method_delete
				FROM permissions p
				JOIN rolepermissions rp ON rp.permission_id = p.id
				WHERE p.slug = '{$slug}'
				AND rp.id = '{$roleId}'
			";
        }

        $db = BS::getDBPDO();
        $db->query($sql);
        $db->execute();
        $row = $db->single();

        if (isset($row[$type]) && $row[$type] == 1) {
            // Continue :) yay...
        } else {
            header('HTTP/1.0 403 Forbidden');
            $uri = $_SERVER['REQUEST_URI'];
            if (substr($uri, 1, 3) == 'api') {
                echo json_encode(array('error' => 'access-denied'));
            } else {
                header("location: /access-denied");
            }
            exit;
        }
    }
}