<?php
function showTable(){
	global $conn;
	$query = "SELECT * FROM tabela1";
	$result = $conn -> prepare($query);
	$result -> execute();
	printTable($result->get_result(),'Tabela1');
}

function printTable($results, $tableName = 'noName'){
	echo $tableName . '<br>';
	foreach($results as $row){
		print_r($row);
		echo '<br>';
	}
}
?>