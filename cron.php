<?php

//phpinfo(); exit;
exit;

ini_set("display_errors", "on");
ini_set("display_startup_errors", "on");
error_reporting(E_ALL & ~E_NOTICE);

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
//print_r( $con );
function bson_to_json($v){
	$c = get_class($v);
	//echo "<div>" . $c . "</div>";
	if( $c == "MongoDB\Model\BSONDocument" || $c == "MongoDB\Model\BSONArray" ){
		$v = $v->getArrayCopy();
	}else if( $c =="MongoDB\BSON\ObjectId" ){
		//$v = (string)$v;
		return $v;
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

$_id = new MongoDB\BSON\ObjectId("6240bfaa3dfd8f71f3814616");
//$_id = "";
$cnt = 0;
$found =0;
while(1){
	echo "loop " . $m . "\n";
	$cond = [];
	if( $_id ){
		$cond['_id']=['$lt'=>$_id];
	}
	$records = $col->find($cond,[
		'projection'=>[
			'id'=>1,
			'wiki_id'=>1,
			'label'=>1,
		],
		'sort'=>['_id'=>-1],
		'limit'=>200
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
		echo $cnt . ": " . $j['_id'] . ": ". $j['id'] . ": " . $j['wiki_id'] . "\n";
		if( !$j['label'] ){
			$col->deleteOne(['_id'=>$j['_id']]);
			echo "Deleted\n";
		}
		if( !$j['wiki_id'] ){
			$id = str_replace("Q","",$j['id']);
			$col->updateOne(['_id'=>$j['_id']],['$set'=>['wiki_id'=>(int)$id]]);
		}else{
			$found += 1;
		}
		if( $found > 100000){
			exit;
		}
		//print_r( bson($j) );
		$cnt++;
	}
}
?>