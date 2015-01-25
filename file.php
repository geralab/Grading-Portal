<?php
	if (array_key_exists('file', $_GET)) 
	{
		$file= $_GET['file'];
		$fileText = file_get_contents('/home/geralab/pass.txt', FILE_USE_INCLUDE_PATH);
	    $password = trim($fileText);
		$user = 'geralab';
		$dbName = $user; 
		$database = new mysqli("cs.okstate.edu", $user, $password, $dbName);
		if (mysqli_connect_errno()) 
		{
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		$f = file_get_contents($file, FILE_USE_INCLUDE_PATH);
		echo $f;
	}  
?>
