<?php
exit;
ini_set( "display_errors", "on" );
ini_set( "display_startup_errors", "on" );
error_reporting( E_ALL & ~E_NOTICE );

require( "../vendor/autoload.php" );

$con = new MongoDB\Client( "mongodb://localhost", [],[
	'typeMap'=>[
	'root'=>'array',
	'document'=>'array',
	'array'=>'array',
	'objectid'=>'string',
	'object'=>'array',
	'id'=>'string',
	'date'=>'date',
	'stdClass'=>'array'
] ] );

$db = $con->{"wiki_data"};
//print_r( $db );
$col = $db->{"wiki_data"};

//$_id = new MongoDB\BSON\ObjectId("622e481eb54ade217c9cf8e7");

$props_score = [];

$_id = "";
$cnt = 0;
while( 1 ){
	echo "loop " . $m . "\n";
	$cond = [];
	if( $_id ){
		$cond['_id']=['$gt'=>$_id];
	}
	$records = $col->find($cond,[
		'sort'=>['_id'=>1],
		'skip'=>10,
		'limit'=>2
	])->toArray();
	//print_r( get_class_methods($records) );
	//exit;
	echo sizeof($records)."\n";
	if( sizeof($records) == 0 ){
		break;
	}
	foreach( $records as $i=>$j ){
		$_id = $j['_id'];
		//$j = json_decode(json_encode($j),true);
		//echo json_encode( $j,JSON_PRETTY_PRINT );
		echo $cnt . ": " . $j['_id'] . ": ". $j['id'] . "\n";
		foreach( $j['claims'] as $p=>$k ){
			$props_score[ $p ] += 1;
		}
		//print_r( bson($j) );
		$cnt++;
	}
}
?>
