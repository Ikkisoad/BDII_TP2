<?php

function readLog($file){
	$i = 0;
	$step = 0;
	$endCKPT = 0;
	$unfinishedTransactions = array();
	$redoTransactions = array();
	$transactions = array();
	while(fseek($file,$i--,SEEK_END) == 0){
		if(fgetc($file) == "\n"){
			fseek($file,$i+2,SEEK_END);
			$buffer = fgets($file);
			if(str_contains($buffer,"start")){
				if($step == 1){
					if(array_search(getTransactionID($buffer),$unfinishedTransactions) !== false){
						array_splice($unfinishedTransactions,array_search(getTransactionID($buffer),$unfinishedTransactions),1);
						if(sizeof($unfinishedTransactions) == 0){
							break;
						}
					}
				}else if($step == 0){
					//array_push($unfinishedTransactions,intval(getTransactionID($buffer)));
					array_push($redoTransactions,intval(getTransactionID($buffer)));
				}
			}else if(str_contains($buffer,"commit")){
				//do nothing
			}else if(str_contains($buffer,"CKPT")){
				if(str_contains($buffer,"Start") && $endCKPT == 1){
					foreach(explode(',',getCKPTTransactions($buffer)) as $ckptTransaction){
						$ckptTransaction = intval($ckptTransaction);
						if($ckptTransaction != 0 && array_search($ckptTransaction,$redoTransactions) === false){
							array_push($redoTransactions,intval($ckptTransaction));
							array_push($unfinishedTransactions,intval($ckptTransaction));
						}
					}
					$step = 1;
				}else{
					$endCKPT = 1;
				}
			}
		}
	}
	
	while($buffer = fgets($file)){
		//print_r($redoTransactions);
		//print_r($transactions);
		if(str_contains($buffer,"start")){
			//do nothing
		}else if(str_contains($buffer,"commit")){
			$transactionID = getTransactionID($buffer);
			if(array_search($transactionID,$redoTransactions) !== false){
				array_splice($redoTransactions,array_search($transactionID,$redoTransactions),1);
				updateBD($transactions[$transactionID],1);
				echo 'Transação T'.$transactionID.' realizou Redo<br>';
			}
		}else if(str_contains($buffer,"CKPT")){
			//do nothing
		}else if(str_contains($buffer,"T")){
			$query = readQuery($buffer);
			if(isset($transactions[$query['transaction']])){
				$transactions[$query['transaction']] .= $query['column'].','.$query['id'].'='.$query['value'].'-';
			}else{
				$transactions[$query['transaction']] = $query['column'].','.$query['id'].'='.$query['value'].'-';
			}
		}
	}
	foreach($redoTransactions as $T){
		echo 'Transação T'.$T.' não realizou Redo<br>';
	}
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

function loadBD($file){
	$step = 1;
	$BD = '';
	while($step == 1){
		$buffer = fgets($file);
		if($buffer == "\n" || strcmp($buffer,PHP_EOL) == 0){
			updateBD($BD);
			showTable("Before");
			$step = 0;
		}else{
			$BD .= $buffer.'-';
		}
	}
}

function getTransactionID($string){
	return intval(str_replace("T",'',str_replace("<commit",'',str_replace(">",'',str_replace("<start ",'',$string)))));
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

function updateBD($values, $verify = 0){
	global $conn;
	foreach(explode('-',$values) as $row){
		if($row != ''){
			$array = explode(',',$row);
			$arrayTwo = explode('=',$array[1]);
			if($verify){
				foreach(selectNode($arrayTwo[0]) as $result){
					if($result[$array[0]] == $arrayTwo[1]){
						echo $array[0].$arrayTwo[0].' não atualizou<br>';
						continue;
					}
				}
			}
			$query = 'UPDATE `tabela1` SET `'.$array[0].'`='.$arrayTwo[1].' WHERE `id`='.$arrayTwo[0].'';
			$result = $conn -> prepare($query);
			$result -> execute();
		}
	}
}

function selectNode($ID = 0){
	global $conn;
	$query = "SELECT `id`, `A`, `B` FROM `tabela1` WHERE id = ?";
	$result = $conn->prepare($query);
	$result->bind_param("i",$ID);
	$result->execute();
	return $result->get_result();
}
?>