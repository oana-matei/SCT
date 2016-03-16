<?php
/*- <connection>
  <connectionaName>localhost</connectionaName> 
  <user>root</user> 
  <password>root</password> 
  <database>NEWDB</database> 
  </connection>*/

//	$connectionPath = '..\environment_vars\Connection_Vars.xml';
	$connectionPath = '../environment_vars/Connection_Vars.xml';
	if (file_exists($connectionPath)) {
		$connection_vars = simplexml_load_file($connectionPath);
		$status=mysqli_connect("{$connection_vars->connectionaName}", "{$connection_vars->user}", "{$connection_vars->password}") or trigger_error(mysqli_error());

		$status=mysqli_select_db("{$connection_vars->database}") or trigger_error(mysqli_error());
	}
	else{
		echo "ERROR WITH CONNECTION XML FILE";
	}
	
?>
