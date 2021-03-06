<?php 

session_start();
include('support/database_connect.php');

?>
<?php 
function constructURI($uri_string){	
	$delimiter = "/";
	return make_uniform(explode($delimiter, $uri_string));
}

// make sure all uri's are of form:
// /firstElem/secondElem/../lastElem
function make_uniform($ar){
	if($ar[0] == "") array_shift($ar);
	if(sizeof($ar)>0){
		if($ar[sizeof($ar)-1] == "") array_pop($ar);
	}
	return $ar;
}
?>
<?php 

function count_row($tableName){
	$getCount = mysqli_query("SELECT count(*) FROM ".$tableName)or trigger_error(mysqli_error());
	$count=mysqli_fetch_row($getCount)or trigger_error(mysqli_error());
	return $count[0]; 
}



function create_table_id($prefix, $tableName){
  //Large parts of this function disabled by Jochem Douw (25 June 2012), because we are abandoning the approach of ID's generated by the code and this function contains a bug.
  //When a table has an integer id field with auto_increment, the database just doesn't store any (string) value that is added but instead inserts it's own unique ID. That's why inserting this empty string doesn't hurt.
  echo "";
  /*	
	$count = count_row($tableName);

	if($count<9){//form the name based on a pattern
		$id = $prefix.'0'.($count+1);
	}else{
		$id = $prefix.($count+1);
	}
	
	return $id;*/
}

$arguemntID = -1;
if(!isset($_GET['submitid'])){
	$submitid='default';
}
else{
	$submitid = $_GET['submitid'];//changed 
	$submittedArray = constructURI($_GET['submitid']);
	$submitid = $submittedArray[0];//changed
	if($submitid == "survey-results"){
		$arguemntID = $submittedArray[1];
	}
}


// The switch statement moves between the pages of the survey.
switch($submitid){
	case "circumstance":
		if($_POST['user_name']){
			$user_name = $_POST['user_name'];
				
		}else{
			Print "error with name";
		}
		
		// a session variable to contain all the information about the user's consultation.
		
		global $user_info;
		//for each user we have one user name and consultation instance.  In pairs (X, Y), the first is
		// an id and the second the table name.
		$user_info = array(create_table_id('user', 'user'), $user_name, create_table_id('consultInst','consultation_inst'));
		
		$_SESSION['user_info'] = $user_info;//user_id, user_name and consultation instance
				
		
		//I think in the future, when we have more argumentation schemes, we can select scheme from this page. 
		//at this stage, we need to determine the practical reasoning as and the conjunction that groups the associated credible source
		$user_info = $_SESSION['user_info'];
		$prID = "prAS01";
		//more will come to decide the which argumentation scheme is going to use. 
		array_push($user_info, $prID);		
		$_SESSION['user_info'] = $user_info;		
		include('main/circumstance.php');
	break;

	case "consequence":			
		$whole_circ = $_SESSION['circumstance'];		
		// First time array numbers are used.  The numbers access positions in the array.
		// See the page on circumstance for further information about these index values.
		// Each session variable is an array.  Each array has subarrays.  The subarrays
		// have elements that need to be tracked.  For example, domain_source may appear
		// in several subarrays, and we need to keep track of that domain_source across
		// these subarrays.
		
		//0 => id, 1 => string, 2 => domain_source, 3 => source_proposition, 4 => domain_proposition 
		//5 => credible source id
		//start from 6th value, elements are the reseponse of each question
		
		// 0 is used here as described below.  These other variables are not used on this
		// page.
		
		global $whole_circ_new;		
		$whole_circ_new = array();		
		// Create new array to hold the user responses relative to the statements
		// on the webpage.  So, in the following, we have the new array for update-circ
		// and the source of the statements circumstances.
		
		// sub_circ is an array.  sub_circ[0] is the first element of the array
		// which is an id.  'domain_source_'.$sub_circ[0]
		// is the name of a radio button that records the user's response to pass
		// it to this session variable.  At the end, the input on this session
		// variable is recorded to the DB.  This connects elements of the argumentation
		// scheme to the user's response for that element.
		
		if( isset($_POST['update-circ']) && isset($_SESSION['circumstance'])){			
			foreach($whole_circ as $sub_circ){
				array_push($sub_circ, $_POST[$sub_circ[0]]);
				array_push($sub_circ, $_POST['domain_source_'.$sub_circ[0]]);
				array_push($sub_circ, $_POST['source_proposition_'.$sub_circ[0]]);
				array_push($sub_circ, $_POST['domain_proposition_'.$sub_circ[0]]);
				array_push($whole_circ_new, $sub_circ);
			}			
		}else{
			header('HTTP/1.0 400 Bad Request');
					echo json_encode(
						array("error"=>"Could not parse request")
					);
		}
		
		global $external_info;		
		$external_info = array();
		
		if(isset($_POST['external-circ'])){
			
			array_push($external_info, $_POST['external-circ']);
			
		}else{
			header('HTTP/1.0 400 Bad Request');
					echo json_encode(
						array("error"=>"Could not parse request")
					);
		}
		
		$_SESSION['external_info'] = $external_info;
		
		$_SESSION['circumstance_new'] = $whole_circ_new;
		
		include('main/consequence.php');
	break;
		
	case "value":
		
		$whole_cons = $_SESSION['consequence'];

		//0 => id, 1 => string, 2 => domain_source, 3 => source_proposition, 4 => domain_proposition 
		//5 => credible source id
		//start from 6th value, elements are the reseponse of each question	
		global $whole_cons_new;
		$whole_cons_new = array();
		

		if( isset($_POST['update-cons']) && isset($_SESSION['consequence'])){
			foreach($whole_cons as $sub_cons){
				array_push($sub_cons, $_POST[$sub_cons[0]]);
				array_push($sub_cons, $_POST['domain_source_'.$sub_cons[0]]);
				array_push($sub_cons, $_POST['source_proposition_'.$sub_cons[0]]);
				array_push($sub_cons, $_POST['domain_proposition_'.$sub_cons[0]]);
				array_push($whole_cons_new, $sub_cons);
			}

		}else{
			header('HTTP/1.0 400 Bad Request');
					echo json_encode(
						array("error"=>"Could not parse request")
					);
		}
		
		$external_info = $_SESSION['external_info'];
		if(isset($_POST['external-cons'])){
			array_push($external_info, $_POST['external-cons']);
		}else{
			header('HTTP/1.0 400 Bad Request');
			echo json_encode(
					array("error"=>"Could not parse request")
			);
		}

		$_SESSION['external_info'] = $external_info;		
		$_SESSION['consequence_new'] = $whole_cons_new;		
		include('main/value.php');
	break;
	
	case "summary":
		
		$whole_value = $_SESSION['value'];
		
		//0 => id, 1 => name,  2 => default_choice 3 => domain_source, 4 => source_proposition, 5 => domain_proposition
		//6 => credible_source_id 7=> value_recognition_as_id
		//8 => statement_authority_recognition 9=> statement_endows_value

		//start from 7th value, elements are the reseponse of each question

		global $whole_value_new;
		$whole_value_new = array();
		if( isset($_POST['update-values']) && isset($_SESSION['value'])){
			foreach($whole_value as $sub_value){
				array_push($sub_value, $_POST[$sub_value[0]]);
				array_push($sub_value, $_POST['domain_source_'.$sub_value[0]]);
				array_push($sub_value, $_POST['source_proposition_'.$sub_value[0]]);
				array_push($sub_value, $_POST['domain_proposition_'.$sub_value[0]]);
				array_push($sub_value, $_POST['authority_recognition_'.$sub_value[0]]);
				array_push($sub_value, $_POST['endorses_value_'.$sub_value[0]]);
				array_push($whole_value_new, $sub_value);
			}

		}else{
			header('HTTP/1.0 400 Bad Request');
			echo json_encode(
					array("error"=>"Could not parse request")
			);
		}
		
		$external_info = $_SESSION['external_info'];
		if(isset($_POST['external-value'])){
			array_push($external_info, $_POST['external-value']);
		}else{
			header('HTTP/1.0 400 Bad Request');
			echo json_encode(
					array("error"=>"Could not parse request")
			);
		}

		$_SESSION['external_info'] = $external_info;		
		$_SESSION['value_new'] = $whole_value_new;
		include('main/summary.php');
	break;
	
	case "exit":
		include("support/submit_database.php");
		include("support/exit.php");
	break;
	
	case "survey-results":
		//TODO: DO THE JSON TRANSFER WITH NEIL
		$_SESSION['pras_id'] = $arguemntID;
		include('main/avt_api.php');
		break;
	
	default:
		include('main/home.php');
		break;
	
}


?>

