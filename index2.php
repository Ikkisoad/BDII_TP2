<!doctype php>
<?php
	require_once "server/connection.php";
	if(isset($_POST['A'])){
		insertAB($_POST['A'],$_POST['B']);
	}
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
			table, th, td {
			  border:1px solid black;
			}
		</style>
	</head>
	<body>
		<h3>Trabalho 2 - BD2</h3>
		<form action="index2.php" method="post">
			<input type="text" name="A" placeholder="A">
			<input type="text" name="B" placeholder="B">
			<input type="submit" value="Submit" name="submit">
		</form>
		<form action="index2.php" method="post" enctype="multipart/form-data">
			<input type="file" name="file">
			<input type="submit" value="Submit" name="submit">
		</form>
		<?php 
			file_put_contents("server/log","Log");
			if(isset($_FILES['file']['tmp_name'])){
				if(isset($_POST) && $_FILES['file']['tmp_name'] != ''){
					$file = fopen($_FILES['file']['tmp_name'],"r");
					loadBD($file);
					//echo '<textarea rows=50>';
					readLog($file);
					//echo '</textarea>';
				}
			}
			showTable("After");
		?>
		
	</body>
</html>