<?php

$item = $wiki_data->findOne([ "id" => $_GET['category'] ]);

?>
<link rel="stylesheet" href="bootstrap-vue.min.css" >
<script src="bootstrap-vue.min.js" ></script>
<script src="bootstrap-vue-icons.min.js" ></script>


<?php
$cond = [
	'_id'=> [
		'$gt'=> $_GET['category'],
		'$lt'=> $_GET['category'] . "zz"
	]
];
if( $_GET['start_keyword'] ){
	if( $_GET['order'] == 'DSC' ){
		$cond = [
			'_id'=> [
				'$gt'=> $_GET['category'],
				'$lte'=> $_GET['category'].":".$_GET['start_keyword']."zz"
			]
		];
	}else{
		$cond = [
			'_id'=> [
				'$gte'=> $_GET['category'].":".$_GET['start_keyword'],
				'$lt'=> $_GET['category'] . "zz"
			]
		];
	}
}
if( $_GET['next'] && $_GET['order'] == 'DSC' ){
	$cond['_id']['$lt'] = $_GET['next'];
}else if( $_GET['order'] == 'DSC' ){
	$cond['_id']['$lt'] = $_GET['category'] . ":zzzzzzzzzzz";
}else if( $_GET['next'] ){
	$cond['_id']['$gt'] = $_GET['next'];
}
//print_r( $cond );exit;
$items = $wiki_data3->find($cond,[
	'sort'=>['_id'=> ($_GET['order']=='DSC'?-1:1) ],
	'limit'=>100,
])->toArray();

//print_pre($items);

$next = "";
$props = [];
foreach( $items as $i=>$j ){
	$next = $j['_id'];
	foreach( array_keys($j['claims']) as $ii=>$jj ){
		$props[ $jj ]+=1;
	}
}
if( sizeof($items) <100){$next= "";}
arsort($props);
//print_pre( $props );
$props_list = $wiki_props->find([
	'id'=>['$in'=> array_keys($props)]
], ['projection'=>[
	'label'=>1,'cnt'=>1,'des'=>1,'id'=>1,'_id'=>false,
] ])->toArray();

//print_pre( $props_list );
$config_properties = [];
foreach( $props_list as $i=>$j ){
	$config_properties[ $j['id'] ] = $j;
}

function cutit($v){
	$x = explode(" ", $v);
	return implode(" ", array_splice($x,0,6) );
}

//exit;

?>
<style>
	.colsp{ min-width: 100px;max-width:200px; overflow:auto; white-space: nowrap; max-height: 150px; }
	.colsp::-webkit-scrollbar {width: 5px; height: 5px;}
	.colsp::-webkit-scrollbar-track {background: #f1f1f1;}
	.colsp::-webkit-scrollbar-thumb {background: #888;}
	.colsp::-webkit-scrollbar-thumb:hover {background: #555;}

	.colsp2{ min-width: 200px;max-width:400px; overflow:auto; white-space: nowrap; max-height: 150px; resize:both; }
	.colsp2::-webkit-scrollbar {width: 5px;height: 5px;}
	.colsp2::-webkit-scrollbar-track {background: #f1f1f1;}
	.colsp2::-webkit-scrollbar-thumb {background: #888;}
	.colsp2::-webkit-scrollbar-thumb:hover {background: #555;}

	.tbody tr.mk th{ border:1px solid black; }
	.mk{ border-bottom:1px solid black!important; }
</style>

<script>
	function goto_startwith(){
		var k = document.getElementById("start_keyword").value;
		if( k ){
			document.location = "?category=<?=$_GET['category'] ?>&start_keyword="+encodeURIComponent(k)+"&order=<?=$_GET['order'] ?>";
		}
	}
	var items = [];
	$(function() {

		$( window ).scroll(function(e) {
			//console.log( document.body.scrollTop );
			if( document.body.scrollTop < 200 ){
				$("#table_header").css("position","initial");
			}else{
				$("#table_header").css("position","sticky");
			}
		});

		//create_search_app ( div_id, hidden_input_id, apiurl )
		create_search_app_v1({
			"mount_to": "search_record",
			"hidden_field": "keyword_search_record_id",
			"url": "?action=category_find_keyword&category=<?=$_GET['category'] ?>&keyword=##KEYWORD##&limit=##LIMIT##",
			"display_text": "Local Search",
			"select_event": "keyword_selected1"
		});

	});
	function keyword_selected1(v){
		document.location = "?id="+v;
		//alert( v );
	}
	function change_order(v){
		document.location = "?category=<?=$_GET['category'] ?>&order="+v;
	}
</script>
<div style="position: fixed; width:100%; height: 100px; top:60px; background-color:white; z-index:20; " >
	<div><b>Category: <?=htmlspecialchars($item['label']) ?></b>
		<?php if( $_COOKIE['admin'] ){ ?>&nbsp;&nbsp;&nbsp;<input type="checkbox" data-id="<?=$item['id'] ?>" onclick="setTimeout(updateit,100,this)" <?=$j['fav']?"checked":"" ?> ><?php  } ?>
	</div>
	<div><?=htmlspecialchars($item['des']) ?></div>
	<?php if( $next ){ ?>
		<a href="?category=<?=$_GET['category'] ?>&next=<?=urlencode($next) ?>&order=<?=$_GET['order'] ?>" style="float:right; margin-right:10%;" >Next</a>
	<?php } ?>
	<div style="display: inline-block; border:1px solid #ccc;">
		<input type="text" id="start_keyword" value="<?=htmlspecialchars( $_GET['start_keyword'] ) ?>" placeholder="Label start with" ><input type="button" value="Goto" onclick="goto_startwith()" >
	</div>
	<div style="display: inline-block; border:1px solid #ccc; padding:0px 5px; margin:0px 5px;">
		Order: <select onchange="change_order(this.value)" id="order">
			<option value="ASC">Ascending</option>
			<option value="DSC" <?=$_GET['order']=='DSC'?'selected':'' ?> >Discending</option>
		</select>
	</div>
	<div style="width:400px; height:25px; padding: 2px; background-color:#f0e8f0; z-index: 5; display: inline-block;" title="Search" >
		<div id="search_record"></div>
	</div>
	<input type="hidden" id="keyword_search_record_id" >
</div>
<div style="height: 100px;" >&nbsp;</div>


  <table class="table table-striped table-hover table-bordered table-sm w-auto" >
    <thead id="table_header" style="position:; top:160px; z-index: 19; background-color: white;">
      <tr class="mk">
        <th>Id</th>
        <th>Label</th>
        <th>Description</th>
        <?php foreach( $props as $prop=>$cnt ){if( $cnt > 30 ){
        	if( !preg_match("/id$/i", $config_properties[ $prop ]['label']) ){ ?>
        	<th><?=htmlspecialchars( cutit($config_properties[ $prop ]['label']) ) ?></th>
        <?php }}} ?>
        <!-- <th>Others</th> -->
      </tr>

    </thead>
    <tbody>
    	<?php foreach( $items as $ii=>$row ){ ?>
      <tr>
      	<td><div class="colsp" ><a target="_blank"><a href="?id=<?=$row['id'] ?>" ><?=htmlspecialchars($row['id']) ?></a></div></td>
        <td><div class="colsp" ><b><?=htmlspecialchars($row['label']) ?></b></div></td>
        <td><div class="colsp" ><?=htmlspecialchars($row['des']) ?></div></td>
        <?php foreach( $props as $prop=>$cnt ){if( $cnt > 30 ){
        if( !preg_match("/id$/i", $config_properties[ $prop ]['label']) ){ ?>
	<td>
	<div class="colsp" >
		<?php
		if( $row['claims'][ $prop ] ){
		foreach( $row['claims'][ $prop ] as $pi=>$pd ){
			if( is_array( $pd['v'] ) ){
				if( $pd['v']['lang'] && $pd['v']['v'] ){
					echo htmlspecialchars($pd['v']['lang'] . ": " . $pd['v']['v']) . "<BR>";
				}else if( $pd['v']['lat'] && $pd['v']['lon'] ){
					echo "Lat:".$pd['v']['lat']."<BR>Lon:" . $pd['v']['lon'] . "<BR>";
				}else if( $pd['v']['u'] && $pd['v']['v'] ){
					echo "<a  target='_blank' href='".$pd['v']['u']."' >".htmlspecialchars($pd['v']['v']) . "</a><BR>";
				}else{
					echo "<pre>". json_encode( $pd['v'], JSON_PRETTY_PRINT ) . "</pre><BR>";
				}
			}else{
				if( preg_match( "/^[\+\ \t]+[0-9]{4}\-[0-9]{2}\-[0-9]{2}/", $pd['v'] ) ){
					$dt = date("Y-m-d", strtotime($pd['v'] ) );
					echo $dt;
				}else if( preg_match( "/^Q[0-9]+$/", $pd['v'] ) ){
					echo "<a target='_blank' href='?id=".$pd['v']."' >".htmlspecialchars( fetch_it( $pd['v'] ) ) . "</a><BR>";
				}else{
					echo htmlspecialchars( $pd['v'] ) . "<BR>";
				}
			}
		}}
		?>
	</div>
	</td>
        <?php }}}
        if(1==2){
        ?><td>
	<div class="colsp2" ><?php
        foreach( $props as $prop=>$cnt ){ if( $cnt <= 30 ){ ?>

		<?php
		if( $row['claims'][ $prop ] ){
		echo "<u>". htmlspecialchars($config_properties[ $prop ]['label']) . ":</u> ";
		foreach( $row['claims'][ $prop ] as $pi=>$pd ){

			if( is_array( $pd['v'] ) ){
				if( $pd['v']['lang'] && $pd['v']['v'] ){
					echo htmlspecialchars($pd['v']['lang'] . ": " . $pd['v']['v']) . "<BR>";
				}else if( $pd['v']['lat'] && $pd['v']['lon'] ){
					echo "Lat:".$pd['v']['lat']."<BR>Lon:" . $pd['v']['lon'] . "<BR>";
				}else if( $pd['v']['u'] && $pd['v']['v'] ){
					echo "<a  target='_blank' href='".$pd['v']['u']."' >".htmlspecialchars($pd['v']['v']) . "</a><BR>";
				}else{
					echo "<pre>". json_encode( $pd['v'], JSON_PRETTY_PRINT ) . "</pre><BR>";
				}
			}else{
				if( preg_match( "/^\+\ [0-9]{4}\-[0-9]{2}\-[0-9]{2}/", $pd['v'] ) ){
					$dt = date("Y-m-d", strtotime($pd['v'] ) );
					echo $dt;
				}else if( preg_match( "/^Q[0-9]+$/", $pd['v'] ) ){
					echo "<a  target='_blank' href='?id=".$pd['v']."' >".htmlspecialchars( fetch_it( $pd['v'] ) ) . "</a><BR>";
				}else{
					echo htmlspecialchars( $pd['v'] ) . "<BR>";
				}
			}
		}
		}
		?>

        <?php }} ?>
        </div>
	</td>
	<?php } ?>
      </tr>
	<?php } ?>
    </tbody>
  </table>


<?php if( $_COOKIE['admin'] ){ ?>
<script>
	function updateit( v ){
		axios.get("?action=update_favourite&id=" + v.getAttribute("data-id") + "&fav=" + (v.checked?"yes":"") ).then(response=>{

		});
	}
</script>
<?php } ?>
