<?php

/**
 * PostgreSQL 9.1 support
 *
 */

include_once('./classes/database/Postgres90.php');

class Postgres91 extends Postgres90 {

	var $major_version = 9.1;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

	// Help functions

	function getHelpPages() {
		include_once('./help/PostgresDoc91.php');
		return $this->help_page;
	}

}

?>