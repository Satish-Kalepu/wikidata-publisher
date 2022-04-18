<?php


$res = new SQLite3( "test.db" );

$list = $res->query( "SELECT name FROM sqlite_master WHERE type='table'" );

print_r( get_class_methods( $list ) );

$row = $list->fetchArray( );
if( $row ){

	print_r( $row );


}else{

	$res = $res->exec("CREATE TABLE IF NOT EXISTS things(label varchar(100) PRIMARY KEY, data TEXT, id )" );
	if( !$res ){
		echo "Error Create table";
			exit;		
	}else{
		echo "Created table";
		exit;
	}


}

exit;

/*
CREATE TABLE [IF NOT EXISTS] table(
   primary_key INTEGER PRIMARY KEY,
   column_name type NOT NULL,
   column_name type NULL,
   ...
);

*/

?>
