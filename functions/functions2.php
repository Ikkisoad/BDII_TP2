<?php

function insertAB($A,$B){
	$query = 'INSERT INTO `tabela1`(`id`, `A`, `B`) VALUES (NULL,'.$A.','.$B.')';
	$result = $conn -> prepare($query);
	$result -> execute();
}

function readLog($file){
	$i = 0;
	$step = 0;
	$redo = array();
	$unfinishedTransactions = array();
	$commitOrder = array();
	$redoTransactions = array();
	while(fseek($file,$i--,SEEK_END) == 0){
		if(fgetc($file) == "\n"){
			fseek($file,$i+2,SEEK_END);
			$buffer = fgets($file);
			if(str_contains($buffer,"start")){
				if($step = 1){
					if(array_search(getTransactionID($buffer),$unfinishedTransactions) !== false){
						array_splice($unfinishedTransactions,array_search(getTransactionID($buffer),$unfinishedTransactions));
						if(sizeof($unfinishedTransactions) == 0) break;
					}
				}else if($step = 0){
					array_push($redoTransactions,intval(getTransactionID($buffer)));
					
				}
			}else if(str_contains($buffer,"commit")){
				array_push($commitOrder,getTransactionID($buffer));
			}else if(str_contains($buffer,"CKPT")){
				if(str_contains($buffer,"Start")){
					foreach(explode(',',getCKPTTransactions($buffer)) as $ckptTransaction){
						$ckptTransaction = intval($ckptTransaction);
						if($ckptTransaction != 0 && array_search($ckptTransaction,$redoTransactions) === false){
							array_push($redoTransactions,intval($ckptTransaction));
							array_push($unfinishedTransactions,intval($ckptTransaction));
						}
					}
					$step = 1;
				}
				print_r($redoTransactions);
			}else if(str_contains($buffer,"T")){
				
			}
		}
	}
	
	while($buffer = fgets($file)){
		echo $buffer;
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
		if(!strcmp($buffer,PHP_EOL)){
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
?>