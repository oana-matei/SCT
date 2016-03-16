<?php
	include('support/header.php');
	include('support/database_connect.php');
?>

<?php 
	$retrieveCirc = mysqli_query($conn, "SELECT proposition_id, proposition_string 
					FROM proposition, practical_reasoning_as, prop_occurrence, consultation
						WHERE practical_reasoning_as.consequences = prop_occurrence.conjunction 
							AND proposition.proposition_id = prop_occurrence.proposition
							AND consultation.practical_reasoning_as=practical_reasoning_as_id
							AND consultation_id='$consultationID'") or trigger_error(mysqli_error($conn)); 
		
?>

<?php 
	//if user agree the the consequence, which presume that he/she agrees the credible source proposition
	
	global $whole_consequence;
	
	$whole_consequence = array();
	//array contains everything related to one 
	
	while($info_consequence =  mysqli_fetch_array($retrieveCirc)){
		$sub_consequence = array();
		//related information about each consequence 
		//0 => id, 1 => string, 2 => domain_source, 3 => source_proposition, 4 => domain_proposition
		
		array_push($sub_consequence, $info_consequence['proposition_id']);//item 0 id
		array_push($sub_consequence, $info_consequence['proposition_string']);

		$retrieveCS =  mysqli_query($conn, "SELECT domain_source,  source_proposition, domain_proposition, credible_source_as_id
									FROM credible_source_as,  consultation, credible_source_occurrence, conjunction, source_proposition, proposition
									WHERE  consultation.credible_source_as = conjunction.conjunction_id
									AND conjunction.conjunction_id= credible_source_occurrence.conjunction
									AND credible_source_occurrence.credible_source_as=credible_source_as_id
									AND credible_source_as.source_proposition = source_proposition_id
									AND source_proposition.proposition = proposition_id
									AND proposition_id=$info_consequence[proposition_id]
									AND consultation_id = $consultationID") or trigger_error(mysqli_error($conn));
		
		
		$info_CredibleSource = mysqli_fetch_array($retrieveCS);

		//information about domain source
		$domain_source_id = $info_CredibleSource['domain_source'];

			$domain_source_info = mysqli_query($conn, "SELECT domain.domain_name, source.source_name 
										FROM domain, source, domain_source
											WHERE domain_source.domain_source_id = '$domain_source_id' 
												AND source.source_id = domain_source.source
												AND domain.domain_id = domain_source.domain") or trigger_error(mysqli_error($conn));
			
			$domain_source = mysqli_fetch_array($domain_source_info);
			
			
			
			$statement_domain_source = $domain_source['source_name']." 
								is an expert in ".$domain_source['domain_name'];
			
			array_push($sub_consequence, $statement_domain_source);

		//information about source proposition	
		$source_proposition_id = $info_CredibleSource['source_proposition'];
		
			$source_proposition_info = mysqli_query($conn, "SELECT proposition.proposition_string, source.source_name 
												FROM proposition, source, source_proposition
													WHERE source_proposition.source_proposition_id = '$source_proposition_id' 
														AND source.source_id = source_proposition.source
														AND proposition.proposition_id = source_proposition.proposition") or trigger_error(mysqli_error($conn));
		
			$source_proposition = mysqli_fetch_array($source_proposition_info);

			$statement_source_proposition = $source_proposition['source_name']." stated: ".'"'.$source_proposition['proposition_string'].'"';
			
			array_push($sub_consequence, $statement_source_proposition);
		
		//information about domain proposition	
		$domain_proposition_id = $info_CredibleSource['domain_proposition'];
		
			$domain_proposition_info = mysqli_query($conn, "SELECT proposition.proposition_string, domain.domain_name 
													FROM proposition, domain, domain_proposition
															WHERE domain_proposition.domain_proposition_id = '$domain_proposition_id' 
																AND domain.domain_id = domain_proposition.domain
																AND proposition.proposition_id = domain_proposition.proposition") or trigger_error(mysqli_error($conn));
		
			$domain_proposition = mysqli_fetch_array($domain_proposition_info);

			$statement_domain_proposition = '"'.$domain_proposition['proposition_string'].'"'." is about ".$domain_proposition['domain_name'];
			
			
			array_push($sub_consequence, $statement_domain_proposition);
			
			array_push($sub_consequence, $info_CredibleSource['credible_source_as_id']);
		
		array_push($whole_consequence, $sub_consequence);
	}
	
	$_SESSION['consequence'] = $whole_consequence;//register the session.
	
?>

<?php 
function create_table_row ($sub_consequence){
	//0 => id, 1 => string, 2 => domain_source, 3 => source_proposition, 4 => domain_proposition
	//the main consultation on consequence
		
	Print "<div class='answer'>";
	
		Print "<div class='question'>".$sub_consequence[1]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='hideCS' name='$sub_consequence[0]' value='agree' Checked>
				<label for='radio1' >Agree</label></li>
			   <li><input type='radio' class='showCS' name='$sub_consequence[0]' value='disagree'>
				<label for='radio1' >Disgree</label></li>
			   <li><input type='radio' class='showCS' name='$sub_consequence[0]' value='n/a'>
				<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
	Print "</div>";	
	
	//the justification about domain source
	Print "<div class='answer'>";
	
		Print "<div class='marker'></div>";
	
		Print "<div class='question'>".$sub_consequence[2]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='domain_source_$sub_consequence[0]' value='agree'Checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_source_$sub_consequence[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_source_$sub_consequence[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";

		//the justification about source proposition
		Print "<div class='question'>".$sub_consequence[3]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='source_proposition_$sub_consequence[0]' value='agree' Checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='source_proposition_$sub_consequence[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='source_proposition_$sub_consequence[0]' value='n/a' >
					<label for='radio1' >Not Applicable</label></li>";
	
			Print "</ul></fieldset>";
		Print "</div>";
		
		//the justification about domain proposition
		Print "<div class='question'>".$sub_consequence[4]."</div> ";
	
		Print "<div class='tool'>";
	
			Print "<fieldset><ul class='list-no-bullets'>";
	
			Print "<li><input type='radio' class='radiobutton' name='domain_proposition_$sub_consequence[0]' value='agree'Checked>
					<label for='radio1' >Agree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_proposition_$sub_consequence[0]' value='disagree'>
					<label for='radio1' >Disgree</label></li>
				   <li><input type='radio' class='radiobutton' name='domain_proposition_$sub_consequence[0]' value='n/a' >
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
            $("div.answer:even").addClass("cons");
            $("div.answer:not(.cons)").hide();
            
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
		<li>Circumstances (+)</li>
		<li><b>Consequences (+)</b></li>
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
	<p>Your opinion on this proposal may depend on the consequences of the action. For each statement below, indicate whether you agree or, disagree that it will result from the action, or that the action will not affect it.</p>
	<p>There are defaults that represent what the policy maker believes will result from the action. If you think that the consequences are not as stated, click on another choice. You will then see a digression giving the policy maker's justification for that consequence. For the digression, defaults are also given, which justify what the policy maker believes. You can accept or reject this justification.</p>
	</div>

	<div class="introductions">
	<p>Below the statements, there is also a text box where you can enter any additional relevant consequences that you think will result from the action.</p>
	</div>

	<div class="introductions">
	<p>Once you are done with this page, go to the next page to consider values associated with the action.</p>
	</div>



	<form id='update-cons' method='post'>

	<?php 
	
	
		foreach($whole_consequence as $sub_consequence){
			create_table_row($sub_consequence);
		}
	?>
		<div class="intro">
			<p>If there any additional consequences that you think will result from the action please enter these in the text area:</p>
		</div>
		<textarea name="external-cons" cols="40" rows="3"></textarea>
		<input type ='hidden' name= 'update-cons' value='submitform'>
	</form>
	
	<p>
	<div class="intro">
		<p>On the next page, you will be asked for your views on the values affected by this proposal.</p>
	</div>
	
	<button id="tovalue" class="next">Next</button>
</div>
</body>
</html>






