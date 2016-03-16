<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '1234';
$db = 'NEWDB' ;
/*- <connection>
  <connectionaName>localhost</connectionaName> 
  <user>root</user> 
  <password>root</password> 
  <database>NEWDB</database> 
  </connection>*/
  
  //Two different setups for different paths.  In the first connectionPath, there is a setup that works
  //for a stand alone version on a PC.
  //In the second, we have a setup that works for a stand alone version a Mac.
  //The other relevant change appears in the Connection_Vars.xml and
  //Consultation_Vars.xml files.  In The connection variable file, the password does
  //not appear; this might be different in different setups.  In the consultation variable
  //file, there is an index for the consultation, which needs to be reset for each
  //new consultation.
  //A general issue is the numbers of consultations that are supported by the tool
  //at any one time.  In the current version, we only support one consultation at a 
  //time.  In a future version, we might have a list menu to select consultations.  For
  //each consultation, either the variables have to be set in the PhP or there needs to
  //be some connection to the variables.

	//$connectionPath = realpath($_SERVER['DOCUMENT_ROOT']).'/environment_vars/Connection_Vars.xml';
	//$connectionPath = 'environment_vars/Connection_Vars.xml';
	//if (file_exists($connectionPath)) {
		//$connection_vars = simplexml_load_file($connectionPath);
		
		$conn = mysqli_connect($dbhost, $dbuser) or trigger_error(mysqli_error());
		$status=mysqli_select_db($conn, 'NEWDB') or die(mysqli_error($conn));
	//}
	//else{
		//echo "ERROR WITH CONNECTION XML FILE : ".$connectionPath;
	//}
   
	//$consultationPath = realpath($_SERVER['DOCUMENT_ROOT']).'/environment_vars/Consultation_Vars.xml';
	$consultationPath = 'environment_vars/Consultation_Vars.xml';
	global $consultationID;
	$consultationID=4;	
	
	if (file_exists($consultationPath)) {
		$consultation_vars = simplexml_load_file($consultationPath);
		$consultationID = $consultation_vars->ID;
	}
	else{
		echo "ERROR WITH CONSULTATION XML FILE";		
	}
	
	
?>
