<?php

/**
 * PostgreSQL 11 support
 *
 */

include_once('./classes/database/Postgres94.php');

class Postgres11 extends Postgres94 {

	// replace proisagg with prokind as f for a normal function, p for a procedure, a for an aggregate function, or w for a window function

	var $major_version = 11;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

	// Help functions

	function getHelpPages() {
		include_once('./help/PostgresDoc11.php');
		return $this->help_page;
	}


	function getFunctions($all = false, $type = null) {
		if ($all) {
			$where = 'pg_catalog.pg_function_is_visible(p.oid)';
			$distinct = 'DISTINCT ON (p.proname)';

			if ($type) {
				$where .= " AND p.prorettype = (select oid from pg_catalog.pg_type p where p.typname = 'trigger') ";
			}
		}
		else {
			$c_schema = $this->_schema;
			$this->clean($c_schema);
			$where = "n.nspname = '{$c_schema}'";
			$distinct = '';
		}

		$sql = "
			SELECT
				{$distinct}
				p.oid AS prooid,
				p.proname,
				p.proretset,
				pg_catalog.format_type(p.prorettype, NULL) AS proresult,
				pg_catalog.oidvectortypes(p.proargtypes) AS proarguments,
				pl.lanname AS prolanguage,
				pg_catalog.obj_description(p.oid, 'pg_proc') AS procomment,
				p.proname || ' (' || pg_catalog.oidvectortypes(p.proargtypes) || ')' AS proproto,
				CASE WHEN p.proretset THEN 'setof ' ELSE '' END || pg_catalog.format_type(p.prorettype, NULL) AS proreturns,
				u.usename AS proowner
			FROM pg_catalog.pg_proc p
				INNER JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace
				INNER JOIN pg_catalog.pg_language pl ON pl.oid = p.prolang
				LEFT JOIN pg_catalog.pg_user u ON u.usesysid = p.proowner
			WHERE
				-- start fix
		--		NOT p.proisagg AND
				(p.prokind = 'f' OR p.prokind = 'p') AND
				-- end fix
				{$where}
			ORDER BY p.proname, proresult
			";

		return $this->selectSet($sql);
	}


	function getAggregate($name, $basetype) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->fieldclean($name);
		$this->fieldclean($basetype);

		$sql = "
			SELECT p.proname, CASE p.proargtypes[0]
				WHEN 'pg_catalog.\"any\"'::pg_catalog.regtype THEN NULL
				ELSE pg_catalog.format_type(p.proargtypes[0], NULL) END AS proargtypes,
				a.aggtransfn, format_type(a.aggtranstype, NULL) AS aggstype, a.aggfinalfn,
				a.agginitval, a.aggsortop, u.usename, pg_catalog.obj_description(p.oid, 'pg_proc') AS aggrcomment
			FROM pg_catalog.pg_proc p, pg_catalog.pg_namespace n, pg_catalog.pg_user u, pg_catalog.pg_aggregate a
			WHERE n.oid = p.pronamespace AND p.proowner=u.usesysid AND p.oid=a.aggfnoid
				-- start fix
		--		AND p.proisagg
				AND (p.prokind = 'a' OR p.prokind = 'w')
				-- end fix
				AND n.nspname='{$c_schema}'
				AND p.proname='" . $name . "'
				AND CASE p.proargtypes[0]
					WHEN 'pg_catalog.\"any\"'::pg_catalog.regtype THEN ''
					ELSE pg_catalog.format_type(p.proargtypes[0], NULL)
				END ='" . $basetype . "'";

		return $this->selectSet($sql);
	}


	function getAggregates() {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$sql = "SELECT p.proname, CASE p.proargtypes[0] WHEN 'pg_catalog.\"any\"'::pg_catalog.regtype THEN NULL ELSE
			   pg_catalog.format_type(p.proargtypes[0], NULL) END AS proargtypes, a.aggtransfn, u.usename,
			   pg_catalog.obj_description(p.oid, 'pg_proc') AS aggrcomment
			   FROM pg_catalog.pg_proc p, pg_catalog.pg_namespace n, pg_catalog.pg_user u, pg_catalog.pg_aggregate a
			   WHERE n.oid = p.pronamespace AND p.proowner=u.usesysid AND p.oid=a.aggfnoid
			   -- start fix
	--		   AND p.proisagg
               AND (p.prokind = 'a' OR p.prokind = 'w')
               -- end fix
			   AND n.nspname='{$c_schema}' ORDER BY 1, 2";

		return $this->selectSet($sql);
	}


	/**
	 * Returns the current default_with_oids setting
	 * @return default_with_oids setting
	 */
	function getDefaultWithOid() {

		$sql = "SHOW default_with_oids";

		return $this->selectField($sql, 'default_with_oids');
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

		$sql = "SELECT relhasoids FROM pg_catalog.pg_class WHERE relname='{$table}'
			AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE nspname='{$c_schema}')";

		$rs = $this->selectSet($sql);
		if ($rs->recordCount() != 1) return null;
		else {
			$rs->fields['relhasoids'] = $this->phpBool($rs->fields['relhasoids']);
			return $rs->fields['relhasoids'];
		}
	}


	// Capabilities
	function hasServerOids() { return true; }



}

?>