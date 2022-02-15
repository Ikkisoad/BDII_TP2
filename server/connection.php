<?php
		require_once "functions/functions.php";
		require_once "functions/functions2.php";
		$db_name = "bdii_tp2";
		$mysql_user = "root";
        $mysql_pass = "root";
        $server_name = "localhost:3306";

        $conn = mysqli_connect($server_name, $mysql_user, $mysql_pass,$db_name);

?>
