<?php
	if (array_key_exists('query', $_GET)) 
	{
		$query= $_GET['query'];
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
		$output = array();
		$result = $database->query($query);
		if (is_object($result))
		{
			while($row=$result->fetch_array(MYSQLI_ASSOC))
			{
				$output[]=$row;
			}
			echo(json_encode($output));
		}
		else
		{
			echo '';
		}
	}  
?>
