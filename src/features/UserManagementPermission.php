<?php namespace Ascend\Feature;

class UserManagementPermission
{

    /**
     * Non Static Functions
     */

    public function __construct()
    {
        $um = new UserManagement;
    }

    /**
     * Non Static Functions
     */

    public static function get($perm)
    {

        /*
        $sql = "SELECT id
            FROM rolepermissions rp
            JOIN permissions p ON p.id = rp.permission_id
            JOIN users u ON u.role_id = rp.role_id
            WHERE p.slug = '{$perm}'
            AND u.id = '{$userId}'
            ";

        $db = BS::getDBPDO();
        $db->query($sql);
        $db->execute();
        $row = $db->single();
        */

        $row = array();
        return count($row) > 0;
    }
}