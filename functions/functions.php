<?php
	//echo fread($openFile,10240).'fread<br>'; //A,1=20 A,2=20 B,1=55 B,2=30
	//$fileArray = file($_FILES['file']['tmp_name']);foreach($fileArray as $row){echo $row;}//Read current values
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
		echo '<tr><td>id</td>';
		echo '<td>A</td>';
		echo '<td>B</td></tr>';
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
	$redo = '';
	$retorno = '';
	$step = "CreateBD";
	$BD = '';
	$transactions = array();
	$log = array(); //Log start of checkpoints
	$bdLog = array();
	while($buffer = fgets($file)){
		if(str_contains($step,"CreateBD")){
			if(!strcmp($buffer,PHP_EOL)){
				updateBD($BD);
				showTable();
				$step = "BD Done";
			}else{
				$BD .= $buffer.'-';
			}
		}
		if(str_contains($buffer,"start")){
			$transactions[getTransactionID($buffer)] = '-';
		}else if(str_contains($buffer,"commit")){
			$transactions[getTransactionID($buffer)] .= 'commit';
			if($step == 'CKPT'){
				$redo .= $transactions[getTransactionID($buffer)];
				$retorno .= "Transação T".getTransactionID($buffer)." realizou Redo<br>";
			}
		}else if(str_contains($buffer,"CKPT")){
			if(str_contains($buffer,"Start")){
				$step = "CKPT";
				$transactions = flushLog($transactions);
				$ckptTrans = getCKPTTransactions($buffer);
			}/*else{
				$ckptTrans = '';
			}*/
		}else if(str_contains($buffer,"T")){
			$query = readQuery($buffer);
			$transactions[$query['transaction']] .= $query['column'].','.$query['id'].'='.$query['value'].'-';
		}else if(str_contains($buffer,"crash")){
			$i = 1;
			do{
			if(($transactions[$i] == '' && str_contains($ckptTrans,strval($i))) || !str_contains($transactions[$i],"commit") && $transactions[$i] != ''){
					$retorno .= "Transação T".$i." não realizou Redo<br>";
				}
			}while($i++ < sizeof($transactions));
			updateBD(str_replace("commit",'',$redo));
		}
		
		
		
	}
	return $retorno;
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
	$retorno = '';
	$i = 0;
	$transactions = str_replace("<Start CKP(",'',str_replace(")>",'',str_replace("T",'',$string)));
	$explode = explode(',',$transactions);
	foreach($explode as $tran){
		$retorno .= $tran.',';
	}
	return $retorno;
}

function readQuery($string){
	$explode = explode(",",str_replace(">",'',str_replace("<T",'',$string)));
	$row = array(
		"transaction" => $explode[0],
		"id" => $explode[1],
		"column" => $explode[2],
		"value" => str_replace(PHP_EOL,'',$explode[3]),
	);
	return $row;
}

function flushLog($transactions){
	$i = 0;
	while($i++ < sizeof($transactions)){
		if(str_contains($transactions[$i],"commit")){
			$transactions[$i] = '';
		}
	}
	return $transactions;
}
?>