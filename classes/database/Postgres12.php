<?php

/**
 * PostgreSQL 12 support
 *
 */

include_once('./classes/database/Postgres11.php');

class Postgres12 extends Postgres11 {

	var $major_version = 12;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

	// Help functions

	function getHelpPages() {
		include_once('./help/PostgresDoc12.php');
		return $this->help_page;
	}

	/**
	 * Checks to see whether or not a table has a unique id column
	 * @param $table The table name
	 * @return True if it has a unique id, false otherwise
	 * @return null error
	 **/
	function hasObjectID($table) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);
		return null;
		/* Fix:
		// https://stackoverflow.com/questions/58461178/how-to-fix-error-column-c-relhasoids-does-not-exist-in-postgres
		// https://postgresql.verite.pro/blog/2019/04/24/oid-column.html
		$sql = "SELECT relhasoids FROM pg_catalog.pg_class WHERE relname='{$table}'
			AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE nspname='{$c_schema}')";

		$rs = $this->selectSet($sql);
		if ($rs->recordCount() != 1) return null;
		else {
			$rs->fields['relhasoids'] = $this->phpBool($rs->fields['relhasoids']);
			return $rs->fields['relhasoids'];
		}
		*/
	}

}

?>