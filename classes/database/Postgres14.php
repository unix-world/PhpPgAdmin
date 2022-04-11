<?php

/**
 * PostgreSQL 14 support
 *
 */

include_once('./classes/database/Postgres13.php');

class Postgres14 extends Postgres13 {

	var $major_version = 14;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

	// Help functions

	function getHelpPages() {
		include_once('./help/PostgresDoc14.php');
		return $this->help_page;
	}

}

?>