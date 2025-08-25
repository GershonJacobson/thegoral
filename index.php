<?php
error_reporting(0);
session_start();

require("config/session.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta content="IE=edge" http-equiv="X-UA-Compatible">
	<meta name="robots" content="index, follow">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Homepage - The Goral</title>
	<link href="assets/images/favicon.svg" rel="icon" type="image/x-icon">
	<link href="assets/css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet">
	<link href="assets/css/bootstrap-datetimepicker.css" rel="stylesheet">
	<link href="assets/font/fontawesome/css/all.min.css" rel="stylesheet">
	
	<script src="assets/js/jquery.min.js">
	</script>
	<script src="assets/js/bootstrap/js/bootstrap.bundle.min.js">
	</script>
	<script src="/assets/js/index.js">
	</script>
	<script src="assets/font/fontawesome/js/all.min.js">
	</script>
	<script src="../assets/js/jquery.creditCardValidator.js"></script>
	<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
	
	<script>
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
		function validateEmail(email) {
			var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
			return expr.test(email);
		};
		
		if( /Android/i.test(navigator.userAgent) ) {
			$(".google-pay").show();
		}
		else if(/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
			$(".apple-pay").show();
		}
		
		$(".purchase-amount1").addClass("box-bn-active");
		$(".summary-purchase").fadeIn();
		$(".total-ticket").text("1 ticket");
		$(".ticket-price").text("$2");
		$("#input-purchase").val(1);
		$("#input-price").val(2);
		
		$(".purchase-amount").click(function () {
			$(".summary-purchase").hide();
			var dataPurchase = $(this).data("purchase");
			var dataPrice = $(this).data("price");
			
			if(dataPurchase == 1) {
				$(".purchase-amount2").removeClass("box-bn-active");
				$(".purchase-amount1").addClass("box-bn-active");
				
				$("#input-purchase").val(1);
				$("#input-price").val(2);
			}
			else {
				$(".purchase-amount1").removeClass("box-bn-active");
				$(".purchase-amount2").addClass("box-bn-active");
				
				$("#input-purchase").val(2);
				$("#input-price").val(3);
			}
			
			$(".summary-purchase").fadeIn();
			$(".total-ticket").text(dataPurchase + " ticket");
			$(".ticket-price").text("$"+dataPrice);
		});
		
		$("#firstNameC, #lastnameC, #emailC, #phoneC").bind("keyup change", function() {
			var firstName = $("#firstNameC").val();
			var lastname = $("#lastnameC").val();
			var email = $("#emailC").val();
			var phone = $("#phoneC").val();
			var filter = /^\d*(?:\.\d{1,2})?$/;
			
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
			
			if(filter.test(phone)) {
				$(".phoneNotValid").hide();
			}
			else {
				$(".phoneNotValid").fadeIn();
			}
		});

		$(".btnBuyTicket").click(function () {
			var campaignID = $("#btnBuyNow").data("campaign-id");
			var firstName = $("#firstNameC").val();
			var lastname = $("#lastnameC").val();
			var email = $("#emailC").val();
			var phone = $("#phoneC").val();
			var filter = /^\d*(?:\.\d{1,2})?$/;
			var cardHolderName = $("#cardHolderName").val();
			var cardNumber = $("#cardNumber").val();
			var expiry = $("#expiry").val();
			var cvv = $("#cvv").val();
			var zip = $("#zip").val();
			var ccValid = $("#cc-valid").val();
			var purchaseAmount = $(".purchase-amount").data("purchase");
			var purchasePrice = $(".purchase-amount").data("price");
			var inputPurchase = $("#input-purchase").val();
			var inputPrice = $("#input-price").val();
			var campaignName = $(".text-dp-input").val();
			var saveCard = $("#saveCard").val();
			
			if(firstName == "" || lastname == "" || email == "" || phone == "" || cardHolderName == "" || cardNumber == "" || expiry == "" || cvv == "" || zip == "") {
				if(firstName == "") {
					$("#firstNameC").focus();
				}
				else if(lastname == "") {
					$("#lastnameC").focus();
				}
				else if(email == "") {
					$("#emailC").focus();
				}
				else if(phone == "") {
					$("#phoneC").focus();
				}
				else if(cardHolderName == "") {
					$("#cardHolderName").focus();
				}
				else if(cardNumber == "") {
					$("#cardNumber").focus();
				}
				else if(expiry == "") {
					$("#expiry").focus();
				}
				else if(cvv == "") {
					$("#cvv").focus();
				}
				else if(zip == "") {
					$("#zip").focus();
				}
			}
			else {
				if(validateEmail($("#emailC").val()) && filter.test(phone) && ccValid == 1) {
					var array = cardList.toString().split(",");

					for(var i in array){
						array[i]
					}
					
					if(jQuery.inArray(cardNumber, array) != -1) {
						$(".btnBuyTicket").text("Processing").prop('disabled', true);
						
						$.ajax({
							url: "functions/buy-ticket",
							type: "POST",
							data: {
								campaignID: campaignID,
								firstName: firstName,
								lastname: lastname,
								email: email,
								phone: phone,
								cardHolderName: cardHolderName,
								cardNumber: cardNumber,
								expiry: expiry,
								cvv: cvv,
								zip: zip,
								inputPurchase: inputPurchase,
								inputPrice: inputPrice,
								campaignName: campaignName
							},
							dataType: "JSON",
							success: function (jsonStr) {
								if(jsonStr.result == "OK") {
									$("#cardHolderName, #cardNumber, #expiry, #cvv, #zip, #cc-valid, #input-purchase, #input-price").val("");
									
									$('#checkoutModal').modal('toggle');
									$(".btn-filter").text("Saved Card");
									
									Swal.fire({
										width: '800px',
										html: '<div class="row"> <div class="col-md-6"> <div class="illus-8"> <img src="assets/images/illu-8.png" alt="" /> </div> </div> <div class="col-md-6"> <div class="row"> <div class="col-md"> <div class="text-tq"> Thanks! <p> Check your email to receive a receipt! <br /> And to see when the raffle will be drawn </p> </div> </div> </div> <div class="row"> <div class="col-md"> <div id="countdown-tq"> <div id="tiles-tq"><span>01</span><span>23</span><span>59</span><span>40</span></div> <div class="labels-tq"> <li>Days</li> <li>Hours</li> <li>Mins</li> <li>Secs</li> </div> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-ticnum"> Ticket Number <p>' + jsonStr.ticketNo + '</p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="btn-drawing-page"> <a href="<?php echo $pageURL; ?>">Go to Drawing Page</a> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-raffle">Share This Raffle</div> </div> </div> <div class="row"> <div class="col-md"> <div class="sosmed"> <img src="../assets/images/fb.png" alt="" /><img src="../assets/images/ig.png" alt="" /><img src="../assets/images/wa.png" alt="" /> </div> </div> </div> </div> </div>',
										showConfirmButton: false,
										allowOutsideClick: false,
										showCloseButton: true
									}).then((result) => {
										if (result.isConfirmed) {
										} 
									});
								}
								else {
									Swal.fire({
										text: "Campaign is closed/not exist!",
										icon: "error",
										confirmButtonText: "OK",
									});
								}
								
								$(".btnBuyTicket").text("Buy Ticket").prop('disabled', false);
							}
						});
					} else {
						Swal.fire({
						  title: 'Confirmation',
						  text: "Would you like to save this card for faster future checkouts?",
						  showDenyButton: true,
						  showCancelButton: true,
						  confirmButtonText: 'Yes',
						  denyButtonText: "No",
						}).then((result) => {
						  /* Read more about isConfirmed, isDenied below */
						  if (result.isConfirmed) {
							
							$("#saveCard").val("Y");
							
							$(".btnBuyTicket").text("Processing").prop('disabled', true);
							
							var saveCard = $("#saveCard").val();
						
						$.ajax({
							url: "functions/buy-ticket",
							type: "POST",
							data: {
								campaignID: campaignID,
								firstName: firstName,
								lastname: lastname,
								email: email,
								phone: phone,
								cardHolderName: cardHolderName,
								cardNumber: cardNumber,
								expiry: expiry,
								cvv: cvv,
								zip: zip,
								inputPurchase: inputPurchase,
								inputPrice: inputPrice,
								campaignName: campaignName,
								saveCard: saveCard
							},
							dataType: "JSON",
							success: function (jsonStr) {
								if(jsonStr.result == "OK") {
									$("#cardHolderName, #cardNumber, #expiry, #cvv, #zip, #cc-valid, #input-purchase, #input-price").val("");
									
									$('#checkoutModal').modal('toggle');
									$(".btn-filter").text("Saved Card");
									
									Swal.fire({
										width: '800px',
										html: '<div class="row"> <div class="col-md-6"> <div class="illus-8"> <img src="assets/images/illu-8.png" alt="" /> </div> </div> <div class="col-md-6"> <div class="row"> <div class="col-md"> <div class="text-tq"> Thanks! <p> Check your email to receive a receipt! <br /> And to see when the raffle will be drawn </p> </div> </div> </div> <div class="row"> <div class="col-md"> <div id="countdown-tq"> <div id="tiles-tq"><span>01</span><span>23</span><span>59</span><span>40</span></div> <div class="labels-tq"> <li>Days</li> <li>Hours</li> <li>Mins</li> <li>Secs</li> </div> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-ticnum"> Ticket Number <p>' + jsonStr.ticketNo + '</p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="btn-drawing-page"> <a href="<?php echo $pageURL; ?>">Go to Drawing Page</a> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-raffle">Share This Raffle</div> </div> </div> <div class="row"> <div class="col-md"> <div class="sosmed"> <img src="../assets/images/fb.png" alt="" /><img src="../assets/images/ig.png" alt="" /><img src="../assets/images/wa.png" alt="" /> </div> </div> </div> </div> </div>',
										showConfirmButton: false,
										allowOutsideClick: false,
										showCloseButton: true
									}).then((result) => {
										if (result.isConfirmed) {
										} 
									});
								}
								else {
									Swal.fire({
										text: "Campaign is closed/not exist!",
										icon: "error",
										confirmButtonText: "OK",
									});
								}
								
								$(".btnBuyTicket").text("Buy Ticket").prop('disabled', false);
							}
						});
							
						  } else if (result.isDenied) {
							
							$("#saveCard").val("");
							$(".btnBuyTicket").click();
						  }
						})
					} 
				}
				else {
					if(!validateEmail($("#emailC").val())) {
						$("#emailC").focus();
					}
					else if(!filter.test(phone)) {
						$("#phoneC").focus();
					}
					else if(ccValid == 0) {
						$("#cardNumber").focus();
					}
				}
			}
		});
		
		$(".cc").click(function () {
			$(".credit-card-option").fadeIn();
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

		  const $ccNumber = $('#cardNumber');
		  const $ccNumberClear = $('#cardNumber-clear');
		  const $ccNumberTest = $('.cardNumber-test');

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
		  
		$('#cvv, #zip, #cardNumber, #expiry').on("cut copy paste",function(e) {
		  e.preventDefault();
		});
		  
		$("#cvv, #zip, #cardNumber").keypress(function (e) {
		 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
		  return false;
		}
	   });
	});
	</script>
</head>
<body>
	<div class="header-ac-bg">
		<div class="container">
			<nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
				<div class="container-fluid">
					<a class="navbar-brand" href=""><img alt="logo" class="logo" src="assets/images/logo.svg"></a> <button aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler collapsed" data-bs-target="#navbarNav" data-bs-toggle="collapse" type="button"><span class="icon-bar top-bar"></span> <span class="icon-bar middle-bar"></span> <span class="icon-bar bottom-bar"></span></button>
					
					<?php require("header.php"); ?>
				</div>
			</nav>
			
			<?php
			$qWeekly = mysqli_query($con, "
				SELECT campaign_id, campaign_name, DATE_FORMAT(end_date,'%M %d') AS end_date_f, end_date, status FROM tbl_campaign WHERE category = 'weekly' AND status = 'open'
			");
			$dWeekly = mysqli_fetch_array($qWeekly);
			
			$campaignID = $dWeekly['campaign_id'];
			$endDate = $dWeekly['end_date'];
			$endDateF = $dWeekly['end_date_f'];
			$status = $dWeekly['status'];
			?>
			
			<div class="row">
				<div class="text-dp"><?php echo $dWeekly['campaign_name']; ?></div>
			
				<div class="weeks-pot" style="width: 200px;">
					<div class="blinking-green" style="margin-right: 10px;"></div>
				
					<?php
					$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
					
					echo mysqli_num_rows($qAccumulateParticipant);
					?>
					Participant
				</div>
			</div>
			<div class="row">
				<h1>
				$<?php
				$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
				$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
				
				echo $dAccumulateTicket['total_accumulate'];
				?>
				</h1>
			</div>
			<div style="border: 0.568125px solid #707070"></div>
			<div class="row">
				<div class="col-md" style="display: flex; flex-flow: column; align-items: center;">
					<div class="title-draw" style="color:#fff;">Draw in :</div>
				
					<div id="countdown" class="countdown-<?php echo $campaignID; ?>">
					<div id="tiles"><span>0</span><span>0</span><span>0</span><span>0</span></div>
					<div class="labels"><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>
					</div>
				</div>
				<div class="col-md text-center" style="margin-bottom: 30px">
					<div class="illus-1">
						<!-- <img alt="" src="assets/animation/illu-1.png"> -->
						
						<lottie-player src="assets/animation/Anim-01.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
				</div>
			</div>
			<div class="row">
				<?php
				if($status == "open") {
				?>
					<button class="btnBuyNow" data-target="#checkoutModal" data-toggle="modal" id="btnBuyNow" type="button" data-campaign-id="<?php echo $campaignID; ?>">Buy Ticket Now</button>
				<?php
				}
				?>
			</div>
			
			<script>
			var countdowns = [{
				campaignID: "<?php echo $campaignID; ?>",
				countdownDate: new Date("<?php echo $endDate; ?>".replace(" ", "T")).getTime()
			}];
			
			$(document).ready(function () {
				var timer = setInterval(function() {
					// Get todays date and time
					
					var d = new Date(new Date().toLocaleString("en-US", {timeZone: "America/New_York"}));
					var now = d.getTime();
					
					var index = countdowns.length - 1;
					// we have to loop backwards since we will be removing
					// countdowns when they are finished
					while (index >= 0) {
						var countdown = countdowns[index];
						// Find the distance between now and the count down date
						
						var distance = countdown.countdownDate - now;
						
						// Time calculations for days, hours, minutes and seconds
						var days = Math.floor(distance / (1000 * 60 * 60 * 24));
						var hours = Math.floor(
							(distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
						var minutes = Math.floor(
							(distance % (1000 * 60 * 60)) / (1000 * 60));
						var seconds = Math.floor((distance % (1000 * 60)) / 1000);
						//var timerElement = document.getElementById("race" + countdown.id);
						
						
						var abc = "<div id='tiles'><span>" + days + "</span><span>" + hours + "</span><span>" + minutes + "</span><span>" + seconds + "</span></div><div class='labels'><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>";
						
						var abc2 = "<div id='tiles-tq'><span>" + days + "</span><span>" + hours + "</span><span>" + minutes + "</span><span>" + seconds + "</span></div><div class='labels-tq'><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>";
						
						$(".countdown-" + countdown.campaignID).html(abc);
						$("#countdown-tq").html(abc2);
						// If the count down is over, write some text
						if (distance < 0) {
							//timerElement.innerHTML = "EXPIRED";
							// this timer is done, remove it
							$(".countdown-" + countdown.campaignID).text("DRAW COMPLETED");
							$("#countdown-tq").html("DRAW COMPLETED");
							
							clearInterval(timer);
						} else {
							//timerElement.innerHTML =  hours + "h " + minutes + "m " + seconds + "s ";
						}
						index -= 1;
					}
					
					// if all countdowns have finished, stop timer
					if (countdowns.length < 1) {
						clearInterval(timer);
					}
				}, 1000);
			});
			</script>
		</div>
	</div>
	<div class="section-two">
		<div class="container">
			<div class="row">
				<div class="title">
					3 simple steps !
				</div>
			</div>
			<div class="row">
				<div class="col-md">
					<div class="illus-2 text-center">
						<!-- <img alt="" src="assets/images/illu-2.png"> -->
						<lottie-player src="assets/animation/Anim-02.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
				</div>
				<div class="col-md">
					<div class="text-subtitle">
						1. Buy
						<p>Purchase a ticket and receive a ticket number for a chance to take home half of the pot.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section-three">
		<div class="container">
			<div class="row row-check">
				<div class="col-md">
					<div class="text-subtitle">
						2. Check
						<p>At the end of the countdown, we will announce the winning number.</p>
					</div>
				</div>
				<div class="col-md">
					<div class="illus-3 text-center">
						<lottie-player src="assets/animation/Anim-03.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section-four">
		<div class="container">
			<div class="row">
				<div class="col-md">
					<div class="illus-4 text-center">
						<lottie-player src="assets/animation/Anim-04.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
				</div>
				<div class="col-md">
					<div class="text-subtitle">
						3. Collect
						<p>If you are the winner, you can easily collect your prize through our platform. Congratulations, you've won!</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section-five">
		<div class="container">
			<div class="row">
				<div class="col-md">
					<div class="illus-5 text-center">
						<lottie-player src="assets/animation/Anim-05.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
				</div>
				<div class="col-md">
					<div class="text-subtitle" style="margin-top: 10px">
						Last 5 weekly raffles
					</div>
					
					<div style="margin-top: 15px;">
						<table class="tbl-raffle">
							<tr style="font-size:12px; font-family:Suwannaphum;">
								<th></th>
								<th></th>
								<th>Date</th>
								<th>Participants</th>
								<th>Pot</th>
							</tr>
							<?php
							$i = 1;
							$qWeeklyRaffles = mysqli_query($con, "
								SELECT
								tbl_campaign.campaign_id,
								tbl_campaign.first_name,
								tbl_campaign.last_name,
								tbl_campaign.email_address,
								tbl_campaign.phone,
								tbl_campaign.page_url,
								tbl_campaign.campaign_name,
								tbl_campaign.public,
								tbl_campaign.status,
								DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
								DATE_FORMAT(tbl_campaign.start_date,'%m/%d/%Y') AS start_date_f,
								DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date,
								DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date_f,
								DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
								DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
								COALESCE(SUM(tbl_ticket.total_price),0) as rating
								FROM tbl_campaign
								LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
								WHERE
								tbl_campaign.category = 'weekly' AND
								tbl_ticket.total_price != 0
								GROUP BY tbl_campaign.campaign_id 
								ORDER BY tbl_campaign.status DESC, rating DESC limit 5
							");
							
							while($dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles)) {
								$campaignID = $dWeeklyRaffles['campaign_id'];
								$endDate = $dWeeklyRaffles['end_date'];
								$status = $dWeeklyRaffles['status'];
								?>
								<tr style="border-top: 1px solid #646464;">
								<td><div class="text-number"><?php echo $i; ?></div></td>
								<td><img style="width:28px" alt="" src="assets/images/user-icon.png"></td>
								<td><span class="text-date"><?php echo $dWeeklyRaffles['end_date']; ?></span></td>
								
								<td><span class="text-date">
									<?php
									$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $dWeeklyRaffles['campaign_id'] . "'");
									
									echo mysqli_num_rows($qAccumulateParticipant);
									?>
								</span></td>
								<td>
									<div class="text-price">
										$<?php
										$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $dWeeklyRaffles['campaign_id'] . "'");
										$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
										
										echo $dAccumulateTicket['total_accumulate'];
										?>
									</div>
								</td>
								</tr>
							<?php
							$i++;
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section-six text-center">
		<div class="title">
			Make your own campaign
		</div>
		
		<?php
		if($getUserID != "") {
		?>
			<button data-toggle="modal" data-target="#createCampaignModal" id="btnGetStarted2" class="btn-get-started">Get Started</button>
		<?php
		}
		else {
		?>
			<a href="sign-in" class="btn-get-started" style="text-decoration:none">Get Started</a>
		<?php
		}
		?>
		
	</div>
	<div class="footer">
		<div class="container">
			<div class="row">
				<div class="social-media text-center">
					<span><img alt="" src="assets/images/logo.svg"></span> <i class="fa-brands fa-facebook-f"></i> <i class="fa-brands fa-twitter"></i> <i class="fa-brands fa-linkedin-in"></i> <i class="fa-brands fa-instagram"></i>
				</div>
			</div>
			<div class="row">
				<div class="menu-footer">
					<a class="active" href="http://thegoral.com">Home</a> <a href="live-campaign">Live Campaigns</a> <a href="all-campaign">All Campaigns</a>
				</div>
			</div>
			<div class="row">
				<div class="text-desc">
					Lörem ipsum od ohet dilogi. Bell trabel, samuligt, ohöbel utom diska. Jinesade bel när feras redorade i belogi. FAR paratyp<br>
					i muvåning, och pesask vyfisat. Viktiga poddradio har un mad och inde.
				</div>
			</div>
			<div class="row">
				<div class="copyright">
					© 2022 The Goral
				</div>
			</div>
		</div>
	</div>
	
	<div aria-hidden="true" aria-labelledby="checkoutModalLabel" class="modal fade bd-example-modal-lg" id="checkoutModal" role="dialog" tabindex="-1">
		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<div class="row">
						<div class="col-md modal-title">
							Buy Now<button class='closeBtn'><i class="fa-solid fa-circle-xmark"></i></button>
						</div>
					</div>
					<div class="row">
						<?php
						$qWeekly = mysqli_query($con, "
							SELECT campaign_id, campaign_name, DATE_FORMAT(end_date,'%M %d') AS end_date_f, end_date, status FROM tbl_campaign WHERE category = 'weekly' AND status = 'open'
						");
						$dWeekly = mysqli_fetch_array($qWeekly);
						
						$campaignIDWeekly = $dWeekly['campaign_id'];
						
						$qTicketPrice = mysqli_query($con, "SELECT * FROM tbl_ticket_price WHERE campaignid_fk = '" . $campaignIDWeekly . "'");
						if(mysqli_num_rows($qTicketPrice) > 0) {
							$dTicketPrice = mysqli_fetch_array($qTicketPrice);
							
							$price1Ticket = $dTicketPrice['1ticket_price'];
							$price2Ticket = $dTicketPrice['2ticket_price'];
						}
						else {
							$price1Ticket = 2;
							$price2Ticket = 3;
						}
						?>
					
						<div class="col-md col-50-bn">
							<div id="box-bn" class="box-bn purchase-amount purchase-amount1" data-purchase="1" data-price="<?php echo $price1Ticket; ?>">
								<h5>1 Ticket for</h5>
								<h3>$<?php echo $price1Ticket; ?></h3>
							</div>
							
							<input type="hidden" id="input-purchase" value=""/>
							<input type="hidden" id="input-price" value=""/>
						</div>
						<div class="col-md col-50-bn">
							<div id="box-bn-b" class="box-bn box-bn-b purchase-amount purchase-amount2" data-purchase="2" data-price="<?php echo $price2Ticket; ?>">
								<h5>2 Ticket for</h5>
								<h3>$<?php echo $price2Ticket; ?></h3>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="firstNameC">First Name<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="firstNameC" type="text" value="<?php echo $getFirstName; ?>">
							</div>
						</div>
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="lastnameC">Last Name<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="lastnameC" type="text" value="<?php echo $getLastName; ?>">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="emailC">Email<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="emailC" type="text" value="<?php echo $getEmailAddress; ?>">
								
								<div class="emailNotValid" style="color: red; display: none;">Email is not valid</div>
							</div>
						</div>
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="phoneC">Phone<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="phoneC" type="text" value="<?php echo $getPhone; ?>">
								
								<div class="phoneNotValid" style="color: red; display: none;">Phone is not valid</div>
							</div>
						</div>
					</div>
					
					<script>
					window.cardList = [];
					</script>
					
					<div class="row mb-1 mt-4">
						<div class="col-md text-pay">
							Payment Method
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md">
							<div class="filter-by-container" style="text-align: right;">
								<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
									Saved Card
								</button>
								
								<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; width: 80px;">
									<?php
									$qCard = mysqli_query($con, "SELECT card_id, card_name, card_number AS card_number2, RIGHT(card_number,4) as card_number, expired, cvv, zip FROM tbl_card WHERE userid_fk = '$getUserID' ORDER BY card_number ASC");
									while($dCard = mysqli_fetch_array($qCard)) {
										array_push($cardList, (object)[
											"cardNumber" => $cardList['card_number']
										]);
										?>
										<script>
										cardList.push(<?php echo $dCard['card_number2']; ?>);
										</script>
										
										<li>
											<div class="label-radio card-list"
											id="<?php echo $dCard['card_id']; ?>"
											data-card-number="<?php echo $dCard['card_number']; ?>"
											data-card-number2="<?php echo $dCard['card_number2']; ?>"
											data-card-name="<?php echo $dCard['card_name']; ?>"
											data-card-expired="<?php echo $dCard['expired']; ?>"
											data-card-cvv="<?php echo $dCard['cvv']; ?>"
											data-zip="<?php echo $dCard['zip']; ?>"
											><label for="pmmtl"> Card <?php echo $dCard['card_number']; ?></label></div>
											<div class="input-radio"><input type="radio" class="radio" name="filter" id="pmmtl" value="pmmtl" style="display: none;"/></div>
										</li>
									<?php
									}
									?>
								</ul>
							</div>
						</div>
					</div>
					<div class="row" style="text-align: center; justify-content: center;">
						<div class="col-md col-25-bn cc">
							<div class="box-pay mb-4"><img alt="" src="assets/images/cc.png"></div>
						</div>
						<div class="col-md col-25-bn apple-pay" style="display: none;">
							<div class="box-pay"><img alt="" src="assets/images/apple-pay.png"></div>
						</div>
						<div class="col-md col-25-bn google-pay" style="display: none;">
							<div class="box-pay"><img alt="" src="assets/images/gpay.png"></div>
						</div>
					</div>
					<div class="row credit-card-option">
						<div class="col-md-6 col-50-bn">
							<div class="form-group">
								<label for="cardHolderName">Card Holder Name<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="cardHolderName" placeholder="Jack" type="text">
							</div>
						</div>
						<div class="col-md-6 col-50-bn">
							<div class="form-group">
								<label for="cardNumber">Card Number<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="cardNumber" placeholder="1234" type="text">
								
								<input type="hidden" id="cc-valid" value="0"/>
								
								<div class="ccNotValid" style="color: red; display: none; font-size: 12px;">Number does not exist</div>
							</div>
						</div>
						<div class="col-md-3 col-25-bn">
							<div class="form-group">
								<label for="expiry">Expiry<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="expiry" placeholder="MM/YY" type="text" maxlength="5" onkeyup="formatString(event);">
							</div>
						</div>
						<div class="col-md-3 col-25-bn">
							<div class="form-group">
								<label for="cvv">CVV<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="cvv" placeholder="CVV" type="text" maxlength="5">
							</div>
						</div>
						<div class="col-md-3 col-50-bn">
							<div class="form-group">
								<label for="zip">Zip<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="zip" type="text">
							</div>
						</div>
					</div>
					
					<div class="summary-purchase" style="display: none;">
						<div class="row">
							<div class="col-md text-pay mt-4 mb-4">
								Summary
							</div>
						</div>
						<div class="row">
							<div class="col-md text-sum col-50-bn total-ticket">
								0 Ticket
							</div>
							<div class="col-md text-sum col-50-bn ticket-price">
								$0
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md mt-4 mb-2 text-center">
							<input type="hidden" id="saveCard"/>
							<button class="btnBuyTicket" type="submit" style="font-weight: bold;">Buy Ticket</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>