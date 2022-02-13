<!doctype php>
<?php
	require_once "server/connection.php";
?>
<html>
	<head>
		<style>
			body{
				background-color: #6e6e6e;
			}
			textarea{
				width: 500;
			}
		</style>
	</head>
	<body>
		<h3>Trabalho 2 - BD2</h3>
		<?php 
			file_put_contents("server/log","Log");
			showTable(); 
			if(isset($_POST) && $_FILES['file']['tmp_name'] != ''){
				getFile(fopen($_FILES['file']['tmp_name'],"r"));
				//echo fread($openFile,10240).'fread<br>'; //A,1=20 A,2=20 B,1=55 B,2=30
				//$fileArray = file($_FILES['file']['tmp_name']);foreach($fileArray as $row){echo $row;}//Read current values
			}
		?>
		
		<form action="index.php" method="post" enctype="multipart/form-data">
			<input type="file" name="file">
			<input type="submit" value="Submit" name="submit">
		</form>
	</body>
</html>