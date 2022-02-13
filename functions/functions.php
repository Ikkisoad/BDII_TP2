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

function getFile($file){
	while($buffer = fgets($file)){
		if(str_contains($buffer,"commit")){
			echo 'check this out';
		}
	}
}

function str_contains($haystack, $needle) {
	return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}
?>