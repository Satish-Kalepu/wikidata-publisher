<?php

require("../vendor/autoload.php");

//header("Content-Type: text/plain");

$con = new MongoDB\Client("mongodb://localhost", [], ['typeMap'=>[
	'array'=>'array', 'root'=>'array', 'document'=>'array'
]]);

$db = $con->wiki_data;

$col = $db->wiki_data3;

$res = $col->find([
	"_id"=>['$gt'=>"Q5:devi sri"]
], [
	'sort'=>['_id'=>-1], 
	'projection'=>['label'=>1,'aliases'=>1, 'inst'=>1],
	'limit'=>5000
])->toArray();

echo "<table border='1' style='border-collapse:collapse;' cellpadding='5'>";

foreach( $res as $i=>$j ){

	$label = trim($j['label']);
	$f = false;
	for($i=0;$i<strlen($label);$i++){
		if( ord( $label[$i] ) > 127 ){ $f = true; }
	}
	if( !$f ){
	$words = [];
	$aliases = [ $label => 1 ];
	echo "<tr>";
	echo "<td>" . $j['_id'] . "</td>";
	echo "<td>" . $j['label'] . "</td>" ;
	echo "<td>";
	if( $j['aliases'] ){
		foreach( $j['aliases'] as $ii=>$jj ){
			echo  $jj . "<BR>";
			$aliases[ $jj ] = 2;
		}
	}
	echo "</td>";
	echo "<td>";

	if( !preg_match("/\W(for|in|of)\W/i", $j['label'] ) && !preg_match("/^[a-z]+[\-\ \_\:]+[0-9]+$/i", $j['label']) && !preg_match("/^[0-9]+[\-\ \_\:]+[a-z]+$/i", $j['label']) ){

	$parts = preg_split("/\W+/", $j['label'] );
	if( sizeof($parts) < 5 ){
		for($pi=0;$pi<sizeof($parts);$pi++){
			if( strlen($parts[$pi])<=1 ){ array_splice($parts,$pi,1); $pi--; }
		}
	}

	if( sizeof($parts) < 4 ){
	
	$word1 = implode(" ", $parts);
	if( !$aliases[ $word1 ]  ){
		$aliases[ $word1 ] = 3;
	}
	
	if( sizeof($parts) > 1 ){
		$t = array_splice($parts,0,1);
		$parts[] = $t[0];
		$word2 = implode(" ", $parts);
		if( $label != $word2 ){
			//echo $word2 . "<BR>";
			if( !$aliases[ $word2 ]  ){
		                $aliases[ $word2 ] = 4;
		        }

		}
	}

	if( sizeof($parts) > 2 ){
                $t = array_splice($parts,0,1);
                $parts[] = $t[0];
		$word3 = implode(" ", $parts);
                //echo $word3. "<BR>";
		if( !$aliases[ $word3 ]  ){
	                $aliases[ $word3 ] = 5;
	        }
        }

	}

	}

	foreach( $aliases as $ad=>$ai){
		echo $ad . "<BR>";
	}
//	print_r( $aliases );

	echo "</td>";
	echo "</tr>\n";
	}

}
echo "</table>";

?>
