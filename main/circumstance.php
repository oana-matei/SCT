<?php
// This module presents and records responses for the circumstances.

// These are just imports of supporting files for header that refers to the toolbox
// and the DB connection.

	include('support/header.php');
	include('support/database_connect.php');
?>

<?php 

// Prepares for sql queries of the propositions of the circumstances.
	$sql = "SELECT proposition_id, proposition_string 
					FROM proposition, practical_reasoning_as, prop_occurrence, consultation
						WHERE practical_reasoning_as.circumstances = prop_occurrence.conjunction 
							AND proposition.proposition_id = prop_occurrence.proposition
							AND consultation.practical_reasoning_as=practical_reasoning_as_id
							AND consultation_id='$consultationID'"; 
	$retrieveCirc = mysqli_query($conn, $sql ) or trigger_error(mysqli_error($conn));
		
?>

<?php 
// This sets up session variables (to record results before writing them to the DB),
// the array for the presentation of the circumstance propositions is set up, and presents 
// the propositions in the arrays.

	//if user agree the the circumstance, which presume that he/she agrees the credible source proposition
	global $whole_circumstance;
	
	//array contains everything related to one circumstance 
	$whole_circumstance = array();
	
	//for each circumstance proposition, present the particular information -
	//id and string.
	while($info_circumstance =  mysqli_fetch_array($retrieveCirc)){
		$sub_circumstance = array();
		//related information about each circumstance 
		//0 => id, 1 => string, 2 => domain_source, 3 => source_proposition, 4 => domain_proposition
		
		// See the function create_table_row below
		// The idea is that we need to gather all of the information from the DB
		// that bears on the proposition, including the id, the string, and all the
		// information about each proposition associated with Credible Source.
		// At this point, it is done with numbers, though this isn't very informative, but it works.
		// A potential problem is that as we add further digression schemes, the order of the
		// information and the numbers used to index it becomes less clear, e.g. adding
		// Value Recognition or other schemes.
		
		// The summary.php and index pages have the numbers and what they are tied to.
		// TODO - check index, value, and summary page.
		
		// The DB represents proposition and credible source as separate tables
		// linked with respect to a proposition.  So, to present data and record responses
		// all the linked information has to be gathered together.
		
		array_push($sub_circumstance, $info_circumstance['proposition_id']);//item 0 id
		array_push($sub_circumstance, $info_circumstance['proposition_string']);
	
		// Retrieve the Credible Source information for each proposition.
	
		
		$retrieveCS =  mysqli_query($conn, "SELECT domain_source,  source_proposition, domain_proposition, credible_source_as_id
									FROM credible_source_as,  consultation, credible_source_occurrence, conjunction, source_proposition, proposition
									WHERE  consultation.credible_source_as = conjunction.conjunction_id
									AND conjunction.conjunction_id= credible_source_occurrence.conjunction
									AND credible_source_occurrence.credible_source_as=credible_source_as_id
									AND credible_source_as.source_proposition = source_proposition_id
									AND source_proposition.proposition = proposition_id
									AND proposition_id='$info_circumstance[proposition_id]'
									AND consultation_id = '$consultationID'") or trigger_error(mysqli_error($conn));
		
		
		$info_CredibleSource = mysqli_fetch_array($retrieveCS);

		// Prepare information about domain source.  Get the domain source id,
		// then extract the relevant information from the domain source table
		// relative to that id.
		$domain_source_id = $info_CredibleSource['domain_source'];
		
			$domain_source_info = mysqli_query($conn, "SELECT domain.domain_name, source.source_name 
										FROM domain, source, domain_source
											WHERE domain_source.domain_source_id = '$domain_source_id' 
												AND source.source_id = domain_source.source
												AND domain.domain_id = domain_source.domain") or trigger_error(mysqli_error($conn)); 
			
			// Gets the result back from the previous query as an array.
			$domain_source = mysqli_fetch_array($domain_source_info);
			
			// Presents the string information.
			$statement_domain_source = $domain_source['source_name']." 
								is an expert in ".$domain_source['domain_name'];
			
			array_push($sub_circumstance, $statement_domain_source);

		// Prepare information about source proposition.  Same as with domain source.
		$source_proposition_id = $info_CredibleSource['source_proposition'];
		
			$source_proposition_info = mysqli_query($conn, "SELECT proposition.proposition_string, source.source_name 
												FROM proposition, source, source_proposition
													WHERE source_proposition.source_proposition_id = '$source_proposition_id' 
														AND source.source_id = source_proposition.source
														AND proposition.proposition_id = source_proposition.proposition") or trigger_error(mysqli_error($conn));
		
			$source_proposition = mysqli_fetch_array($source_proposition_info);

			$statement_source_proposition = $source_proposition['source_name']." stated: ".'"'.$source_proposition['proposition_string'].'"';
			
			array_push($sub_circumstance, $statement_source_proposition);
		
		// Prepare information about domain proposition.  Same as with domain source.
		$domain_proposition_id = $info_CredibleSource['domain_proposition'];
		
			$domain_proposition_info = mysqli_query($conn, "SELECT proposition.proposition_string, domain.domain_name 
													FROM proposition, domain, domain_proposition
															WHERE domain_proposition.domain_proposition_id = '$domain_proposition_id' 
																AND domain.domain_id = domain_proposition.domain
																AND proposition.proposition_id = domain_proposition.proposition") or trigger_error(mysqli_error($conn)); 
		
			$domain_proposition = mysqli_fetch_array($domain_proposition_info);

			$statement_domain_proposition = '"'.$domain_proposition['proposition_string'].'"'." is about ".$domain_proposition['domain_name'];
		
			array_push($sub_circumstance, $statement_domain_proposition);
			
			// For each proposition, we have an associated Credible Source scheme instance.
			// This information gets stored in an array that is represented here as a subarray
			// - the array within the array of the proposition information.  Here, it prepares
			// the information to be used later.
		
			array_push($sub_circumstance, $info_CredibleSource['credible_source_as_id']);
		
		// Each of the subcircumstances is added to a whole array for all the circumstances.
		array_push($whole_circumstance, $sub_circumstance);
	}
	
	// This registers the session
	$_SESSION['circumstance'] = $whole_circumstance;
	
?>

<?php

// HTML for presentation of the user response tables and interaction.
// The structure here is like folding and unfolding in multi-threaded discussion lists
// where the unfolded structure contains the folded information, but not visually
// presented.

// NOTE:  It may be better to change discussion of this functionality to folded
// and unfolded, since that is familiar and what it actually does.

function create_table_row ($sub_circumstance){
	//0 => id, 1 => string, 2 => domain_source, 3 => source_proposition, 4 => domain_proposition
	//the main consultation on circumstance
	
	// Various style elements, which can only be changed in the CSS.
	// What effects the presentation and interaction is found in the code
	// for these elements.
	
	// This presents a proposition
	Print "<div class='answer'>";
		
		// The number here, 1, is related to the proposition string.  And generally
		// this refers to the discussion above about how to track the arrays
		// and subarrays.  Other numbers refer to other parts of the array.
		Print "<div class='question'>".$sub_circumstance[1]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";

			// The number in the name property is to distinguish different radio buttons
			// and to have references to the users' responses that are submitted with the form.
			// There is a system to the numbers.  TODO:  Chenxi has this in mind and implemented 
			// it, but we need the documentation about how that works.
			
			Print "<li><input type='radio' class='hideCS' name='$sub_circumstance[0]' value='agree' checked>
				<label for='radio1' >Agree</label></li>
			   <li><input type='radio' class='showCS' name='$sub_circumstance[0]' value='disagree'>
				<label for='radio1' >Disgree</label></li>
			   <li><input type='radio' class='showCS' name='$sub_circumstance[0]' value='n/a'>
				<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
	Print "</div>";	

	// Similar to the above, but they present each element of the credible source scheme.
	// There is no space between a propositions - the visual presentation has implicit
	// the information structure.
	
	//the justification about domain source
	Print "<div class='answer'>";

		Print "<div class='marker'></div>";
		
		Print "<div class='question'>".$sub_circumstance[2]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='domain_source_$sub_circumstance[0]' value='agree' checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_source_$sub_circumstance[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_source_$sub_circumstance[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";

		//the justification about source proposition
		Print "<div class='question'>".$sub_circumstance[3]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='source_proposition_$sub_circumstance[0]' value='agree' checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='source_proposition_$sub_circumstance[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='source_proposition_$sub_circumstance[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
		
		//the justification about domain proposition
		Print "<div class='question'>".$sub_circumstance[4]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='domain_proposition_$sub_circumstance[0]' value='agree' checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_proposition_$sub_circumstance[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_proposition_$sub_circumstance[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
	Print "</div>";
}
?>

<html>
<head>
<!-- 
The jQuery.  Essentially, presents a multi-threaded discussion list type
page that folds and unfolds each page.  In this example, the circumstances.
-->

<script type="text/javascript">  
        $(document).ready(function(){
            $("div.answer:even").addClass("circ");
            $("div.answer:not(.circ)").hide();
            
            $(".list-no-bullets").find(".hideCS").click(function(){
            	$(this).closest("div.answer").next().hide(500);
            });

            $(".list-no-bullets").find(".showCS").click(function(){
            	$(this).closest("div.answer").next().show(500);
            });
            
        });
</script>
</head>
<body>
<div class="breadcrumb">
	<ul>
		<li>Introduction</li>
		<li><b>Circumstances (+)</b></li>
		<li>Consequences (+)</li>
		<li>Values (+)</li>
		<li>Summary</li>
	</ul>
</div>

<div class="survey toInit">
	<div class="intro">
	<p>In response to the question about staff training in NHS cancer centres:</p>
	</div>
	
	<div class="question">
	<p>Q9: Should the Scottish Government provide additional funding for staff training, in order to improve quality of care and meet waiting time targets?</p>
	</div>

	<div class="introductions">
	<p>An action was proposed:</p>
	
	<div class="question">
	<p>The Scottish Government should provide additional funding for staff training</p>
	</div>

	<div class="introductions">
	<p>Your opinion on this proposal may depend on what you believe to be the current circumstances. For each statement below, indicate whether you agree with it, disagree with it, or find it not applicable.</p>
	<p>The selection presented shows what the policy maker believes.  If you think that the circumstances are not as stated, click on another choice. You will then see a "digression" to the policy maker's justification for that circumstance. For the digression, defaults are also given, which represent the policy maker's position. You can change these defaults if you disagree with the justification.</p>
	</div>

	<div class="introductions">
	<p>Below the statements there is a text box where you can enter your any additional facts that you think need to be considered when answering this question.</p>
	</div>

	<div class="introductions">
	<p>Once you are done with this page, go to the next page to consider the consequences associated with the action.</p>
	</div>

	<!-- Posts to the session variables -->

	<form id='update-circ' method='post'>

	<?php 
		
		// Call the PhP functions and display the tables.
		foreach($whole_circumstance as $sub_circumstance){
			create_table_row($sub_circumstance);
		}
	?>
		<div class="intro">
			<p>If you think any other circumstances need to be considered, please enter these in the text area:</p>
		</div>
		<textarea name="external-circ" cols="40" rows="3"></textarea>
		
		<input type ='hidden' name= 'update-circ' value='submitform'>
	</form>
	
	<p>
	<div class="intro">
		<p>On the next page, you will be asked for your views on the consequences of the proposed action.</p>
	</div>
	
	<button id="toconsequence" class="next">Next</button>
</div>
</body>
</html>
