<?php
$records = $wiki_props->find([],['limit'=>100])->toArray();
//echo "<pre>";print_r( $records[0] );exit;
$props = [];
foreach( $records as $i=>$j ){
	if( $j['claims'] ){
	foreach( $j['claims'] as $ii=>$jj ){
		$props[ $ii ] += 1;
	}
	}
}
arsort($props);

find_prop_labels();
//echo '<pre>';
//print_r( $headings );exit;

$props_group_below = sizeof($records)*.2;
?>

<table class="table table-bordered table-hover table-sm w-auto" >
<thead class="bg-dark text-white" style="position: sticky;top:0px;">
<tr>
	<?php
	echo "<td>id</td>";
	echo "<td>Label</td>";
	echo "<td>Alias</td>";
	echo "<td>Des</td>";
	foreach( $props as $p=>$pi){if( $pi> $props_group_below ){
		echo "<td>" . ($headings[$p]?$headings[$p]:$p) . "</td>";
	}}
	echo "<td>Extras</td>";
	?>
</tr>
</thead>
<tbody>
<?php foreach( $records as $i=>$rec ){ ?>
<tr>
	<?php
	echo "<td><a target='_blank' href='?id=".$rec['id']."' >".$rec['id']."</a></td>";
	echo "<td nowrap><div class='bb' >".$rec['label']."</div></td>";
	echo "<td nowrap><div class='bb' >";
	foreach( $rec['aliases'] as $r=>$rd ){
		echo "<div>".$rd."</div>";
	}
	echo "</div></td>";
	echo "<td nowrap><div class='bb' >".$rec['des']."</div></td>";
	foreach( $props as $p=>$pi){if( $pi> $props_group_below ){
		echo "<td nowrap><div class='bb' >";
		if( gettype($rec['claims'][ $p ]) == "array" ){
			//print_r( $rec['claims'][$p] );
			foreach( $rec['claims'][$p] as $kk=>$mm ){
				if( gettype($mm['v']) == "string" ){
					if( preg_match("/^Q[0-9]+$/", $mm['v']) ){
						echo "<a href='?id=".$mm['v']."' >" .fetch_it($mm['v']) . "</a><BR>";
					}else if( preg_match("/^P[0-9]+$/", $mm['v']) ){
						echo "<a href='?id=".$mm['v']."' >" .fetch_prop($mm['v']) . "</a><BR>";
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
			echo $rec['claims'][ $p ] . "<BR>";
		}
		echo "</div></td>";
	}}
	echo "<td nowrap><div class='bb' >";
	foreach( $props as $p=>$pi){if( $pi<= $props_group_below ){
		if( $rec['claims'][ $p ] ){
		echo "<div><i><u>" . ($headings[$p]?$headings[$p]:$p) . ":</i></u> ";
		if( gettype($rec['claims'][ $p ]) == "array" ){
			//print_r( $rec['claims'][$p] );
			foreach( $rec['claims'][$p] as $kk=>$mm ){
				if( gettype($mm['v']) == "string" ){
					if( preg_match("/^Q[0-9]+$/", $mm['v']) ){
						echo "<a href='?id=".$mm['v']."' >" .fetch_it($mm['v']) . "</a><BR>";
					}else if( preg_match("/^P[0-9]+$/", $mm['v']) ){
						echo "<a href='?id=".$mm['v']."' >" .fetch_prop($mm['v']) . "</a><BR>";
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
			echo $rec['claims'][ $p ] . "<BR>";
		}
		echo "</div>";
		}
	}}
	echo "</div></td>";
	?>
</tr>
<?php } ?>
</tbody>
</table>