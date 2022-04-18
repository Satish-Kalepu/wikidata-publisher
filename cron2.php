<?php
exit;
ini_set("display_errors", "on");
ini_set("display_startup_errors", "on");
error_reporting(E_ALL & ~E_NOTICE);

require( "../vendor/autoload.php" );
header("Content-Type: text/plain");

$redis = new Redis();
$redis->connect("localhost");

$con = new MongoDB\Client( "mongodb://localhost", [],[
	'typeMap'=>[
	'root'=>'array',
	'document'=>'array',
	'array'=>'array',
	'objectid'=>'string',
	'object'=>'array',
	'id'=>'string',
	'date'=>'date'
] ] );
//print_r( $con );
function bson_to_json($v){
	$c = get_class($v);
	//echo "<div>" . $c . "</div>";
	if( $c == "MongoDB\Model\BSONDocument" || $c == "MongoDB\Model\BSONArray" ){
		$v = $v->getArrayCopy();
	}else if( $c =="MongoDB\BSON\ObjectId" ){
		//$v = (string)$v;
		return ['oid'=>(string)$v];
	}else{
		//print_r( get_class_methods($v) );
		$v = (array)$v;
	}
	if( is_array($v) ){
		foreach( $v as $i=>$j ){
			if( gettype($j) == "object" ){
				$v[ $i ] = bson_to_json($j);
			}
		}
	}
	return $v;
}

$db = $con->{"wiki_data"};
//print_r( $db );
$col = $db->{"wiki_props"};

//$_id = new MongoDB\BSON\ObjectId("622e481eb54ade217c9cf8e7");
$_id = "";
$cnt = 0;

while( 1 ){
	$cond = [];
	if( $_id ){
		$cond['_id']=['$gt'=>$_id];
	}
	$records = $col->find( $cond,['sort'=>['_id'=>1],'limit'=>100] )-toArray();
	//print_r( get_class_methods($records) );
	//exit;
	if( sizeof($records) == 0 ){break;}
	foreach( $records as $i=>$j ){
	//	print_r( $j );
		//print_r( bson($j) );
		echo $cnt . ": " . $j['id'] . "\n";
		$redis->hmset("wiki_props:". $j['id'], $j);
		$_id = $j['_id'];
		$cnt++;
	}
}

?>