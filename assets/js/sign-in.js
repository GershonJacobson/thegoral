$(document).ready(function () {
	$("#btnBuyNow").click(function () {
		$("#checkoutModal").modal("show");
	});
	
	$(".cancel").click(function () {
		$("#checkoutModal").modal("hide");
	});
	
	$(".fa-circle-xmark").click(function () {
		$("#checkoutModal").modal("hide");
	});
	
	function handleEmail(e) {
		var email = $(this).val();
		
		if(e.which == 13) {
			if(email == "") {}
			else {
				$(this).bind('keypress', handleEmail);
				
				$("#password").focus();
			}
		}
	}
	$("#email").keypress(handleEmail);
	
	function handlePassword(e) {
		var password = $(this).val();
		
		if(e.which == 13) {
			if(password == "") {}
			else {
				$("#btnLogin").trigger("click");
			}
		}
	}
	$("#password").keypress(handlePassword);
  
	$("#btnLogin").click(function () {
		var rememberMe = $('#remember-me').is(':checked') == true ? 1 : 0;
		var email = $("#email").val();
		var password = $("#password").val();
	
		if(email == "" || password == "") {
			if(email =="") {
				$("#email").focus();
			}
			else if(password == "") {
				$("#password").focus();
			}
		}
		else {
			$("#btnLogin").text("Logging in").prop('disabled', true);
			
			$.ajax({
				url: "functions/login",
				type: "POST",
				data: {
					email: email,
					password: password,
					rememberMe: rememberMe
				},
				dataType: "JSON",
				success: function (jsonStr) {
					if(jsonStr.result == "emailNotFound") {
						Swal.fire({
							text: "The account email is not found!",
							icon: "error",
							confirmButtonText: "OK",
						});
						
						$("#btnLogin").text("Log in").prop('disabled', false);
					}
					else if(jsonStr.result == "notActive") {
						Swal.fire({
							text: "The account is not yet activate!",
							icon: "error",
							confirmButtonText: "OK",
						});
						
						$("#btnLogin").text("Log in").prop('disabled', false);
					}
					else if(jsonStr.result == "wrongPassword") {
						Swal.fire({
							text: "The account password is wrong!",
							icon: "error",
							confirmButtonText: "OK",
						});
						
						$("#btnLogin").text("Log in").prop('disabled', false);
					}
					else {
						window.location.href = "/user-dashboard";
					}
				}
			});
		}
	});
});
