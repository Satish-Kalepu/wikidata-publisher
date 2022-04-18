<div class="container" >
<p><b>Property</b> <a href="?show=properties" >Browse Properties</a></p>
<?php
$item = $wiki_props->findOne(["id"=>$_GET['prop']]);

//print_r( $item );

if( $item ){
if( $item['claims'] ){
$pnames = array_keys($item['claims']);

//exit;
$props_order = $wiki_props->find([
	"id"=>['$in'=> $pnames ]
],[
	'projection'=>["id"=>1,"cnt"=>1,'label'=>1],
	'sort'=>['cnt'=>-1]
])->toArray();
foreach( $props_order as $i=>$j ){
	$headings[ $j['id'] ] = $j['label'];
}
}
?>
<table class="table table-bordered table-sm w-auto" >
<tbody>
<tr>
	<td>Label</td>
	<td><?=htmlspecialchars($item['label']) ?></td>
</tr>
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
<tr>
	<td>Example</td>
	<td><?php foreach( $item['example'] as $ai=>$aj ){
		echo "<div>" . htmlspecialchars($aj) ."</div>";
	} ?></td>
</tr>
<tr>
	<td>Used</td>
	<td><?=$item['cnt'] ?> times</td>
</tr>
<?php
foreach( $props_order as $_i=>$prec ){
	$p = $prec['id'];
	$v = $item['claims'][ $p ];
	echo "<tr>";
	echo "<td><a href='?id=".$p."' >" . fetch_prop($p) . "</a></td>";
	echo "<td>";
	if( gettype($v) == "array" ){
		//print_r( $rec['claims'][$p] );
		foreach( $v as $kk=>$mm ){
			if( gettype($mm['v']) == "string" ){
				if( preg_match("/^Q[0-9]+$/", $mm['v']) ){
					echo "<a href='?id=".$mm['v']."' >" .fetch_it($mm['v']) . "</a><BR>";
				}else if( preg_match("/^P[0-9]+$/", $mm['v']) ){
					echo "<a href='?id=".$mm['v']."' >" .fetch_prop($mm['v']) . "</a><BR>";
				}else if( $mm['v']['lang'] && $mm['v']['v'] ){
					echo $mm['v']['lang'].": ". $mm['v']['v'] . "<BR>";
				}else{
					echo $mm['v'] . "<BR>";
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
}
?>
</tbody>
</table>
<?php }else{
echo "<div class='alert alert-danger' >Prop not found!</div>";
} ?>

</div>
