$(document).ready(function () {
	$('input[name="filter-dashboard"]:radio').change(function () {
		var selectedFilter = $(this).val();
		
		$.ajax({
			url: "functions/filter-raffles-done",
			type: "POST",
			data: {
				filter: selectedFilter
			},
			beforeSend:function(){
				$(".raffles-done").text("Loading");
				$(".weekly-raffles").text("Loading");
				$(".user-campaigns").text("Loading");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				$(".raffles-done").text(jsonStr.rafflesDone);
				$(".weekly-raffles").text(jsonStr.totalWeeklyRaffles);
				$(".user-campaigns").text(jsonStr.totalUserCampaign);
			}
		});
	});
	
	$('input[name="filter-weekly-raffle"]:radio').change(function () {
		$('#row').val(0);
		$(".load-more").hide();
		
		var selectedFilter = $(this).val();
		var row = Number($('#row').val());
		var rowperpage = 5;
		row = row + rowperpage;
		
		$("#filter").val(selectedFilter);
		
		$(".tbl-weekly-raffless tbody").empty();
		
		$.ajax({
			url: "functions/filter-weekly-raffle",
			type: "POST",
			data: {
				selectedFilter: selectedFilter,
				row: row
			},
			beforeSend: function() {
				$(".tbl-weekly-raffless tbody").append("<td colspan='5' align='center'>Loading</td>");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				if(jsonStr[1] > 2) {
					$(".load-more").show();
					$(".load-more").html('<i class="fa-solid fa-chevron-down"></i>');
				}
				else {
					$(".load-more").hide();
				}
				
				if(jsonStr[0].length > 0) {
					$(".tbl-weekly-raffless tbody").empty();
					
					for(var i=0; i<jsonStr[0].length; i++) {
						var campaignID = jsonStr[0][i].campaignID;
						var endDate = jsonStr[0][i].endDate;
						var weeklyNo = jsonStr[0][i].weeklyNo;
						var status = jsonStr[0][i].status;
						var totalPrice = "$" + jsonStr[0][i].totalPrice;
						var fee = jsonStr[0][i].fee;
						var winner = jsonStr[0][i].winner;
						var paymentOption = jsonStr[0][i].paymentOption;
						var total = jsonStr[0][i].total;
						var userRole = jsonStr[0][i].userRole;
						
						if(paymentOption == "Zelle") {
							var checkedZelle = "checked";
						}
						else {
							var checkedZelle = "";
						}
						
						if(paymentOption == "Paypal") {
							var checkedPaypal = "checked";
						}
						else {
							var checkedPaypal = "";
						}
						
						if(paymentOption != "Zelle" && paymentOption != "Paypal") {
							var checkedCustom = "checked";
							var elementInput = "";
						}
						else {
							var checkedCustom = "";
							var elementInput = "style='display: none;'";
						}
						
						if(status == "open") {
							var statusElement = `
								<div class="blinking-green"></div>
							`;
						}
						else {
							var statusElement = `
								<div class="blinking-red"></div>
							`;
						}
						
						if(winner == "" || (/^ *$/.test(winner))) {
							var payElement = '';
						}
						else {
							if(userRole == 3) {
								var payElement = `
									<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>
								`;
							}
							else {
								var payElement = `
									<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>

									<div class="filter-by-container filter-by-container${campaignID}">
										<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
											Pay Option
										</button>
										
										<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
											<li>
												<div class="label-radio"><label for="zelle${campaignID}"> Zelle $${fee}</label></div>
												<div class="input-radio"><input type="radio" class="radio-pay-option radio${campaignID}" name="pay-option${campaignID}" data-campaign-id="${campaignID}" data-pay="${fee}" id="zelle${campaignID}" value="Zelle" ${checkedZelle}/></div>
											</li>
											
											<li>
												<div class="label-radio"><label for="paypal${campaignID}"> Paypal $${fee}</label></div>
												<div class="input-radio"><input type="radio" class="radio-pay-option radio${campaignID}" name="pay-option${campaignID}" data-campaign-id="${campaignID}" data-pay="${fee}" id="paypal${campaignID}" value="Paypal" ${checkedPaypal}/></div>
											</li>
											
											<li>
												<div class="label-radio"><label for="custom${campaignID}"> Custom $${fee}</label></div>
												<div class="input-radio"><input type="radio" class="radio-pay-option radio${campaignID}" name="pay-option${campaignID}" data-campaign-id="${campaignID}" data-pay="${fee}" id="custom${campaignID}" value="Custom" ${checkedCustom}/></div>
												
												<div class="container-custom-text${campaignID}" ${elementInput}>
													<input type="text" class="custom-text custom-text${campaignID}" id="${campaignID}" data-pay="${fee}" style="width: 100%; box-sizing: border-box;" value="${paymentOption}"/>
												</div>
											</li>
										</ul>
									</div>
								`;
							}
						}
					
						var element = `
							<tr>
								<td align="center">
									<div style="display: inline-block; vertical-align: middle;">
										${statusElement}
									</div>
									
									<div style="display: inline-block; vertical-align: middle;"><span style="padding-left: 3px;">${weeklyNo}</span>
									</div>
								</td>
								<td>${endDate}</td>
								<td align="center">
									${totalPrice}
								</td>
								<td>
									${winner}
								</td>
								<td align="center">
									${payElement}
								</td>
							  </tr>
						`;
						
						$(".tbl-weekly-raffless tbody").append(element);
					}
				}
			}
		});
	});
	
	$('input[name="filter-campaign-user"]:radio').change(function () {
		$('#row-user').val(0);
		$(".load-more-user").hide();
		
		var selectedFilter = $(this).val();
		var row = Number($('#row-user').val());
		var rowperpage = 5;
		row = row + rowperpage;
		
		$("#filter-user").val(selectedFilter);
		
		$(".tbl-user-campaigns tbody").empty();
		
		$.ajax({
			url: "functions/filter-user-campaign",
			type: "POST",
			data: {
				selectedFilter: selectedFilter,
				row: row
			},
			beforeSend: function() {
				$(".tbl-user-campaigns tbody").append("<td colspan='5' align='center'>Loading</td>");
			},
			dataType: "JSON",
			success: function (jsonStr) {
				if(jsonStr[1] > 2) {
					$(".load-more-user").show();
					$(".load-more-user").html('<i class="fa-solid fa-chevron-down"></i>');
				}
				else {
					$(".load-more-user").hide();
				}
				
				if(jsonStr[0].length > 0) {
					$(".tbl-user-campaigns tbody").empty();
					
					for(var i=0; i<jsonStr[0].length; i++) {
						var campaignID = jsonStr[0][i].campaignID;
						var endDate = jsonStr[0][i].endDate;
						var status = jsonStr[0][i].status;
						var totalPrice = "$" + jsonStr[0][i].totalPrice;
						var fee = jsonStr[0][i].fee;
						var winner = jsonStr[0][i].winner;
						var paymentOption = jsonStr[0][i].paymentOption;
						var total = jsonStr[0][i].total;
						var userRole = jsonStr[0][i].userRole;
						var pageURL = jsonStr[0][i].pageURL;
						
						if(paymentOption == "Zelle") {
							var checkedZelle = "checked";
						}
						else {
							var checkedZelle = "";
						}
						
						if(paymentOption == "Paypal") {
							var checkedPaypal = "checked";
						}
						else {
							var checkedPaypal = "";
						}
						
						if(paymentOption != "Zelle" && paymentOption != "Paypal") {
							var checkedCustom = "checked";
							var elementInput = "";
						}
						else {
							var checkedCustom = "";
							var elementInput = "style='display: none;'";
						}
						
						if(status == "open") {
							var statusElement = `
								<div class="blinking-green"></div>
							`;
						}
						else {
							var statusElement = `
								<div class="blinking-red"></div>
							`;
						}
						
						if(winner == "" || (/^ *$/.test(winner))) {
							var payElement = '';
						}
						else {
							if(userRole == 3) {
								var payElement = `
									<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>
								`;
							}
							else {
								var payElement = `
									<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>

									<div class="filter-by-container filter-by-container${campaignID}">
										<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
											Pay Option
										</button>
										
										<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
											<li>
												<div class="label-radio"><label for="zelle${campaignID}"> Zelle $${fee}</label></div>
												<div class="input-radio"><input type="radio" class="radio-pay-option radio${campaignID}" name="pay-option${campaignID}" data-campaign-id="${campaignID}" data-pay="${fee}" id="zelle${campaignID}" value="Zelle" ${checkedZelle}/></div>
											</li>
											
											<li>
												<div class="label-radio"><label for="paypal${campaignID}"> Paypal $${fee}</label></div>
												<div class="input-radio"><input type="radio" class="radio-pay-option radio${campaignID}" name="pay-option${campaignID}" data-campaign-id="${campaignID}" data-pay="${fee}" id="paypal${campaignID}" value="Paypal" ${checkedPaypal}/></div>
											</li>
											
											<li>
												<div class="label-radio"><label for="custom${campaignID}"> Custom $${fee}</label></div>
												<div class="input-radio"><input type="radio" class="radio-pay-option radio${campaignID}" name="pay-option${campaignID}" data-campaign-id="${campaignID}" data-pay="${fee}" id="custom${campaignID}" value="Custom" ${checkedCustom}/></div>
												
												<div class="container-custom-text${campaignID}" ${elementInput}>
													<input type="text" class="custom-text custom-text${campaignID}" id="${campaignID}" data-pay="${fee}" style="width: 100%; box-sizing: border-box;" value="${paymentOption}"/>
												</div>
											</li>
										</ul>
									</div>
								`;
							}
						}
					
						var element = `
							<tr>
								<td align="center">
									<a href="../${pageURL}">
										<div style="display: inline-block; vertical-align: middle;">
											${statusElement}
										</div>
									</a>
								</td>
								<td><a href="../${pageURL}">${endDate}</a></td>
								<td align="center">
									<a href="../${pageURL}">${totalPrice}</a>
								</td>
								<td>
									<a href="../${pageURL}">${winner}</a>
								</td>
								<td align="center">
									${payElement}
								</td>
							  </tr>
						`;
						
						$(".tbl-user-campaigns tbody").append(element);
					}
				}
			}
		});
	});
	
	$(document).on('keydown', '.custom-text', function (e) {
		var key = e.which;
		if(key == 13) {
			var campaignID = $(this).attr('id');
			var thisVal = $(this).val();
			var pay = $(this).data("pay");
			
			if(thisVal != "") {
				$(".pay-option-text" + campaignID).text(thisVal + " $" + pay);
				
				$.ajax({
					url: 'functions/save-pay-option',
					type: 'post',
					data: {campaignID:campaignID, payOption: thisVal, pay: pay},
					dataType: "JSON",
					success: function(jsonStr){
					}
				});
			}
			
			return false;
		}
	});
	
	$(document).on('change', '.radio-pay-option', function() {
		var campaignID = $(this).data("campaign-id");
		var pay = $(this).data("pay");
		var payOption = $(this).val();
		
		if(payOption == "Custom") {
			$(".container-custom-text" + campaignID).show();
		}
		else {
			$(".container-custom-text" + campaignID).hide();
			$(".pay-option-text" + campaignID).text(payOption + " $" + pay);
			
			$.ajax({
				url: 'functions/save-pay-option',
				type: 'post',
				data: {campaignID:campaignID, payOption: payOption, pay: pay},
				dataType: "JSON",
				success: function(jsonStr){
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
				url: 'functions/get-weekly-raffle-data',
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
								var endDate = jsonStr[i].endDate;
								var weeklyNo = jsonStr[i].weeklyNo;
								var status = jsonStr[i].status;
								var totalPrice = "$" + jsonStr[i].totalPrice;
								var fee = jsonStr[i].fee;
								var winner = jsonStr[i].winner;
								var paymentOption = jsonStr[i].paymentOption;
								var total = jsonStr[i].total;
								var userRole = jsonStr[i].userRole;
								var pageURL = jsonStr[i].pageURL;
								
								if(paymentOption == "Zelle") {
									var checkedZelle = "checked";
								}
								else if(paymentOption == "Paypal") {
									var checkedPaypal = "checked";
								}
								else {
									var checkedCustom = "checked";
								}
								
								if(status == "open") {
									var statusElement = `
										<div class="blinking-green"></div>
									`;
								}
								else {
									var statusElement = `
										<div class="blinking-red"></div>
									`;
								}
							
								if(winner == "" || (/^ *$/.test(winner))) {
									var payElement = '';
								}
								else {
									if(userRole == 3) {
										var payElement = `
											<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>
										`;
									}
									else {
										var payElement = `
											<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>

											<div class="filter-by-container filter-by-container${campaignID}">
												<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
													Pay Option
												</button>
												
												<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
													<li>
														<div class="label-radio"><label for="zelle"> Zelle $${fee}</label></div>
														<div class="input-radio"><input type="radio" class="radio radio${campaignID}" name="pay-option" data-campaign-id="${campaignID}" data-pay="${fee}" id="zelle" value="Zelle" ${checkedZelle}/></div>
													</li>
													
													<li>
														<div class="label-radio"><label for="paypal"> Paypal $${fee}</label></div>
														<div class="input-radio"><input type="radio" class="radio radio${campaignID}" name="pay-option" data-campaign-id="${campaignID}" data-pay="${fee}" id="paypal" value="Paypal" ${checkedPaypal}/></div>
													</li>
													
													<li>
														<div class="label-radio"><label for="custom"> Custom $${fee}</label></div>
														<div class="input-radio"><input type="radio" class="radio radio${campaignID}" name="pay-option" data-campaign-id="${campaignID}" data-pay="${fee}" id="custom" value="Custom" ${checkedCustom}/></div>
														
														<div class="container-custom-text${campaignID}" style="display: none;">
															<input type="text" class="custom-text custom-text${campaignID}" id="${campaignID}" data-pay="${fee}" style="width: 100%; box-sizing: border-box;"/>
														</div>
													</li>
												</ul>
											</div>
										`;
									}
								}
							
								var element = `
									<tr>
										<td align="center">
											<div style="display: inline-block; vertical-align: middle;">
												${statusElement}
											</div>
											
											<div style="display: inline-block; vertical-align: middle;"><span style="padding-left: 3px;"><a href="../${pageURL}">${weeklyNo}</a></span>
											</div>
										</td>
										<td><a href="../${pageURL}">${endDate}</a></td>
										<td align="center">
											<a href="../${pageURL}">${totalPrice}</a>
										</td>
										<td>
											<a href="../${pageURL}">${winner}</a>
										</td>
										<td align="center">
											${payElement}
										</td>
									  </tr>
								`;
								
								$(".tbl-weekly-raffless tbody").append(element);
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
	
	$('.load-more-user').click(function(){
		var row = Number($('#row-user').val());
		var allcount = Number($('#all-user').val());
		var currentNo = Number($('#currentNo-user').val());
		var filter = $("#filter-user").val();
		var rowperpage = 5;
		row = row + rowperpage;

		if(row <= allcount){
			$("#row-user").val(row);

			$.ajax({
				url: 'functions/get-user-campaign-data',
				type: 'post',
				data: {row:row, currentNo: currentNo, filter: filter},
				dataType: "JSON",
				beforeSend:function(){
					$(".load-more-user").text("Loading...");
				},
				success: function(jsonStr){
					setTimeout(function() {
						if(jsonStr.length > 0) {
							for(var i=0; i<jsonStr.length; i++) {
								var number = jsonStr[i].number + i;
								var campaignID = jsonStr[i].campaignID;
								var endDate = jsonStr[i].endDate;
								var status = jsonStr[i].status;
								var totalPrice = "$" + jsonStr[i].totalPrice;
								var fee = jsonStr[i].fee;
								var winner = jsonStr[i].winner;
								var userRole = jsonStr[i].userRole;
								var pageURL = jsonStr[i].pageURL;
								
								if(jsonStr[i].paymentOption == null) {
									var paymentOption = "";
									var total = "";
								}
								else {
									var paymentOption = jsonStr[i].paymentOption;
									var total = jsonStr[i].total;
									
									if(paymentOption == "Zelle") {
										var checkedZelle = "checked";
									}
									else if(paymentOption == "Paypal") {
										var checkedPaypal = "checked";
									}
									else {
										var checkedCustom = "checked";
									}
								}
							
								if(status == "open") {
									var statusElement = `
										<div class="blinking-green"></div>
									`;
								}
								else {
									var statusElement = `
										<div class="blinking-red"></div>
									`;
								}
						
								if(winner == "" || (/^ *$/.test(winner))) {
									var payElement = '';
								}
								else {
									if(userRole == 3) {
										var payElement = `
											<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>
										`;
									}
									else {
										var payElement = `
											<span class="pay-option-text pay-option-text${campaignID}">${paymentOption} ${total}</span>

											<div class="filter-by-container filter-by-container${campaignID}">
												<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
													Pay Option
												</button>
												
												<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
													<li>
														<div class="label-radio"><label for="zelle"> Zelle $${fee}</label></div>
														<div class="input-radio"><input type="radio" class="radio radio${campaignID}" name="pay-option" data-campaign-id="${campaignID}" data-pay="${fee}" id="zelle" value="Zelle" ${checkedZelle}/></div>
													</li>
													
													<li>
														<div class="label-radio"><label for="paypal"> Paypal $${fee}</label></div>
														<div class="input-radio"><input type="radio" class="radio radio${campaignID}" name="pay-option" data-campaign-id="${campaignID}" data-pay="${fee}" id="paypal" value="Paypal" ${checkedPaypal}/></div>
													</li>
													
													<li>
														<div class="label-radio"><label for="custom"> Custom $${fee}</label></div>
														<div class="input-radio"><input type="radio" class="radio radio${campaignID}" name="pay-option" data-campaign-id="${campaignID}" data-pay="${fee}" id="custom" value="Custom" ${checkedCustom}/></div>
														
														<div class="container-custom-text${campaignID}" style="display: none;">
															<input type="text" class="custom-text custom-text${campaignID}" id="${campaignID}" data-pay="${fee}" style="width: 100%; box-sizing: border-box;"/>
														</div>
													</li>
												</ul>
											</div>
										`;
									}
								}
							
								var element = `
									<tr>
										<td align="center">
											<div style="display: inline-block; vertical-align: middle;">
												<a href="../${pageURL}">${statusElement}</a>
											</div>
											
											<div style="display: inline-block; vertical-align: middle;">
												<a href="../${pageURL}">${number}</a>
											</div>
										</td>
										<td><a href="../${pageURL}">${endDate}</a></td>
										<td align="center">
											<a href="../${pageURL}">${totalPrice}</a>
										</td>
										<td>
											<a href="../${pageURL}">${winner}</a>
										</td>
										<td align="center">
											${payElement}
										</td>
									  </tr>
								`;
								
								$(".tbl-user-campaigns tbody").append(element);
							}
						}
						
						var rowno = row + rowperpage;
						$('#currentNo-user').val(rowno);

						if(rowno > allcount){
							$('.load-more-user').hide();
						}else{
							$(".load-more-user").html('<i class="fa-solid fa-chevron-down"></i>');
						}
					}, 2000);
				}
			});
		}
	});
});