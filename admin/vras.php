<?php
/*Displays existing instances of value recognition scheme
 * allows the user to add new instance from existing components*/
session_start();
include('../support/database_connect_admin.php');
include('admin_utility.php');
?>

<?php 
/*if the mode is not set --> set to undefined to avoid error message*/
	if (!isset($_POST['mode'])) {
		$_POST['mode'] = "undefine";
	}

/*The actual entry to the database - depends on the forum submitted.
 * Ids are auto generated by the sql (auto incremented fields) */
	switch($_POST['mode']){
		case "insert-vras":		
			InsertIntermediateTable("value_recognition_as", "value", "source", $_POST['value'], $_POST['source']);
			break;				
		default;
	}
	
	//delete an instance from DB
	if (isset($_POST['DeleteVRAS'])) {
		DeleteFromTable("value_recognition_as", $_POST['DeleteVRAS']);
	}	
?>




<?php
/**Display existing VRAS instances*/
	$vrasData = mysqli_query("select value_recognition_as_id, value_name, source_name
	from value_recognition_as, source, value
	where source_id = value_recognition_as.source
	and value_id = value_recognition_as.value")
 	or trigger_error(mysqli_error()); 
	
	Print "<legend><h2>Value Recognition Argumentation Schemes</h2></legend>";
	Print "<p>";
	Print "<table border cellpadding=3>";
	Print "<tr>";
	Print "<th>ID</th><th>Value</th><th>Source</th><th>Remove</th>";
	while($info = mysqli_fetch_array( $vrasData )) { 
		Print "<tr>";
		Print "<td>".$info['value_recognition_as_id'] . "</td> ";
		Print "<td>".$info['value_name']."</td> ";
		Print "<td>".$info['source_name']."</td> ";
		Print "<td>";
		print "<form action='vras.php' method='POST'>";
		print "<input type='submit' value='delete'/>";
		print "<input type='hidden' name='DeleteVRAS' value=".$info['value_recognition_as_id']." />";
		print "</form>";
		print "</td> ";
		
	} 
	Print "</table>"; 
Print "</p>";
?>
<?php 
/**Generates the submit form*/
Print "<p>";
Print "<legend><h2>New Value Recognition Argumentation Scheme Instance</h2></legend>";
Print "<p>";
print "<fieldset>";
print "<form action='vras.php' method='POST'>";
print "<p>Value: <select name = 'value'>";
echo createOptions('value', 'value_id', 'value_name');
print "</select></p>";
print "<p>Expert: <select name = 'source'>";
echo createOptions('source', 'source_id', 'source_name');
print "</select></p>";
print "<input type='hidden' name='mode' value='insert-vras'/>";
print "<input type='submit'>";
print "</form>";
print "</fieldset>";

print "</body>";
print "</html>";
?>