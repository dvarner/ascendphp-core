<?php namespace Ascend\CommandLine;

use Ascend\CommandLine\_CommandLineAbstract;

class PasswordCost extends _CommandLineAbstract {
	
	protected $command = 'password:cost';
	protected $name = 'Password Cost';
	protected $detail = 'Benchmark to determine cost for password strength';
	
	public function run() {
		/**
		 * This code will benchmark your server to determine how high of a cost you can
		 * afford. You want to set the highest cost that you can without slowing down
		 * you server too much. 8-10 is a good baseline, and more is good if your servers
		 * are fast enough. The code below aims for = 50 milliseconds stretching time,
		 * which is a good baseline for systems handling interactive logins.
		 */
		$timeTarget = 0.05; // 50 milliseconds

		$cost = 8;
		do {
			$cost++;
			$start = microtime(true);
			password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
			$end = microtime(true);
		} while (($end - $start) < $timeTarget);

		echo "Appropriate Cost Found: " . $cost . RET;
	}
}