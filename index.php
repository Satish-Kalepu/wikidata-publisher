<?php

ini_set("display_errors", "On");
ini_set("display_startup_errors", "On");
error_reporting(E_ALL & ~E_NOTICE);

if( $_GET['action'] == "setadmin" ){
	setcookie("admin", "yes", time()+86400, "/");
	header("Location: /wiki/?event=refresh");
	exit;
}

$start = microtime(true);

//phpinfo();exit;
function print_pre($v){
	if( is_array($v) ){
		echo "<pre>";print_r($v);echo "</pre>";
	}else{
		echo $v;
	}
}

require( $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php" );

$con = new MongoDB\Client("mongodb://localhost", [], [
	'typeMap'=>[
		'root'=>'array',
		'document'=>'array',
		'array'=>'array',
		'objectid'=>'string',
		'object'=>'array',
		'id'=>'string',
		'date'=>'date'
	]
]);

$db = $con->{"wiki_data"};
//print_r( $db );
$wiki_data = $db->{"wiki_data"};
$wiki_images = $db->{"wiki_images"};
$keywords = $db->{"keywords"};
$wiki_data3 = $db->{"wiki_data3"};
$wiki_props = $db->{"wiki_props"};
$wiki_instances = $db->{"wiki_instances"};

$fetches = [];
function fetch_it($v){
	global $fetches;
	global $wiki_data;
	if( $fetches[ $v ] ){
		return $fetches[ $v ];
	}else{
		$r = $wiki_data->findOne(["id"=>$v],['projection'=>['label'=>1] ]);
		$fetches[ $v ] = $r['label'];
		return $r['label'];
	}
}

$headings = [];
function find_prop_labels(){
	global $headings;
	global $props;
	global $wiki_props;
	if( is_array($props)){
	foreach( $props as $i=>$j ){
		$r = $wiki_props->findOne(["id"=>$i],['projection'=>['label'=>1] ]);
		$headings[ $i ] = $r['label'];
	}
	}
}
function fetch_prop( $v ){
	global $headings;
	global $wiki_props;
	if( $headings[ $v ] ){
		return $headings[ $v ];
	}else{
		//echo "fetching . " . $v ;
		$r = $wiki_props->findOne(["id"=>$v],['projection'=>['label'=>1] ]);
		if( $r ){
			$headings[ $v ] = $r['label'];
		//	echo "found";
			return $r['label'];
		}else{
			return $v;
		}
	}
}

function get_image_file($v){
	$k = pathinfo($v);
	$k['basename'] = str_replace(".".$k['extension'], "", $k['basename']);
	$d = file_get_contents("https://commons.wikimedia.org/wiki/File:".urlencode(str_replace(" ","_",$k['basename'])) . ".". $k['extension']);
	preg_match("/\<img(.*?)File\:(.*?)\/\>/", $d, $m );
	if( $m ){
		preg_match("/src\=\"(.*?)\"/", $m[0], $mm );
		if( $mm ){
			return $mm[1];
		}else{
			return "";
		}
	}else{
		return "";
	}
}
if( $_GET['action'] == "update_favourite" ){
	if( $_GET['fav'] ){
		$wiki_instances->updateOne(["_id"=>$_GET['id']], ['$set'=>['fav'=>true]] );
	}else{
		$wiki_instances->updateOne(["_id"=>$_GET['id']], ['$unset'=>['fav'=>1]] );
	}
	header("Content-Type: application/json");
	echo json_encode([ "status"=>"success" ]);
	exit;
}

if( $_GET['action'] == "check_image_file" ){
	header("Content-Type: application/json");
	echo json_encode(["vid"=>$_GET['vid'], "file"=>get_image_file( $_GET['file'] ) ] );
	exit;
}

if( $_GET['action'] == "category_search_keyword" ){
	$records = $wiki_data->find([
		"claims.P31.v"=>$_GET['category'],
		"label"=>[
			'$gte'=>$_GET['keyword'],
			'$lt'=>$_GET['keyword']
		]
	], [
		'limit'=>50,
		'sort'=>['label'=>1]
	])->toArray();
	header("Content-Type: application/json");
	echo json_encode(["status"=>"success", "records"=>$records]);
	exit;
}


if( $_GET['action'] == "category_items" ){
	$cond = [
		'_id'=> [
			'$gt'=> $_GET['category'],
			'$lt'=> $_GET['category'] . "zz"
		]
	];
	if( $_GET['next'] ){
		$cond['_id']['$gt'] = $_GET['next'];
	}
	$records = $wiki_data3->find($cond,[
		'sort'=>['_id'=>1],
		'limit'=>100,
	])->toArray();

	header("Content-Type: application/json");
	echo json_encode(["status"=>"success", "records"=>$records]);
	exit;
}

if( $_GET['action'] == "category_find_keyword" ){

	$where = "";$where2 = "";
	$limit = ($_GET['limit']?$_GET['limit']:100);
	if( $_GET['keyword'] ){

	}else{
		header("Content-Type: application/json");
		echo json_encode([
			"status"=> "success",
			"keywords"=> []
		]);
		exit;
	}

	$keys = [];
	$res = $keywords->find([
		'_id'=>[
			'$gte'=>$_GET['category'].":".$_GET['keyword'],
			'$lte'=>$_GET['category'].":".$_GET['keyword']."zzzz"
		]
	],[
		'sort'=>['_id'=>1],
		'limit'=>100
	])->toArray()	;
	header("Content-Type: application/json");
	echo json_encode([
		"status"=>"success",
		"keyword"=>$_GET['keyword'],
		"keywords"=>$res,
		"where1"=>[
			'_id'=>[
				'$gte'=>$_GET['category'].":".$_GET['keyword'],
				'$lte'=>$_GET['category'].":".$_GET['keyword']
			]
		],
	]);
	exit;

	exit;
}
if( $_GET['action'] == "global_find_keyword" ){

	$limit = ($_GET['limit']?$_GET['limit']:100);
	if( $_GET['keyword'] ){

	}else{
		header("Content-Type: application/json");
		echo json_encode([
			"status"=> "success",
			"keywords"=> []
		]);
		exit;
	}

	$keys = [];
	$res = $keywords->find([
		'_id2'=>[
			'$gte'=>$_GET['keyword'],
			'$lte'=>$_GET['keyword']."zzzz"
		]
	],[
		'sort'=>['_id2'=>1],
		'limit'=>100
	])->toArray()	;
	header("Content-Type: application/json");
	echo json_encode([
		"status"=>"success",
		"keyword"=>$_GET['keyword'],
		"keywords"=>$res,
		"size"=>sizeof($res)
	]);
	exit;

	exit;
}



if( $_GET['action']== "satish_product_search" ){

	$where = "";$where2 = "";
	$limit = ($_GET['limit']?$_GET['limit']:100);
	if( $_GET['keyword'] ){
		$keyword = $_GET['keyword'];
		$keyword = preg_replace("/\W+/", " ", trim($keyword));
		$words = preg_split("/\W+/", $keyword);
		$keyword = implode("%",$words);
		array_reverse($words);
		$keyword2 = implode("%", $words);
		$where1 = "product > '" . $_GET['keyword'] . "' and product like '". $keyword . "%' ";
		$where2 = "product like '%" . $keyword . "%' or product like '%" . $keyword2 . "%' ";
	}else{
		header("Content-Type: application/json");
		echo json_encode([
			"status"=> "success",
			"keywords"=> []
		]);
		exit;
	}

	$keys = [];
	$res = $db->select([
		"table"=>"cases_product_names_unique",
 		"where"=>$where1,
 		"fields"=>"id, product as keyword",
		"orderby"=>"product",
		"limit"=>$limit
	]);
	foreach( $res as $i=>$j ){ $keys[ $j['id'] ] = 1;}
	$res2 = $db->select([
		"table"=>"cases_product_names_unique",
		"fields"=>"id, product as keyword",
 		"where"=>$where2,
		"orderby"=>"product",
		"limit"=>$limit
	]);
	foreach( $res2 as $i=>$j ){ if( !$keys[ $j['id'] ] ){ $res[] = $j; } }

	if( $db->error ){
		header("Content-Type: application/json");
		echo json_encode([
			"status"=> "fail",
			"error"=> $db->error
		]);
		exit;
	}
	header("Content-Type: application/json");
	echo json_encode(["status"=>"success", "keyword"=>$_GET['keyword'], "keywords"=>$res, "where1"=>$where1, "where2"=>$where2]);
	exit;
}


//echo get_image_file("Muybridge race horse animated.gif");
//exit;

//print_r( $props );exit;

?><html>
<head>
	<title>Wiki</title>
	<link rel="stylesheet" href="bootstrap-5.1.3/css/bootstrap.min.css" >
	<script src="vue.min.js" ></script>
	<script src="jquery-3.6.0.js"></script>
	<script src="jquery-ui.js"></script>
	<script src="axios.min.js"></script>
	<script src="app_search_v1.js?v=<?=time()?>" ></script>
	<script src="app_search_v2.js?v=<?=time()?>" ></script>
	<link rel="stylesheet" href="jquery-ui.css">
	<style>
		.bb{ max-width:200px; max-height:50px; overflow: auto; }
		.bb::-webkit-scrollbar { width: 5px; height: 5px; }
		.bb::-webkit-scrollbar-track { background: #f1f1f1;}
		.bb::-webkit-scrollbar-thumb { background: #888;}
		.bb::-webkit-scrollbar-thumb:hover { background: #555;}
	</style>
</head>
<body>

	<div style="position: fixed; z-index: 501; top:0px; height: 50px; padding: 5px 0px; width: 100%; background-color: #f0f0f0;" >
		<a href="?show=all" style="float:right; margin-right:10%;" >Categories</a>
		<div style="width:150px; padding: 2px; display: inline-block;" ><b>Wiki Data</b></div>
		<div style="width:400px; max-width: 600px; height:25px; padding: 2px; background-color:#f0e8f0; z-index: 5; display: inline-block;" title="Search" >
			<div id="global_keyword_search"></div>
		</div>
	</div>
	<div style="height: 60px;" >&nbsp;</div>

	<?php if( $_GET['id'] ){

		require("include_id.php");

	}elseif( $_GET['prop'] ){

		require("include_prop.php");

	}elseif( $_GET['category'] ){

		require("include_category.php");

	}elseif( $_GET['show'] == "properties" ){

		require("include_properties.php");

	}else{ ?>

		<style>
			.box1{ border-bottom: 1px solid #fed; height: 120px; overflow: hidden;}
			.box1 .cnt{float: right;}
			.fr{float: right;}
		</style>
		<?php
		$perpage = 1000;
		$wiki_instances = $db->wiki_instances;
		$o = ['sort'=>['cnt'=>-1],'limit'=>$perpage];
		if( $_GET['skip'] ){ $o['skip'] = (int)$_GET['skip']; }
		$records = $wiki_instances->find([],$o)->toArray();

		$o = ['sort'=>['cnt'=>-1],'limit'=>$perpage];
		$favs = $wiki_instances->find(['fav'=>true],$o)->toArray();
		//print_r($records);
		?>
		<div class="container" >

			<?php if( 1==2 ){ ?>

				<div class="row" >
				<?php foreach( $records as $i=>$j ){

					$k = implode(" ", array_splice( preg_split("/[\ ]+/",$j['des']) , 0,15 ) );
					$t = htmlspecialchars( ucwords( $k ) );

					echo "<div class='col-4 pb-3 text-left'>
					<div class='text-left'><a class=\"p-0 m-0 text-left\" href='?category=".$j['_id']."' >". $j['label'] . "</a></div>
					<div class='btn btn-outline-default fr' >".$j['cnt']."</div>
					<div class='text-secondary' >".$t."</div>
					</div>";
				}
				?>
				</div>

			<?php }else{ ?>

				<style>
					.apr{text-decoration: none; display: block;}
					.apr:hover{ background-color: #e0e0f0; }
				</style>


				<table class="table table-bordered table-striped table-hover table-sm w-auto" >
					<?php foreach( $favs as $i=>$j ){
						$k = implode(" ", array_splice( preg_split("/[\ ]+/",$j['des']) , 0,15 ) );
						$t = htmlspecialchars( ucwords( $k ) );
						?>
					<tr>
						<td>
							<?php if( $_COOKIE['admin'] ){ ?>
							<input type="checkbox" data-id="<?=$j['_id'] ?>" onclick="setTimeout(updateit,100,this)" <?=$j['fav']?"checked":"" ?> style="float:right;" >
							<?php  } ?>
							<a class="apr" href='?category=<?=$j['_id'] ?>' ><?=htmlspecialchars(ucwords($j['label'])) ?></a>
						</td>
						<td><?=$t ?></td>
						<td align="right"><?=$j['cnt'] ?></td>
					</tr>
					<?php } ?>
				</table>

				<div>---</div>

				<table class="table table-bordered table-striped table-hover table-sm w-auto" >
					<?php foreach( $records as $i=>$j ){
						$k = implode(" ", array_splice( preg_split("/[\ ]+/",$j['des']) , 0,15 ) );
						$t = htmlspecialchars( ucwords( $k ) );
						?>
					<tr>
						<td>
							<?php if( $_COOKIE['admin'] ){ ?>
							<input type="checkbox" data-id="<?=$j['_id'] ?>" onclick="setTimeout(updateit,100,this)" <?=$j['fav']?"checked":"" ?> style="float:right;" >
							<?php  } ?>
							<a class="apr" href='?category=<?=$j['_id'] ?>' ><?=htmlspecialchars(ucwords($j['label'])) ?></a>
						</td>
						<td><?=$t ?></td>
						<td align=right><?=$j['cnt'] ?></td>
					</tr>
					<?php } ?>
				</table>
				<div align="center"><a class="btn btn-outline-lik" href="?skip=<?=($_GET['skip']+$perpage) ?>" >Next Page</a></div>

			<?php } ?>
		</div>

		<?php if( $_COOKIE['admin'] ){ ?>
		<script>
			function updateit( v ){
				axios.get("?action=update_favourite&id=" + v.getAttribute("data-id") + "&fav=" + (v.checked?"yes":"") ).then(response=>{

				});
			}
		</script>
		<?php } ?>


	<?php } ?>

<script>
	$(function() {

		//create_search_app ( div_id, hidden_input_id, apiurl )
		create_search_app_v2({
			"mount_to": "global_keyword_search",
			"hidden_field": "",
			"url": "?action=global_find_keyword&keyword=##KEYWORD##&limit=##LIMIT##",
			"display_text": "Global Search",
			"select_event": "global_keyword_selected"
		});
	});
	function global_keyword_selected(v){
		document.location = "?id="+v;
	}
</script>

</body>
</html>
<?php
$end = microtime(true);
echo "<!-- duration: " . ($end-$start) . " -->";
?>