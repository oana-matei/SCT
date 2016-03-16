<?php
	include('support/header.php');
	include('support/database_connect.php');	
?>

<?php
			// For questions about values on the value screen.
			// Retrieve the value id, name, and default choice from the relevant tables.
			$retrieveValue = mysqli_query($conn, "SELECT value_id, value_name, default_choice, value_occurrence_id
											 FROM value_occurrence_default_choice, value_occurrence, value, 
                             					practical_reasoning_as, consultation
												WHERE practical_reasoning_as.values = value_occurrence.conjunction
                        						AND value_occurrence_id = value_occurrence_default_choice.value_occurrence                        
												AND consultation.practical_reasoning_as = practical_reasoning_as_id
												AND value.value_id = value_occurrence.value
												AND consultation_id =$consultationID") or trigger_error(mysqli_error($conn));
?>

<?php 
	//if user agree the the value, which presume that he/she agrees the credible source proposition
	global $whole_value;
	
	$whole_value = array();
	//array contains everything related to one 
	
	while($info_value =  mysqli_fetch_array($retrieveValue)){
		$sub_value = array();
		//related information about each value 
		//0 => id, 1 => name,  2 => default_choice 3 => domain_source, 4 => source_proposition, 5 => domain_proposition
		
		array_push($sub_value, $info_value['value_id']);//item 0 id
		array_push($sub_value, $info_value['value_name']);
		array_push($sub_value, $info_value['default_choice']);
		
		// For questions about justifications of values, using the credible source argumentation
		// scheme, we reuse the credible source screen here.  The propositions are 'cooked' to
		// relate to the value rather than being automatically constructed from source.
		// This sort of justification arises when a user changes from the default, meaning
		// that the 'system' presumes an argument for the default on the value that is
		// supported by an argument from credible source.
		
			
		$retrieveVCS =  mysqli_query($conn, "SELECT value_credible_source_as_id, domain_source,  action_name
									FROM value_credible_source_as, consultation, value_credible_source_occurrence, 
									conjunction, value_occurrence_default_choice, action
									WHERE consultation_id = $consultationID
									and value_occurrence_default_choice.value_occurrence = $info_value[value_id]
									and   consultation.value_credible_source_as = conjunction_id
									and   value_credible_source_occurrence.conjunction = conjunction_id
									and   value_credible_source_occurrence.value_credible_source_as = value_credible_source_as_id
									and   value_credible_source_as.value_occurrence_default_choice = value_occurrence_default_choice_id
									and   value_credible_source_as.action = action_id") or trigger_error(mysqli_error($conn));
		
		$info_ValueCredibleSource = mysqli_fetch_array($retrieveVCS);
		
		$value_credible_source_as_id = $info_ValueCredibleSource['value_credible_source_as_id'];
		
//**************************

		
		//information about domain source
		$domain_source_id = $info_ValueCredibleSource['domain_source'];

		$domain_source_info = mysqli_query($conn, "SELECT domain.domain_name, source.source_name 
										FROM domain, source, domain_source
											WHERE domain_source.domain_source_id = $domain_source_id
												AND source.source_id = domain_source.source
												AND domain.domain_id = domain_source.domain") or trigger_error(mysqli_error($conn));
			
		$domain_source = mysqli_fetch_array($domain_source_info);
			
		$statement_domain_source = $domain_source['source_name']." 
								is an expert in ".$domain_source['domain_name'];
	
			array_push($sub_value, $statement_domain_source);
			//**************************

			
		//information about source proposition	
			$statement_string = $info_ValueCredibleSource['action_name']." ".$info_value['default_choice']."s ".$info_value['value_name'];
			$statement_source_proposition = $domain_source['source_name']." stated: ".'"'.$statement_string.'"';		
			array_push($sub_value, $statement_source_proposition);
		//**************************

					
		//information about domain proposition	
			$statement_domain_proposition = '"'.$statement_string.'"'." is about ".$domain_source['domain_name'];
			array_push($sub_value, $statement_domain_proposition);
			array_push($sub_value, $value_credible_source_as_id);
			

//**************************

		// We have a separate scheme that is introduced when the user says
		// that the value is not worthwhile, so does not recognise the value.
		// The user then has to argue against the justification that the value
		// is worth recognising, which amounts to arguing against the authority
		// who proposed the value and its status.
		
		//MAYA: changed this query - it is now linked to the consultation instance. As we may have different Value Recognition schemes linked to the same value
		$retrieveRV =  mysqli_query($conn, "SELECT source, value_recognition_as_id
						FROM value_recognition_as, consultation, conjunction, value_recognition_occurrence 
							WHERE consultation.value_recognition_as = conjunction.conjunction_id
							AND conjunction.conjunction_id = value_recognition_occurrence.conjunction
							AND value_recognition_occurrence.value_recognition_as = value_recognition_as_id
							AND value_recognition_as.value = $info_value[value_id]
							AND consultation_id =$consultationID") or trigger_error(mysqli_error($conn));
		
		
		$info_recognise_value = mysqli_fetch_array($retrieveRV);
		
		array_push($sub_value, $info_recognise_value['value_recognition_as_id']);
		
			$source_info = mysqli_query($conn, "SELECT source_name FROM source
											WHERE source.source_id = '$info_recognise_value[source]'") or trigger_error(mysqli_error($conn));
			
			$source_name = mysqli_fetch_array($source_info);

			$statement_authority_recognition = $source_name['source_name']." is an authority that you recognise";
			
			array_push($sub_value, $statement_authority_recognition);
			
			$statement_endorses_value = $source_name['source_name']." endorses the value  ".'"'.$info_value['value_name'].'"';
			
			
			array_push($sub_value, $statement_endorses_value);
			
			
		array_push($whole_value, $sub_value);
	}
	
	$_SESSION['value'] = $whole_value
?>

<?php 
function create_table_row ($sub_value){
	//0 => id, 1 => name,  2 => default_choice 3 => domain_source, 4 => source_proposition, 5 => domain_proposition
	//6 => credible_source_id 7=> value_recognition_as_id
	//8 => statement_authority_recognition 9=> statement_endorses_value
	//the main consultation on consequence
	$agree_checked = "";
	$disagree_checked = "";
	$neutral_checked = "";
	
	
	$agree_hide = "showCS";
	$disagree_hide = "showCS";
	$neutral_hide = "showCS";
	
	switch($sub_value[2]){
		case "promote":
			$agree_checked = "Checked";
			
			$agree_hide = "hideCS";
			break;
		
		case "demote":
			$disagree_checked = "Checked";
			$disagree_hide = "hideCS";
			break;
		
		case "neutral":
			$neutral_checked = "Checked";
			$neutral_hide = "hideCS";
			break;
				
	}
//**********************************
	Print "<div id = 'value' class='answer'>";
	
		Print "<div  class='question'>".$sub_value[1]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
				Print "<li><input type='radio' class='$agree_hide' name='$sub_value[0]' value='promote' $agree_checked>
					<label for='radio1' >Promote</label></li>
				<li><input type='radio' class='$neutral_hide' name='$sub_value[0]' value='neutral' $neutral_checked>
					<label for='radio1' >Neutral</label></li>
				<li><input type='radio' class='$disagree_hide' name='$sub_value[0]'  value='demote' $disagree_checked>
					<label for='radio1' >Demote</label></li>
				<li><input type='radio' class='showRS' name='$sub_value[0]' value='NotWorthwhile'>
					<label for='radio1' >Not Worthwhile</label></li>";
			
			Print "</ul></fieldset>";
		Print "</div>";
	Print "</div>";	
//**********************************
	//the justification about domain source
	Print "<div id = 'value_cs' class='answer'>";

		Print "<div class='marker'></div>";
		
		Print "<div class='question'>".$sub_value[3]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='domain_source_$sub_value[0]' value='agree' Checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_source_$sub_value[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_source_$sub_value[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";

		//the justification about source proposition
		Print "<div class='question'>".$sub_value[4]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='source_proposition_$sub_value[0]' value='agree' Checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='source_proposition_$sub_value[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='source_proposition_$sub_value[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
		
		//the justification about domain proposition
		Print "<div class='question'>".$sub_value[5]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='domain_proposition_$sub_value[0]' value='agree'Checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_proposition_$sub_value[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_proposition_$sub_value[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
	Print "</div>";

//**********************************	
	Print "<div id = 'value_rs' class='answer'>";
		//8 => statement_authority_recognition 9=> statement_endorses_value

		//statement_authority_recognition

		Print "<div class='marker'></div>";
	
		Print "<div class='question'>".$sub_value[8]."</div> ";

	

			Print "<div class='tool'>";

	

				Print "<fieldset><ul class='list-no-bullets'>";

	

					Print "<li><input type='radio' class='radiobutton' name='authority_recognition_$sub_value[0]' value='agree' Checked>

						<label for='radio1' >Agree</label></li>

					<li><input type='radio' class='radiobutton' name='authority_recognition_$sub_value[0]' value='disagree'>

						<label for='radio1' >Disgree</label></li>

					<li><input type='radio' class='radiobutton' name='authority_recognition_$sub_value[0]' value='n/a' >

						<label for='radio1' >Not Applicable</label></li>";

	

				Print "</ul></fieldset>";

			Print "</div>";

	

		//statement_endorses_value

		Print "<div class='question'>".$sub_value[9]."</div> ";

	

			Print "<div class='tool'>";

	

				Print "<fieldset><ul class='list-no-bullets'>";

	

					Print "<li><input type='radio' class='radiobutton' name='endorses_value_$sub_value[0]' value='agree' Checked>

								<label for='radio1' >Agree</label></li>

							<li><input type='radio' class='radiobutton' name='endorses_value_$sub_value[0]' value='disagree'>

								<label for='radio1' >Disgree</label></li>

							<li><input type='radio' class='radiobutton' name='endorses_value_$sub_value[0]' value='n/a' >

								<label for='radio1' >Not Applicable</label></li>";

	

				Print "</ul></fieldset>";

		Print "</div>";

	Print "</div>";
}
?>
<html>
<head>
<script type="text/javascript">  
        $(document).ready(function(){
            $("div#value").addClass("value");
           	$("div.answer:not(#value)").hide();
            
            $(".list-no-bullets").find(".hideCS").click(function(){
            	$(this).closest("div.answer").next().hide(500);
            	$(this).closest("div.answer").next().next().hide(500);
            });

            $(".list-no-bullets").find(".showRS").click(function(){
            	$(this).closest("div.answer").next().hide(500);
            	$(this).closest("div.answer").next().next().show(500);
            });
            
            $(".list-no-bullets").find(".showCS").click(function(){
            	$(this).closest("div.answer").next().show(500);
            	$(this).closest("div.answer").next().next().hide(500);
            });
            
        });
</script> 
</head>
<div class="breadcrumb">
	<ul>
		<li>Introduction</li>
		<li>Circumstances (+)</li>
		<li>Consequences (+)</li>
		<li><b>Values (+)</b></li>
		<li>Summary</li>
	</ul>
</div>

<div class="survey toInit">
	<div class="intro">
	<p>In response to the following question derived from a response to the Better Cancer Care - A Discussion paper, issued by the Scottish Government:</p>
	</div>
	
	<div class="question">
	<p>Q9: Should the Scottish Government provide additional funding for staff training, in order to improve quality of care and meet waiting time targets?</p>
	</div>

	<div class="introductions">
	<p>An action was proposed:</p>
	
	<div class="question">
	<p>The Scottish Government should provide additional funding for staff training.</p>
	</div>
	
	<div class="introductions">
	<p>Your opinion on this proposal may depend on the social values affected by  the action. For each statement below, indicate whether you think the action promotes, demotes, or is neutral with respect to the social value. You can also indicate whether or not you think the social value should be considered in the context of this question.</p>
	<p>There are defaults that represent the position of the policy maker who has proposed the action; that is, the policy maker believes the action has the effect on the social value as indicated. If you think that the effect on the social value is not as indicated, click on another choice. You will then see a digression to the policy maker's justification for the belief. For the digression, defaults are also given, which represent the policy maker's position. You can accept or reject this justification.</p>
	</div>

	<div class="introductions">
	<p>Below the statements there is a text box where you can suggest other social values which you think should be considered in the context of this question.</p>
	</div>

	<div class="introductions">
	<p>Once you are done with this page, go to the next page where you get a summary of all your responses.</p>
	</div>

	<form id = 'update-values' method='post'>
	
	<?php 
		foreach($whole_value as $sub_value){
			create_table_row($sub_value);
		}
	?>
		<div class="intro">
			<p>If there are other social values you think should be considered, please enter these in the text area:</p>
		</div>
		<textarea name="external-value" cols="40" rows="3"></textarea>
		
		<input type='hidden' name = 'update-values' value='submitform'>
	</form>
	
	<p>
	<div class="intro">
		<p>On the next page, you will be taken to a summary page.</p>
	</div>
	
	<button id="tosummary" class="next">Next</button>
</div>
</body>
</html>
	
