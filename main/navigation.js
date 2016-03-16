		
$(document).ready(function(){
	$("#toproposal").click(function(){
		//window.location = "../copyrightScreens/copyrightProposalv2.php";
		$("#user_name").attr("action","index.php?submitid=proposal");
		$("#user_name").submit();
	});
	
	$("#tocircumstance").click(function(){
		//window.location = "index.php?submitid=circumstance";
		$("#user_name").attr("action","index.php?submitid=circumstance");
		$("#user_name").submit();
	});
	
	$("#toconsequence").click(function(){
		$("#update-circ").attr("action","index.php?submitid=consequence");
		$("#external-circ").attr("action","index.php?submitid=consequence");
		$("#update-circ").submit();
		$("#external-circ").submit();
		}
	);

	$("#tovalue").click(function(){
		$("#update-cons").attr("action","index.php?submitid=value");
		$("#update-cons").submit();
		
		}
	);
	
	$("#tosummary").click(function(){
		$("#update-values").attr("action","index.php?submitid=summary");
		$("#update-values").submit();
		
		}
	);
	
	$("#submit").click(function(){
		$("#submit_sessions").attr("action","index.php?submitid=exit");
		$("#submit_sessions").submit();
		}
	);

	$("#submitCS").click(function(){
		$("#submit_sessions").attr();
		$("#update-csUserResp").submit();
		}
	);
	
	$("#backhome").click(function(){
		$("#finish_sessions").attr("action","index.php?submitid=default");
		$("#finish_sessions").submit();
		}
	);
	
	$("#back").click(function(){
		window.close();
		}
	);
	
});