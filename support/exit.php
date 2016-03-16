<?php
	include('support/header.php');
?>

<div class="survey toInit">
	<div class="intro"><p>
	<b>Thank you very much.</b>
	</p></div>
	<div class="question"><p>
	<b>Your response submited successfully!</b>
	</p>
	</div>
	
	<form id = 'finish_sessions' method='post'>
		<input type ='hidden' name= 'finish_sessions' value='FinishSurvey'>
	</form>
	<button id="backhome" class="next">Back home</button>
</div>