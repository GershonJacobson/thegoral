$(document).ready(function () {
	function validateEmail(email) {
		var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
		return expr.test(email);
	};
	
	$("#fullName, #phoneC, #emailC, #message").bind("keyup change", function() {
		var fullName = $("#fullName").val();
		var phone = $("#phoneC").val();
		var email = $("#emailC").val();
		var message = $("#message").val();
		var filter = /^\d*(?:\.\d{1,2})?$/;
		
		if(fullName != "") {
			$(".fullNameCantBeEmpty").hide();
		}
		else {
			$(".fullNameCantBeEmpty").fadeIn();
		}
		
		if(email != "") {
			if(validateEmail($("#emailC").val())) {
				$(".emailNotValid").hide();
			}
			else {
				$(".emailNotValid").fadeIn();
			}
		}
		else {
			$(".emailNotValid").hide();
		}
		
		if(phone != "") {
			if(filter.test(phone)) {
				$(".phoneNotValid").hide();
			}
			else {
				$(".phoneNotValid").fadeIn();
			}
		}
		
		if(message != "") {
			$(".messageCantBeEmpty").hide();
		}
		else {
			$(".messageCantBeEmpty").fadeIn();
		}
	});
  
	$("#btnSubmit").click(function () {
		var fullName = $("#fullName").val();
		var phone = $("#phoneC").val();
		var email = $("#emailC").val();
		var message = $("#message").val();
		var filter = /^\d*(?:\.\d{1,2})?$/;
		
		if(fullName == "" || email == "" || message == "") {
			if(fullName == "") {
				$("#fullName").focus();
			}
			else if(email == "") {
				$("#emailC").focus();
			}
			else if(message == "") {
				$("#message").focus();
			}
		}
		else {
			if(validateEmail($("#emailC").val()) && email != "" && fullName != "" && message != "") {
				$("#btnSubmit").text("Submitting").prop('disabled', true);
				
				$.ajax({
					url: "functions/get-support",
					type: "POST",
					data: {
						fullName: fullName,
						phone: phone,
						email: email,
						message: message
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if(jsonStr.result == "OK") {
							Swal.fire({
								icon: 'success',
								title: 'Successfully send your support ticket.',
								text: "Please check your email!",
								showConfirmButton: true,
								allowOutsideClick: false
							});
							
							$("#fullName, #phoneC, #emailC, #message").val("");
							
							$("#btnSubmit").text("Submit").prop('disabled', false);
						}
						else {
							Swal.fire({
								text: "An error has occured!",
								icon: "error",
								confirmButtonText: "OK",
							});
							
							$("#btnSubmit").text("Submit").prop('disabled', false);
						}
					}
				});
			}
			else {
				if(fullName == "") {
					$("#fullName").focus();
				}
				else if(!validateEmail($("#emailC").val())) {
					$("#emailC").focus();
				}
				else if(message.length != "") {
					$("#message").focus();
				}
			}
		}
  });
});
