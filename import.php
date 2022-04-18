<?php


require("../vendor/autoload.php");

$config_db_path = "/data/sqlite/";

$con = new MongoDB\Client("mongodb://localhost", [], ['typeMap'=>[
	'array'=>'array', 'root'=>'array', 'document'=>'array'
]]);
$db = $con->wiki_data;
$w = $db->wiki_data;

$cond = [];
$cnt = 0;
while( 1 ){
	$res = $w->find($cond, ['sort'=>['_id'=>1], 'limit'=>1000 ]);

	foreach( $res as $i=>$j ){
		foreach( $j['i_of'] as $ii=>$inst ){
			$file = $config_db_path . $inst . ".db";
			$dbfile = SQLite3::open( $file );

						


		}
	}
	$cnt++;
	if( $cnt > 10 ){ break; }
}

?>
