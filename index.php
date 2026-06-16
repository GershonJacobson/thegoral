<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

require("config/db.php");
require("config/session.php");
require("config/payarc.php");

// The pot currently sitting at /drawing — either the live (open) one, or a
// just-finished one in its 30-min reveal window (closed but still at /drawing).
// Used by the hero, checkout modal and emails.
$qWeekly = mysqli_query($con, "
	SELECT campaign_id, campaign_name, page_url, DATE_FORMAT(end_date,'%M %d') AS end_date_f, end_date, status FROM tbl_campaign WHERE category = 'weekly' AND page_url = 'drawing' AND (status = 'open' OR status = 'closed') ORDER BY (status = 'open') DESC LIMIT 1
");
$dWeekly = mysqli_fetch_array($qWeekly);

$campaignID = $dWeekly['campaign_id'] ?? '';
$campaignName = $dWeekly['campaign_name'] ?? 'Weeks Pot';
$endDate = $dWeekly['end_date'] ?? '';
$endDateF = $dWeekly['end_date_f'] ?? '';
$status = $dWeekly['status'] ?? '';
$pageURL = $dWeekly['page_url'] ?? 'drawing';

// The countdown can hit zero before the cron flips status to "closed". Once the
// draw time has passed, buying is pointless — send people to the drawing page.
$drawPassed = ($endDate !== '' && strtotime($endDate) <= time());
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
	<!-- Homepage revamp layer (overrides style.css; homepage only) -->
	<link href="assets/css/home.css" rel="stylesheet">

	<script src="assets/js/jquery.min.js">
	</script>
	<script src="assets/js/bootstrap/js/bootstrap.bundle.min.js">
	</script>
	<script src="/assets/js/index.js">
	</script>
	<script src="assets/font/fontawesome/js/all.min.js">
	</script>
	<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
	<!-- PayArc hosted-fields tokenizer (PCI-safe). Card data never touches our server. -->
	<script src="<?php echo GORAL_PAYARC_IFRAME_JS; ?>" defer></script>
	<style id="payarc-styles">
		.payarc-fields { width: 100%; }
		.payarc-row-2 { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
		.payarc-row-2 > div { flex: 1; min-width: 90px; min-height: 48px; overflow: hidden; }
		#card-token-container > div { width: 100%; min-height: 48px; }
		/* PayArc injects its iframes at a fixed 300x150 — constrain them or the
		   modal overflows horizontally on phones and gaps vertically. */
		#card-token-container iframe { width: 100% !important; max-width: 100% !important; height: 48px !important; display: block; }
		#checkoutModal .modal-body { overflow-x: hidden; }
		.payarc-input { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
		.payarc-label { display: none; }
		.payarc-container { background: transparent; }
		.payarc-input-error { border-color: #d9534f; color: #d9534f; }
		/* PayArc marks fields "success" just for being filled in — misleading. Keep neutral. */
		.payarc-input-success { border-color: #ced4da; }
	</style>
	
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
			// 7-15 digits, allowing +, spaces, dots, dashes and parentheses
			var filter = /^(?=(?:\D*\d){7,15}\D*$)[+\d\s().-]+$/;

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

			if(phone == "" || filter.test(phone)) {
				$(".phoneNotValid").hide();
			}
			else {
				$(".phoneNotValid").fadeIn();
			}
		});

		// ---- PayArc hosted-fields checkout (card data tokenized in-browser) ----
		var PAYARC_CLIENT_ID = "<?php echo htmlspecialchars(GORAL_PAYARC_CLIENT_ID, ENT_QUOTES, 'UTF-8'); ?>";

		// Remember a guest's ticket on THIS browser so the drawing page can show
		// "your number" + win/lose even when they are not logged in.
		function goralRememberTicket(campaignID, ticketNo) {
			if (!campaignID || ticketNo == null) return;
			try {
				var k = 'goral_tickets_' + campaignID;
				var list = JSON.parse(localStorage.getItem(k) || '[]');
				if (list.indexOf(ticketNo) === -1) { list.push(ticketNo); localStorage.setItem(k, JSON.stringify(list)); }
			} catch (e) {}
		}

		function goralThanks(jsonStr) {
			goralRememberTicket(jsonStr.campaignID, jsonStr.ticketNo);
			$('#checkoutModal').modal('toggle');
			var drawingURL = '/' + jsonStr.pageURL;
			Swal.fire({
				width: '800px',
				html: '<div class="row"> <div class="col-md-6"> <div class="illus-8"> <img src="assets/images/illu-8.png" alt="" /> </div> </div> <div class="col-md-6"> <div class="row"> <div class="col-md"> <div class="text-tq"> Ticket bought successfully! <p> Check your email for a receipt. <br /> Taking you to the drawing page&hellip; </p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-ticnum"> Your Ticket Number <p>' + jsonStr.ticketNo + '</p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="btn-drawing-page"> <a href="' + drawingURL + '">Go to Drawing Page</a> </div> </div> </div> </div> </div>',
				showConfirmButton: false, allowOutsideClick: false, showCloseButton: true
			});
			// The second they buy, take them to the drawing page where their ticket shows.
			setTimeout(function () { window.location.href = drawingURL; }, 2600);
		}

		function goralResetBuyBtn() { $(".btnBuyTicket").text("Buy Ticket").prop('disabled', false); }

		function goralPayarcLast4(response) {
			if (response.last_four) return String(response.last_four);
			if (response.last4) return String(response.last4);
			if (response.card_number) return String(response.card_number).replace(/\D/g, '').slice(-4);
			return '';
		}

		function goralDoPurchase(token, last4, brand) {
			$.ajax({
				url: "functions/buy-ticket",
				type: "POST",
				data: {
					campaignID: $("#btnBuyNow").data("campaign-id"),
					firstName: $("#firstNameC").val(),
					lastname: $("#lastnameC").val(),
					email: $("#emailC").val(),
					phone: $("#phoneC").val(),
					cardHolderName: $("#cardHolderName").val(),
					inputPurchase: $("#input-purchase").val(),
					campaignName: $(".text-dp-input").val(),
					paymentToken: token,
					cardLast4: last4 || "",
					cardBrand: brand || "",
					saveCard: $("#saveCard").val()
				},
				dataType: "JSON",
				success: function (jsonStr) {
					if(jsonStr.result == "OK") { goralThanks(jsonStr); }
					else if(jsonStr.result == "declined" || jsonStr.result == "invalidName") { Swal.fire({ text: jsonStr.message || "Your card was declined.", icon: "error", confirmButtonText: "OK" }); }
					else { Swal.fire({ text: "Campaign is closed or not available.", icon: "error", confirmButtonText: "OK" }); }
					goralResetBuyBtn();
				},
				error: function () { Swal.fire({ text: "Something went wrong. Please try again.", icon: "error", confirmButtonText: "OK" }); goralResetBuyBtn(); }
			});
		}

		var PAYARC_SETTINGS = {
			FIELDS_CONTAINER: 'card-token-container',
			INITIATE_PAYMENT: 'initiate-payment',
			TOKEN_CALLBACK: {
				success: function (obj) {
					try {
						var response = JSON.parse(obj.response);
						if (response && response.token) {
							goralDoPurchase(response.token, goralPayarcLast4(response), response.card_type || response.brand || '');
							return;
						}
					} catch (e) {}
					Swal.fire({ text: "Could not read your card. Please re-check the details.", icon: "error", confirmButtonText: "OK" });
					goralResetBuyBtn();
				},
				error: function (obj) {
					var msg = "We couldn’t verify this card — please check the number and expiry date.";
					try {
						var r = JSON.parse(obj.response);
						if (r && (r.message || r.error)) { msg = r.message || r.error; }
					} catch (e) {}
					$(".ccNotValid").text(msg).show();
					goralResetBuyBtn();
				},
				paymentWindowClosed: function () { goralResetBuyBtn(); }
			}
		};

		if (PAYARC_CLIENT_ID && typeof initPayarcTokenizer === 'function') {
			initPayarcTokenizer(PAYARC_CLIENT_ID, PAYARC_SETTINGS);
		}

		$('#initiate-payment').click(function () {
			if (typeof getPayarcToken === 'function') { getPayarcToken(this); }
		});

		$(".btnBuyTicket").click(function () {
			var firstName = $("#firstNameC").val();
			var lastname = $("#lastnameC").val();
			var email = $("#emailC").val();
			var phone = $("#phoneC").val();
			var cardHolderName = $("#cardHolderName").val();
			var filter = /^(?=(?:\D*\d){7,15}\D*$)[+\d\s().-]+$/;

			if(firstName == "") { $("#firstNameC").focus(); return; }
			if(lastname == "") { $("#lastnameC").focus(); return; }

			// Names must look like names — winners are announced publicly by name.
			var nameRule = /^\p{L}[\p{L} .'-]{0,48}\p{L}$/u;
			if(!nameRule.test(firstName) || !nameRule.test(lastname)) {
				Swal.fire({ text: "Please enter your real first and last name — winners are announced by name.", icon: "error", confirmButtonText: "OK" });
				$(!nameRule.test(firstName) ? "#firstNameC" : "#lastnameC").focus();
				return;
			}

			if(email == "" || !validateEmail(email)) { $("#emailC").focus(); $(".emailNotValid").fadeIn(); return; }
			if(phone == "" || !filter.test(phone)) { $("#phoneC").focus(); return; }
			if(cardHolderName == "") { $("#cardHolderName").focus(); return; }

			if (!PAYARC_CLIENT_ID || typeof initPayarcTokenizer === 'undefined') {
				Swal.fire({ text: "Payments aren't switched on yet. Please try again shortly.", icon: "error", confirmButtonText: "OK" });
				return;
			}

			$(".btnBuyTicket").text("Processing").prop('disabled', true);
			$('#initiate-payment').click(); // hand off to PayArc to tokenize -> TOKEN_CALLBACK.success
		});

		$(".cc").click(function () {
			$(".credit-card-option").fadeIn();
		});

		$("#saveCardChk").on("change", function () {
			$("#saveCard").val(this.checked ? "Y" : "");
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
			
			<div class="row">
				<div class="text-dp"><?php echo htmlspecialchars($campaignName, ENT_QUOTES, 'UTF-8'); ?></div>
				<input type="hidden" class="text-dp-input" value="<?php echo htmlspecialchars($campaignName, ENT_QUOTES, 'UTF-8'); ?>"/>
			
				<div class="weeks-pot">
					<div class="blinking-green"></div>

					<?php
					$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
					$totalParticipants = mysqli_num_rows($qAccumulateParticipant);
					echo $totalParticipants . " " . ($totalParticipants == 1 ? "Participant" : "Participants");
					?>
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
			<div class="hero-divider"></div>
			<div class="row">
				<div class="col-md" style="display: flex; flex-flow: column; align-items: center;">
					<div class="title-draw"><?php echo $drawPassed ? "This week&rsquo;s winner is in" : "Draws in"; ?></div>
				
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
				<?php if($status == "open") { ?>
					<button class="btnBuyNow" data-target="#checkoutModal" data-toggle="modal" id="btnBuyNow" type="button" data-campaign-id="<?php echo $campaignID; ?>"<?php if($drawPassed) echo ' style="display:none;"'; ?>>Buy Ticket Now</button>
				<?php } ?>
				<?php if($campaignID !== '') { ?>
					<a class="btnBuyNow btn-go-drawing" id="goDrawingBtn" href="<?php echo htmlspecialchars($pageURL, ENT_QUOTES, 'UTF-8'); ?>"<?php if(!$drawPassed) echo ' style="display:none;"'; ?>>Go to Drawing Page</a>
				<?php } ?>
			</div>
			<script>
			var countdowns = [{
				campaignID: "<?php echo $campaignID; ?>",
				countdownDate: <?php echo $endDate ? strtotime($endDate) * 1000 : 0; ?>
			}];

			$(document).ready(function () {
				var timer = setInterval(function() {
					// Server emits the draw time as an absolute epoch (server TZ is
					// America/New_York), so this is correct in every browser zone.
					var now = Date.now();

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

						// Two-digit tiles read better and keep the tile widths steady.
						var pad = function (n) { n = Math.max(0, n); return (n < 10 ? "0" : "") + n; };

						var abc = "<div id='tiles'><span>" + pad(days) + "</span><span>" + pad(hours) + "</span><span>" + pad(minutes) + "</span><span>" + pad(seconds) + "</span></div><div class='labels'><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>";

						var abc2 = "<div id='tiles-tq'><span>" + pad(days) + "</span><span>" + pad(hours) + "</span><span>" + pad(minutes) + "</span><span>" + pad(seconds) + "</span></div><div class='labels-tq'><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>";
						
						$(".countdown-" + countdown.campaignID).html(abc);
						$("#countdown-tq").html(abc2);
						// If the count down is over, write some text
						if (distance < 0) {
							//timerElement.innerHTML = "EXPIRED";
							// this timer is done, remove it
							$(".countdown-" + countdown.campaignID).text("DRAW COMPLETED");
							$("#countdown-tq").html("DRAW COMPLETED");
							// Buying is over — swap the hero CTA to the drawing page.
							$("#btnBuyNow").hide();
							$("#goDrawingBtn").show();

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
					How it works
				</div>
			</div>
			<div class="row steps-row">
				<div class="col-md step-col">
					<div class="illus-2 text-center">
						<lottie-player src="assets/animation/Anim-02.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
					<div class="text-subtitle">
						<span class="step-no">01</span>Buy
						<p>Purchase a ticket and receive a ticket number for a chance to take home half of the pot.</p>
					</div>
				</div>
				<div class="col-md step-col">
					<div class="illus-3 text-center">
						<lottie-player src="assets/animation/Anim-03.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
					<div class="text-subtitle">
						<span class="step-no">02</span>Check
						<p>At the end of the countdown, we will announce the winning number.</p>
					</div>
				</div>
				<div class="col-md step-col">
					<div class="illus-4 text-center">
						<lottie-player src="assets/animation/Anim-04.json" background="Transparent" speed="1" loop autoplay></lottie-player>
					</div>
					<div class="text-subtitle">
						<span class="step-no">03</span>Collect
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
							<tr>
								<th></th>
								<th></th>
								<th>Date</th>
								<th>Participants</th>
								<th>Winner</th>
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
								tbl_campaign.status = 'closed' AND
								tbl_campaign.page_url != 'drawing' AND
								tbl_ticket.total_price != 0
								GROUP BY tbl_campaign.campaign_id
								ORDER BY tbl_campaign.end_date DESC limit 5
							");
							
							while($dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles)) {
								// NB: deliberately NOT $campaignID — that still holds the
								// live weekly's id, which the checkout modal below uses.
								$rowCampaignID = $dWeeklyRaffles['campaign_id'];
								$rowStatus = $dWeeklyRaffles['status'];

								// Open weeklies live at /drawing; closed ones were archived
								// by the cron to /drawing<N>, so page_url works for both.
								$rowURL = htmlspecialchars($dWeeklyRaffles['page_url'], ENT_QUOTES, 'UTF-8');

								$qRowWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE campaignid_fk = '" . $rowCampaignID . "' AND win = 'Y' LIMIT 1");
								if($qRowWinner && ($dRowWinner = mysqli_fetch_array($qRowWinner))) {
									$wFirst = trim($dRowWinner['first_name'] ?? '');
									$wInitial = strtoupper(substr(trim($dRowWinner['last_name'] ?? ''), 0, 1));
									$rowWinner = htmlspecialchars($wFirst . ($wInitial !== '' ? ' ' . $wInitial . '.' : ''), ENT_QUOTES, 'UTF-8');
								}
								else {
									$rowWinner = '&mdash;';
								}
								?>
								<tr class="raffle-row" data-href="<?php echo $rowURL; ?>">
								<td><div class="text-number"><?php echo $i; ?></div></td>
								<td><img style="width:28px" alt="" src="assets/images/user-icon.png"></td>
								<td><span class="text-date"><?php echo $dWeeklyRaffles['end_date']; ?></span></td>

								<td><span class="text-date">
									<?php
									$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $rowCampaignID . "'");

									echo mysqli_num_rows($qAccumulateParticipant);
									?>
								</span></td>
								<td><span class="text-winner"><?php echo $rowWinner; ?></span></td>
								<td>
									<div class="text-price">
										$<?php
										$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $rowCampaignID . "'");
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

						<script>
						$(function () {
							$(".raffle-row").on("click", function () {
								window.location.href = $(this).data("href");
							});
						});
						</script>
					</div>
				</div>
			</div>
		</div>
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
					<a class="active" href="/">Home</a>
					<a href="drawing">Drawing Page</a>
					<a href="contact">Contact Us</a>
				</div>
			</div>
			<div class="row">
				<div class="text-desc">
					The Goral is a weekly community split-the-pot drawing. One ticket can win the pot &mdash; and a share of every pot goes to tzedakah. Winners are drawn and posted publicly each week.
				</div>
			</div>
			<div class="row">
				<div class="copyright">
					© <?php echo date('Y'); ?> The Goral
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
						$qTicketPrice = mysqli_query($con, "SELECT * FROM tbl_ticket_price WHERE campaignid_fk = '" . mysqli_real_escape_string($con, $campaignID) . "'");
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
								<label for="firstNameC">First Name<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="firstNameC" type="text" value="<?php echo htmlspecialchars($getFirstName, ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="lastnameC">Last Name<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="lastnameC" type="text" value="<?php echo htmlspecialchars($getLastName, ENT_QUOTES, 'UTF-8'); ?>">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="emailC">Email<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="emailC" type="text" value="<?php echo htmlspecialchars($getEmailAddress, ENT_QUOTES, 'UTF-8'); ?>">
								
								<div class="emailNotValid" style="display: none;">Please enter a valid email address.</div>
							</div>
						</div>
						<div class="col-md col-50-bn">
							<div class="form-group">
								<label for="phoneC">Phone<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="phoneC" type="text" value="<?php echo htmlspecialchars($getPhone, ENT_QUOTES, 'UTF-8'); ?>">
								
								<div class="phoneNotValid" style="display: none;">Please enter a valid phone number.</div>
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
					<!-- Saved-card vault returns in the wallet/token phase. Card-first MVP collects the card via the PayArc iframe each checkout. -->
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
						<div class="col-md-12 col-50-bn">
							<div class="form-group">
								<label for="cardHolderName">Card Holder Name<small style="color: red">*</small></label> <input autocomplete="off" class="form-control" id="cardHolderName" placeholder="Jack" type="text">
							</div>
						</div>
						<!-- PayArc hosted fields. Card data never touches our server. -->
						<div class="col-md-12">
							<div id="card-token-container" class="payarc-fields">
								<div id="pa-card-number" data-payarc="CARD_NUMBER" data-placeholder="Card Number"></div>
								<div class="payarc-row-2">
									<div id="pa-card-exp" data-payarc="EXP" data-placeholder="MM/YY"></div>
									<div id="pa-card-cvv" data-payarc="CVV" data-placeholder="CVV"></div>
									<div id="pa-card-zip" data-payarc="ZIP" data-placeholder="ZIP"></div>
								</div>
							</div>
							<div class="ccNotValid" style="display: none;">We couldn&rsquo;t verify this card &mdash; please check the number and expiry date.</div>
							<button type="button" id="initiate-payment" style="display:none;"></button>
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
					<?php if($getUserID != "") { ?>
					<div class="row">
						<div class="col-md text-center mt-3">
							<label class="save-card-label"><input type="checkbox" id="saveCardChk"> Save my card for faster checkout</label>
						</div>
					</div>
					<?php } ?>
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