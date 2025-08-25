$(document).ready(function() {
	$('.deleteCampaign').on('click', function() {
		var id = $(this).attr('id');
		
		Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			showCancelButton: true,
			confirmButtonText: 'Yes, delete it!',
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "functions/delete-campaign",
					type: "POST",
					data: {
						campaignID: id
					},
					dataType: "JSON",
					success: function (jsonStr) {
						if (jsonStr.result == "OK") {
							$("#" + id).fadeOut("normal", function() {
								$(this).remove();
							});
							Swal.fire("Deleted!", "Campaign has been deleted.", "success");
						} 
						else if (jsonStr.result == "stillOpen") {
							Swal.fire({
								text: "Campaign is still open!",
								icon: "error",
								confirmButtonText: "OK",
							});
						}
						else {
							Swal.fire({
								text: "Campaign is not existed!",
								icon: "error",
								confirmButtonText: "OK",
							});
						}
						
					}
				});
			}
		});
	});
	
	
	
	$(".tbl-campaign").on('click', '.editCampaign', function () {
		$("#editCampModal").modal("show");
		
		var id = $(this).attr('id');
		var firstName = $(this).data("first-name");
		var lastName = $(this).data("last-name");
		var email = $(this).data("email");
		var phone = $(this).data("phone");
		var startDate = $(this).data("start-date");
		var startTime = $(this).data("start-time");
		var endDate = $(this).data("end-date");
		var endTime = $(this).data("end-time");
		var campaignName = $(this).data("campaign-name");
		var pageURL = $(this).data("page-url");
		var public = $(this).data("public");
		
		$("#edtEndDate").attr({
			"min" : startDate
		});

		if(public == 1) {
			$('#edtPublic').prop('checked', true);
		}
		else {
			$('#edtPublic').prop('checked', false);
		}
	
		$("#editCampModal #edtCampaignID").val(id);
		$("#editCampModal #edtFirstName").val(firstName);
		$("#editCampModal #edtLastName").val(lastName);
		$("#editCampModal #edtEmail").val(email);
		$("#editCampModal #edtPhone").val(phone);
		$("#editCampModal #edtCampaignName").val(campaignName);
		$("#editCampModal #edtPageUrl").val(pageURL);
		$("#editCampModal #edtStartDate").val(startDate);
		$("#editCampModal #edtEndDate").val(endDate);
		$("#editCampModal #edtStartTime").val(startTime);
		$("#editCampModal #edtEndTime").val(endTime);
	});
	
	function leadingZero(value) {
		if (value < 10) {
			return "0" + value.toString();
		}
		return value.toString();
	}
	
	$("#btnUpdateCamp").click(function () {
		var campaignID = $("#edtCampaignID").val();
		var startDate = $("#edtStartDate").val();
		var startDateF = $("#edtStartDate").val();
		var endDate = $("#edtEndDate").val();
		var endDateF = $("#edtEndDate").val();
		var campaignName = $("#edtCampaignName").val();
		var publicOrPrivate = $("#edtPublic").is(":checked") ? "true" : "false";
		var publicOrPrivateF = $("#edtPublic").is(":checked") ? 1 : 0;
		
		if(startDate == "" || endDate == "" || campaignName == "" || publicOrPrivate == "") {
			if(startDate == "") {
				$("#edtStartDate").focus();
			}
			else if(endDate == "") {
				$("#edtEndDate").focus();
			}
			else if(campaignName == "") {
				$("#edtCampaignName").focus();
			}
			else if(publicOrPrivate == "") {
				$("#edtPublic").focus();
			}
		}
		else {
			var startDateD = new Date($('#edtStartDate').val());
			dayD = startDateD.getDate();
			
			var endDateD = new Date($('#edtEndDate').val());
			dayE = endDateD.getDate();
			
			$("#btnUpdateCamp").text("Updating campaign").prop('disabled', true);
			
			$.ajax({
				url: "functions/update-campaign",
				type: "POST",
				data: {
					campaignID: campaignID,
					startDate: startDate,
					endDate: endDate,
					campaignName: campaignName,
					publicOrPrivate: publicOrPrivate
				},
				dataType: "JSON",
				success: function (jsonStr) {
					if(jsonStr.result == "OK") {
						$(".label-campaign-name" + campaignID + " a").text(campaignName);
						$(".label-sd-ed" +  campaignID + " a").text(leadingZero(dayD) + " / " + leadingZero(dayE));
						$(".label-public" + campaignID).text(publicOrPrivate);
						
						$(".editCampaign" + campaignID).data('campaign-name', campaignName);
						$(".editCampaign" + campaignID).data('start-date', startDateF);
						$(".editCampaign" + campaignID).data('end-date', endDateF);
						$(".editCampaign" + campaignID).data('public', publicOrPrivateF);
						
						Swal.fire("Updated", "Campaign has been updated.", "success");
					}
					
					$("#btnUpdateCamp").text("Update My Campaign").prop('disabled', false);
				}
			});
		}
	});
	
	$('.load-more').click(function(){
		var row = Number($('#row').val());
		var allcount = Number($('#all').val());
		var currentNo = Number($('#currentNo').val());
		var filter = $("#filter").val();
		var rowperpage = 5;
		row = row + rowperpage;

		if(row <= allcount){
			$("#row").val(row);

			$.ajax({
				url: 'functions/get-campaign-data',
				type: 'post',
				data: {row:row, currentNo: currentNo, filter: filter},
				dataType: "JSON",
				beforeSend:function(){
					$(".load-more").text("Loading...");
				},
				success: function(jsonStr){
					setTimeout(function() {
						if(jsonStr.length > 0) {
							for(var i=0; i<jsonStr.length; i++) {
								var campaignID = jsonStr[i].campaignID;
								var campaignName = jsonStr[i].campaignName;
								var startDate = jsonStr[i].startDate;
								var endDate = jsonStr[i].endDate;
								var totalParticipant = jsonStr[i].totalParticipant;
								var totalPrice = "$" + jsonStr[i].totalPrice;
								var pageURL = jsonStr[i].pageURL;
								var firstName = jsonStr[i].firstName;
								var lastName = jsonStr[i].lastName;
								var email = jsonStr[i].email;
								var phone = jsonStr[i].phone;
								var startDateF = jsonStr[i].startDateF;
								var startTime = jsonStr[i].startTime;
								var endDateF = jsonStr[i].endDateF;
								var endTime = jsonStr[i].endTime;
								var public = jsonStr[i].public;
								var status = jsonStr[i].status;
								
								if(status == "open") {
									var statusElement = `
										<div class="blinking-green"></div>
									`;
									
									var buttonElement = `
										<button class="btn editCampaign editCampaign${campaignID}" id="${campaignID}" type="button" style="background: #e7e7e7; font-size: 12px;"
											data-first-name="${firstName}"
												data-last-name="${lastName}"
												data-email="${email}"
												data-phone="${phone}"
												data-start-date="${startDateF}"
												data-start-time="${startTime}"
												data-end-date="${endDateF}"
												data-end-time="${endTime}"
												data-campaign-name="${campaignName}"
												data-page-url="${pageURL}"
												data-public="${public}">
											Edit
										</button>
									`;
								}
								else {
									var statusElement = `
										<div class="blinking-red"></div>
									`;
									
									var buttonElement = "";
								}
							
								var element = `
									<tr class="post" id="post_${campaignID}">
										<td class="label-campaign-name${campaignID}"><a href="${pageURL}">${campaignName}</a></td>
										<td class="label-sd-ed${campaignID}"><a href="${pageURL}">${startDate} / ${endDate}</a></td>
										<td><a href="${pageURL}">${totalParticipant}</a></td>
										<td><a href="${pageURL}">${totalPrice}</a></td>
										<td align="center" style="position: relative;">
											${statusElement}
											<input type="hidden" class="label-public${campaignID}"/>
										</td>
				
										<td>
											${buttonElement}
										</td>
									</tr>
								`;
								
								$(".tbl-campaign tbody").append(element);
							}
						}
						
						var rowno = row + rowperpage;
						$('#currentNo').val(rowno);

						if(rowno > allcount){
							$('.load-more').hide();
						}else{
							$(".load-more").html('<i class="fa-solid fa-chevron-down"></i>');
						}
					}, 2000);
				}
			});
		}
	});
	
	$('.load-more-raffle').click(function(){
		var row = Number($('#row-raffle').val());
		var allcount = Number($('#all-raffle').val());
		var currentNo = Number($('#currentNo-raffle').val());
		var filter = $("#filter-raffle").val();
		var rowperpage = 5;
		row = row + rowperpage;

		if(row <= allcount){
			$("#row-raffle").val(row);

			$.ajax({
				url: 'functions/get-raffle-data',
				type: 'post',
				data: {row:row, currentNo: currentNo, filter: filter},
				dataType: "JSON",
				beforeSend:function(){
					$(".load-more-raffle").text("Loading...");
				},
				success: function(jsonStr){
					setTimeout(function() {
						if(jsonStr[0].length > 0) {
							for(var i=0; i<jsonStr[0].length; i++) {
								var campaignID = jsonStr[0][i].campaignID;
								var paymentStatus = jsonStr[0][i].paymentStatus;
								var paymentMethod = jsonStr[0][i].paymentMethod;
								var campaignName = jsonStr[0][i].campaignName;
								var url = jsonStr[0][i].url;
								var purchasedDate = jsonStr[0][i].purchasedDate;
								var totalPrice = jsonStr[0][i].totalPrice;
								
								var element = `
									<tr>
										<td><a href="${url}">${campaignName}</a></td>
										<td><a href="${url}">${purchasedDate}</a></td>
										<td><a href="${url}">$${totalPrice}</a></td>
										<td><a href="${url}">${paymentMethod}</a></td>
										<td><a href="${url}">${paymentStatus}</a></td>
									</tr>
								`;
								
								$(".tbl-raffle tbody").append(element);
							}
						}
						
						var rowno = row + rowperpage;
						$('#currentNo-raffle').val(rowno);

						if(rowno > allcount){
							$('.load-more-raffle').hide();
						}else{
							$(".load-more-raffle").html('<i class="fa-solid fa-chevron-down"></i>');
						}
					}, 2000);
				}
			});
		}
	});
	
	$('input[name="filter"]:radio').change(function () {
		$('#row').val(0);
		$(".load-more").hide();
		
		var selectedFilter = $(this).val();
		var row = Number($('#row').val());
		var rowperpage = 5;
		row = row + rowperpage;
		
		$("#filter").val(selectedFilter);
		
		$(".tbl-campaign tbody").empty();
		
		$.ajax({
			url: "functions/filter-campaign",
			type: "POST",
			data: {
				selectedFilter: selectedFilter,
				row: row
			},
			beforeSend: function() {
				$(".tbl-campaign tbody").append("<td colspan='6' align='center'>Loading</td>");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				if(jsonStr[1] > 5) {
					$(".load-more").show();
					$(".load-more").html('<i class="fa-solid fa-chevron-down"></i>');
				}
				else {
					$(".load-more").hide();
				}
				
				if(jsonStr[0].length > 0) {
					$(".tbl-campaign tbody").empty();
					
					for(var i=0; i<jsonStr[0].length; i++) {
						var campaignID = jsonStr[0][i].campaignID;
						var campaignName = jsonStr[0][i].campaignName;
						var startDate = jsonStr[0][i].startDate;
						var endDate = jsonStr[0][i].endDate;
						var totalParticipant = jsonStr[0][i].totalParticipant;
						var totalPrice = "$" + jsonStr[0][i].totalPrice;
						var pageURL = jsonStr[0][i].pageURL;
						var firstName = jsonStr[0][i].firstName;
						var lastName = jsonStr[0][i].lastName;
						var email = jsonStr[0][i].email;
						var phone = jsonStr[0][i].phone;
						var startDateF = jsonStr[0][i].startDateF;
						var startTime = jsonStr[0][i].startTime;
						var endDateF = jsonStr[0][i].endDateF;
						var endTime = jsonStr[0][i].endTime;
						var public = jsonStr[0][i].public;
						var status = jsonStr[0][i].status;
						
						if(status == "open") {
							var statusElement = `
								<div class="blinking-green"></div>
							`;
							
							var buttonElement = `
								<button class="btn editCampaign editCampaign${campaignID}" id="${campaignID}" type="button" style="background: #e7e7e7; font-size: 12px;"
									data-first-name="${firstName}"
										data-last-name="${lastName}"
										data-email="${email}"
										data-phone="${phone}"
										data-start-date="${startDateF}"
										data-start-time="${startTime}"
										data-end-date="${endDateF}"
										data-end-time="${endTime}"
										data-campaign-name="${campaignName}"
										data-page-url="${pageURL}"
										data-public="${public}">
									Edit
								</button>
							`;
						}
						else {
							var statusElement = `
								<div class="blinking-red"></div>
							`;
							
							var buttonElement = "";
						}
					
						var element = `
							<tr class="post" id="post_${campaignID}">
								<td class="label-campaign-name${campaignID}"><a href="${pageURL}">${campaignName}</a></td>
								<td class="label-sd-ed${campaignID}"><a href="${pageURL}">${startDate} / ${endDate}</a></td>
								<td><a href="${pageURL}">${totalParticipant}</a></td>
								<td><a href="${pageURL}">${totalPrice}</a></td>
								<td align="center" style="position: relative;">
									${statusElement}
									<input type="hidden" class="label-public${campaignID}"/>
								</td>
		
								<td>
									${buttonElement}
								</td>
							</tr>
						`;
						
						$(".tbl-campaign tbody").append(element);
					}
				}
			}
		});
	});
	
	$('input[name="filter-dashboard"]:radio').change(function () {
		$('#row-raffle').val(0);
		$(".load-more-raffle").hide();
		
		var selectedFilter = $(this).val();
		
		$(".tbl-raffle tbody").empty();
		
		$.ajax({
			url: "functions/filter-dashboard",
			type: "POST",
			data: {
				selectedFilter: selectedFilter
			},
			beforeSend:function(){
				$(".money-spent").text("Loading");
				$(".ticket-bought").text("Loading");
				$(".raffles-joined").text("Loading");
				$(".money-won").text("Loading");
				
				$(".tbl-raffle tbody").append("<td colspan='5' align='center'>Loading</td>");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				$(".money-spent").text("$" + jsonStr.totalTicketPrice);
				$(".ticket-bought").text(jsonStr.totalTicket);
				$(".raffles-joined").text(jsonStr.totalRafflesJoined);
				$(".money-won").text("$" + jsonStr.totalRafflesWon);
				
				if(jsonStr.allcount > 5) {
					$(".load-more-raffle").show();
					$(".load-more-raffle").html('<i class="fa-solid fa-chevron-down"></i>');
				}
				else {
					$(".load-more-raffle").hide();
				}
				
				if(jsonStr.list.length > 0) {
					$(".tbl-raffle tbody").empty();
					
					for(var i=0; i<jsonStr.list.length; i++) {
						var campaignID = jsonStr.list[i].campaignID;
						var paymentStatus = jsonStr.list[i].paymentStatus;
						var paymentMethod = jsonStr.list[i].paymentMethod;
						var campaignName = jsonStr.list[i].campaignName;
						var url = jsonStr.list[i].url;
						var purchasedDate = jsonStr.list[i].purchasedDate;
						var totalPrice = jsonStr.list[i].totalPrice;
						
						var element = `
							<tr>
								<td><a href="${url}">${campaignName}</a></td>
								<td><a href="${url}">${purchasedDate}</a></td>
								<td><a href="${url}">$${totalPrice}</a></td>
								<td><a href="${url}">${paymentMethod}</a></td>
								<td><a href="${url}">${paymentStatus}</a></td>
							</tr>
						`;
						
						$(".tbl-raffle tbody").append(element);
					}
				}
			}
		});
	});
});