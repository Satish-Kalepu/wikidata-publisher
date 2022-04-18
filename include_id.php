<div class="container" >
<?php



//$count = $wiki_data->count(['claims.P31.v'=>$_GET['id']]);

$item = $wiki_data->findOne(["id"=>$_GET['id']]);
if( $_GET['test'] == "test" ){
	print_pre( $item );
}
if( $item ){

	$pnames = array_keys($item['claims']);
	//print_r( $pnames );
	//exit;
	$props_order = $wiki_props->find([
		"id"=>['$in'=> $pnames ]
	],[
		'projection'=>["id"=>1,"cnt"=>1,'label'=>1],
		'sort'=>['cnt'=>-1]
	])->toArray();
	$pranks = [];
	foreach( $props_order as $i=>$j ){
		$headings[ $j['id'] ] = $j['label'];
		$pranks[ $j['id'] ] = $j['cnt'];
	}

	?>
	<p>
		<?php if( $item['claims']['P31'] ){
		echo "<div style='float:right;' >";
		echo " in ";
		foreach( $item['claims'][ "P31" ] as $i=>$j ){
			if( preg_match("/^Q[0-9]+$/", $j['v']) ){
				$ctr = $wiki_instances->findOne(['_id'=>$j['v'] ]);
			?>
			<a href="?category=<?=$j['v'] ?>" class="btn btn-outline-primary btn-sm" ><?=htmlspecialchars(ucwords($ctr['label'])) ?> <spam class="badge bg-dark text-white" ><?=$ctr['cnt'] ?></spam></a>
			<?php }
		}
		echo "</div>";
		}
		?>
		<b><?=htmlspecialchars(ucwords($item['label'])) ?></b>
		<?php 
		$ctr = $wiki_instances->findOne(['_id'=>$item['id'] ]);

		if( $ctr ){ ?>
		<a class="btn btn-light text-black btn-sm" href="?category=<?=$_GET['id'] ?>" >Browse <spam class="badge bg-dark text-white" ><?=$ctr['cnt'] ?></spam></a>
		<?php } ?>

	</p>
	<table class="table table-bordered table-sm w-auto" >
	<tbody>
	<tr>
		<td>Des</td>
		<td><?=htmlspecialchars($item['des']) ?></td>
	</tr>
	<tr>
		<td>Alias</td>
		<td><?php foreach( $item['aliases'] as $ai=>$aj ){
			echo "<div>" . htmlspecialchars($aj) ."</div>";
		} ?></td>
	</tr>
	<?php

	$end = microtime(true);
	echo "<!-- duration: " . ($end-$start) . " -->";

	foreach( $props_order as $_i=>$prec ){
		$p = $prec['id'];
		$v = $item['claims'][ $p ];
		if( $p != "P31" && $p != "P646"){
		echo "<tr>";
		if( $pranks[ $p ] > 100000 ){
			echo "<td><a href='?id=".$p."' >" . fetch_prop($p) ."</a></td>";
		}else{
			echo "<td>" . fetch_prop($p) . "</td>";
		}
		echo "<td>";
		if( gettype($v) == "array" ){
			//print_r( $rec['claims'][$p] );
			foreach( $v as $kk=>$mm ){
				if( gettype($mm['v']) == "string" ){
					if( preg_match("/^Q[0-9]+$/", $mm['v']) ){
						echo "<a href='?id=".$mm['v']."' >" .fetch_it($mm['v']) . "</a><BR>";
					}else if( preg_match("/^P[0-9]+$/", $mm['v']) ){
						echo "<a href='?id=".$mm['v']."' >" .fetch_prop($mm['v']) . "</a><BR>";
					}else if( preg_match( "/^[\+\ \t]+[0-9]{4}\-[0-9]{2}\-[0-9]{2}/", $mm['v'] ) ){
						$dt = date("Y-m-d", strtotime($mm['v'] ) );
						echo $dt;
					}else if( $p == "P18"){
						echo "<div class='dataimageid' data-img-id='".$mm['v']."' >Loading...</div>";
					}else{
						echo $mm['v'] . "<BR>";
					}
				}else if( is_array($mm['v']) ){
					if( $mm['v']['u'] && $mm['v']['v'] ){
						echo "<a href='".$mm['v']['u'] ."' >".$mm['v']['v']."</a>" . "<BR>";
					}else if( $mm['v']['lat'] && $mm['v']['lon'] ){
						echo "Lat:".$mm['v']['lat']."<BR>Lon:".$mm['v']['lon'] . "<BR>";
					}else if( $mm['v']['lang'] && $mm['v']['v'] ){
						echo $mm['v']['lang'].": ". $mm['v']['v'] . "<BR>";
					}else{
						print_r( $mm['v'] );
					}
				}else{
					print_r( $mm['v'] );
				}
			}
		}else{
			echo $v . "<BR>";
		}
		echo "</td>";
		echo "</tr>";
		$end = microtime(true);
		echo "<!-- duration: " . ($end-$start) . " -->";
	}}
	?>
	</tbody>
	</table>
<?php }else{
	echo "<div class='alert alert-danger' >Record not found!</div>";
} ?>

</div>

<script>
$(function(){

	var v = document.getElementsByClassName("dataimageid");
	for(var i=0;i<v.length;i++){
		var im = v[i].getAttribute("data-img-id");
		axios.get("?action=check_image_file&file="+encodeURIComponent(im)+"&vid="+i).then(response=>{
			var v = document.getElementsByClassName("dataimageid");
			v[ Number(response.data['vid']) ].innerHTML = `<a href="`+response.data['file']+`" target='_blank' >
				<img width='200' src="`+response.data['file']+`">
			</a>`;
		});
	}

});
</script>