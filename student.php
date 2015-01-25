<!DOCTYPE html>
<html>
<head>

<link rel="stylesheet" type="text/css" href="style2.css">
  <title> STUDENT</title>
</head>
<body>
<?php
session_start();
if($_SESSION['student'] != 1)
{
	header('Location:denied');
}	
echo '<h1 class = "title">'.$_SESSION['name'].'\'s STUDENT PAGE</h1>';
?>
		<ul id="navbar">
		    <li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOME</a></li>
			<li><a href = "http://www.cs.okstate.edu/~geralab/Project/Login.php">LOGIN</a></li>
		</ul>
<?php
		
			echo '<h4 class = "details"> WELCOME '.$_SESSION['name'].'</h4>';
			echo '<h4 class = "details"> STUDENT ID: '.$_SESSION['userId'].'</h4>';
			echo '<h4 class = "details"> MAJOR: '.$_SESSION['major'].'</h4>';
			echo '<h4 class = "details"> YEAR: '.$_SESSION['year'].'</h4>';
		

?>
</body>
<pre class = "normal" id ="debug">CLASSIFICATION STUDENT</pre>
<div id = "studentQ" name = "studentQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="student.php" method="POST">
        <select name="selection" id="selection" onchange = "changeOptions()">
		<option value = "Z">PLEASE SELECT AN OPTION</option>
	    <option value="addDrop">ADD/DROP</option>
		<option value="transcript">TRANSCRIPT</option>
		<option value="classInfo"> CLASS INFORMATION</option>
		</select><br/>
		</form>
<?php
			$fileText = file_get_contents('/home/geralab/pass.txt', FILE_USE_INCLUDE_PATH);
			$dbPassword = trim($fileText);
			$dbUser = 'geralab';
			$dbName = $dbUser; 
			$database = new mysqli("cs.okstate.edu", $dbUser, $dbPassword, $dbName);
			if (mysqli_connect_errno()) 
			{
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
			if(array_key_exists('addDrop2', $_POST) 
			&& array_key_exists('act', $_POST))
			{
				$class = $_POST['addDrop2'];
				$act = $_POST['act'];
				if($act == "add")
				{
				    $userId = $_SESSION['userId'];
					$query = "INSERT INTO Takes (userId, classId, grade) VALUES
					($userId,$class, 'N');";
					$result = $database->query($query);
					confirm($result);
				}
				else if($act =="drop")
				{
					$userId = $_SESSION['userId'];
					$query="DELETE FROM Takes WHERE userId = $userId  
					AND classId = $class;";
					$result = $database->query($query);
					confirm($result);
				}
			}
			if(array_key_exists('transcriptFlag', $_POST))
			{
					$userId = $_SESSION['userId'];
					$query="Select DISTINCT className,
					classNum,sectionNum,semester,year,
					creditHours,grade From Class Natural Join Takes WHERE userId = $userId;";
					printTable($query,$database);	
			}
			if(array_key_exists('classInfo2', $_POST))
			{
					$userId = $_SESSION['userId'];
					$classId = $_POST['classInfo2'];
					$query="Select DISTINCT classNum AS COURSENUMBER, className AS 
					CLASSNAME, assignmentName AS ASSIGNMENT, points 
					AS POINTSEARNED, numPoints AS POINTSPOSSIBLE FROM 
					Assignment Natural JOIN AssignmentGrade Natural Join Takes
					Natural Join Class WHERE userId=$userId AND classId = $classId;";
					printTable($query,$database);	
					$query = "Select sum(points) AS
					 TOTALEARNEDPOINTS ,sum(numPoints) AS TOTALPOINTSPOSSIBLE FROM 
					Assignment Natural JOIN AssignmentGrade Natural Join Takes
					Natural Join Class WHERE userId=$userId AND classId = $classId;";
					printTable($query,$database);	
					$x = getInfo($query,'TOTALEARNEDPOINTS',$database);
					$y = getInfo($query,'TOTALPOINTSPOSSIBLE',$database);
					if($y!=0)
					{
						$per = (((float)$x)/$y)*100;
						outputG("$x/$y = $per%");
					}
					else
					{
						outputG("NO GRADES");
					}
			}
?>	
</div>
<script>
function changeOptions()
{
	if(document.getElementById("selection").value == "Z")
	{
		document.getElementById("studentQ").innerHTML = '<div id = "studentQ" name = "studentQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="student.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="addDrop">ADD/DROP</option>'+
				'<option value="transcript">TRANSCRIPT</option>'+
				'<option value="classInfo"> CLASS INFORMATION</option>'+
			'</select></br>'+
			"</div>";
			document.getElementById("selection").value = 'Z';
	}
	else if(document.getElementById("selection").value == "addDrop")
	{
	  var temp = '<div id = "studentQ" name = "studentQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="student.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="addDrop">ADD/DROP</option>'+
				'<option value="transcript">TRANSCRIPT</option>'+
				'<option value="classInfo"> CLASS INFORMATION</option>'+
			'</select><br/>';
		
		var json = JSON.parse(getQueryText("SELECT className,classId,classNum,sectionNum,semester,year FROM Class WHERE open = TRUE;"));
		temp+='<label for="addDrop2">SELECT A CLASS TO ADD/DROP</label>';
		temp+='<select name = "addDrop2" id = "addDrop2"><option value="ZZZ">SELECT CLASS</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classId+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		
		temp+='</select><select name = "act" id = "act" onchange = "updateAddDrop()"><option value ="add">ADD</option>'+
		'<option value = "drop">DROP</option></select><br/>';
		temp+='</select><br/><input class = "button" type = "submit" value = "SUBMIT REQUEST"></form><br/></div>';
		
		 
			document.getElementById("studentQ").innerHTML = temp;
			document.getElementById("selection").value = 'addDrop';
	}
	else if(document.getElementById("selection").value == "transcript")
	{
		document.getElementById("studentQ").innerHTML = ('<div id = "studentQ" name = "studentQ"'+
		' class = "login"><form class = "login" id="queryForm" name="queryForm" action="student.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="addDrop">ADD/DROP</option>'+
			'<option value="transcript">TRANSCRIPT</option>'+
			'<option value="classInfo"> CLASS INFORMATION</option>'+
			'</select></br>'+
			'<input type="hidden" name="transcriptFlag" value="transcriptFlag">'+
			"</form><br/>"+
			"</div>");
			document.getElementById("queryForm").submit();
	}
	else if(document.getElementById("selection").value == "classInfo")
	{
		 var temp = '<div id = "studentQ" name = "studentQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="student.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="addDrop">ADD/DROP</option>'+
				'<option value="transcript">TRANSCRIPT</option>'+
				'<option value="classInfo"> CLASS INFORMATION</option>'+
			'</select><br/>';
		
		var json = JSON.parse(getQueryText("SELECT className,classId,classNum,"+
		"sectionNum,semester,year FROM Class Natural Join Takes "+
		"WHERE userId = "+<?php echo $_SESSION['userId'];?>+";"));
		temp+='<label for="addDrop2">SELECT A CLASS TO GET INFORMATION FROM: </label>';
		temp+='<select name = "classInfo2" id = "classInfo2" '+
		'onchange = "this.form.submit()"><option value="ZZZ">SELECT CLASS</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classId+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		
		temp+='</select></form><br/></div>';
		
		 
			document.getElementById("studentQ").innerHTML = temp;
			document.getElementById("selection").value = 'classInfo';
	}
}//END CHANGE OPTIONS

function updateAddDrop()
{
	var addDropVal = document.getElementById("act").value;
	var query;
	if(addDropVal == "add")
	{
		query = "SELECT className,classId,classNum,sectionNum,semester,year FROM Class WHERE open = TRUE;";

	}
	else if(addDropVal == "drop")
	{
		query = "Select * From Class Natural Join Takes WHERE userId = "+
		<?php echo $_SESSION['userId'];?>+";";

	}

var json = JSON.parse(getQueryText(query));
var temp ='<option value="ZZZ">SELECT CLASS</option>';
for(var key in json)
{
	temp+= '<option value="'+json[key].classId+'">'+json[key].className+ " "+
	json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
	json[key].year+'</option>';
}
	document.getElementById("addDrop2").innerHTML = temp;
}


function getQueryText(query)
{
	var httpRequest = new XMLHttpRequest();
	var url = "query.php?query=" + query;
	httpRequest.open("GET", url, false);
	httpRequest.send(null);
	return httpRequest.responseText;
}
</script>
<?php
     function confirm($result)
	 {
		 if($result)
		 {
			echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY SUCCESFUL!!!</pre></h4></div>';
		 }
		 else
		 {
			echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY FAILED</pre></h4></div>';
		 }
	 }
	 function outputG($t)
	 {
			echo '<div class = "out"><h4 id="out"><pre class = "normal2">'.$t.'</pre></h4></div>';
		 
	 }
	 function printTable($query,$database)
	 {
		$result = $database->query($query);
		
		echo "<br><br><br><div>\n";
		//echo $query;
	
		if (!is_object($result))
		{
			if($result)
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY SUCCESFUL!!!</pre></h4></div>';
			}
			else
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY FAILED</pre></h4></div>';
			}
		}
		else 
		{
			// MAKE HTML TABLE
			echo '<table border="2" cellPadding="3">', "\n";
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if ($row) 
			{
				$keys = array_keys($row);
				echo '<tr>';
				foreach ($keys as $key)
				{
					echo "<th>$key</th>";
				}
				echo '</tr>';
				while ($row)
				{
					echo '<tr>';
					foreach ($row as $cell) 
					{
						echo '<td>' . $cell . '</th>';
					}
					echo '</tr>';
					$row = $result->fetch_array(MYSQLI_ASSOC);
				}
			}
			echo "</table>\n";
			echo "<br/></div>";
		} 
	 }
	 function printTable2($result,$database)
	 {
		echo "<br><br><br><div>\n";
		//echo $query;
		
		if (!is_object($result))
		{
			if($result)
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY SUCCESFUL!!!</pre></h4></div>';
			}
			else
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY FAILED</pre></h4></div>';
			}
		}
		else 
		{
			// MAKE HTML TABLE
			echo '<table border="2" cellPadding="3">', "\n";
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if ($row) 
			{
				$keys = array_keys($row);
				echo '<tr>';
				foreach ($keys as $key)
				{
					echo "<th>$key</th>";
				}
				echo '</tr>';
				while ($row)
				{
					echo '<tr>';
					foreach ($row as $cell) 
					{
						echo '<td>' . $cell . '</th>';
					}
					echo '</tr>';
					$row = $result->fetch_array(MYSQLI_ASSOC);
				}
			}
			echo "</table>\n";
			echo "<br/></div>";
		} 
	 }
	 
	 function getInfo($query,$col,$database)
	{
		$result = $database->query($query);
		$info='';
		if (is_object($result))
		{
			$row = $result->fetch_array(MYSQLI_ASSOC);
			$info = $row[$col];
		}
			return $info;
	}
	?>

</html>