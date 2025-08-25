function formatString(e) {
  var inputChar = String.fromCharCode(event.keyCode);
  var code = event.keyCode;
  var allowedKeys = [8];
  if (allowedKeys.indexOf(code) !== -1) {
	return;
  }

  event.target.value = event.target.value.replace(
	/^([1-9]\/|[2-9])$/g, '0$1/' // 3 > 03/
  ).replace(
	/^(0[1-9]|1[0-2])$/g, '$1/' // 11 > 11/
  ).replace(
	/^([0-1])([3-9])$/g, '0$1/$2' // 13 > 01/3
  ).replace(
	/^(0?[1-9]|1[0-2])([0-9]{2})$/g, '$1/$2' // 141 > 01/41
  ).replace(
	/^([0]+)\/|[0]+$/g, '0' // 0/ > 0 and 00 > 0
  ).replace(
	/[^\d\/]|^[\/]*$/g, '' // To allow only digits and `/`
  ).replace(
	/\/\//g, '/' // Prevent entering more than 1 `/`
  );
}

$(document).ready(function () {
	$(document).on('click', '.btnEditCards', function (e) {
		$("#editCardModal").modal("show");
		
		var id = $(this).attr('id');
		var cardName = $(this).data("card-name");
		var cardNumber = $(this).data("card-number");
		var cardExpired = $(this).data("card-expired");
		var cardCVV = $(this).data("card-cvv");
		var cardZIP = $(this).data("zip");
	
		$("#cc-id").val(id);
		$("#cc-valid").val(1);
		$("#edtCardHolderName").val(cardName);
		$("#edtCardNumber").val(cardNumber);
		$("#edtExpiry").val(cardExpired);
		$("#edtCVV").val(cardCVV);
		$("#edtZIP").val(cardZIP);
		
		$('.delete-card').attr('id', id);
	});
	
	$(document).on('click', '.delete-card', function (e) {
		var id = $(this).attr('id');
		
		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "functions/delete-card",
					type: "POST",
					data: {
						cardID: id
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if (jsonStr.result == "OK") {
							$("#editCardModal").modal('hide');
							
							$(".card_" + id).fadeOut();
							
							Swal.fire("Deleted!", "Card has been deleted.", "success");
						}
						else {
							Swal.fire({
								text: "Card is not existed!",
								icon: "error",
								confirmButtonText: "OK",
							});
						}
						
					}
				});
			}
		});
	});
	
	$(document).on('click', '.btnSaveCard', function (e) {
		var ccName = $("#cardHolderName").val();
		var ccNumber = $("#cardNumber").val();
		var ccExpired = $("#expiry").val();
		var ccCVV = $("#cvv").val();
		var zip = $("#zip").val();
		var ccValid = $("#cc-valid2").val();
		
		if(ccName == "" || ccNumber == "" || ccExpired == "" || ccCVV == "" || zip == "") {
			if(ccName == "") {
				$("#cardHolderName").focus();
			}
			else if(ccNumber == "") {
				$("#cardNumber").focus();
			}
			else if(ccExpired == "") {
				$("#expiry").focus();
			}
			else if(ccCVV == "") {
				$("#cvv").focus();
			}
			else if(zip == "") {
				$("#zip").focus();
			}
		}
		else {
			if(ccValid == 1) {
				$(".btnSaveCard").text("Saving Card").prop('disabled', true);
	
				$.ajax({
					url: "functions/save-card",
					type: "POST",
					data: {
						ccName: ccName,
						ccNumber: ccNumber,
						ccExpired: ccExpired,
						ccCVV: ccCVV,
						zip: zip
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if(jsonStr.result == "OK") {
							Swal.fire("Saved", "Card has been saved.", "success");
							
							var id = jsonStr.id;
							var cNumber = jsonStr.number;
							
							var element = `
								<tr class="card_${id}">
									<td class="card-name${id}">${ccName}</td>
									<td class="card-number${id}">${cNumber}</td>
									<td class="card-expired${id}">${ccExpired}</td>
									<td class="card-cvv${id}" style="display: none;">${ccCVV}</td>
									<td class="zip${id}" style="display: none;">${zip}</td>
									<td style="display: flex; justify-content: center; gap: 10px;">
									  <div class="btn-edit-card"
									  >
										<button type="submit" class="btnEditCards btnEditCards${id}" id="${id}"
										data-card-name="${ccName}"
										data-card-number="${ccNumber}"
										data-card-expired="${ccExpired}"
										data-card-cvv="${ccCVV}"
										data-zip="${zip}">Edit</button>
									  </div>
									</td>
								</tr>
							`;
							
							$(".tbl-card tbody").append(element);
							
							$("#cardHolderName, #cardNumber, #expiry, #cvv, #zip").val("");
							$("#cc-valid2").val(0);
							
							$("#addCardModal").modal("hide");
						}
						else {
							Swal.fire({
								text: "Card number already existed!",
								icon: "error",
								confirmButtonText: "OK",
							});
						}
						
						$(".btnSaveCard").text("Save Card").prop('disabled', false);
					}
				});
			}
			else {
				$("#cardNumber").focus();
			}
		}
	});
	
	$(document).on('click', '.btnUpdateCard', function (e) {
		var ccID = $("#cc-id").val();
		var ccValid = $("#cc-valid").val();
		var ccName = $("#edtCardHolderName").val();
		var ccNumber = $("#edtCardNumber").val();
		var ccExpired = $("#edtExpiry").val();
		var ccCVV = $("#edtCVV").val();
		var zip = $("#edtZIP").val();
		
		if(ccName == "" || ccNumber == "" || ccExpired == "" || ccCVV == "" || zip == "") {
			if(ccName == "") {
				$("#edtCardHolderName").focus();
			}
			else if(ccNumber == "") {
				$("#edtCardNumber").focus();
			}
			else if(ccExpired == "") {
				$("#edtExpiry").focus();
			}
			else if(ccCVV == "") {
				$("#edtCVV").focus();
			}
			else if(zip == "") {
				$("#edtZIP").focus();
			}
		}
		else {
			if(ccValid == 1) {
				$(".btnUpdateCard").text("Updating Card").prop('disabled', true);
	
				$.ajax({
					url: "functions/update-card",
					type: "POST",
					data: {
						ccID: ccID,
						ccName: ccName,
						ccNumber: ccNumber,
						ccExpired: ccExpired,
						ccCVV: ccCVV,
						zip: zip
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if(jsonStr.result == "OK") {
							Swal.fire("Updated", "Card has been updated.", "success");
							
							$(".card-name" + ccID).text(ccName);
							$(".card-number" + ccID).text(ccNumber);
							$(".card-expired" + ccID).text(ccExpired);
							$(".card-cvv" + ccID).text(ccCVV);
							$(".zip" + ccID).text(zip);
							
							$(".btnEditCards" + ccID).data('card-name', ccName);
							$(".btnEditCards" + ccID).data('card-number', ccNumber);
							$(".btnEditCards" + ccID).data('card-expired', ccExpired);
							$(".btnEditCards" + ccID).data('card-cvv', ccCVV);
							$(".btnEditCards" + ccID).data('zip', zip);
						}
						
						$(".btnUpdateCard").text("Update Card").prop('disabled', false);
					}
				});
			}
			else {
				$("#edtCardNumber").focus();
			}
		}
	});
	
	const acceptedCards = [
		'amex',
		// 'dankort',
		// 'diners_club_carte_blanche',
		'diners_club_international',
		'discover',
		// 'jcb',
		// 'laser',
		// 'maestro',
		'mastercard',
		// 'uatp',
		'visa',
		// 'visa_electron',
	  ];

	  const $ccNumber = $('#edtCardNumber');
	  const $ccNumberClear = $('#edtCardNumber-clear');
	  const $ccNumberTest = $('.edtCardNumber-test');

	  $ccNumber.on('input', function() {
		ccNumberValidate($(this).val());
	  });

	  $ccNumberClear.on('click', function() {
		ccNumberValidate('');
	  });
	  
	  $ccNumberTest.on('click', function() {
		ccNumberValidate($(this).text());
	  });

	  const ccNumberValidate = (ccNumber = '') => {
		$ccNumber.val(ccNumber);
		$ccNumber.removeClass('error success');

		if (ccNumber !== '') {
			const ccNumberClass = $ccNumber.validateCreditCard({accept: acceptedCards}).valid ? 'success' : 'error';
			$ccNumber.addClass(ccNumberClass);
			
			if($ccNumber.validateCreditCard({accept: acceptedCards}).valid == true) {
				$(".ccNotValid").hide();
				$("#cc-valid").val(1);
			}
			else {
				$(".ccNotValid").show();
				$("#cc-valid").val(0);
			}
		}
	  };
	  
	  const $ccNumber2 = $('#cardNumber');
	  const $ccNumberClear2 = $('#cardNumber-clear');
	  const $ccNumberTest2 = $('.cardNumber-test');

	  $ccNumber2.on('input', function() {
		ccNumberValidate2($(this).val());
	  });

	  $ccNumberClear2.on('click', function() {
		ccNumberValidate2('');
	  });
	  
	  $ccNumberTest2.on('click', function() {
		ccNumberValidate2($(this).text());
	  });

	  const ccNumberValidate2 = (ccNumber2 = '') => {
		$ccNumber2.val(ccNumber2);
		$ccNumber2.removeClass('error success');

		if (ccNumber2 !== '') {
			const ccNumberClass = $ccNumber2.validateCreditCard({accept: acceptedCards}).valid ? 'success' : 'error';
			$ccNumber2.addClass(ccNumberClass);
			
			if($ccNumber2.validateCreditCard({accept: acceptedCards}).valid == true) {
				$(".ccNotValid").hide();
				$("#cc-valid2").val(1);
			}
			else {
				$(".ccNotValid").show();
				$("#cc-valid2").val(0);
			}
		}
	  };
	  
	$('#edtCVV, #edtZIP, #edtCardNumber, #edtExpiry, #cvv, #zip, #expiry').on("cut copy paste",function(e) {
	  e.preventDefault();
	});
	  
	$("#edtCVV, #edtZIP, #edtCardNumber, #cvv, #zip, #cardNumber").keypress(function (e) {
	 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
	  return false;
	}
   });
});
