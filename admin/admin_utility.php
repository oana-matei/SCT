<?php  
/*creates option menues*/
function createOptions($tableName, $value1, $value2){
	$information = mysqli_query("SELECT * FROM $tableName") or trigger_error(mysqli_error());
	$optionsReturn = '<option  value=-1 selected>N/A</option>';
	while($info = mysqli_fetch_array($information)){
		$optionsReturn .= '<option value='.$info[$value1].'>'.$info[$value2].'</option>';
	}
	return $optionsReturn;
}

function createOptionsFromDataSet($information, $value1, $value2){
	$optionsReturn = '<option  value=-1 selected>N/A</option>';
	while($info = mysqli_fetch_array($information)){
		$optionsReturn .= '<option value='.$info[$value1].'>'.$info[$value2].'</option>';
	}
	return $optionsReturn;
}

function createStringOptions($tableName, $value){
	$information = mysqli_query("SELECT * FROM $tableName") or trigger_error(mysqli_error());
	$optionsReturn = '<option selected>N/A</option>';
	while($info = mysqli_fetch_array($information)){
		$optionsReturn .= '<option>'.$info[$value].'</option>';
	}
	return $optionsReturn;
}

/*creates options menue from data array*/
function createOptionsArray($infoArray){
	$optionsReturn = '<option value=-1 selected>N/A</option>';
	foreach($infoArray as $info ){
		$optionsReturn .= '<option value='.$info[0].'>'.$info[1].'</option>';
	}
	return $optionsReturn;
}

/*creates value options menues*/
function createValueOptions(){
	$optionsReturn = '<option selected>neutral</option>';
	$optionsReturn .= '<option selected>promoted</option>';
	$optionsReturn .= '<option selected>demoted</option>';
	return $optionsReturn;
}

/*Deletes from Table $tableName, the relation with id carried in the session $id_session and unset the session*/
function DeleteFromTable($tableName, $id_session){
	$idField = $tableName."_id";
	$sql="DELETE FROM $tableName WHERE $idField={$id_session}";
	mysqli_query($sql) or trigger_error(mysqli_error());
	unset ($id_session);
}


/*Creates Display Table for Primitives with Two Values*/
function CreateDisplayTableTwoFields($tableName, $idField, $secondField, $formName, $DeletFormName){
	$data = mysqli_query("SELECT * FROM $tableName") or trigger_error(mysqli_error());
	Print "<table border cellpadding=3>";
	Print "<tr>";
	Print "<th>ID</th><th>$tableName</th><th>Remove</th>";
	while($info = mysqli_fetch_array($data)) {
		Print "<tr>";
		Print "<td>".$info[$idField] . "</td> ";
		Print "<td>".$info[$secondField] . "</td> ";
		Print "<td>";
		print "<form action='$formName' method='POST'>";
		print "<input type='submit' value='delete'/>";
		print "<input type='hidden' name='$DeletFormName' value=".$info[$idField]." />";
		print "</form> ";
		print "</td> ";
	}
	Print "</table>";
}

/*Create Display Table for Intermediate Tables*/
function CreateDisplayIntermediateTable($intermediateTable, $firstPrimitiveTable, $secondPrimitiveTable, $idField, $firstField, $secondField, $formName, $DeletFormName){
	$firstPrimitiveTableID = $firstPrimitiveTable."_id";
	$secondPrimitiveTableID =  $secondPrimitiveTable."_id";
	
	$data = mysqli_query("SELECT $idField, $firstField, $secondField 
	FROM $intermediateTable, $firstPrimitiveTable, $secondPrimitiveTable
	where $intermediateTable.$firstPrimitiveTable = $firstPrimitiveTableID
	and $intermediateTable.$secondPrimitiveTable = $secondPrimitiveTableID")
 	or trigger_error(mysqli_error()); 	
	
	Print "<table border cellpadding=3>";
	Print "<tr>";
	Print "<th>ID</th><th>Proposition</th><th>Domain</th><th>Remove</th>";
	while($info = mysqli_fetch_array( $data )) { 
		Print "<tr>";
		Print "<td>".$info[$idField] . "</td> ";
		Print "<td>".$info[$firstField]."</td> ";
		Print "<td>".$info[$secondField]."</td> ";
		Print "<td>";
		print "<form action='$formName' method='POST'>";
		print "<input type='submit' value='delete'/>";
		print "<input type='hidden' name='$DeletFormName' value=".$info[$idField]." />";
		print "</form> ";
		print "</td> ";
	} 
	Print "</table>";
}

/*Insert new Relations into Primitive Tables*/
/*TODO: to avoid inserting dublicated values, it is better to check the DB for same values first, if such rows exist there is no need to add them.*/
function InsertPrimitveTable($TableName, $ValueName, $Value){
	$sql="INSERT INTO $TableName ($ValueName) VALUES ('$Value')";
	mysqli_query($sql) or trigger_error(mysqli_error());
}

/*Insert new Relations into Intermediate Tables*/
/*TODO: to avoid inserting dublicated values, it is better to check the DB for same values first, if such rows exist there is no need to add them.*/
function InsertIntermediateTable($TableName, $firstFieldName, $secondFieldName, $firstFieldValue, $secondFieldValue){
	$sql="INSERT INTO $TableName ($firstFieldName, $secondFieldName)
			VALUES($firstFieldValue,$secondFieldValue)";
			mysqli_query($sql) or trigger_error(mysqli_error());
}

function InsertIntermediateTableWithID($TableName, $firstFieldName, $secondFieldName, $firstFieldValue, $secondFieldValue, $id){
	$idField = $TableName."_id";
	$sql="INSERT INTO $TableName ($idField, $firstFieldName, $secondFieldName)
	VALUES($id, $firstFieldValue,$secondFieldValue)";
	mysqli_query($sql) or trigger_error(mysqli_error());
}


/*Generate IDs for Conjunction Tables and insert new conjunction*/
function GenerateConjunctionID(){
	$maxConjunction = mysqli_query("select MAX(conjunction_id) as maxConj from conjunction") or trigger_error(mysqli_error());
	$infoMaxCon = mysqli_fetch_array( $maxConjunction );
	$conjunctionID = $infoMaxCon['maxConj'];
	$conjunctionID = $conjunctionID+1;
	mysqli_query("INSERT INTO conjunction (conjunction_id) VALUES ('$conjunctionID')") or trigger_error(mysqli_error());
	return $conjunctionID;
}

/*gets new ID (Max id +1) for table $table_name*/
function GetNewID($tableName){
	$idField = $tableName."_id";
	$maxID = mysqli_query("select MAX($idField) as maxID from $tableName") or trigger_error(mysqli_error());
	$infoID = mysqli_fetch_array($maxID);
	$newID = intval($infoID['maxID'])+1;
	return $newID;
}

/*Inserts of all elements of the cojunction to the appropriate tables, from the array held by the session: $conjunctionSession
* These conjunctions are created in two parts:
* 1) an array of conjuncts is firstly created - the members of the array are displayed on the right hand side of each forum
* 2) the array is then submitted to the database when the user is happy with it, this involves: creating new entry in the conjunction table to bind the elements of the array together
* then each element is added to the adequate _occurrence table*/

function InsertNewConjunction($occurrenceTable, $occurrenceName, $nameField, $conjunctionSession){
	$conjunctionID = GenerateConjunctionID();
	foreach ($conjunctionSession as $conjunct){
		$idField = $occurrenceName."_id";
		$IdQuery = mysqli_query("select $idField from $occurrenceName where $nameField='$conjunct'") or trigger_error(mysqli_error());
		$id = -1;
		while($info = mysqli_fetch_array($IdQuery)){
			$id = $info[$idField];
		}
		if($id!=-1){
			InsertIntermediateTable($occurrenceTable, "conjunction", $occurrenceName, $conjunctionID, $id);
		}
	}
	unset ($_SESSION['conjPorporties']);
}

/*returns a dataset containing a list of the distinct conjunctions joining items of the type supported by $occurrenceTable */
function GetDistinctConjunctions($occurrenceTable){
	$dataConj= mysqli_query("select distinct conjunction_id
								from $occurrenceTable, conjunction
								where conjunction_id = $occurrenceTable.conjunction") or trigger_error(mysqli_error());
	return $dataConj;
}

//Get dataset for component of PRAS instant 
function GetPrasComponentInfo($tableName, $attrName, $occurrenceTable, $cojunctionID){
	$idField = $tableName."_id";
	$data = mysqli_query("select $attrName
			from $tableName, conjunction, $occurrenceTable
			where conjunction_id = $occurrenceTable.conjunction
			and $idField = $occurrenceTable.$tableName
			and conjunction_id = $cojunctionID") or trigger_error(mysqli_error());
	return $data;
}
//Generates a string of the values/propositions (seperated by -) that features in a given practical reasoning scheme
function GetPRASComponentInfoString($tableName, $attrName, $occurrenceTable, $cojunctionID){
	$data = GetPrasComponentInfo($tableName, $attrName, $occurrenceTable, $cojunctionID);
	$infoString ="";
	while($info = mysqli_fetch_array($data)){
		$infoString.= $info[$attrName]." - ";
	}
	return $infoString;
}

//returns the id of the field with the given name (assuming unique names)
function GetIDFromName($tableName, $searchName){
	$idField = $tableName."_id";
	$nameField = $tableName."_name";
	$data = mysqli_query("Select $idField from $tableName where $nameField = '$searchName'") or trigger_error(mysqli_error());
	if(mysqli_num_rows($data)>0){
		$info = mysqli_fetch_array($data);
		$id = $info[$idField];
		return $id;
	}
	else{
		return -1;

	}
}
?>