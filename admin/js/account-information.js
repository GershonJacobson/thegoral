$(document).ready(function () {
  $(document).on('click', '.card-info-edit', function (e) {
    $("#accountInformationModal").modal("show");
	
	var firstName = $(this).data("first-name");
	var lastName = $(this).data("last-name");
	var email = $(this).data("email");
	var phone = $(this).data("phone");
	var number = $(this).data("number");

	$("#id-number").val(number);
	$(".profile-name").text(firstName + " " + lastName);
	$(".email-text").text(email);
	$(".first-name-text").text(firstName);
	$(".last-name-text").text(lastName);
	$(".phone-text").text(phone);
	
	$(".tbl-payment-history tbody").empty();
	
	$.ajax({
		url: 'functions/get-payment-history',
		type: 'post',
		data: {email:email},
		dataType: "JSON",
		beforeSend:function(){
			$(".tbl-payment-history tbody").append("<td colspan='4'; align='center'>Loading...</td>");
		},
		success: function(jsonStr){
			if(jsonStr.length > 0) {
				$(".tbl-payment-history tbody").empty();
				
				for(var i=0; i<jsonStr.length; i++) {
					var cardNumber = jsonStr[i].card_number;
					var paymentMethod = jsonStr[i].payment_method;
					var purchasedDate = jsonStr[i].purchased_date;
					var totalPrice = jsonStr[i].total_price;
					var ticketID = jsonStr[i].ticketID;
					var userRole = jsonStr[i].userRole;
					
					if(jsonStr[i].totalRefund == null) {
						var totalRefund = "";
					}
					else {
						var totalRefund = "$" + jsonStr[i].totalRefund;
					}
					
					if(userRole == 1 || userRole == 2) {
						var elementRefund = `
							<div class="text-refund text-refund${ticketID}">${totalRefund}</div>
							<button type="button" class="btn-refund" id="${ticketID}">Refund</button>
						`;
					}
					else {
						var elementRefund = "";
					}
					
					var element = `
						<tr>
							<td>${purchasedDate}</td>
							<td>$${totalPrice}</td>
							<td>${paymentMethod}</td>
							<td>
								${elementRefund}
							</td>
						</tr>
					`;
					
					$(".tbl-payment-history tbody").append(element);
				}
			}
			else {
				$(".tbl-payment-history tbody").append("<td colspan='4'; align='center'>No data</td>");
			}
		}
	});
  });
  
	$(document).on('click', '.btn-refund', function (e) {
		var id = $(this).attr('id');
		
		$("#ticketID").val(id);
		$("#refundModal").modal("show");
	});
   
  $("#btnEditProfile").click(function () {
    $("#updateProfile").modal("show");
	
	var firstName = $(".first-name-text").text();
	var lastName = $(".last-name-text").text();
	var email = $(".email-text").text();
	var phone = $(".phone-text").text();
	
	$("#edtFirstName").val(firstName);
	$("#edtLastName").val(lastName);
	$("#edtEmail").val(email);
	$("#edtPhone").val(phone);
  });
  
   $("#btnUpdateProfile").click(function () {
		var firstName = $("#edtFirstName").val();
		var lastName = $("#edtLastName").val();
		var email = $("#edtEmail").val();
		var phone = $("#edtPhone").val();
		var number = $("#id-number").val();
		
		if(firstName == "" || lastName == "" || email == "") {
			if(firstName == "") {
				$("#edtFirstName").focus();
			}
			else if(lastName == "") {
				$("#edtLastName").focus();
			}
			else if(email == "") {
				$("#edtEmail").focus();
			}
		}
		else {
			$("#btnUpdateProfile").text("Updating Profile").prop('disabled', true);
			
			$.ajax({
				url: 'functions/update-account-information',
				type: 'post',
				data: {firstName:firstName, lastName: lastName, email: email, phone: phone},
				dataType: "JSON",
				success: function(jsonStr){
					if(jsonStr.result == "OK") {
						Swal.fire("Updated", "Profile has been updated.", "success");
					}
					
					$("#btnUpdateProfile").text("Update Profile").prop('disabled', false);
					
					$(".profile-name").text(firstName + " " + lastName);
					$(".first-name-text").text(firstName);
					$(".last-name-text").text(lastName);
					$(".phone-text").text(phone);
					
					$(".card-info-edit" + number + " .text-info-ds").text(firstName + " " + lastName);
					
					$(".card-info-edit" + number).data('first-name', firstName);
					$(".card-info-edit" + number).data('last-name', lastName);
					$(".card-info-edit" + number).data('email', email);
					$(".card-info-edit" + number).data('phone', phone);
				}
			});
		}
   });
  
  $("#closeBtn").click(function () {
    $("#accountInformationModal,#updateProfile").modal("hide");
  });
  $("#closeBtn2").click(function () {
    $("#updateProfile").modal("hide");
  });
  $("#closeBtn3").click(function () {
    $("#refundModal").modal("hide");
  });
  
  $("#btnRefund").click(function () {
	var ticketID = $("#ticketID").val();
	var edtAmount = $("#edtAmount").val();
	
	if(edtAmount.length > 0) {
		$("#btnRefund").text("Processing").prop('disabled', true);
		
		$.ajax({
			url: 'functions/refund',
			type: 'post',
			data: {ticketID: ticketID, edtAmount:edtAmount},
			dataType: "JSON",
			success: function(jsonStr){
				if(jsonStr.result == "OK") {
					Swal.fire("Refunded", "Refund has been success.", "success");
					
					$("#edtAmount").val("");
					$(".text-refund" + ticketID).text("$" + edtAmount);
				}
			}
		});
	}
	else {
		$("#edtAmount").focus();
	}
  });
  
  $("#edtAmount").keydown(function (event) {
	if (event.shiftKey == true) {
		event.preventDefault();
	}

	if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

	} else {
		event.preventDefault();
	}
	
	if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
		event.preventDefault();
});
  
  $('#search').keyup(function(event){
	var searchVal = $(this).val();
		
	if(searchVal == "") {}
	else {
		$(".card-container").hide();
		$(".data-account").empty();
		
		$.ajax({
			url: 'functions/get-account-information',
			type: 'post',
			data: {searchVal:searchVal},
			dataType: "JSON",
			beforeSend:function(){
				$(".card-container").show();
				$(".loading").show();
			},
			success: function(jsonStr){
				if(jsonStr.length > 0) {
					$(".loading").hide();
					
					for(var i=0; i<jsonStr.length; i++) {
						var firstName = jsonStr[i].firstName;
						var lastName = jsonStr[i].lastName;
						var emailAddress = jsonStr[i].emailAddress;
						var phone = jsonStr[i].phone;
						
						var element = `
							<div class="col-xl-12 mb-2">
							  <div class="card-info-edit card-info-edit${i}" style="text-align: start !important" data-first-name="${firstName}" data-last-name="${lastName}" data-email="${emailAddress}" data-phone="${phone}" data-number="${i}">
								<span class="user-icon"><img src="../assets/images/user-icon.png" alt=""/></span>
								<span class="text-info-ds" style="font-weight: 700">${firstName} ${lastName}</span>
							  </div>
							</div>
						`;
						
						$(".data-account").append(element);
					}
				}
				else {
					$(".loading").hide();
					$(".card-container").hide();
				}
			}
		});
	}
  });
});
