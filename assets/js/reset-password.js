$(document).ready(function () {
	$("#password, #confirmPassword").bind("keyup change", function() {
		var password = $("#password").val();
		var confirmPassword = $("#confirmPassword").val();
		
		if(password != "") {
			if(password.length > 6) {
				$(".minPassword").hide();
			}
			else {
				$(".minPassword").fadeIn();
			}
		}
		
		if(password.length > 6) {
			if(confirmPassword != "") {
				if(password == confirmPassword) {
					$(".mustSamePassword").hide();
				}
				else {
					$(".mustSamePassword").fadeIn();
				}
			}
		}
	});
	
	$("#btnResetPassword").click(function () {
		var email = $("#email").val();
		var password = $("#password").val();
		var confirmPassword = $("#confirmPassword").val();
		
		if(email == "" || password == "" || confirmPassword == "") {
			if(email == "") {
				$("#email").focus();
			}
			else if(password == "") {
				$("#password").focus();
			}
			else if(confirmPassword == "") {
				$("#confirmPassword").focus();
			}
		}
		else {
			if(password.length > 6 && password == confirmPassword) {
				$("#btnResetPassword").text("Resetting").prop('disabled', true);
				
				$.ajax({
					url: "functions/reset",
					type: "POST",
					data: {
						email: email,
						password: password
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if(jsonStr.result == "OK") {
							Swal.fire({
								icon: 'success',
								title: 'Your password has been updated',
								text: "Now you can log in",
								showConfirmButton: true,
								allowOutsideClick: false
							}).then((result) => {
							  if (result.isConfirmed) {
								window.location.href = "/";
							  }
							});;
						}
						else {
							$("#password, #confirmPassword").val("");
							
							Swal.fire({
								text: "Email address is not found!",
								icon: "error",
								confirmButtonText: "OK",
							});
							
							$("#btnResetPassword").text("Reset Password").prop('disabled', false);
						}
					}
				});
			}
			else {
				if(password.length < 7) {
					$("#password").focus();
				}
				else if(password != confirmPassword) {
					$("#password").focus();
				}
			}
		}
  });
});
