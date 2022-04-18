<?php

//phpinfo(); exit;

ini_set("display_errors", "on");
ini_set("display_startup_errors", "on");
error_reporting(E_ALL & ~E_NOTICE);

require( $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php" );

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
$col = $db->{"wiki_data"};

$records = $col->find([],['sort'=>['_id'=>1],'limit'=>5]);
echo "<pre>";
//print_r( get_class_methods($records) );
//exit;
foreach( $records as $i=>$j ){
	print_r( $j );
	//print_r( bson($j) );
}
echo "<pre>";
echo sizeof($records);
print_r( $records );

?>