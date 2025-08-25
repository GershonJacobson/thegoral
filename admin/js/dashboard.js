$(document).ready(function () {
	$('input[name="filter-dashboard"]:radio').change(function () {
		var selectedFilter = $(this).val();
		
		$.ajax({
			url: "functions/filter-dashboard",
			type: "POST",
			data: {
				filter: selectedFilter
			},
			beforeSend:function(){
				$(".total-earnings").text("Loading");
				$(".total-profits").text("Loading");
				$(".raffles-done").text("Loading");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				$(".total-earnings").text("$" + jsonStr.totalEarnings);
				$(".total-profits").text("$" + jsonStr.totalProfits);
				$(".raffles-done").text(jsonStr.rafflesDone);
			}
		});
	});
	
	// var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
	// $('#edtStartDate').datepicker({
	// 	uiLibrary: 'bootstrap5',
	// 	iconsLibrary: 'fontawesome',
	// 	format: 'mm/dd/yyyy',
	// 	minDate: today,
	// 	maxDate: function () {
	// 		return $('#edtEndDate').val();
	// 	}
	// });
	// $('#edtEndDate').datepicker({
	// 	uiLibrary: 'bootstrap5',
	// 	iconsLibrary: 'fontawesome',
	// 	format: 'mm/dd/yyyy',
	// 	minDate: function () {
	// 		return $('#edtStartDate').val();
	// 	}
	// });
	
	$(document).on('click', '.closeBtn', function (e) {
		e.preventDefault();
		$("#editCampModal").modal(
		  "hide"
		);
	});
	
	$("#1ticketPrice, #2ticketPrice").keydown(function (event) {
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
	
	$(document).on('click', '.edit-campaign', function () {
		$("#editCampModal").modal("show");
		
		var id = $(this).attr('id');
		var startDate = $(this).data("start-date");
		var startTime = $(this).data("start-time");
		var endDate = $(this).data("end-date");
		var endTime = $(this).data("end-time");
		var campaignName = $(this).data("campaign-name");
		var public = $(this).data("public");
		var category = $(this).data("category");
		var ticketPrice1 = $(this).data("ticket-price1");
		var ticketPrice2 = $(this).data("ticket-price2");

		if(public == 1) {
			$('#edtPublic').prop('checked', true);
		}
		else {
			$('#edtPublic').prop('checked', false);
		}
		
		if(category != "weekly") {
			$('.switch-public').show();
		}
		else {
			$('.switch-public').hide();
		}
	
		$("#editCampModal #edtCampaignID").val(id);
		$("#editCampModal #edtCampaignName").val(campaignName);
		$("#editCampModal #edtStartDate").val(startDate);
		$("#editCampModal #edtEndDate").val(endDate);
		$("#editCampModal #edtStartTime").val(startTime);
		$("#editCampModal #edtEndTime").val(endTime);
		$("#editCampModal #edtCategory").val(category);
		$("#editCampModal #1ticketPrice").val(ticketPrice1);
		$("#editCampModal #2ticketPrice").val(ticketPrice2);
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
		var startTime = $("#edtStartTime").val();
		var endTime = $("#edtEndTime").val();
		var campaignName = $("#edtCampaignName").val();
		var publicOrPrivate = $("#edtPublic").is(":checked") ? "true" : "false";
		var publicOrPrivateF = $("#edtPublic").is(":checked") ? 1 : 0;
		var category = $("#edtCategory").val();
		var ticketPrice1 = $("#1ticketPrice").val();
		var ticketPrice2 = $("#2ticketPrice").val();
		
		if(startDate == "" || endDate == "" || startTime == "" || endTime == "" || campaignName == "" || ticketPrice1 == "" || ticketPrice2 == "") {
			if(startDate == "") {
				$("#edtStartDate").focus();
			}
			else if(endDate == "") {
				$("#edtEndDate").focus();
			}
			else if(startTime == "") {
				$("#edtStartTime").focus();
			}
			else if(endTime == "") {
				$("#edtEndTime").focus();
			}
			else if(campaignName == "") {
				$("#edtCampaignName").focus();
			}
			else if(ticketPrice1 == "") {
				$("#ticketPrice1").focus();
			}
			else if(ticketPrice2 == "") {
				$("#ticketPrice2").focus();
			}
		}
		else {
			var startDateD = new Date($('#edtStartDate').val());
			dayD = startDateD.getDate();
			
			var endDateD = new Date($('#edtEndDate').val());
			dayE = endDateD.getDate();
			
			startDate = startDate + " " + startTime;
			endDate = endDate + " " + endTime;
			
			$("#btnUpdateCamp").text("Updating campaign").prop('disabled', true);
			
			$.ajax({
				url: "functions/update-campaign",
				type: "POST",
				data: {
					campaignID: campaignID,
					startDate: startDate,
					endDate: endDate,
					campaignName: campaignName,
					publicOrPrivate: publicOrPrivate,
					category: category,
					ticketPrice1: ticketPrice1,
					ticketPrice2: ticketPrice2
				},
				dataType: "JSON",
				success: function (jsonStr) {
					if(jsonStr.result == "OK") {
						$(".campaign-name" + campaignID).text(campaignName);
						//$(".label-sd-ed" +  campaignID + " a").text(leadingZero(dayD) + " / " + leadingZero(dayE));
						//$(".label-public" + campaignID).text(publicOrPrivate);
					
						$(".edit-campaign" + campaignID).data('campaign-name', campaignName);
						$(".edit-campaign" + campaignID).data('start-date', startDateF);
						$(".edit-campaign" + campaignID).data('end-date', endDateF);
						$(".edit-campaign" + campaignID).data('start-time', startTime);
						$(".edit-campaign" + campaignID).data('end-time', endTime);
						$(".edit-campaign" + campaignID).data('public', publicOrPrivateF);
						$(".edit-campaign" + campaignID).data('ticket-price1', ticketPrice1);
						$(".edit-campaign" + campaignID).data('ticket-price2', ticketPrice2);
						
						Swal.fire("Updated", "Campaign has been updated.", "success");
					}
					
					$("#btnUpdateCamp").text("Update Campaign").prop('disabled', false);
				}
			});
		}
	});
	
	$(document).on('click', '.deleteCampaign', function () {
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
							$("#post_" + id).fadeOut();
							
							Swal.fire("Deleted!", "Campaign has been deleted.", "success");
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
	
	$('input[name="filter"]:radio').change(function () {
		$('#row').val(0);
		$(".load-more").hide();
		
		var selectedFilter = $(this).val();
		var row = Number($('#row').val());
		var rowperpage = 5;
		row = row + rowperpage;
		
		$("#filter").val(selectedFilter);
		
		$(".campaign-list").empty();
		
		$.ajax({
			url: "functions/filter-campaign-dashboard",
			type: "POST",
			data: {
				selectedFilter: selectedFilter,
				row: row
			},
			beforeSend:function(){
				$(".campaign-list").append("<div class='post'>Loading</div>");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				if(jsonStr[0].length > 0) {
					$(".campaign-list").empty();
					
					if(jsonStr[1] > 5) {
						$(".load-more").show();
						$(".load-more").html('<i class="fa-solid fa-chevron-down" style="font-size: 20px; cursor: pointer;"></i>');
					}
					else {
						$(".load-more").hide();
					}
					
					for(var i=0; i<jsonStr[0].length; i++) {
						var campaignID = jsonStr[0][i].campaignID;
						var campaignName = jsonStr[0][i].campaignName;
						var status = jsonStr[0][i].status;
						var totalParticipant = jsonStr[0][i].totalParticipant;
						var totalAccumulate = jsonStr[0][i].totalAccumulate;
						var category = jsonStr[0][i].category;
						var startDateF = jsonStr[0][i].startDateF;
						var endDateF = jsonStr[0][i].endDateF;
						var startTime = jsonStr[0][i].startTime;
						var endTime = jsonStr[0][i].endTime;
						var public = jsonStr[0][i].public;
						var userRole = jsonStr[0][i].userRole;
						var pageURL = jsonStr[0][i].pageURL;
						
						if(status == "open") {
							var elementStatus = `<div class="blinking-green"></div>`;
						}
						else {
							var elementStatus = `<div class="blinking-red"></div>`;
						}
						
						if(userRole == 3) {
							var elementAction = "";
						}
						else {
							if(category == "weekly") {
								var elementAction = `
									<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
										<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
											Action
										</button>
										
										<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
											<li>
												<a class="edit-campaign" id="${campaignID}"
												data-start-date="${startDateF}"
												data-end-date="${endDateF}"
												data-start-time="${startTime}"
												data-end-time="${endTime}"
												data-campaign-name="${campaignName}"
												data-category="${category}"
												data-public="${public}"
												>Edit</a>
											</li>
										</ul>
									</div>
								`;
							}
							else {
								if(status == "open") {
									if(totalParticipant > 0) {
										var elementAction = `
											<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
												<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
													Action
												</button>
												
												<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
													<li>
														<a class="edit-campaign" id="${campaignID}"
														data-start-date="${startDateF}"
														data-end-date="${endDateF}"
														data-start-time="${startTime}"
														data-end-time="${endTime}"
														data-campaign-name="${campaignName}"
														data-category="${category}"
														data-public="${public}"
														>Edit</a>
													</li>
												</ul>
											</div>
										`;
									}
									else {
										var elementAction = `
											<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
												<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
													Action
												</button>
												
												<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
													<li>
														<a class="edit-campaign" id="${campaignID}"
														data-start-date="${startDateF}"
														data-end-date="${endDateF}"
														data-start-time="${startTime}"
														data-end-time="${endTime}"
														data-campaign-name="${campaignName}"
														data-category="${category}"
														data-public="${public}"
														>Edit</a>
													</li>
													
													<li>
														<a class="deleteCampaign" id="${campaignID}">Delete</a>
													</li>
												</ul>
											</div>
										`;
									}
								}
								else {
									
									if(totalParticipant > 0) {
										var elementAction = "";
									}
									else {
										var elementAction = `
											<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
												<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
													Action
												</button>
												
												<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
													<li>
														<a class="edit-campaign" id="${campaignID}"
														data-start-date="${startDateF}"
														data-end-date="${endDateF}"
														data-start-time="${startTime}"
														data-end-time="${endTime}"
														data-campaign-name="${campaignName}"
														data-category="${category}"
														data-public="${public}"
														>Edit</a>
													</li>
													
													<li>
														<a class="deleteCampaign" id="${campaignID}">Delete</a>
													</li>
												</ul>
											</div>
										`;
									}
								}
							}
						}
						
						var element = `
							<div class="row post" id="post_${campaignID}">
							  <div class="col-xl-12 mb-4">
								<div class="card-info">
								  <div class="row text-info-ds">
									<div class="col-md-3 user-icon">
										<a href="../${pageURL}">
											<img src="../assets/images/user-icon.png" alt="" />
											${campaignName}
										</a>
									</div>
									<div class="col-md-2">
										<a href="../${pageURL}">${totalParticipant} Participant</a>
									</div>
									<div class="col-md-3"><a href="../${pageURL}">Time Left <span class="countdown-${campaignID}">0d 0h 0m 0s</span></a></div>
									<div class="col-md-1" style="display: flex; align-items: center;">
										<a href="../${pageURL}">${elementStatus}</a>
										
										<a href="../${pageURL}"><span style="padding-left: 5px;"> $${totalAccumulate}
										</span></a>
									</div>
									
									<div>${elementAction}</div>
								  </div>
								</div>
							  </div>
							</div>
						`;
						
						$(".campaign-list").append(element);
					}
				}
			}
		});
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
				url: 'functions/get-live-campaign-data',
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
								var status = jsonStr[i].status;
								var totalParticipant = jsonStr[i].totalParticipant;
								var totalAccumulate = jsonStr[i].totalAccumulate;
								var category = jsonStr[i].category;
								var startDateF = jsonStr[i].startDateF;
								var endDateF = jsonStr[i].endDateF;
								var startTime = jsonStr[i].startTime;
								var endTime = jsonStr[i].endTime;
								var public = jsonStr[i].public;
								var userRole = jsonStr[i].userRole;
								var pageURL = jsonStr[i].pageURL;
								
								if(status == "open") {
									var elementStatus = `<div class="blinking-green"></div>`;
								}
								else {
									var elementStatus = `<div class="blinking-red"></div>`;
								}
								
								if(userRole == 3) {
									var elementAction = "";
								}
								else {
									if(category == "weekly") {
										var elementAction = `
											<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
												<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
													Action
												</button>
												
												<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
													<li>
														<a class="edit-campaign" id="${campaignID}"
														data-start-date="${startDateF}"
														data-end-date="${endDateF}"
														data-start-time="${startTime}"
														data-end-time="${endTime}"
														data-campaign-name="${campaignName}"
														data-category="${category}"
														data-public="${public}"
														>Edit</a>
													</li>
												</ul>
											</div>
										`;
									}
									else {
										if(status == "open") {
											if(totalParticipant > 0) {
												var elementAction = `
													<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
														<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
															Action
														</button>
														
														<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
															<li>
																<a class="edit-campaign" id="${campaignID}"
																data-start-date="${startDateF}"
																data-end-date="${endDateF}"
																data-start-time="${startTime}"
																data-end-time="${endTime}"
																data-campaign-name="${campaignName}"
																data-category="${category}"
																data-public="${public}"
																>Edit</a>
															</li>
														</ul>
													</div>
												`;
											}
											else {
												var elementAction = `
													<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
														<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
															Action
														</button>
														
														<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
															<li>
																<a class="edit-campaign" id="${campaignID}"
																data-start-date="${startDateF}"
																data-end-date="${endDateF}"
																data-start-time="${startTime}"
																data-end-time="${endTime}"
																data-campaign-name="${campaignName}"
																data-category="${category}"
																data-public="${public}"
																>Edit</a>
															</li>
															
															<li>
																<a class="deleteCampaign" id="${campaignID}">Delete</a>
															</li>
														</ul>
													</div>
												`;
											}
										}
										else {
											
											if(totalParticipant > 0) {
												var elementAction = "";
											}
											else {
												var elementAction = `
													<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
														<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
															Action
														</button>
														
														<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
															<li>
																<a class="edit-campaign" id="${campaignID}"
																data-start-date="${startDateF}"
																data-end-date="${endDateF}"
																data-start-time="${startTime}"
																data-end-time="${endTime}"
																data-campaign-name="${campaignName}"
																data-category="${category}"
																data-public="${public}"
																>Edit</a>
															</li>
															
															<li>
																<a class="deleteCampaign" id="${campaignID}">Delete</a>
															</li>
														</ul>
													</div>
												`;
											}
										}
									}
								}
								
								var element = `
									<div class="row post" id="post_${campaignID}">
									  <div class="col-xl-12 mb-4">
										<div class="card-info">
										  <div class="row text-info-ds">
											<div class="col-md-3 user-icon">
												<a href="../${pageURL}">
													<img src="../assets/images/user-icon.png" alt="" />
													${campaignName}
												</a>
											</div>
											<div class="col-md-2">
												<a href="../${pageURL}">
													${totalParticipant} Participant
												</a>
											</div>
											<div class="col-md-3">
												<a href="../${pageURL}">Time Left <span class="countdown-${campaignID}">0d 0h 0m 0s</span>
												</a></div>
											<div class="col-md-1" style="display: flex; align-items: center;">
												<a href="../${pageURL}">
													${elementStatus}
													
													<span style="padding-left: 5px;"> $${totalAccumulate}
													</span>
												</a>
											</div>
											
											<div>${elementAction}</div>
										  </div>
										</div>
									  </div>
									</div>
								`;
								
								$(".campaign-list").append(element);
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
});