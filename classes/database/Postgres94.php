<?php

/**
 * PostgreSQL 9.4 support
 *
 */

include_once('./classes/database/Postgres.php');

class Postgres94 extends Postgres {

	var $major_version = 9.4;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

	// Help functions

	function getHelpPages() {
		include_once('./help/PostgresDoc94.php');
		return $this->help_page;
	}

	/**
	 * Returns all available process information.
	 * @param $database (optional) Find only connections to specified database
	 * @return A recordset
	 */
	function getProcesses($database = null) { // #Fix: waiting = 0
		if($database === null) {
			$sql = "SELECT datname, usename, pid, 0, state_change as query_start,
                  case when state='idle in transaction' then '<IDLE> in transaction' when state = 'idle' then '<IDLE>' else query end as query
				FROM pg_catalog.pg_stat_activity
				ORDER BY datname, usename, pid";
		} else {
			$this->clean($database);
			$sql = "SELECT datname, usename, pid, 0, state_change as query_start,
                  case when state='idle in transaction' then '<IDLE> in transaction' when state = 'idle' then '<IDLE>' else query end as query
				FROM pg_catalog.pg_stat_activity
				WHERE datname='{$database}'
				ORDER BY usename, pid";
		}
		return $this->selectSet($sql);
	}

}

?>