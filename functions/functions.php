<?php
function showTable(){
	global $conn;
	$query = "SELECT * FROM tabela1";
	$result = $conn -> prepare($query);
	$result -> execute();
	printTable($result->get_result(),'Tabela');
}

function printTable($results, $tableName = 'noName'){
	echo $tableName;
	echo '<table>';
		echo '<td>id</td>';
		echo '<td>A</td>';
		echo '<td>B</td>';
	foreach($results as $row){
		echo '<tr>';
		echo '<td>'.$row['id'] . '</td>';
		echo '<td>'.$row['A'] . '</td>';
		echo '<td>'.$row['B'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}

function getFile($file){
	$line = 1;
	$step = "Create BD";
	$BD = '';
	$transactions = array();
	$log = array(); //Log start of checkpoints
	$bdLog = array();
	echo '<textarea rows="50">';
	while($buffer = fgets($file)){
		echo '
'.$line++.':';
		if(!strcmp($buffer,"\n") && str_contains($step,"Create BD")){
			updateBD($BD);
			$step = "BD Done";
		}
		if(str_contains($buffer,"start")){
			$transactions[getTransactionID($buffer)] = '-';
			echo 'begin tran - ' . getTransactionID($buffer);
		}else if(str_contains($buffer,"commit")){
			echo 'Commit - ' . getTransactionID($buffer);
		}else if(str_contains($buffer,"CKPT")){
			if(str_contains($buffer,"Start")){
				$step = "CKPT";
				$ckptTrans = getCKPTTransactions($buffer);
				//echo sizeof($ckptTrans);
			}else{
				
			}
		}else if(str_contains($buffer,"T")){
			echo 'Update';
			$query = readQuery($buffer);
			$transactions[$query['transaction']] .= $query['column'].','$query['id'].'='.$query['value'].'-';
		}
		
		if(str_contains($step,"Create BD")){
			$BD .= $buffer.'-';
		}
		
	}
	echo '</textarea>';
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

function getTransactionID($string){
	return intval(str_replace("T",'',str_replace("<commit",'',str_replace(">",'',str_replace("<start ",'',$string)))));
}

function getCKPTTransactions($string){
	$retorno = array();
	$i = 0;
	$transactions = str_replace("<Start CKP(",'',str_replace(")>",'',str_replace("T",'',$string)));
	$explode = explode(',',$transactions);
	foreach($explode as $tran){
		$retorno[$i++] = intval($tran);
	}
	return $retorno;
}

function readQuery($string){
	$explode = explode(",",str_replace(">",'',str_replace("<T",'',$string)));
	$row = array(
		"transaction" => $explode[0],
		"id" => $explode[1],
		"column" => $explode[2],
		"value" => $explode[3],
	);
	return $row;
}
?>