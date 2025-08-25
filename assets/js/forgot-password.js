$(document).ready(function () {
	function handleEmail(e) {
		var email = $(this).val();
		
		if(e.which == 13) {
			if(email == "") {}
			else {
				$("#btnForgotPassword").trigger("click");
			}
		}
	}
	$("#email").keypress(handleEmail);
	
	$("#btnForgotPassword").click(function () {
		var email = $("#email").val();
		
		if(email == "") {
			if(email == "") {
				$("#email").focus();
			}
		}
		else {
			$("#btnForgotPassword").text("Submitting").prop('disabled', true);
			
			$.ajax({
				url: "functions/forgot",
				type: "POST",
				data: {
					email: email
				},
				dataType: "JSON",
				success: function (jsonStr) {
					if(jsonStr.result == "OK") {
						Swal.fire({
							icon: 'success',
							title: 'Account recovery email sent to ' + email,
							text: "If you don’t see this email in your inbox within 15 minutes, look for it in your junk mail folder. If you find it there, please mark it as “Not Junk”.",
							showConfirmButton: true,
							allowOutsideClick: false
						});
						
						$("#email").val("");
						$("#btnForgotPassword").text("Submit").prop('disabled', false);
					}
					else {
						Swal.fire({
							text: "Email address is not found!",
							icon: "error",
							confirmButtonText: "OK",
						});
						
						$("#btnForgotPassword").text("Submit").prop('disabled', false);
					}
				}
			});
		}
  });
});
