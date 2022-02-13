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
	$step = "Create BD";
	$BD = '';
	while($buffer = fgets($file)){
		if(!strcmp($buffer,"\n") && str_contains($step,"Create BD")){
			updateBD($BD);
			$step = "BD Done";
		}
		if(str_contains($step,"Create BD")){
			$BD .= $buffer.'-';
		}
		
		if(str_contains($buffer,"commit")){
			echo 'Commit<br>';
		}
	}
}

function str_contains($haystack, $needle) {
	return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}

function updateBD($values){
	global $conn;
	foreach(explode('-',$values) as $row){
		if($row != ''){
			$array = explode(',',$row);
			$arrayTwo = explode('=',$array[1]);
			$query = 'UPDATE `tabela1` SET `'.$array[0].'`='.$arrayTwo[1].' WHERE `id`='.$arrayTwo[0].'';
			$result = $conn -> prepare($query);
			$result -> execute();
		}
	}
}
?>