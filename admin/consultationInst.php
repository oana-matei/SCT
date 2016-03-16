<?php
/*Displays details of existing consultations and allows the user to create new consultation from existing instances of the various schemes

AZW - Nov 12 removed

include ('admin_header.php');

from here as in some others.  This added nice colour, but did not seem to be used
regularly.

*/
	session_start();
	include('../support/database_connect_admin.php');
	include ('admin_utility.php');

?>

<?php 
/*Glabals*/
/*$consultationDetails: an array of the various elements of existing consultations*/
	global $consultationDetails;
	$consultationDetails= array();	
/*$newConsultation: an array of the various elements of new consultations*/
	global $newConsultation;
	$newConsultation= array();

?>

<?php 
/*Local Functions*/
/*inserts new occurence of an argumentation scheme to the DB*/
function InsertSchemeOccurrence($occurrenceName, $conjunctionID, $occurrenceID){
	$occurrenceTable = $occurrenceName."_occurrence";
	$occurrenceSource = $occurrenceName."_as";
	$sql="INSERT INTO $occurrenceTable (conjunction, $occurrenceSource)
			VALUES ($conjunctionID, $occurrenceID)";
	mysqli_query($sql) or trigger_error(mysqli_error()."in query".$sql);

}

/*displays the details of the consultation and has two modes:
 * 0: completed consultation: either as queried from DB or a new consultation in its final phase of construction
 * 1: under construction*/
function displayConsultation($cdetails, $mode){
	if(sizeof($cdetails)>0){
		if($mode==0){
			print"<form action='consultationInst.php' method='POST'>";
			print"<input type='hidden' name='mode' value='HideDetalis'/>";
			print"<input type='submit' value='Hide'/>";
			print"</form>";
			print"<legend><h4>Practical Reasoning Scheme</h4></legend>";
			print"<legend><h5>Circumstances</h5></legend>";
		}
		else{
			print"<table>";
			print"<tr>";
			print"<td>";
			print"<form action='consultationInst.php' method='POST'>";
			print"<input type='hidden' name='mode' value='insert-consultation'/>";
			print"<input type='submit' value='Submit Consultation'>";
			print"</form>";
			print"</TD>";
			print"<TD>";
			print"<form action='consultationInst.php' method='POST'>";
			print"<input type='hidden' name='mode' value='reset-consultation'/>";
			print"<input type='submit' value='Reset'>";
			print"</form>";
			print"</TD>";
			print"</TR>";
			print"</table>";
				
			print"<legend><h4>Practical Reasoning Scheme</h4></legend>";
			print"<legend><h5>Circumstances</h5></legend>";
						
		}
		
		/* AZW - November 14, 2012.  There is a typo in the code, though
		 it may not bear on functionality.  cricumstances should be
		 circumstances.  I will consult Maya about making this change.
		 It only appears that this typo is in this section.  The issue about
		 making the change is that we don't want to bind things elsewhere.
		*/
		
		//the details of the practical reasoning instance
		$prasData =$cdetails[0];
		$cricumstances = $prasData[1];
		$action = $prasData[2];
		$consequences = $prasData[3];				
		$values = $prasData[4];
					
		foreach ($cricumstances as $cricumstance){
			print"<li>".$cricumstance[0]." - ".$cricumstance[2]."</li>";
		}
					
		print"<legend><h5>Action</h5></legend>";
		echo $action;
		print"<legend><h5>Consquences</h5></legend>";
		foreach ($consequences as $consequence){
			print"<li>".$consequence[0]." - ".$consequence[2]."</li>";
		}
	
		print"<legend><h5>Values</h5></legend>";			
		foreach ($values as $value){
			print"<li>".$value[0]." - ".$value[2]."</li>";
		}
					
		print"<legend><h4>Credible Sources Schemes - Circumstances</h4></legend>"; 
		if(sizeof($cdetails)>1){
			$CSASCricumData=$cdetails[1];
			foreach ($CSASCricumData as $csas){
				$sourceString = $csas[0]." - Source: ".$csas[3]." - In Domain: ".$csas[4];
				print"<li>".$sourceString."</li>";
				print"<li>".$csas[5]."</li>";
				print"<li>".$csas[6]."</li>";
			}
		}
					
		print"<legend><h4>Credible Sources Schemes - Consequences</h4></legend>";
		if(sizeof($cdetails)>2){ 
			$CSASConsData=$cdetails[2];
			foreach ($CSASConsData as $csas){
				$sourceString = $csas[0]." - Source: ".$csas[3]." - In Domain: ".$csas[4];
				print "<li>".$sourceString."</li>";
				print "<li>"."Source Proposition: " . $csas[5]."</li>";
				print "<li>"."Domain Proposition: " .$csas[6]."</li>";
			}
		}
		
		
		print"<legend><h4>Value Credible Sources Schemes</h4></legend>";
		if(sizeof($cdetails)>3){
			$VCSASData=$cdetails[3];
			foreach ($VCSASData as $vcsas){	
			$sourceString = $vcsas[0]." - Source: ".$vcsas[3]." - In Domain: ".$vcsas[4];
				print "<li>".$sourceString."</li>";
				print "<li>"."Asserts that action: " . $vcsas[6]."</li>";
				print "<li>"."affects value: " .$vcsas[5]."</li>";
			}
		}
					
		print "<legend><h4>Value Recognition Schemes</h4></legend>";
		if(sizeof($cdetails)>4){
			$VRASData=$cdetails[4];
			foreach ($VRASData as $vras){
				$valueString = $vras[0]." - Source: ".$vras[3]." - Value: ".$vras[4];
				print "<li>".$valueString."</li>";
			}
		}
		if(sizeof($cdetails)>5){
			$consultationInfo=$cdetails[5];
			print "<legend><h4>Consultation Information</h4></legend>";
			print "<li>".$consultationInfo."</li>";
		}	
	}
}		  	

/*returns the instance of Practical Reasoning Scheme used in the input consultation */
Function GetConsultationPRAS($consultationID){
	$consultationPRAS = mysqli_query("select practical_reasoning_as_id,
				practical_reasoning_as.circumstances,
				practical_reasoning_as.consequences,
				practical_reasoning_as.values,
				action_name, agent_name
				from practical_reasoning_as, consultation, action, agent
				where practical_reasoning_as_id = consultation.practical_reasoning_as
				and practical_reasoning_as.action = action.action_id and agent.agent_id = action.agent
				and consultation_id=$consultationID") or trigger_error(mysqli_error());
	$info = mysqli_fetch_array($consultationPRAS);
	return $info;
}

/*returns the ID and Name attributes of the propositions conjoined together by the input conjunction*/
Function  GetPropositionsData($propConj){
	$data = mysqli_query("select proposition_id, proposition_string
	from proposition, conjunction, prop_occurrence where
	conjunction_id = prop_occurrence.conjunction and
	proposition_id = prop_occurrence.proposition and conjunction_id = $propConj") or trigger_error(mysqli_error());
	return $data;
}

/*returns the ID and Name attributes of the values conjoined together by the input conjunction*/
Function GetValueData($valueConj){
	$data = mysqli_query("select  value_id, value_name
	from value, conjunction, value_occurrence
	where conjunction_id = value_occurrence.conjunction
	and value_id = value_occurrence.value
	and conjunction_id = $valueConj") or trigger_error(mysqli_error());
	return $data;
}

/*returns the instance of Credible Source Scheme used in the input consultation in relation to the input proposition*/
Function GetCSASForProposition($propID, $consultationID){
	$data = mysqli_query("select credible_source_as_id, source_name, domain_name,
	s.proposition_string as s_proposition,
	d.proposition_string as d_proposition
	from consultation,  credible_source_as, credible_source_occurrence, conjunction,
	domain_source, domain_proposition, source_proposition,
	domain, source, proposition as s, proposition as d
	where consultation.credible_source_as = conjunction.conjunction_id
	and conjunction.conjunction_id = credible_source_occurrence.conjunction
	and credible_source_occurrence.credible_source_as = credible_source_as_id
	and credible_source_as.domain_source = domain_source_id
	and credible_source_as.domain_proposition = domain_proposition_id
	and credible_source_as.source_proposition = source_proposition_id
	and domain_source.domain = domain_id
	and domain_source.source = source_id
	and source_proposition.source = source_id
	and domain_proposition.domain = domain_id
	and domain_proposition.proposition = d.proposition_id
	and source_proposition.proposition = s.proposition_id
	and consultation_id=$consultationID
	and s.proposition_id=$propID") or trigger_error(mysqli_error());
	return $data;
}

/*returns the instance of Value Recognition Scheme used in the input consultation in relation to the input value*/
Function GetVRASForValue($valueID,  $consultationID){
	$data = mysqli_query("Select value_recognition_as_id, source_name, value_name
						from consultation, conjunction, value_recognition_occurrence,
						value_recognition_as, source, value
						where value_recognition_as_id = value_recognition_occurrence.value_recognition_as
						and value_recognition_occurrence.conjunction = conjunction_id
						and consultation.value_recognition_as = conjunction_id
						and value_recognition_as.value = value.value_id
						and value_recognition_as.source = source.source_id
						and value_recognition_occurrence.value_recognition_as = value_recognition_as_id
						and conjunction_id = value_recognition_occurrence.conjunction
						and consultation_id=$consultationID and value_id=$valueID") or trigger_error(mysqli_error());
	return $data;
}


/*returns the instance of Value Credible Source Scheme used in the input consultation in relation to the input value*/
Function GetVCSASForValue($valueID,  $consultationID){
	$data = mysqli_query("select value_credible_source_as_id, source_name, domain_name, Concat(value_name, ' - ' , default_choice) as value_choice, action_name
							from consultation, value_credible_source_as, value_credible_source_occurrence,
							     domain_source, domain, source,
							     action, 
							     value_occurrence_default_choice, value_occurrence, value
							where consultation_id = $consultationID and value_id=$valueID
							and consultation.value_credible_source_as = value_credible_source_occurrence.conjunction
							and value_credible_source_occurrence.value_credible_source_as = value_credible_source_as_id
							and value_credible_source_as.domain_source=domain_source_id
							and value_credible_source_as.action = action_id
							and domain_source.source = source_id
							and domain_source.domain=domain_id
							and value_credible_source_as.value_occurrence_default_choice = value_occurrence_default_choice_id
							and value_occurrence_default_choice.value_occurrence = value_occurrence_id
							and value_occurrence.value = value_id") or trigger_error(mysqli_error());
	return $data;
}

/*returns the details of the input instance of Credible Source scheme*/
Function GetCSASData($csasID){
	$data = mysqli_query("select credible_source_as_id, source_name, domain_name,
			s.proposition_string as s_proposition,
			d.proposition_string as d_proposition
			from domain, source, credible_source_as, domain_source, domain_proposition, source_proposition, proposition as s, proposition as d
			where credible_source_as.domain_source = domain_source_id
			and credible_source_as.domain_proposition = domain_proposition_id
			and credible_source_as.source_proposition = source_proposition_id
			and domain_source.domain = domain_id
			and domain_source.source = source_id
			and source_proposition.source = source_id
			and domain_proposition.domain = domain_id
			and domain_proposition.proposition = d.proposition_id
			and source_proposition.proposition = s.proposition_id
			and credible_source_as_id=$csasID") or trigger_error(mysqli_error());
	return $data;
}

/*returns the details of the input instance of Value Recognition scheme*/
Function GetVRASData($vrasID){
	$data =  mysqli_query("Select value_recognition_as_id, source_name, value_name
	from value_recognition_as, source, value
	where value_recognition_as.value = value.value_id
	and value_recognition_as.source = source.source_id
	and value_recognition_as_id=$vrasID") or trigger_error(mysqli_error());
	return $data;
}

/*returns the details of the input instance of Value Credible Source scheme*/
Function GetVCSASData($vcsasID){
	$data =  mysqli_query("select value_credible_source_as_id, source_name, domain_name, Concat(value_name, ' - ' , default_choice) as value_choice, action_name
							from value_credible_source_as, 
							     domain_source, domain, source,
							     action, 
							     value_occurrence_default_choice, value_occurrence, value
							where value_credible_source_as.domain_source=domain_source_id
							and value_credible_source_as.action = action_id
							and domain_source.source = source_id
							and domain_source.domain=domain_id
							and value_credible_source_as.value_occurrence_default_choice = value_occurrence_default_choice_id
							and value_occurrence_default_choice.value_occurrence = value_occurrence_id
							and value_occurrence.value = value_id
							and value_credible_source_as_id = $vcsasID") or trigger_error(mysqli_error());
	return $data;
}

/*returns a dataset of all credible source schemes linked to the circumstances of the input instance of the practical reasoning scheme
 * TODO: a consultation ID maybe added to this query in case same PRAS is used in different consultations*/
Function GetCSASforPRASCircumstances($selectedPRAS){
	$data = mysqli_query("select credible_source_as_id, source_name, domain_name, s.proposition_string as source_p, d.proposition_string as domain_p
	from domain, source, credible_source_as, domain_source, domain_proposition, source_proposition, proposition as s, proposition as d
	where credible_source_as.domain_source = domain_source_id
	and credible_source_as.domain_proposition = domain_proposition_id
	and credible_source_as.source_proposition = source_proposition_id
	and domain_source.domain = domain_id
	and domain_source.source = source_id
	and source_proposition.source = source_id
	and domain_proposition.domain = domain_id
	and domain_proposition.proposition = d.proposition_id
	and source_proposition.proposition = s.proposition_id
	and s.proposition_id in
	(select proposition_id
	from proposition, prop_occurrence, conjunction, practical_reasoning_as
	where prop_occurrence.proposition = proposition_id
	and prop_occurrence.conjunction = conjunction_id
	and practical_reasoning_as.circumstances = conjunction_id
	and practical_reasoning_as_id = $selectedPRAS)")
	or trigger_error(mysqli_error());
	return $data;
}

/*returns a dataset of all credible source schemes linked to the consequences of the input instance of the practical reasoning scheme

 * TODO: a consultation ID maybe added to this query in case same PRAS is used in different consultations*/
Function GetCSASforPRASConsequences($selectedPRAS){
	$data = mysqli_query("select credible_source_as_id, source_name, domain_name, s.proposition_string as source_p, d.proposition_string as domain_p
			from domain, source, credible_source_as, domain_source, domain_proposition, source_proposition, proposition as s, proposition as d
			where credible_source_as.domain_source = domain_source_id
			and credible_source_as.domain_proposition = domain_proposition_id
			and credible_source_as.source_proposition = source_proposition_id
			and domain_source.domain = domain_id
			and domain_source.source = source_id
			and source_proposition.source = source_id
			and domain_proposition.domain = domain_id
			and domain_proposition.proposition = d.proposition_id
			and source_proposition.proposition = s.proposition_id
			and s.proposition_id in
			(select proposition_id
			from proposition, prop_occurrence, conjunction, practical_reasoning_as
			where prop_occurrence.proposition = proposition_id
			and prop_occurrence.conjunction = conjunction_id
			and practical_reasoning_as.consequences = conjunction_id
			and practical_reasoning_as_id = $selectedPRAS)")
			or trigger_error(mysqli_error());
	return $data;

}
?>


<?php 
if (isset($_POST['ShowConsultation'])) {
	/*SHOWS DETAILS OF CONSULTATION AND UNSETS*/
	/*we create an array comprising all information in the consultation
	 * $consultationDetails comprises three arrays:
	* $consultationDetails[0]: practical reasoning array
	* $PRASInfoArray[0]: circumstances - array of Number - Proposition
	* $PRASInfoArray[1]: Action - string:  $action performed by $Agent
	* $PRASInfoArray[2]: consequences - array of Number - Proposition
	* $PRASInfoArray[3]: values - array of Number - value
	* --------------------------------------
	* $consultationDetails[1]: credible sources array
	* $consultationDetails[1][0]: credible sources for Circumstances
	*  $consultationDetails[1][2]: credible sources for Consequences
	* each element $consultationDetails[x][y] is an array $CSASInfoArray
	* $CSASInfoArray[0] number (counter)
	* $CSASInfoArray[1] source
	* $CSASInfoArray[2] domain
	* $CSASInfoArray[3] proposition of source
	* $CSASInfoArray[4] proposition is in the domain
	* --------------------------------------
	* $consultationDetails[2]: value credible sources array
	* each element $consultationDetails[2] is an array $VCSASInfoArray
	* $VCSASInfoArray[0] number (counter)
	* $VCSASInfoArray[1] source
	* $VCSASInfoArray[2] domain
	* $VCSASInfoArray[3] action
	* $VCSASInfoArray[4] value_option
	* --------------------------------------
	* $consultationDetails[3]: value recognition array
	* each element is an array $VRASInfoArray
	* $VRASInfoArray[0] counter
	* $VRASInfoArray[1] source
	* $VRASInfoArray[2] value*/

	$consultationID = $_POST['ShowConsultation'];
	$consultationDetails= array();

	//1) PRACTICAL REASONING SCHEME
	$info =GetConsultationPRAS($consultationID); 
	$conjCircum = $info['circumstances'];
	$conjConsq = $info['consequences'];
	$conjValue = $info['values'];
	$actionString = $info['action_name']." - Performed by Agent : ".$info['agent_name'];

	//1.1) circumstances
	$circumstances = array();
	$dataCircum = GetPropositionsData($conjCircum);
	$counter=1;
	while($infoCircum= mysqli_fetch_array($dataCircum)){
		$circumstance = array();
	    array_push($circumstance, $counter);
	   	array_push($circumstance, $infoCircum['proposition_id']);
	    array_push($circumstance, $infoCircum['proposition_string']);
	    array_push($circumstances, $circumstance);
	    $counter++;
	}

	//1.3) consequences	
	$consequences = array();
	$dataCons =GetPropositionsData($conjConsq); 
	$counter=1;
	while($infoCons = mysqli_fetch_array($dataCons)){
		$consequence = array();
		array_push($consequence, $counter);
		array_push($consequence, $infoCons['proposition_id']);
		array_push($consequence, $infoCons['proposition_string']);
		array_push($consequences, $consequence);
		$counter++;
	}

	//1.3) values	
	$values = array();
	$dataValues = GetValueData($conjValue);
	$counter=1;
	while($infoValues = mysqli_fetch_array($dataValues)){
		$value = array();
		array_push($value, $counter);
  		array_push($value, $infoValues['value_id']);
		array_push($value, $infoValues['value_name']);
		array_push($values, $value);
		$counter++;
	}

	//1.4) Push all elements into $consultationDetails
	$PRASInfoArray = array();
	array_push($PRASInfoArray, $info['practical_reasoning_as_id']);
	array_push($PRASInfoArray, $circumstances);
	array_push($PRASInfoArray, $actionString);
	array_push($PRASInfoArray, $consequences);
	array_push($PRASInfoArray, $values);
	array_push($consultationDetails, $PRASInfoArray);

	/***************************************************************************************/
	//2) CREDIBLE SOURCE SCHEME
	//2.1)CREDIBLE SOURCEs for Circumstances
	$CSASCircunInfoArray = array();
	foreach($circumstances as $circumstance){
		
		//2.1.1 find the credible source scheme where source_proposition is the same as $circumstances[1] that is also part of consultation $_POST["ShowConsultation"]
		$dataCSAS=GetCSASForProposition($circumstance[1], $consultationID); 			
		if(mysqli_num_rows ($dataCSAS)>0){		
			$credible_source=array();
				while($info = mysqli_fetch_array( $dataCSAS )){
					array_push($credible_source, $circumstance[0]);
						array_push($credible_source, $circumstance[1]);
						array_push($credible_source, $info['credible_source_as_id']);
						array_push($credible_source, $info['source_name']);
						array_push($credible_source, $info['domain_name']);
						array_push($credible_source, $info['s_proposition']);
						array_push($credible_source, $info['d_proposition']);
					}
					array_push($CSASCircunInfoArray, $credible_source);
			}
		}
		//2.1.2) Push all elements into $consultationDetails
		array_push($consultationDetails, $CSASCircunInfoArray);

		//2.3)CREDIBLE SOURCEs for Consequences
		$CSASConseqInfoArray = array();
		foreach($consequences as $consequence){
		//2.2.1 find the credible source scheme where source_proposition is the same as $circumstances[1] that is also part of consultation $_POST["ShowConsultation"]
			$dataCSAS=GetCSASForProposition($consequence[1], $consultationID);
			if(mysqli_num_rows ($dataCSAS)>0){
				$credible_source=array();
				while($info = mysqli_fetch_array( $dataCSAS )){
						array_push($credible_source, $consequence[0]);
						array_push($credible_source, $consequence[1]);
						array_push($credible_source, $info['credible_source_as_id']);
						array_push($credible_source, $info['source_name']);
						array_push($credible_source, $info['domain_name']);
						array_push($credible_source, $info['s_proposition']);

						array_push($credible_source, $info['d_proposition']);
				}
			array_push($CSASConseqInfoArray, $credible_source);
			}
		}
		//2.3.2) Push all elements into $consultationDetails
		array_push($consultationDetails, $CSASConseqInfoArray);

		/***************************************************************************************/
		//3) VALUE CREDIBLE SOURCE SCHEME		
		$VCSASInfoArray = array();
		foreach($values as $value){
			$dataVCSAS= GetVCSASForValue($value[1], $consultationID);
			if(mysqli_num_rows ($dataVCSAS)>0){
				$value_csas=array();
				while($info = mysqli_fetch_array($dataVCSAS)){
					array_push($value_csas, $value[0]);//0
					array_push($value_csas, $value[1]);//1
					array_push($value_csas, $info['value_credible_source_as_id']); //2
					array_push($value_csas, $info['source_name']);//3
					array_push($value_csas, $info['domain_name']);//4
					array_push($value_csas, $info['value_choice']);//5
					array_push($value_csas, $info['action_name']);//6
				}
				array_push($VCSASInfoArray, $value_csas);
			}
		}
		//3.2) Push all elements into $consultationDetails
		array_push($consultationDetails, $VCSASInfoArray);

		/***************************************************************************************/
		//4) VALUE RECOGNITION SCHEME
		$VRASInfoArray = array();
		foreach($values as $value){	
			$dataVRAS= GetVRASForValue($value[1], $consultationID);
			if(mysqli_num_rows ($dataVRAS)>0){
				$value_recognition=array();
				while($info = mysqli_fetch_array($dataVRAS)){
					array_push($value_recognition, $value[0]);
					array_push($value_recognition, $value[1]);
					array_push($value_recognition, $info['value_recognition_as_id']);
					array_push($value_recognition, $info['source_name']);
					array_push($value_recognition, $info['value_name']);
				}
				array_push($VRASInfoArray, $value_recognition);
			}
		}
		//4.2) Push all elements into $consultationDetails
		array_push($consultationDetails, $VRASInfoArray);

		/***************************************************************************************/		
		//5) consultation info
		$consultationInfo = mysqli_query("select consultation_info from consultation
			where consultation_id=$consultationID") or trigger_error(mysqli_error());
			$info = mysqli_fetch_array($consultationInfo);
			array_push($consultationDetails, $info['consultation_info']);

		/***************************************************************************************/
			//UNSET
			unset ($_POST['ShowConsultation']);
			unset ($_SESSION['newConsultation']);
			unset ($_SESSION['step']);
		}
?>
<?php 
	/*if the mode is not set --> set to undefined to avoid error message*/
	if (!isset($_POST['mode'])) {
		$_POST['mode'] = "undefine";
	}

/*The actual entry to the database - depends on the forum submitted.
 * Ids are auto generated by the sql (auto incremented fields) */
	
	switch($_POST['mode']){
		/*INSERT NEW CONSULTATION TO DB:
		 * 1) GENERATES CONJUNCTION OF CSAS/VRAS/VCSAS ELEMENTS AND INSERTS THE conjunctions and the occurrences to DB
		 * 2) inserts the consultation into the DB with the values from 1*/
		/*TODO: in the future this is better done as transaction on the SQL side so that ROLLBACKS can be triggered should any of the intermediate 
		 * DB insersts do not work.*/
		case "insert-consultation":		
			if(isset($_SESSION['newConsultation'])){
				$newConsultationArray = $_SESSION['newConsultation'];
				$pras = $newConsultationArray[0][0];
				
				//insert csas conjunction
				$csasConj = GenerateConjunctionID();		
				//CSAS
				$consultationCSAS= $newConsultationArray[1];
				foreach ($consultationCSAS as $csas){
					InsertSchemeOccurrence("credible_source", $csasConj, $csas[2]);

				}				
				$consultationCSAS = $newConsultationArray[2];
				foreach ($consultationCSAS as $csas){
					InsertSchemeOccurrence("credible_source", $csasConj, $csas[2]);
				}
				
				//VCSAS
				$vcsasConj = GenerateConjunctionID();
				$consultationVCSAS = $newConsultationArray[3];
				foreach ($consultationVCSAS as $vcsas){
					InsertSchemeOccurrence("value_credible_source", $vcsasConj, $vcsas[2]);
				}
				
				//insert vras conjunction
				$vrasConj = GenerateConjunctionID();
				$consultationVRAS = $newConsultationArray[4];
				//insert given values accordingly
				foreach ($consultationVRAS as $vras){
					InsertSchemeOccurrence("value_recognition", $vrasConj, $vras[2]);	

				}
				//insert the consultation to DB
				$consultationInfo = $newConsultationArray[5];			
				$sql="INSERT INTO consultation (practical_reasoning_as, credible_source_as, value_credible_source_as, value_recognition_as, consultation_info)
					VALUES ($pras,$csasConj,$vcsasConj, $vrasConj,'$consultationInfo')";
				mysqli_query($sql) or trigger_error(mysqli_error()) or trigger_error(mysqli_error());
			}
			unset ($_SESSION['newConsultation']);
			unset ($_SESSION['step']);
			break;
		
		//HIDE THE DETAILS OF CONSULTATION
		case "HideDetalis":
			$consultationDetails= array();
			unset ($_SESSION['newConsultation']);
			break;

	   //RESET A CONSULTATION CONSTRUCTion PROCESS
		case "reset-consultation":
			unset ($_SESSION['newConsultation']);
			unset ($_SESSION['step']);
			break;						
			default;
	}
	
	
	if (isset($_POST['DeleteConsultation'])) {
		DeleteFromTable("consultation", $_POST['DeleteConsultation']);		
		unset ($_SESSION['newConsultation']);
		unset ($_SESSION['step']);
	}	
	
?>	
<?php 
/*THIS CODE GENERATES THE RIGHT HAND SIDE HTML 
 * the user can see the details of the consultation under construction
 * TODO: very similar to the code above, needs tidying up*/	
/*1 PRACTICAL REASONING SCHEME*/
	if (isset($_POST['selectPRAS'])) {
		$_SESSION['step']=2;
		$newconsultationDetails=array();
		$newPRAS = mysqli_query("select practical_reasoning_as_id, practical_reasoning_as.circumstances, practical_reasoning_as.consequences, 
								practical_reasoning_as.values, action_name, agent_name
								from practical_reasoning_as, action, agent		
				where practical_reasoning_as.action = action.action_id and agent.agent_id = action.agent
				and practical_reasoning_as_id={$_POST["selectPRAS"]}") or trigger_error(mysqli_error());

				
		$info = mysqli_fetch_array($newPRAS);
		$conjCircum = $info['circumstances'];
		$conjConsq = $info['consequences'];
		$conjValue = $info['values'];
		$actionString = "".$info['action_name']." - Performed by Agent : ".$info['agent_name'];
	    $circumstances = array();
	    $dataCircum = GetPropositionsData($conjCircum);


		$counter=1;
		while($infoCircum= mysqli_fetch_array($dataCircum)){
			$circumstance = array();
			array_push($circumstance, $counter);
			array_push($circumstance, $infoCircum['proposition_id']);
			array_push($circumstance, $infoCircum['proposition_string']);
	    	array_push($circumstances, $circumstance);
			$counter++;

	    }
	    
	    $consequences = array();
		$dataCons =GetPropositionsData($conjConsq); 			    
		$counter=1;

		while($infoCons = mysqli_fetch_array($dataCons)){
		    $consequence = array();
		    array_push($consequence, $counter);
		    array_push($consequence, $infoCons['proposition_id']);
		    array_push($consequence, $infoCons['proposition_string']);
		    array_push($consequences, $consequence);
		    $counter++;

	    }

		$values = array();
		$dataValues =GetValueData($conjValue);	    		 
   		$counter=1;
   		while($infoValues = mysqli_fetch_array($dataValues)){
    		$value = array();
    		array_push($value, $counter);
    		array_push($value, $infoValues['value_id']);
    		array_push($value, $infoValues['value_name']);
    		array_push($values, $value);
    		$counter++;

   		}
 
		$PRASArray = array();
		array_push($PRASArray, $info['practical_reasoning_as_id']);
		array_push($PRASArray, $circumstances);
	    array_push($PRASArray, $actionString);
		array_push($PRASArray, $consequences);
		array_push($PRASArray, $values);
		array_push($newconsultationDetails, $PRASArray);
		
		//UNSET PRAS
		$_SESSION['newConsultation'] = $newconsultationDetails;
		unset ($_POST['selectPRAS']);

	}
	
	/*2 CSAS for the CIRCUMSTANCES OF THE PRACTICAL REASONING SCHEME*/
	if (isset($_POST['selectCircumCSAS'])) {
		$_SESSION['step']=3;
		$newconsultationDetails = $_SESSION['newConsultation'];
		$CSASCircuMInfoArray = array();
		$v=intval($_POST['selectCircumCSAS']);
		
		for ($i = 1; $i <= $v; $i++){	
		$name = "CSASCircum_".$i;
		if(isset($_POST[$name])){	
			$dataNewCSAS = GetCSASData($_POST[$name]);
			$credible_source=array();
			while($info = mysqli_fetch_array( $dataNewCSAS )){
				array_push($credible_source, "");
				array_push($credible_source, "");
				array_push($credible_source, $info['credible_source_as_id']);
				array_push($credible_source, $info['source_name']);
				array_push($credible_source, $info['domain_name']);
				array_push($credible_source, $info['s_proposition']);
				array_push($credible_source, $info['d_proposition']);
				}
			array_push($CSASCircuMInfoArray, $credible_source);		
			}
		}
		array_push($newconsultationDetails, $CSASCircuMInfoArray);

		$_SESSION['newConsultation'] = $newconsultationDetails;
		//unset
		unset ($_POST['selectCircumCSAS']);
	}
	
	/*3 CSAS for the CONSEQUENCES OF THE PRACTICAL REASONING SCHEME*/
	if (isset($_POST['selectConsCSAS'])) {
		$_SESSION['step']=4;
		$newconsultationDetails = $_SESSION['newConsultation'];		
		$CSASConsInfoArray = array();
		$v=intval($_POST['selectConsCSAS']);
		for ($i = 1; $i <= $v; $i++){
			$name = "CSAScons_".$i;
			if(isset($_POST[$name])){
					$dataNewCSAS=GetCSASData($_POST[$name]); 
					$credible_source_con=array();
					while($info = mysqli_fetch_array( $dataNewCSAS )){
						array_push($credible_source_con, "");
	  					array_push($credible_source_con, "");
						array_push($credible_source_con, $info['credible_source_as_id']);
						array_push($credible_source_con, $info['source_name']);
						array_push($credible_source_con, $info['domain_name']);
						array_push($credible_source_con, $info['s_proposition']);
						array_push($credible_source_con, $info['d_proposition']);
					}
			array_push($CSASConsInfoArray, $credible_source_con);
		}
	}

	array_push($newconsultationDetails, $CSASConsInfoArray);
	$_SESSION['newConsultation'] = $newconsultationDetails;
	unset ($_POST['selectConsCSAS']);
	}
	
	/*4 VCSAS for the VALUES OF THE PRACTICAL REASONING SCHEME*/
	if (isset($_POST['selectVCSAS'])) {
		$_SESSION['step']=5;
		$newconsultationDetails = $_SESSION['newConsultation'];
  		$VCSASInfoArray = array();
		$v=intval($_POST['selectVRAS']);
		for ($i = 1; $i <= $v; $i++){
			$name = "vcsas_".$i;
			if(isset($_POST[$name])){
				$dataNewVCSAS=GetVCSASData($_POST[$name]);
				$value_csas=array();
				while($info = mysqli_fetch_array($dataNewVCSAS)){
					array_push($value_csas, "");
					array_push($value_csas, "");
					array_push($value_csas, $info['value_credible_source_as_id']);
					array_push($value_csas, $info['source_name']);
					array_push($value_csas, $info['domain_name']);
					array_push($value_csas, $info['value_choice']);//5
					array_push($value_csas, $info['action_name']);//6
				}
				array_push($VCSASInfoArray, $value_csas);
			}
		}
		array_push($newconsultationDetails, $VCSASInfoArray);
		$_SESSION['newConsultation'] = $newconsultationDetails;
		unset ($_POST['selectVCSAS']);
	}
	
	/*5 VRAS for the VALUES OF THE PRACTICAL REASONING SCHEME*/
	if (isset($_POST['selectVRAS'])) {
		$_SESSION['step']=6;
		$newconsultationDetails = $_SESSION['newConsultation'];

		$VRASInfoArray = array();
		$v=intval($_POST['selectVRAS']);
		for ($i = 1; $i <= $v; $i++){
			$name = "vras_".$i;
			if(isset($_POST[$name])){
				$dataNewVRAS=GetVRASData($_POST[$name]);  
				$value_recog=array();
				while($info = mysqli_fetch_array( $dataNewVRAS )){
					array_push($value_recog, "");
					array_push($value_recog, "");
					array_push($value_recog, $info['value_recognition_as_id']);
					array_push($value_recog, $info['source_name']);
					array_push($value_recog, $info['value_name']);
					}
				array_push($VRASInfoArray, $value_recog);
			}
		}
		array_push($newconsultationDetails, $VRASInfoArray);
		$_SESSION['newConsultation'] = $newconsultationDetails;
		unset ($_POST['selectVRAS']);

	}
	
	/*6 Additional INFO*/
	if (isset($_POST['additionalInfo'])) {
		$_SESSION['step']=7;
		$newconsultationDetails = $_SESSION['newConsultation'];
		array_push($newconsultationDetails, $_POST['additionalInfoText']);
		$_SESSION['newConsultation'] = $newconsultationDetails;
		unset ($_POST['additionalInfo']);
	}
?>


<?php 
/*CODE TO DISPLAY EXISTING INFORMATION
 * additional details of each consultation are generated when the user hits the show details button*/
	print "<p><legend><h2>Consultations</h2></legend></p>";
	print "<div id='contentWrap'>";
	print "<div id='contentLeft'>";

		$dataConsultation = mysqli_query("select consultation.* from consultation") or trigger_error(mysqli_error());
		Print "<table border cellpadding=3 width=80%>";
		Print "<th>ID</th><th>Consultation Information</th>
			   <th> Practical Reasoning Scheme</th><th>Credible Source Schemes</th><th>Value Credible Source Schemes</th><th>Value Recognition Schemes</th>
			   <th>Remove</th><th>Details</th>";

		while($info = mysqli_fetch_array( $dataConsultation )){
			$consultationID = $info['consultation_id'];

			$dataVRAS = mysqli_query(" select value_recognition_as.*
					from value_recognition_as, value_recognition_occurrence, consultation, conjunction
					where value_recognition_as_id = value_recognition_occurrence.value_recognition_as
					and value_recognition_occurrence.conjunction = conjunction_id
					and consultation.value_recognition_as = conjunction_id
					and consultation_id=$consultationID") or trigger_error(mysqli_error());

		

			$dataCSAS= mysqli_query("select credible_source_as.*
 				from credible_source_as, credible_source_occurrence, consultation, conjunction
					where credible_source_as_id = credible_source_occurrence.credible_source_as
					and credible_source_occurrence.conjunction = conjunction_id
					and consultation.credible_source_as = conjunction_id
					and consultation_id=$consultationID") or trigger_error(mysqli_error());

		$dataVCSAS= mysqli_query("select value_credible_source_as.*

		from value_credible_source_as, value_credible_source_occurrence, consultation, conjunction

		where value_credible_source_as_id = value_credible_source_occurrence.value_credible_source_as

		and value_credible_source_occurrence.conjunction = conjunction_id

		and consultation.value_credible_source_as = conjunction_id

		and consultation_id=$consultationID") or trigger_error(mysqli_error());

//NOTE: mysqli_num_rows may cause problem in glassfish
			$rowsCSAS = mysqli_num_rows($dataCSAS);
			$rowsVRAS = mysqli_num_rows($dataVRAS);
			$rowsVCSAS = mysqli_num_rows($dataVCSAS);


			Print "<tr>";
			Print "<td>".$info['consultation_id']. "</td> ";
			Print "<td>".$info['consultation_info']."</td> ";
			Print "<td>".$info['practical_reasoning_as']. "</td> ";
			Print "<td>".$rowsCSAS. "</td> ";
			Print "<td>".$rowsVCSAS. "</td> ";
			Print "<td>".$rowsVRAS. "</td> ";

			Print "<td>";
			print "<form action='consultationInst.php' method='POST'>";
			print "<input type='submit' value='delete'/>";
			print "<input type='hidden' name='DeleteConsultation' value=".$info['consultation_id']." />";
			print "</form> ";
			print "</td> ";

				
			Print "<td>";
			print "<form action='consultationInst.php' method='POST'>";
			print "<input type='submit' value='details'/>";
			print "<input type='hidden' name='ShowConsultation' value=".$info['consultation_id']." />";
			print "</form> ";
			print "</td> ";
			Print "</tr>";

		}
		

	Print "</table>";


print "</div>";
?>
<?php
	print " <div id='contentRight'>";
		if(isset ($_SESSION['newConsultation']) && sizeof($_SESSION['newConsultation'])>0) 
			displayConsultation($_SESSION['newConsultation'], 1);
		else{
			displayConsultation($consultationDetails, 0);
		}
 	
	print "</div>";
	print "</div>";
?>

<?php
/*Generates the html to enable the creation of new consultation; this is done in a number of steps:
 * 1: the user selects an instance of practical_reasoning_as
 * 2: for this instance the user selects the Credible sources for the 
 * 2.1: consequences and 
 * 2.2: circumstances 
 * 3: the user selects the value credible source instances for the values supported by the practical reasoning instance
 * 4: the user selects the value recognition instances for the values supported by the practical reasoning instance
 * 5: the user adds any additional information and submits or resets the consultation
 * the user can reset the consultation construction process at any step.*/
print "<div id='contentWrap'>";
print " <legend><h2>Add New Consultation</h2></legend>";	
if(!isset ($_SESSION['step'])){
	$_SESSION['step'] = 1;
}

switch ($_SESSION['step']){
		case 1: //pras
			//get a list of all PRAS in DB
			$dataPRAS = mysqli_query("SELECT practical_reasoning_as_id, practical_reasoning_as.circumstances, 
			practical_reasoning_as.consequences, practical_reasoning_as.values, action_name, agent_name
			 from practical_reasoning_as, action, agent
			where practical_reasoning_as.action = action.action_id 
			and agent.agent_id = action.agent") or trigger_error(mysqli_error());

			//PRINT THIS LIST TO THE USER WITH CHECK BOX BUTTONS TO SELECT FROM
			Print"<legend><h3>Practical Reasoning Scheme</h3></legend>";
			Print "<table border cellpadding=3 width=80%>";
			Print "<tr>";
			Print "<th>ID</th><th>Select</th><th colspan=2>Description</th>";	
			while($info = mysqli_fetch_array($dataPRAS)){
			Print "<tr>";
				$conjCircum = $info['circumstances'];
				$conjConsq = $info['consequences'];
				$conjValue = $info['values'];

				$valuesString = GetPRASComponentInfoString("value", "value_name", "value_occurrence", $conjValue);

				$conseqString = GetPRASComponentInfoString("proposition", "proposition_string", "prop_occurrence",$conjConsq);

				$circumString = GetPRASComponentInfoString("proposition", "proposition_string", "prop_occurrence", $conjCircum);

				Print "<td width = 1% rowspan =4>".$info['practical_reasoning_as_id']."</td> ";
						Print "<td width = 1% rowspan =4>";
						print "<form action='consultationInst.php' method='POST'>";
						print "<input type='submit' value='Select'/>";
						print "<input type='hidden' name='selectPRAS' value=".$info['practical_reasoning_as_id']." />";
						print "</form> ";
						print "</td> ";
						Print "<td width = 5%>Circumstances</td> ";
						Print "<td width = 20%>".$circumString."</td> ";
						Print "</tr>";
						Print "<tr>";
						Print "<td width = 5%>Action</td> ";
						Print "<td width = 10%>".$info['action_name']." - Performed By: ".$info['agent_name']."</td> ";
						Print "</tr>";
						Print "<tr>";
						Print "<td width = 5%>Consequences</td> ";
						Print "<td width = 20%>".$conseqString."</td> ";
						Print "</tr>";
						Print "<tr>";
						Print "<td width = 5%>Values</td> ";
						Print "<td width = 20%>".$valuesString."</td> ";
						Print "</tr>";

			}
			break;
		
		case 2: //csas 	circumstances
			/*the second step is */
			if(isset($_SESSION['newConsultation']) && sizeof($_SESSION['newConsultation'])>0){
				$selectedPRAS = $_SESSION['newConsultation'][0][0];
				$dataCSAS = GetCSASforPRASCircumstances($selectedPRAS); 
				$avaliableCSAS = mysqli_num_rows ($dataCSAS);
				print "<form action='consultationInst.php' method='POST'>";
				print "<input type='submit' value='submit schemes'/>";
				print "<input type='hidden' name='selectCircumCSAS' value=".$avaliableCSAS." />"; //size of data
				
				/* AZW - Nov 14 corrected a typo here.  Was:
					Print"<legend><h3>Credible Source Schemes - Circumstances</h3></legend>";
				now as below.*/
				
				Print"<legend><h3>Credible Source Schemes - Circumstances</h3></legend>";
			
				Print "<table border cellpadding=3 width=80%>";
				Print "<tr>";
				Print "<th>ID</th><th>Select</th><th colspan=2>Description</th>";
				$counter=1;
				while($info = mysqli_fetch_array($dataCSAS)){
					Print "<tr>";
					Print "<td width = 1% rowspan =4>".$info['credible_source_as_id']."</td> ";
					Print "<td width = 1% rowspan =4>";
			
					//print "<input type='submit' value='Select'/>";
					print "<input type=checkbox name=CSAScons_".$counter." value=".$info['credible_source_as_id']." id=".$info['credible_source_as_id']." />";
					$counter++;
					print "</td> ";
					Print "<td width = 5%>Source</td> ";
					Print "<td width = 20%>".$info['source_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Domain</td> ";
					Print "<td width = 10%>".$info['domain_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Source Proposition</td> ";
					Print "<td width = 20%>".$info['source_p']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Domain Proposition</td> ";
					Print "<td width = 20%>".$info['domain_p']."</td> ";
					Print "</tr>";
				}
				print "</table> ";
				print"</form>";

			}
			else{
				unset($_SESSION['step']);
			}			
			break;
		
		case 3: //csas  conseq
			if(isset($_SESSION['newConsultation']) && sizeof($_SESSION['newConsultation'])>0){

				$selectedPRAS = $_SESSION['newConsultation'][0][0];

				$dataConsCSAS = GetCSASforPRASConsequences($selectedPRAS);

				$avaliableConCSAS = mysqli_num_rows($dataConsCSAS);
				print "<form action='consultationInst.php' method='POST'>";
				print "<input type='submit' value='submit schemes'/>";
				print "<input type='hidden' name='selectConsCSAS' value=".$avaliableConCSAS." />"; //size of data
					
				Print"<legend><h3>Credible Source Schemes - Consequences</h3></legend>";
			
				Print "<table border cellpadding=3 width=80%>";
				Print "<tr>";
				Print "<th>ID</th><th>Select</th><th colspan=2>Description</th>";
				$counter=1;
				while($info = mysqli_fetch_array($dataConsCSAS)){
					Print "<tr>";
					Print "<td width = 1% rowspan =4>".$info['credible_source_as_id']."</td> ";
					Print "<td width = 1% rowspan =4>";
			
					//print "<input type='submit' value='Select'/>";
					print "<input type=checkbox name=CSAScons_".$counter." value=".$info['credible_source_as_id']." id=".$info['credible_source_as_id']." />";
					$counter++;
					print "</td> ";
					Print "<td width = 5%>Source</td> ";
					Print "<td width = 20%>".$info['source_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Domain</td> ";
					Print "<td width = 10%>".$info['domain_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Source Proposition</td> ";
					Print "<td width = 20%>".$info['source_p']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Domain Proposition</td> ";
					Print "<td width = 20%>".$info['domain_p']."</td> ";
					Print "</tr>";
				}
				print "</table> ";
				print"</form>";

			}

			else{

				unset($_SESSION['step']);

			}
			break;
		
		case 4: //vcsas
			if(isset($_SESSION['newConsultation']) && sizeof($_SESSION['newConsultation'])>0){
				$selectedPRAS = $_SESSION['newConsultation'][0][0];
				$dataVCSAS = mysqli_query("select value_credible_source_as_id, source_name, domain_name, 
										 Concat(value_name, ' - ' , default_choice) as value_choice, action_name
												from value_credible_source_as, 
												     domain_source, domain, source,
												     action, 
												     value_occurrence_default_choice, value_occurrence, value
												where value_credible_source_as.domain_source=domain_source_id
												and value_credible_source_as.action = action_id
												and domain_source.source = source_id
												and domain_source.domain=domain_id
												and value_credible_source_as.value_occurrence_default_choice = value_occurrence_default_choice_id
												and value_occurrence_default_choice.value_occurrence = value_occurrence_id
												and value_occurrence.value = value_id
												and value.value_id in 
												(select value_id 
													from practical_reasoning_as, value, value_occurrence, conjunction
													where value_occurrence.value = value.value_id
													and value_occurrence.conjunction = conjunction_id
													and practical_reasoning_as.values = conjunction_id
													and practical_reasoning_as_id = $selectedPRAS)
												and action_id in 
												(select action 
													from practical_reasoning_as
                          							where practical_reasoning_as_id = $selectedPRAS)")
													or trigger_error(mysqli_error());
					
				$avaliableVCSAS = mysqli_num_rows($dataVCSAS);
				print "<form action='consultationInst.php' method='POST'>";
				print "<input type='submit' value='submit schemes'/>";
				print "<input type='hidden' name='selectVCSAS' value=".$avaliableVCSAS."/>"; //size of data
					
				Print"<legend><h3>Value Credible Source Schemes</h3></legend>";
				Print "<table border cellpadding=3 width=80%>";
				Print "<tr>";
				Print "<th>ID</th><th>Select</th><th colspan=2>Description</th>";
				
				$counter=1;
				while($info = mysqli_fetch_array($dataVCSAS)){
					Print "<tr>";
					Print "<td width = 1% rowspan =4>".$info['value_credible_source_as_id']."</td> ";
					Print "<td width = 1% rowspan =4>";
					print "<input type=checkbox name=vcsas_".$counter." value=".$info['value_credible_source_as_id']." id=".$info['value_credible_source_as_id']." />";
					$counter++;
					print "</td> ";
					Print "<td width = 5%>Source</td> ";
					Print "<td width = 20%>".$info['source_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Domain</td> ";
					Print "<td width = 10%>".$info['domain_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Action</td> ";
					Print "<td width = 10%>".$info['action_name']."</td> ";
					Print "</tr>";					
					Print "<tr>";
					Print "<td width = 5%>Value</td> ";
					Print "<td width = 10%>".$info['value_choice']."</td> ";
					Print "</tr>";
				}
				print "</table> ";
				print "</form> ";
			}
			else{
				unset($_SESSION['step']);
			}
		break;

		case 5: //vras
			if(isset($_SESSION['newConsultation']) && sizeof($_SESSION['newConsultation'])>0){
				$selectedPRAS = $_SESSION['newConsultation'][0][0];
				$dataVRAS = mysqli_query("Select value_recognition_as_id, source_name, value_name
				from value_recognition_as, source, value
								where value_recognition_as.value = value.value_id
								and value_recognition_as.source = source.source_id
							 and value.value_id in 
						(select value_id 
						from practical_reasoning_as, value, value_occurrence, conjunction
						where value_occurrence.value = value.value_id
						and value_occurrence.conjunction = conjunction_id
						and practical_reasoning_as.values = conjunction_id
						and practical_reasoning_as_id = $selectedPRAS)")
						or trigger_error(mysqli_error());
					
				$avaliableVRAS = mysqli_num_rows ($dataVRAS);
				print "<form action='consultationInst.php' method='POST'>";
				print "<input type='submit' value='submit schemes'/>";
				print "<input type='hidden' name='selectVRAS' value=".$avaliableVRAS." />"; //size of data
					
				Print"<legend><h3>Value Recognition Schemes</h3></legend>";
				Print "<table border cellpadding=3 width=80%>";
				Print "<tr>";
				Print "<th>ID</th><th>Select</th><th colspan=2>Description</th>";
				$counter=1;
				while($info = mysqli_fetch_array($dataVRAS)){
					Print "<tr>";
					Print "<td width = 1% rowspan =2>".$info['value_recognition_as_id']."</td> ";
					Print "<td width = 1% rowspan =2>";
					print "<input type=checkbox name=vras_".$counter." value=".$info['value_recognition_as_id']." id=".$info['value_recognition_as_id']." />";
					$counter++;
					print "</td> ";
					Print "<td width = 5%>Source</td> ";
					Print "<td width = 20%>".$info['source_name']."</td> ";
					Print "</tr>";
					Print "<tr>";
					Print "<td width = 5%>Value</td> ";
					Print "<td width = 10%>".$info['value_name']."</td> ";
					Print "</tr>";
				}
				print "</form> ";
				print "</table> ";
			}
			else{
				unset($_SESSION['step']);
			}
			break;

		case 6: //Additional Information 
			if(isset($_SESSION['newConsultation']) && sizeof($_SESSION['newConsultation'])>0){
				Print"<legend><h3>Additional Information </h3></legend>";
				print "<form action='consultationInst.php' method='POST'>";
				print "<input type='text' name='additionalInfoText' />";
				print "<input type='submit' value='submit schemes'/>";
				print "<input type='hidden' name='additionalInfo' value='additionalInfo'/>"; 
				
				print "</form> ";
			}
			else{
				unset($_SESSION['step']);
			}

			break;
		default:
			break;
}


print "</div>";
print "</body>";
print "</html>";

?>