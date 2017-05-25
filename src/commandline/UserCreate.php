<?php namespace Ascend\CommandLine;

use \Ascend\Bootstrap as BS;
use \Ascend\CommandLine\_CommandLineAbstract;
use \Ascend\CommandLineArguments;
use \App\Model\User;

class UserCreate extends _CommandLineAbstract
{

    protected $command = 'user:create';
    protected $name = 'User Create: Parameters role_id, username, password';
    protected $detail = 'Create a user';

    public function run()
    {

        $argv = CommandLineArguments::getArgv();

        if (isset($argv[2]) && is_numeric($argv[2]) && isset($argv[3]) && isset($argv[4])) {

            $exist = User::where('username', '=', $argv[3])->first();

            if (is_null($exist)) {
                $user = new User;
                $user->role_id = $argv[2]; // @todo Eventually make this accept the int or string format
                $user->username = $argv[3];
                $user->password = $this->passwordHash($argv[4]);
                $user->confirm = '';
                $user->save();
                echo 'Saved!' . RET;
            } else {
                echo 'Already Exist!' . RET;
            }
        } else {
            echo 'Not enough parameters passed!' . RET;
        }
    }

    private function passwordHash($password)
    {
        $cost = BS::getConfig('password_cost');
        return password_hash($password, PASSWORD_BCRYPT, array('cost', $cost));
    }
}