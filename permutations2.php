<?php

ini_set("memory_limit", "2G");

require("../vendor/autoload.php");

//header("Content-Type: text/plain");
//

$labels = [];

$con = new MongoDB\Client("mongodb://localhost", [], ['typeMap'=>[
	'array'=>'array', 'root'=>'array', 'document'=>'array'
]]);

$db = $con->wiki_data;

$col = $db->wiki_data;
$glcol = $db->keywords;

$last_id = "";
$last_id = new MongoDB\BSON\ObjectId("622e48670a4630cf8f6dc633");
$cond = [];
$cnt=0;
while( 1 ){
$cnt++;
echo $cnt." : ". (string)$last_id . "\n" ;
//if( $cnt > 100 ){break;}
if( $last_id ){ $cond[ "_id" ] = ['$gt'=>$last_id]; }
//$cond['id'] = "Q513";
$res = $col->find($cond, [
	'projection'=>['_id'=>1, 'label'=>1,'des'=>1,'i_of'=>1,'id'=>1, 'wiki_id'=>1,'aliases'=>1 ],
	'sort'=>['_id'=>1], 
	'limit'=>500
])->toArray();

//print_r( $res );

if( sizeof($res) == 0 ){
	echo "\n\nno results";exit;
}

foreach( $res as $i=>$j ){
	$last_id = $j['_id'];
	$label = trim($j['label']);
	//echo $j['id'] . " : " . $label . "\n\n";
	$f = false;
	for($i=0;$i<strlen($label);$i++){
		if( ord( $label[$i] ) > 127 ){ $f = true; }
	}
	if( !$f ){
	$words = [];
	$aliases = [ $label => 1 ];
	if( $j['aliases'] ){
		foreach( $j['aliases'] as $ii=>$jj ){
			$aliases[ $jj ] = 2;
		}
	}
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

	//print_r($aliases);exit;

	foreach( $aliases as $ad=>$ai){
		foreach( $j['i_of'] as $insti=>$inst ){
			//echo $inst.":".$ad . "\n";

			if( !$labels[ $inst ] ){
				$_l = $col->findOne(["id"=>$inst],['projection'=>['label'=>1] ]);
				$labels[ $inst ] = $_l['label'];
				echo "Lables: ". sizeof($labels) . "\n";
			}

			try{
				$ures = $glcol->updateOne([
					"_id"=>$inst.":".$ad,
				],[
					'$set'=>[
						"_id2"=>$ad,
						"wiki_id"=>$j['wiki_id'],
						"des"=>$j['des'],
						"m"=>($j['label']==$ad?"y":"n"),
						"id"=>$j['id'],
						"o_id"=>(string)$j['_id'],
						"inst"=>$inst,
						"label_in"=>$labels[ $inst ]
					]
				], [
					"upsert"=>true
				]);
				//print_r($ures);
			}catch(Exception $ex){
				$er = $ex->getMessage();
				if( !preg_match("/duplicate/i", $er ) ){
					echo (string)$j['_id'] . "\n";
					echo $inst .":" . $ad . "\n";
					echo $er; exit;
				}
			}
		}

	}

	}

}

//exit;

}

?>
