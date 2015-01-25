<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style2.css">
  <title> INSTRUCTOR</title>
</head>
<body>
<?php
session_start();
if($_SESSION['instructor'] != 1)
{
	header('Location:denied');
}	
echo '<h1 class = "title">'.$_SESSION['name'].'\'s INSTRUCTOR PAGE</h1>';
?>
		<ul id="navbar">
		    <li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOME</a></li>
			<li><a href = "http://www.cs.okstate.edu/~geralab/Project/Login.php">LOGIN</a></li>
		</ul>
<?php		
			echo '<h4 class = "details"> WELCOME '.$_SESSION['name'].'</h4>';
			echo '<h4 class = "details"> USER ID: '.$_SESSION['userId'].'</h4>';
			echo '<h4 class = "details"> DEPARTMENT: '.$_SESSION['department'].'</h4>';
?>
</body>
<pre class = "normal" id ="hud">CLASSIFICATION INSTRUCTOR</pre>
<div id = "instructorQ" name = "instructorQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="instructor.php" method="POST">
        <select name="selection" id="selection" onchange = "changeOptions()">
		<option value = "Z">PLEASE SELECT AN OPTION</option>
	    <option value="portal">GRADING PORTAL</option>

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
				
?>	
</div>
<script>
function changeOptions()
{
	document.getElementById("hud").innerHTML = "LOADING";
	if(document.getElementById("selection").value == "Z")
	{
		document.getElementById("instructorQ").innerHTML = '<div id = "instructorQ" name = "instructorQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="instructor.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="portal">GRADING PORTAL</option>'+
				
			'</select></br>'+
			"</div>";
			document.getElementById("selection").value = 'Z';
	}
	else if(document.getElementById("selection").value == "portal")
	{
	  var temp = '<div id = "instructorQ" name = "instructorQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="instructor.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="portal">GRADING PORTAL</option>'+
			
			'</select><br/>';
		//courses
		var json = JSON.parse(getQueryText("SELECT className,classId,classNum,sectionNum,semester,year "+
		"FROM Class Natural Join Teaches WHERE userId = "+<?php echo $_SESSION['userId'];?>+";"));;
		temp+='<label for="courses">SELECT A COURSE TO VIEW</label>';
		temp+='<select name = "courses" id = "courses" onchange="updateAssignments()">'+
		'<option value="ZZZ">SELECT COURSE</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classId+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		
	    temp+='</select><br/><br/><br/><br/>';
		//assignment
		temp+='<label for="assign">SELECT AN ASSIGNMENT TO GRADE</label>';
		temp+='<select name = "assign" id = "assign" onchange ="updateGradeCard()">'+
		'<option value="ZZZ">SELECT A COURSE TO VIEW ASSIGNMENTS</option>';
		temp+='</select>'+
		'<br><br><br><div id = "addDelDiv"><select id = "addDelOpt" onchange = "addRemove()"><option value = "ZZZ">COURSE OPTIONS'+
		'</option><option value = "add">ADD ASSIGNMENTS</option>'+
		'<option value = "del">DELETE ASSIGNMENTS</option>'+
		'<option value = "update">UPDATE ASSIGNMENTS</option>'+
		'<option value = "final">ASSIGN FINAL GRADES</option>'+
		'</select></div>'+
		'<div id = "gradeCard" name = "gradeCard"></div><div id = "finalCard" name - "finalCard"></div></form><br/></div>';
		
		 
			document.getElementById("instructorQ").innerHTML = temp;
			document.getElementById("selection").value = 'portal';
	}
	document.getElementById("hud").innerHTML = "CLASSIFICATION INSTRUCTOR";
}//END CHANGE OPTIONS
function addRemove()
{
	document.getElementById("hud").innerHTML = "LOADING";
	var temp='<select id = "addDelOpt" onchange = "addRemove()"><option value = "ZZZ">COURSE OPTIONS'+
			'</option><option value = "add">ADD ASSIGNMENTS</option>'+
			'<option value = "del">DELETE ASSIGNMENTS</option>'+
			'<option value = "update">UPDATE ASSIGNMENTS</option>'+
			'<option value = "final">ASSIGN FINAL GRADES</option>'+
			'</select>';
	var state = document.getElementById("addDelOpt").value;
	
	if(state == "add")
	{		
		temp+='<br><label for="assignmentName">ASSIGNMENT NAME</label>'+
				'<input type = "textfield" name = "assignmentName" id = "assignmentName"><br>'+
				'<label for="numPoints">NUMBER OF POINTS</label>'+
				'<input type = "textfield" name = "numPoints" id = "numPoints">'+
				'<input class = "button" type = "button"  onclick = "refreshPortal()" value = "ADD ASSIGNMENT"><br><br>';
				
	}
	else if(state == "del")
	{
		temp+='<label for="rbutton">YOU MAY NOW REMOVE ASSIGNMENT FROM LIST</label>'+
		'<input class = "button" type = "button" name = "rbutton" onclick = "refreshPortal()"'+
		' value="REMOVE SELECTED ASSIGNMENT">';
	}
	else if(state == "update")
	{
	    
		var course = document.getElementById("courses").value;
		var assign = (document.getElementById("assign"))?document.getElementById("assign").value:'';
		var query = "SELECT DISTINCT numPoints FROM Assignment "+
		"Natural Join Teaches WHERE userId = "+<?php echo $_SESSION['userId'];?>+" AND classId "+"= "+course+
		" AND assignmentName = \'"+ assign+"\';"
		var json = JSON.parse(getQueryText(query));
		var numPoints;
		for(var key in json)
		{
			numPoints = json[key].numPoints;
		}
		temp+='<br><label for="assignmentName">ASSIGNMENT NAME</label>'+
				'<input type = "textfield" name = "assignmentName" id = "assignmentName"'+
				'value = "'+assign+'"><br>'+
				'<label for="numPoints">NUMBER OF POINTS</label>'+
				'<input type = "textfield" name = "numPoints" id = "numPoints"'+
				' value = "'+numPoints+'">'+
				'<input class = "button" type = "button"  onclick = "refreshPortal()" value = "UPDATE ASSIGNMENT"><br><br>';
	}
	else if(state == "final")
	{
	    var ftemp='';
		var classId = document.getElementById("courses").value;
		 var json = JSON.parse(getQueryText("Select DISTINCT * FROM "+
					"Takes Natural Join Student WHERE classId = "+classId +";"));
		ftemp+='<table>';
		ftemp+='<tr><td> STUDENT NAME</td><td>FINAL GRADE</td></tr>';
		for(var key in json)
		{
		  var  query = "'REPLACE INTO Takes (userId, classId, grade) VALUES "+	
           "("+json[key].userId+","+json[key].classId+", uniquePLACE1734);'";
		  var theID = "finalCard"+key.toString();
		  var queryText2 = "getQueryText3(" + query + ",'" + theID + "')";
			ftemp+= '\n<tr><td>'+json[key].name+'</td><td><input id = "'+theID+'" onchange="'+queryText2+'" '+
			' type="textfield" value="'+json[key].grade+'">'+
			'</td></tr>';
		}
		
		temp+='</table>';
		document.getElementById("gradeCard").innerHTML = ftemp;
	}
	else if(state =="ZZZ")
	{
	}
	document.getElementById("addDelDiv").innerHTML = temp;
	document.getElementById("addDelOpt").value = state;
	document.getElementById("hud").innerHTML = "CLASSIFICATION INSTRUCTOR";
}

function refreshPortal()
{
    document.getElementById("hud").innerHTML = "LOADING";
	var state = document.getElementById("addDelOpt").value;
	var classId = (document.getElementById("courses"))?document.getElementById("courses").value:'NO COURSE';
	var assignmentName = (document.getElementById("assignmentName"))?document.getElementById("assignmentName").value:'NO NAME';
	var assign = (document.getElementById("assign"))?document.getElementById("assign").value:'NO ASSIGNMENT';
	var numPoints = (document.getElementById("numPoints"))?document.getElementById("numPoints").value:'';
	if(state == "add")
	{
		getQueryText("REPLACE INTO Assignment (classId, assignmentName, numPoints) VALUES "+	
           "("+classId + ",\'"+assignmentName+"\',"+ numPoints+");");
	}
	else if(state == "del")
	{
		getQueryText("DELETE FROM Assignment WHERE classId = "+	
           classId + " AND assignmentName = \'"+assign+"\'; ");
		  getQueryText("DELETE FROM AssignmentGrade WHERE classId = "+	
           classId + " AND assignmentName = \'"+assign+"\'; ");
	}
	else if(state == "update")
	{
		getQueryText("UPDATE Assignment SET assignmentName = \'"+assignmentName+"\',numPoints = "+ numPoints+
		" WHERE classId = "+classId + " AND assignmentName = \'"+assign+"\';");
		getQueryText("UPDATE AssignmentGrade SET assignmentName = \'"+assignmentName+"\'"+
		" WHERE classId = "+classId + " AND assignmentName = \'"+assign+"\';");
	}
	document.getElementById("courses").value="ZZZ";
	document.getElementById("assign").value="ZZZ";
	document.getElementById("gradeCard").innerHTML = '';
	document.getElementById("hud").innerHTML = "CLASSIFICATION INSTRUCTOR";
}
function updateAssignments()
{
    var temp ="";
	var course = document.getElementById("courses").value;
   var json = JSON.parse(getQueryText("SELECT DISTINCT* FROM Assignment "+
		"Natural Join Teaches WHERE userId = "+<?php echo $_SESSION['userId'];?>+" AND classId "+
		"="+course+";"));
		temp+='<option value = "ZZZ">CURRENT ASSIGNMENTS FOR COURSE</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].assignmentName+'">'+json[key].assignmentName+
			'</option>';
		}
		document.getElementById("assign").innerHTML = temp;
		

}

function updateGradeCard()
{
    document.getElementById("hud").innerHTML = "LOADING";
    var course = document.getElementById("courses").value;
	var state = document.getElementById("addDelOpt").value;
	var assignmentName = (document.getElementById("assignmentName"))?document.getElementById("assignmentName").value:'';
	var numPoints = (document.getElementById("numPoints"))?document.getElementById("numPoints").value:'';
	var temp ="";
	var assign = document.getElementById("assign").value;
	var classId = document.getElementById("courses").value;
	if(state == "update")
	{
		var query = "SELECT DISTINCT numPoints FROM Assignment "+
		"Natural Join Teaches WHERE userId = "+<?php echo $_SESSION['userId'];?>+" AND classId "+"= "+course+
		" AND assignmentName = \'"+ assign+"\';";
		var json2 = JSON.parse(getQueryText(query));
		var numPoints;
		for(var key in json)
		{
			document.getElementById("numPoints").value = json2[key].numPoints;
		}
		document.getElementById("assignmentName").value=assignmentName;
		
	}
    var json = JSON.parse(getQueryText("Select DISTINCT * FROM "+
					"Assignment Natural Join Takes "+
					" Class Natural Join Student WHERE "+
					" assignmentName = \'"+assign+"\' AND classId = "+classId +";"));
	
	
	
		temp+='<table>';
		temp+='<tr><td>ASSIGNMENT NAME</td><td>STUDENT NAME</td><td>POINTS</td><td>NUMBER OF POINTS</td></tr>';
		 var points='';
		for(var key in json)
		{
		 var rt = getQueryText("Select points FROM AssignmentGrade WHERE "+
			" assignmentName = \'"+json[key].assignmentName+"\' AND studentId = "+json[key].userId+";");
		    if(rt!='')
			{
				var json2 = JSON.parse(rt);
				for(var key2 in json2)
				{
					points = json2[key2].points;
				}
			}
		  var  query = "'REPLACE INTO AssignmentGrade (classId, assignmentName, studentId, points) VALUES "+	
           "("+json[key].classId+
		   ",\\\'"+json[key].assignmentName+"\\\',"+json[key].userId+", uniquePLACE1734);'";
		  var theID = "gradeCard"+key.toString();
		  var queryText2 = "getQueryText2(" + query + ",'" + theID + "')";
			temp+= '\n<tr><td>'+json[key].assignmentName+'</td>'+
			'<td>'+json[key].name+'</td><td><input id = "'+theID+'" onchange="' + queryText2 + '" '+
			' type="number" value="'+points+'">'+
			'</td><td>'+json[key].numPoints+'</td></tr>';
			points='';
		}
		
		temp+='</table>';
		document.getElementById("gradeCard").innerHTML = temp;
		//console.log(temp);
		document.getElementById("hud").innerHTML = "CLASSIFICATION INSTRUCTOR";
}
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
function getQueryText3(query,ID)
{
    if(document.getElementById(ID).value == '')
	{
		document.getElementById(ID).value=0;
	}
    var theChar = document.getElementById(ID).value;
	query = query.replace("uniquePLACE1734","\'"+theChar+"\'");
	var httpRequest = new XMLHttpRequest();
	var url = "query.php?query=" + query;
	httpRequest.open("GET", url, false);
	httpRequest.send(null);
	return httpRequest.responseText;
}
function getQueryText2(query,ID)
{
    if(document.getElementById(ID).value == '')
	{
		document.getElementById(ID).value=0;
	}
    var number = document.getElementById(ID).value;
	query = query.replace("uniquePLACE1734",number);
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