$(document).ready(function () {
	function validateEmail(email) {
		var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
		return expr.test(email);
	};
	
	function getValidNumber(value) {
		value = $.trim(value).replace(/\D/g, '');
		if (value.substring(0, 1) == '1') {
			value = value.substring(1);
		}

		if (value.length == 10) {

			return value;
		}

		return false;
	}
	
	$("#email, #phone, #password, #confirmPassword").bind("keyup change", function() {
		var email = $("#email").val();
		var phone = $("#phone").val();
		var filter = /^\d*(?:\.\d{1,2})?$/;
		var password = $("#password").val();
		var confirmPassword = $("#confirmPassword").val();
		
		if(email != "") {
			if(validateEmail($("#email").val())) {
				$(".emailNotValid").hide();
			}
			else {
				$(".emailNotValid").fadeIn();
			}
		}
		else {
			$(".emailNotValid").hide();
		}
		
		if(filter.test(phone)) {
			$(".phoneNotValid").hide();
		}
		else {
			$(".phoneNotValid").fadeIn();
		}
		
		if(password != "") {
			if(password.length > 6) {
				$(".minPassword").hide();
			}
			else {
				$(".minPassword").fadeIn();
			}
		}
		
		if(password == confirmPassword) {
			$(".mustSamePassword").hide();
		}
		else {
			$(".mustSamePassword").fadeIn();
		}
	});
  
	$("#btnSignup").click(function () {
		var firstName = $("#firstName").val();
		var lastName = $("#lastName").val();
		var email = $("#email").val();
		var phone = $("#phone").val();
		var filter = /^\d*(?:\.\d{1,2})?$/;
		var password = $("#password").val();
		var confirmPassword = $("#confirmPassword").val();
		
		if(firstName == "" || lastName == "" || email == "" || phone == "" || password == "" || confirmPassword == "") {
			if(firstName == "") {
				$("#firstName").focus();
			}
			else if(lastName == "") {
				$("#lastName").focus();
			}
			else if(email == "") {
				$("#email").focus();
			}
			else if(phone == "") {
				$("#phone").focus();
			}
			else if(password == "") {
				$("#password").focus();
			}
			else if(confirmPassword == "") {
				$("#confirmPassword").focus();
			}
		}
		else {
			if(validateEmail($("#email").val()) && password.length > 6 && password == confirmPassword && filter.test(phone)) {
				if($('input[name="tos"]').is(':checked')) {
					$("#btnSignup").text("Signing you up").prop('disabled', true);
					
					$.ajax({
						url: "functions/register",
						type: "POST",
						data: {
							firstName: firstName,
							lastName: lastName,
							email: email,
							phone: phone,
							password: password
						},
						dataType: "JSON",
						success: function (jsonStr) {
							if(jsonStr.result == "OK") {
								Swal.fire({
									icon: 'success',
									title: 'Successfully created the account.',
									text: "Please check your email!",
									showConfirmButton: true,
									allowOutsideClick: false
								}).then((result) => {
								  if (result.isConfirmed) {
									window.location.href = "/";
								  }
								});
							}
							else {
								Swal.fire({
									text: "Email address has been registered!",
									icon: "error",
									confirmButtonText: "OK",
								});
								
								$("#btnSignup").text("Sign up").prop('disabled', false);
							}
						}
					});
				}
				else {
					alert("Please agree with our Term of Services and Privacy");
				}
			}
			else {
				if(!validateEmail($("#email").val())) {
					$("#email").focus();
				}
				else if(!filter.test(phone)) {
					$("#phone").focus();
				}
				else if(password.length < 7) {
					$("#password").focus();
				}
				else if(password != confirmPassword) {
					$("#password").focus();
				}
			}
		}
  });
});
