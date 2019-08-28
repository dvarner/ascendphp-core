<?php namespace Ascend\Core;


// http://php.net/manual/en/function.session-set-save-handler.php
class SessionDB implements \SessionHandlerInterface
{
    private $pdo;
    private $db_type = 'mysql';
    private $db_host = 'localhost';
    private $db_name = '';
    private $db_user = '';
    private $db_pass = '';
    private $table = 'sessions';

    public function __construct($type, $host, $database, $username, $password) {
        // @todo change this to use Database Class...
        $arr['options'] = array(
            \PDO::ATTR_PERSISTENT => true, // Sets to persistent. Increase performance by checking if already connected.
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION // This allows handling errors gracefully
        );
        $dsn = $type . ':host=' . $host . ';dbname=' . $database;
        try {
            $this->pdo = new \PDO($dsn, $username, $password, $arr['options']);
        } catch (\PDOException $e) {
            var_dump($e);
            exit;
        }
    }

    public function open($savePath, $sessionName)
    {
        $this->error_log_split();
        $this->error_log('Session Connect!');
        // $this->error_log(varDumpToString($pdo));
        if ($this->pdo) {
            $this->error_log('Success!');
            return true;
        } else {
            $this->error_log('Failed!');
            return false;
        }
    }

    public function close()
    {
        $this->error_log_split();
        $this->error_log('Session Close!');
        $this->pdo = null;
        return true;
    }

    public function read($id)
    {
        $this->error_log_split();
        $sql = "SELECT id, session_data FROM {$this->table} WHERE session_id = :sessionId AND session_expires >= :sessionDate";
        $stmt = $this->pdo->prepare($sql);
        $bind = [
            'sessionId' => $id,
            'sessionDate' => date('Y-m-d H:i:s'),
        ];
        $stmt->execute($bind);
        $this->error_log('Session read()');
        $this->error_log($sql);
        $this->error_log(varDumpToString($bind));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->error_log(varDumpToString($row));
        if (isset($row['id'])) {
            $this->error_log('Found!');
            return $row['session_data'];
        } else {
            $this->error_log('Doesnt Exist!');
            return "";
        }
    }

    public function write($session_id, $data)
    {
        $this->error_log_split();
        $datetime = date('Y-m-d H:i:s');
        $datetime_new = date('Y-m-d H:i:s', strtotime($datetime . ' + 24 hour'));

        $sql = "SELECT id FROM {$this->table} WHERE session_id = :session_id";
        $stmt = $this->pdo->prepare($sql);
        $bind = ['session_id' => $session_id];
        $stmt->execute($bind);
        $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (isset($exists['id'])) {
            $this->error_log('Session write() -> Update!');
            $sql = "UPDATE {$this->table} SET session_expires = :session_expires, session_data = :session_data, updated_at = :updated_at WHERE session_id = :session_id";
            $stmt = $this->pdo->prepare($sql);
            $bind = [ 'session_id' => $session_id, 'session_expires' => $datetime_new, 'session_data' => $data,'updated_at' => $datetime];
            $stmt->execute($bind);
        } else {
            $this->error_log('Session write() -> Insert!');
            $sql = "INSERT INTO {$this->table} SET user_id = 0, session_id = :session_id, session_expires = :session_expires, session_data = :session_data, created_at = :created_at, updated_at = :updated_at";
            $stmt = $this->pdo->prepare($sql);
            $bind = ['session_id' => $session_id, 'session_expires' => $datetime_new, 'session_data' => $data, 'created_at' => $datetime, 'updated_at' => $datetime];
            $stmt->execute($bind);
        }

        $this->error_log($sql);
        $this->error_log(varDumpToString($bind));

        $count = $stmt->rowCount(); // THIS CAN THROW FALSE POSITIVES IF session_data VALUE IS THE SAME AS WHAT YOU ARE UPDATING; 0 will result.

        $this->error_log('Session write() count: ' . $count);
        $this->error_log('Success!');
        return true;
        /*
        if ($count) {
            $this->error_log('Success!');
            return true;
        } else {
            $this->error_log('Failed!');
            return false;
        }
        */
    }

    public function destroy($id)
    {
        $this->error_log_split();
        $sql = "DELETE FROM {$this->table} WHERE session_id = :session_id";
        $stmt = $this->pdo->prepare($sql);
        $bind = ['session_id' => $id];
        $stmt->execute($bind);
        $this->error_log('Session delete()');
        /*
        $sql = "UPDATE {$this->table} SET session_data = '' WHERE session_id = :session_id";
        $stmt = $this->pdo->prepare($sql);
        $bind = ['session_id' => $id];
        $stmt->execute($bind);
        */
        /*
        $sql = "DELETE FROM {$this->table} WHERE session_id = :sessionId";
        $stmt = $this->pdo->prepare($sql);
        $bind = ['sessionId' => $id];
        $stmt->execute($bind);
        $this->error_log('Session delete()');
        $this->error_log($sql);
        $this->error_log(varDumpToString($bind));
        $row = $stmt->rowCount();
        $this->error_log(varDumpToString($row));
        if($row){
        $this->error_log('Success!');
        return true;
        }else{
        $this->error_log('Failed!');
        return false;
        }
        */
    }

    public function gc($maxlifetime)
    {
        $this->error_log_split();
        // @todo 20181209 check to see if php session gc aka garbage collect runs. If not do self by cron!
        $sql = "DELETE FROM {$this->table} WHERE ((UNIX_TIMESTAMP(session_expires) + " . $maxlifetime . ") < " . $maxlifetime . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $this->error_log('Session write()');
        $this->error_log($sql);
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->error_log('Success!');
            return true;
        } else {
            $this->error_log('Failed!');
            return false;
        }
    }
    public function create_sid(){
        // available since PHP 5.5.1
        // invoked internally when a new session id is needed
        // no parameter is needed and return value should be the new session id created
        // ...
    }
    public function validateId($sessionId){
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true if the session id is valid otherwise false
        // if false is returned a new session id will be generated by php internally
        // ...
    }
    public function updateTimestamp($sessionId, $sessionData){
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true for success or false for failure
        // ...
    }
    /*
    public function updateUserId($userId, $sessionId)
    {
        $this->error_log_split();

        $arr['options'] = array(
            \PDO::ATTR_PERSISTENT => true, // Sets to persistent. Increase performance by checking if already connected.
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION // This allows handling errors gracefully
        );
        $dsn = $this->db_type . ':host=' . $this->db_host . ';dbname=' . $this->db_name;
        $pdo = new\PDO($dsn, $this->db_user, $this->db_pass, $arr['options']);
        $this->error_log(varDumpToString($pdo));

        $sql = "UPDATE {$this->table} SET user_id = :userId WHERE session_id = :sessionId";
        $stmt = $pdo->prepare($sql);
        $bind = ['userId' => $userId, 'sessionId' => $sessionId];
        $this->error_log($sql);
        $this->error_log(varDumpToString($bind));
        $stmt->execute($bind);
        $row = $stmt->rowCount();
        $this->error_log(varDumpToString($row));
    }

    public function deleteUserId($userId)
    {
        $this->error_log_split();

        $arr['options'] = array(
            \PDO::ATTR_PERSISTENT => true, // Sets to persistent. Increase performance by checking if already connected.
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION // This allows handling errors gracefully
        );
        $dsn = $this->db_type . ':host=' . $this->db_host . ';dbname=' . $this->db_name;
        $pdo = new \PDO($dsn, $this->db_user, $this->db_pass, $arr['options']);
        $this->error_log(varDumpToString($pdo));

        $this->error_log('User delete(' . $userId . ')!');
        $sql = "DELETE FROM {$this->table} WHERE user_id = :userId";
        $stmt = $pdo->prepare($sql);
        $bind = ['userId' => $userId];
        $stmt->execute($bind);
        $this->error_log('User delete()');
        $this->error_log($sql);
        $this->error_log(varDumpToString($bind));
        $row = $stmt->rowCount();
        $this->error_log(varDumpToString($row));
        if ($row) {
            $this->error_log('Success!');
            return true;
        } else {
            $this->error_log('Failed!');
            return false;
        }
    }
    */

    private function error_log($msg)
    {
        error_log(TimeZone::dateFormatDB(time()) . ': ' . $msg . RET, 3, PATH_STORAGE . 'log/php.session.log');
    }

    private function error_log_split()
    {
        error_log(' ------------------------------------------------- ' . RET, 3, PATH_STORAGE . 'log/php.session.log');
    }
}