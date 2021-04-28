<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class extending datatables SSP to use Moodle $DB.
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class cul_ssp {
	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output ($columns, $data) {
		$out = array();

		foreach($data as $id => $rollover) {
			$row = array();

			foreach ($columns as $column) {
				// Is there a formatter?
				if (isset($column['formatter'])) {
					$row[$column['dt']] = $column['formatter']($rollover->{$column['db']}, $rollover);
				}
				else {
					$row[$column['dt']] = $rollover->{$column['db']};
				}
			}

			$out[] = $row;

		}
		return $out;
	}
	
	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit ($request, $columns)
	{
		$limit = '';

		if (isset($request['start']) && $request['length'] != -1) {
			$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
		}

		return $limit;
	}


	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order ($request, $columns)
	{
		$order = '';

		if (isset($request['order']) && count($request['order'])) {
			$orderBy = array();
			$dtColumns = static::pluck($columns, 'dt');

			for ($i=0, $ien=count($request['order']) ; $i<$ien ; $i++) {
				// Convert the column index into the column data property
				$columnIdx = intval($request['order'][$i]['column']);
				$requestColumn = $request['columns'][$columnIdx];

				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[ $columnIdx ];

				if ($requestColumn['orderable'] == 'true') {
					$dir = $request['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = $column['al'] . ' ' . $dir;
				}
			}

			$order = 'ORDER BY ' . implode(', ', $orderBy);
		}

		return $order;
	}

	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	static function filter ($request, $columns, &$bindings)
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = static::pluck($columns, 'dt');

		if (isset($request['search']) && $request['search']['value'] != '') {
			$str = $request['search']['value'];

			for ($i=0, $ien=count($request['columns']) ; $i<$ien ; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[ $columnIdx ];

				if ($requestColumn['searchable'] == 'true') {
					$binding = static::bind($bindings, '%' . $str . '%');
					$globalSearch[] = $column['al'] . " LIKE ?";
				}
			}
		}

		// Individual column filtering
		if (isset($request['columns'])) {
			for ($i=0, $ien=count($request['columns']) ; $i<$ien ; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				$str = $requestColumn['search']['value'];

				if ($requestColumn['searchable'] == 'true' &&
				 $str != '') {
					// $binding = static::bind($bindings, '%' . $str . '%');
					$bindings[] = '%' . $str . '%';
					$columnSearch[] = $column['al'] . " LIKE ? ";
				}
			}
		}
// var_dump($bindings);
		// Combine the filters into a single string
		$where = '';

		if (count($globalSearch)) {
			$where = '(' . implode(' OR ', $globalSearch) . ')';
		}

		if (count($columnSearch)) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where . ' AND ' . implode(' AND ', $columnSearch);
		}

		if ($where !== '') {
			$where = 'WHERE ' . $where;
		}

		return $where;
	}

	/**
	 * The difference between this method and the `simple` one, is that you can
	 * apply additional `where` conditions to the SQL queries. These can be in
	 * one of two forms:
	 *
	 * * 'Result condition' - This is applied to the result set, but not the
	 *   overall paging information query - i.e. it will not effect the number
	 *   of records that a user sees they can have access to. This should be
	 *   used when you want apply a filtering condition that the user has sent.
	 * * 'All condition' - This is applied to all queries that are made and
	 *   reduces the number of records that the user can access. This should be
	 *   used in conditions where you don't want the user to ever have access to
	 *   particular records (for example, restricting by a login id).
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  string $whereResult WHERE condition to apply to the result set
	 *  @param  string $whereAll WHERE condition to apply to all queries
	 *  @return array          Server-side processing response array
	 */
	static function complex ($request, $columns, $whereResult=null, $whereAll=null)
	{
		global $DB;

		$bindings = array();
		$localWhereResult = array();
		$localWhereAll = array();
		$whereAllSql = '';

		// Build the SQL query string from the request
		$limit = static::limit($request, $columns);
		$order = static::order($request, $columns);
		$where = static::filter($request, $columns, $bindings);
// die(var_dump($bindings));
		$whereResult = static::_flatten($whereResult);
		$whereAll = static::_flatten($whereAll);

		if ($whereResult) {
			$where = $where ?
				$where . ' AND ' . $whereResult :
				'WHERE ' . $whereResult;
		}

		if ($whereAll) {
			$where = $where ?
				$where . ' AND ' . $whereAll :
				'WHERE ' . $whereAll;

			$whereAllSql = 'WHERE ' . $whereAll;
		}

		// $sql = "SELECT `" . implode("`, `", static::pluck($columns, 'db')) . "`
		$sql = "SELECT 
			 cr.id,
			 cr.datesubmitted,
			 cr.datesubmitted,
			 cr.sourceid,
			 cr.destid,
			 cr.type,
			 cr.status,
			 cr.userid,
			 cr.id,
			 cr.schedule,
			 cr.merge,
			 cr.includegroups,
			 cr.enrolments,
			 cr.visible,
			 cr.visibledate,
			 cr.completiondate,
			 cr.notify,
			 sc.shortname as srcshortname,
			 sc.fullname as srcfullname,
			 dc.shortname as dstshortname,
			 dc.fullname as dstfullname,
			 u.firstname,
			 u.lastname,
			 u.alternatename
			 FROM {cul_rollover} cr
			 LEFT OUTER JOIN {course} sc
			 ON cr.sourceid = sc.id
			 LEFT OUTER JOIN {course} dc
			 ON cr.destid = dc.id
			 LEFT OUTER JOIN {user} u
			 ON cr.userid = u.id
			 $where
			 $order
			 $limit";
// echo $sql;
		$data = $DB->get_records_sql($sql, $bindings);

		$sql = "SELECT COUNT(*)
			 FROM   {cul_rollover} cr
			 LEFT OUTER JOIN {course} sc
			 ON cr.sourceid = sc.id
			 LEFT OUTER JOIN {course} dc
			 ON cr.destid = dc.id
			 LEFT OUTER JOIN {user} u
			 ON cr.userid = u.id
			 $where";

		$recordsFiltered = $DB->count_records_sql($sql, $bindings);

		$sql = "SELECT COUNT(*)
			 FROM   {cul_rollover} cr
			 LEFT OUTER JOIN {course} sc
			 ON cr.sourceid = sc.id
			 LEFT OUTER JOIN {course} dc
			 ON cr.destid = dc.id
			 LEFT OUTER JOIN {user} u
			 ON cr.userid = u.id
			 $whereAllSql";


		$recordsTotal = $DB->count_records_sql($sql, $bindings);

		/*
		 * Output
		 */
		return array(
			"draw"            => isset ($request['draw']) ?
				intval($request['draw']) :
				0,
			"recordsTotal"    => intval($recordsTotal),
			"recordsFiltered" => intval($recordsFiltered),
			"data"            => static::data_output($columns, $data)
		);
	}	


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function bind (&$a, $val)
	{
		$a[] = $val;
	}

	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck ($a, $prop)
	{
		$out = array();

		for ($i=0, $len=count($a) ; $i<$len ; $i++) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}


	/**
	 * Return a string from an array or a string
	 *
	 * @param  array|string $a Array to join
	 * @param  string $join Glue for the concatenation
	 * @return string Joined string
	 */
	static function _flatten ($a, $join = ' AND ')
	{
		if (! $a) {
			return '';
		}
		else if ($a && is_array($a)) {
			return implode($join, $a);
		}
		return $a;
	}	
}

