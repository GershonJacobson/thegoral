<?php
session_start();

require("config/session.php");

$pageURL = $_GET['campaign'];

$qChkData = mysqli_query($con, "
	SELECT * FROM tbl_campaign
	WHERE
	(page_url = '" . $pageURL . "' and status = 'open') OR
	(campaign_id = '" . $pageURL . "' AND status = 'closed') OR
	(page_url = '" . $pageURL . "' AND category = 'weekly' and (status = 'open' OR status = 'closed'))
");
if(mysqli_num_rows($qChkData) > 0) {
	
$data = mysqli_fetch_array($qChkData);

$campaignID = $data['campaign_id'];
$campaignName = $data['campaign_name'];
$endDate = $data['end_date'];
$public = $data['public'];
$status = $data['status'];
$pageURL = $data['page_url'];

$qWinningNo =  mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "' AND win = 'Y'");
$dWinningNo = mysqli_fetch_array($qWinningNo);
$winningTicketNo = $dWinningNo['ticket_no'];
?>
	<!DOCTYPE html>
	<html lang="en">
	  <head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Drawing - The Goral</title>
		<link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

		<link
		  rel="stylesheet"
		  href="../assets/css/bootstrap/css/bootstrap.min.css"
		/>
		<link rel="stylesheet" href="../assets/css/style.css" />
		<link rel="stylesheet" href="../assets/font/fontawesome/css/all.min.css" />
		<style>
		.swal2-html-container {
			overflow: hidden !important;
		}
		</style>
	
		<script src="../assets/js/jquery.min.js"></script>
		<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="../assets/js/index.js"></script>
		<script src="../assets/font/fontawesome/js/all.min.js"></script>
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
				var defaultPaymentMethod = $("#default-payment-method").val();
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
								campaignName: campaignName,
								defaultPaymentMethod: defaultPaymentMethod,
								saveCard: saveCard
							},
							dataType: "JSON",
							success: function (jsonStr) {
								if(jsonStr.result == "OK") {
									$("#campaignID, #firstNameC, #lastnameC, #emailC, #phoneC, #cardHolderName, #cardNumber, #expiry, #cvv, #zip, #cc-valid, #input-purchase, #input-price").val("");
									
									$('#checkoutModal').modal('toggle');
									
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
			
			
			$("#saveCard").change(function(){
				var cardName = $(this).find(':selected').data('card-name');
				var cardNumber = $(this).find(':selected').data('card-number');
				var cardExpired = $(this).find(':selected').data('card-expired');
				var cardCVV = $(this).find(':selected').data('card-cvv');
				var zip = $(this).find(':selected').data('zip');
				
				if(cardNumber != "") {
					$("#cardHolderName").val(cardName);
					$("#cardNumber").val(cardNumber);
					$("#expiry").val(cardExpired);
					$("#cvv").val(cardCVV);
					$("#zip").val(zip);
					$("#cc-valid").val(1);
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
				<a class="navbar-brand" href="/">
				  <img class="logo" src="../assets/images/logo.png" alt="logo" />
				</a>
				<button
				  class="navbar-toggler collapsed"
				  type="button"
				  data-bs-toggle="collapse"
				  data-bs-target="#navbarNav"
				  aria-controls="navbarNav"
				  aria-expanded="false"
				  aria-label="Toggle navigation"
				>
				  <span class="icon-bar top-bar"></span>
				  <span class="icon-bar middle-bar"></span>
				  <span class="icon-bar bottom-bar"></span>
				</button>
				
				<?php require("header.php"); ?>
			  </div>
			</nav>
			<div class="row">
				<?php
				if($public == 0) {
				?>
					<div class="flag-private">Private</div>
				<?php
				}
				?>
			  <div class="text-dp"><?php echo $campaignName; ?></div>
			  <input type="hidden" class="text-dp-input" value="<?php echo $campaignName; ?>"/>
			  <div class="weeks-pot" style="width: 200px">
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
			  <div class="row">
			  <div class="title-draw" style="color:#fff;">Draw in :</div>
			</div>
			  <div id="countdown" class="countdown-<?php echo $data['campaign_id']; ?>">
					<div id="tiles"><span>0</span><span>0</span><span>0</span><span>0</span></div>
					<div class="labels"><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>
				</div>
			  </div>
			  <div class="col-md" style="margin-bottom: 25px">
				<div class="illus-6 justify-content-center">
					<lottie-player src="assets/animation/Anim-06.json" background="Transparent" speed="1" loop autoplay></lottie-player>
				</div>
			  </div>
			</div>
		  </div>
		</div>
		<div class="content-draw">
		  <div class="container">
			
			<div class="row mb-1">
			  <div class="box-draw justify-content-center">
				<script>
				var countdowns = [{
					campaignID: "<?php echo $campaignID; ?>",
					countdownDate: new Date("<?php echo $endDate; ?>".replace(" ", "T")).getTime()
				}];
				</script>
				<!-- <div class="box-win-y">
				  <div class="box-win-number-y">0</div>
				  <div class="box-win-number-y">0</div>
				  <div class="box-win-number-y">0</div>
				</div> -->
				<script>
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
			  
			  <div class="justify-content-center" style="text-align: center; margin-top: 30px;">
				<?php
				if($status == "open") {
				?>
					<button class="btnBuyNow" data-target="#checkoutModal" data-toggle="modal" id="btnBuyNow" type="button" data-campaign-id="<?php echo $campaignID; ?>">Buy Ticket Now</button>
				<?php
				}
				?>
			  </div>
			</div>
			<div class="row mt-1 justify-content-center">
			  <div class="box-win">
				<h5>Winning number is :</h5>
				<div class="box-win-x">
					<?php
					$a = str_split($winningTicketNo, 1);
					
					for($i=0; $i<count($a); $i++) {
					?>
						<div class="box-win-number"><?php echo $a[$i]; ?></div>
					<?php
					}
					?>
				</div>
			  </div>
			</div>
		  </div>
		</div>
		<div class="content-draw-b">
		  <div class="container">
			<div class="row">
			  <div class="col-md">
				<h5 style="padding-bottom: 10px;">
				  Thank you all for joining, If you’re the winner, check you’re
				  email to claim the money <span class="myOS"></span>
				</h5>
				<a href="/" class="btnBackHome" style="text-decoration: none;">
				  Go back to home page
				</a>
			  </div>
			  <div class="col-md">
				<div class="illus-7">
					<lottie-player src="assets/animation/Anim-07.json" background="Transparent" speed="1" loop autoplay></lottie-player>
				</div>
			  </div>
			</div>
		  </div>
		</div>
		<div class="section-six text-center" style="margin-bottom: 40px;">
			<div class="title">Make your own campaign</div>
			
			<?php
			if($getUserID != "") {
			?>
				<button data-toggle="modal" data-target="#createCampaignModal" id="btnGetStarted5" class="btn-get-started">Get Started</button>
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
				<span><img src="../assets/images/logo.png" alt="" /></span>
				<i class="fa-brands fa-facebook-f"></i>
				<i class="fa-brands fa-twitter"></i>
				<i class="fa-brands fa-linkedin-in"></i>
				<i class="fa-brands fa-instagram"></i>
			  </div>
			</div>
			<div class="row">
			  <div class="menu-footer">
				<a class="active" href="/">Home</a>
				<a href="live-campaign">Live Campaigns</a>
				<a href="all-campaign">All Campaigns</a>
			  </div>
			</div>
			<div class="row">
			  <div class="text-desc">
				Lörem ipsum od ohet dilogi. Bell trabel, samuligt, ohöbel utom
				diska. Jinesade bel när feras redorade i belogi. FAR paratyp <br />
				i muvåning, och pesask vyfisat. Viktiga poddradio har un mad och
				inde.
			  </div>
			</div>
			<div class="row">
			  <div class="copyright">© 2022 The Goral</div>
			</div>
		  </div>
		</div>
		
		<div aria-hidden="true" aria-labelledby="checkoutModalLabel" class="modal fade bd-example-modal-lg" id="checkoutModal" role="dialog" tabindex="-1">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-body">
						<div class="row">
							<div class="col-md modal-title">
								Buy Now<button class='closeBtn'><i class="fa-solid fa-circle-xmark"></i></button>
							</div>
						</div>
						<div class="row">
							<?php
							$qTicketPrice = mysqli_query($con, "SELECT * FROM tbl_ticket_price WHERE campaignid_fk = '" . $campaignID . "'");
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
						
						
						<div class="row mb-1 mt-4">
							<div class="col-md text-pay">
								Payment Method
							</div>
						</div>
						
						<?php
						if($getUserID != "") {
						?>
							<div class="row mb-3">
								<div class="col-md">
									<div class="form-group">
										<select id="saveCard">
											<option value="save-card">
												Save Card
											</option>
											<option value="dont-save-card">
												Don't Save Card
											</option>
											
											<?php
											$qCard = mysqli_query($con, "SELECT card_id, card_name, card_number AS card_number2, RIGHT(card_number,4) as card_number, expired, cvv, zip FROM tbl_card WHERE userid_fk = '$getUserID' ORDER BY card_number ASC");
											while($dCard = mysqli_fetch_array($qCard)) {
											?>
												<option value="<?php echo $dCard['card_id']; ?>"
													data-card-name="<?php echo $dCard['card_name']; ?>"
													data-card-number="<?php echo $dCard['card_number2']; ?>"
													data-card-expired="<?php echo $dCard['expired']; ?>"
													data-card-cvv="<?php echo $dCard['cvv']; ?>"
													data-zip="<?php echo $dCard['zip']; ?>"
												>Card <?php echo $dCard['card_number']; ?></option>
											<?php
											}
											?>
										</select>
									</div>
								</div>
							</div>
						
							<input type="hidden" id="default-payment-method" value="Credit Card"/>
						<?php
						}
						?>
						
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
								<button class="btnBuyTicket" type="submit" style="font-weight: bold;">Buy Ticket</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	  </body>
	  <script>
		$("#box-bn").click(function(){

			$("#box-bn").css({"background-image": "url(../assets/images/bg-buynow-fill-2.png)"});

			$("#box-bn-b").css({"background-image": "url(../assets/images/bg-buynow-1.png)"});

		});

		$("#box-bn-b").click(function(){

			$("#box-bn-b").css({"background-image": "url(../assets/images/bg-buynow-fill-1.png)"});

			$("#box-bn").css({"background-image": "url(../assets/images/bg-buynow-2.png)"});

		});
        </script>
	</html>
<?php
}
else {
	header("Location: ../404");
}
?>