<?php
require_once __DIR__.'/../admin/init.php';


// FUNCTIONS
function getTables($dtb)
{
	// Get all different tables
	$req = $dtb->prepare('SELECT DISTINCT user_table as user_table FROM logs ORDER BY user_table');
	$req->execute(array());
	$tables_db = $req->fetchALL(PDO::FETCH_ASSOC);
	$req->closeCursor();

	if (!$tables_db) {
		$all_tables = false;
	} else {
		foreach ($tables_db as $line) {
			$all_tables[] = $line['user_table'];
		}
	}
	return $all_tables;
}
