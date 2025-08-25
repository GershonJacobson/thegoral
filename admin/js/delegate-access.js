function onlyOne(checkbox) {
	var checkboxes = document.getElementsByName('delegate-access')
	checkboxes.forEach((item) => {
		if (item !== checkbox) item.checked = false
	})
}

function onlyOne1(checkbox) {
	var checkboxes = document.getElementsByName('edt-delegate-access')
	checkboxes.forEach((item) => {
		if (item !== checkbox) item.checked = false
	})
}

function validateEmail(email) {
	var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
	return expr.test(email);
};

$(document).ready(function () {
	$('.delegate-access').click(function(){
		$(".delegateAccessCantBeEmpty").hide();
	});
	
	$("#btnAddUser").click(function () {
		$("#delegateAccessModal").modal("show");
		
		$(".delegate-access").prop("checked", false);
		$("#email-address").val("");
		
		$(".emailNotValid").hide();
		$(".delegateAccessCantBeEmpty").hide();
		$(".message").hide();
	});
	
	$(".closeBtn").click(function (e) {
		e.preventDefault();
		$("#editDelegateAccessModal,#delegateAccessModal").modal("hide");
	});
	
	$('.btnSubmit').click(function(){
		var emailAddress = $("#email-address").val();
		var delegateAccess = $('.delegate-access:checkbox:checked').length > 0;
		
		if(emailAddress == "" || delegateAccess == false) {
			if(delegateAccess == false) {
				$(".delegateAccessCantBeEmpty").fadeIn();
			}
			else {
				$(".delegateAccessCantBeEmpty").hide();
			}
			
			if(emailAddress == "") {
				$("#email-address").focus();
			}
		}
		else {
			if(validateEmail($("#email-address").val())) {
				$(".emailNotValid").hide();
				$(".delegateAccessCantBeEmpty").hide();
				$(".message").hide();
				
				var access = $('.delegate-access:checkbox:checked').val();
				
				$.ajax({
					url: 'functions/add-delegate-access',
					type: 'post',
					data: {emailAddress:emailAddress, access: access},
					dataType: "JSON",
					beforeSend:function(){
						$(".btnSubmit").html('Processing').attr("disabled", true);
					},
					success: function(jsonStr){
						if(jsonStr.result == "emailNotExisted") {
							$(".message").show();
							$(".message").text("Email is not existed!");
						}
						else if(jsonStr.result == "existed") {
							$(".message").show();
							$(".message").text("Email already existed!");
						}
						else if(jsonStr.result == "OK") {
							$(".delegate-access").prop("checked", false);
							$("#email-address").val("");
							
							$(".emailNotValid").hide();
							$(".delegateAccessCantBeEmpty").hide();
							$(".message").hide();
							
							$("#delegateAccessModal").modal("hide");
							
							Swal.fire("Delegate", "Delegate access for " + emailAddress + " has been added.", "success");
							
							if(access == "full-admin") {
								var roleText = "Everything the main admins have.";
							}
							else if(access == "edit-cant-see-money") {
								var roleText = "Can only edit campaigns and can’t see money.";
							}
							else if(access == "just-view-cant-edit") {
								var roleText = "Can just view (including money) and not edit anything";
							}
							else {
								var roleText = "-";
							}
							
							var userID = jsonStr.userID;
							
							var element = `
								<tr id="list_${userID}">
									<td>${emailAddress}</td>
									<td class="access${userID}">
										${roleText}
									</td>
									<td class="filter-by-container3">
									  <button
										class="btn dropdown-toggle btn-filter"
										type="button"
										id="dropdownMenuClickableInside"
										data-bs-toggle="dropdown"
										data-bs-auto-close="outside"
										aria-expanded="false"
										style="background: #e7e7e7"
									  >
										Action
									  </button>

									  <ul
										class="dropdown-menu"
										aria-labelledby="dropdownMenuClickableInside"
										style="box-shadow: 0 5px 5px -5px #333"
									  >
										<li>
										  <a class="edit edit${userID}" id="${userID}" data-access="${access}" data-email="${emailAddress}">Edit</a>
										  <a class="remove" id="${userID}">Remove</a>
										</li>
									  </ul>
									</td>
								  </tr>
							`;
							
							$(".tblDelegateAccess tbody").prepend(element);
						}
						
						$(".btnSubmit").html('Submit').attr("disabled", false);
					}
				});
			}
			else {
				$(".emailNotValid").fadeIn();
			}
		}
	});
	
	$('.btnUpdateDelegate').click(function(){
		var userID = $("#userID").val();
		var emailAddress = $("#edt-email-address").val();
		var delegateAccess = $('.edt-delegate-access:checkbox:checked').length > 0;
		
		if(emailAddress == "" || delegateAccess == false) {
			if(delegateAccess == false) {
				$(".delegateAccessCantBeEmptyEdt").fadeIn();
			}
			else {
				$(".delegateAccessCantBeEmptyEdt").hide();
			}
			
			if(emailAddress == "") {
				$("#edt-email-address").focus();
			}
		}
		else {
			if(validateEmail($("#edt-email-address").val())) {
				$(".emailNotValidEdt").hide();
				$(".delegateAccessCantBeEmptyEdt").hide();
				$(".messageEdt").hide();
				
				var access = $('.edt-delegate-access:checkbox:checked').val();
				
				$.ajax({
					url: 'functions/update-delegate-access',
					type: 'post',
					data: {emailAddress:emailAddress, access: access},
					dataType: "JSON",
					beforeSend:function(){
						$(".btnUpdateDelegate").html('Updating').attr("disabled", true);
					},
					success: function(jsonStr){
						if(jsonStr.result == "emailNotExisted") {
							$(".messageEdt").show();
							$(".messageEdt").text("Email is not existed!");
						}
						else if(jsonStr.result == "notExisted") {
							$(".messageEdt").show();
							$(".messageEdt").text("Email delegate is not existed!");
						}
						else if(jsonStr.result == "OK") {
							$(".emailNotValidEdt").hide();
							$(".delegateAccessCantBeEmptyEdt").hide();
							$(".messageEdt").hide();
							
							$('.edit' + userID).data('access', access);
							
							if(access == "full-admin") {
								var roleText = "Everything the main admins have.";
							}
							else if(access == "edit-cant-see-money") {
								var roleText = "Can only edit campaigns and can’t see money.";
							}
							else if(access == "just-view-cant-edit") {
								var roleText = "Can just view (including money) and not edit anything";
							}
							else {
								var roleText = "-";
							}
							
							$('.access' + userID).text(roleText);
							
							Swal.fire("Delegate", "Delegate access for " + emailAddress + " has been updated.", "success");
						}
						
						$(".btnUpdateDelegate").html('Update').attr("disabled", false);
					}
				});
			}
			else {
				$(".emailNotValidEdt").fadeIn();
			}
		}
	});
	
	$(document).on('click', '.edit', function() {
		$("#editDelegateAccessModal").modal("show");
		
		var id = $(this).attr('id');
		var access = $(this).data("access");
		var emailAddress = $(this).data("email");
		
		$(".edt-delegate-access").prop("checked", false);
		
		$('input[name="edt-delegate-access"][value="' + access + '"]').prop("checked", true);
		$("#edt-email-address").val(emailAddress);
		$("#userID").val(id);
	});
	
	$(document).on('click', '.remove', function() {
		var id = $(this).attr('id');
		
		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "functions/remove-delegate-access",
					type: "POST",
					data: {
						userID: id
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if (jsonStr.result == "OK") {
							$("#list_" + id).fadeOut("normal", function() {
								$(this).remove();
							});
							Swal.fire("Removed!", "Delegate access has been removed.", "success");
						}
						else {
							Swal.fire({
								text: "User is not existed!",
								icon: "error",
								confirmButtonText: "OK",
							});
						}
						
					}
				});
			}
		});
	});
});