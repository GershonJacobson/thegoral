<?php
session_start();

require("config/session.php");
require("config/payarc.php");

$pageURL = mysqli_real_escape_string($con, $_GET['campaign'] ?? '');

$qChkData = mysqli_query($con, "
	SELECT * FROM tbl_campaign
	WHERE
	(page_url = '" . $pageURL . "' and status = 'open') OR
	(campaign_id = '" . $pageURL . "' AND status = 'closed') OR
	(page_url = '" . $pageURL . "' AND category = 'weekly' and (status = 'open' OR status = 'closed'))
");
if($qChkData && mysqli_num_rows($qChkData) > 0) {

$data = mysqli_fetch_array($qChkData);

$campaignID = $data['campaign_id'];
$campaignName = $data['campaign_name'];
$endDate = $data['end_date'];
$public = $data['public'];
$status = $data['status'];
$pageURL = $data['page_url'];

$qWinningNo =  mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk = '" . mysqli_real_escape_string($con, $campaignID) . "' AND win = 'Y'");
$dWinningNo = mysqli_fetch_array($qWinningNo);
$winningTicketNo = $dWinningNo['ticket_no'] ?? '';
$drawDone = ($winningTicketNo !== '' && $winningTicketNo !== null);
// The clock has run out (the winner may not be picked yet — the page triggers
// that in real time via functions/draw-now.php). Used to stop sales at zero.
$drawEnded = ($endDate !== '' && $endDate !== null && strtotime($endDate) <= time());

// The signed-in viewer's own ticket numbers for this campaign, so we can show
// "your number" + did-I-win. Guests get the same client-side via localStorage
// (written at checkout) — the server can't identify a guest on a later visit.
$myTickets = [];
$iWonServer = false;
if ($getUserID !== "") {
	$mt = $con->prepare("SELECT ticket_no, win FROM tbl_ticket WHERE campaignid_fk = ? AND purchased_by = ? ORDER BY ticket_no ASC");
	if ($mt) {
		$uid = (int)$getUserID;
		$mt->bind_param("si", $campaignID, $uid);
		$mt->execute();
		$mr = $mt->get_result();
		while ($mrow = $mr->fetch_assoc()) {
			$myTickets[] = (int)$mrow['ticket_no'];
			if ($mrow['win'] === 'Y') { $iWonServer = true; }
		}
		$mt->close();
	}
}
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
		<!-- Drawing page layer (overrides style.css; this page only) -->
		<link rel="stylesheet" href="../assets/css/drawing.css" />
		<style>
		.swal2-html-container {
			overflow: hidden !important;
		}
		</style>
	
		<script src="../assets/js/jquery.min.js"></script>
		<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="../assets/js/index.js"></script>
		<script src="../assets/font/fontawesome/js/all.min.js"></script>
		<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
		<!-- PayArc hosted-fields tokenizer (PCI-safe card iframe) -->
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
			
			// ---- PayArc hosted-fields checkout (card tokenized in-browser) ----
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
						if(jsonStr.result == "OK") {
							goralRememberTicket(jsonStr.campaignID, jsonStr.ticketNo);
							$('#checkoutModal').modal('toggle');
							Swal.fire({
								width: '800px',
								html: '<div class="row"> <div class="col-md-6"> <div class="illus-8"> <img src="assets/images/illu-8.png" alt="" /> </div> </div> <div class="col-md-6"> <div class="row"> <div class="col-md"> <div class="text-tq"> Ticket bought successfully! <p> Check your email for a receipt. <br /> Taking you to the drawing page&hellip; </p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-ticnum"> Your Ticket Number <p>' + jsonStr.ticketNo + '</p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="btn-drawing-page"> <a href="/' + jsonStr.pageURL + '">Go to Drawing Page</a> </div> </div> </div> </div> </div>',
								showConfirmButton: false, allowOutsideClick: false, showCloseButton: true
							});
								// Land them on the drawing page where their ticket number shows.
								setTimeout(function () { window.location.href = '/' + jsonStr.pageURL; }, 2600);
						}
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
				$('#initiate-payment').click();
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
				<a class="navbar-brand" href="/">
				  <img class="logo" src="../assets/images/logo.svg" alt="logo" />
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
			  <div class="row">
			  <div class="title-draw"><?php echo $status == "open" ? "Draws in" : "This draw has ended"; ?></div>
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
					countdownDate: <?php echo $endDate ? strtotime($endDate) * 1000 : 0; ?>
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
						
						// Server emits the draw time as an absolute epoch (server TZ
						// is America/New_York), correct in every browser zone.
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
								// Sales are closed and the draw happens NOW (real-time).
								$("#btnBuyNow").hide();
								if (window.goralEnsureDrawn) { window.goralEnsureDrawn(); }

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
				if($status == "open" && !$drawEnded) {
				?>
					<button class="btnBuyNow" data-target="#checkoutModal" data-toggle="modal" id="btnBuyNow" type="button" data-campaign-id="<?php echo $campaignID; ?>">Buy Ticket Now</button>
				<?php
				}
				?>
			  </div>
			</div>
			<div class="row mt-1 justify-content-center">
			  <div class="box-win">
				<h5>Winning Number</h5>
				<div class="box-win-x<?php echo $drawDone ? " is-spinning" : ""; ?>">
					<?php
					if($drawDone) {
						$a = str_split((string)$winningTicketNo, 1);
						for($i=0; $i<count($a); $i++) {
						?>
							<div class="box-win-number" data-final="<?php echo $a[$i]; ?>"><?php echo $a[$i]; ?></div>
						<?php
						}
					}
					else {
					?>
						<div class="box-win-number">?</div>
					<?php
					}
					?>
				</div>
				<?php
				// Winner's name (first name + last initial) once the draw is done.
				$wFirst = trim($dWinningNo['first_name'] ?? '');
				$wInitial = strtoupper(substr(trim($dWinningNo['last_name'] ?? ''), 0, 1));
				if($drawDone && $wFirst !== '') {
				?>
					<div class="box-win-winner" id="winnerName" style="visibility:hidden;">Won by <?php echo htmlspecialchars($wFirst . ($wInitial !== '' ? ' ' . $wInitial . '.' : ''), ENT_QUOTES, 'UTF-8'); ?></div>
				<?php
				}
				?>
			  </div>
			</div>

			<!-- The viewer's own ticket(s) + did-I-win. Populated server-side for
			     logged-in buyers; merged with localStorage for same-browser guests. -->
			<div class="row justify-content-center">
			  <div class="box-myticket" id="myTicketPanel" style="display:none;">
				<div class="myticket-label">My Ticket Number</div>
				<div class="myticket-nums" id="myTicketNums"></div>
				<div class="myticket-result" id="myTicketResult"></div>
			  </div>
			</div>
			<script>
			// ---- Winning-number reveal + "my ticket" ----------------------------
			// The winner is decided server-side (tbl_ticket.win='Y'); this only
			// ANIMATES that already-decided number. It never picks a winner.
			(function () {
				var goralCampaignID = "<?php echo $campaignID; ?>";
				var goralWinningNo  = "<?php echo $drawDone ? (int)$winningTicketNo : ''; ?>";
				var goralDrawDone   = <?php echo $drawDone ? 'true' : 'false'; ?>;
				var goralMyTickets  = <?php echo json_encode(array_values($myTickets)); ?>;
				var goralDrawEnded  = <?php echo $drawEnded ? 'true' : 'false'; ?>;
				var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

				function revealMyTicket() {
					// Merge logged-in tickets (server) with same-browser guest tickets.
					var mine = goralMyTickets.map(String);
					try {
						var saved = JSON.parse(localStorage.getItem('goral_tickets_' + goralCampaignID) || '[]');
						saved.forEach(function (n) { if (mine.indexOf(String(n)) === -1) mine.push(String(n)); });
					} catch (e) {}
					if (!mine.length) return; // nothing to show this viewer

					mine.sort(function (a, b) { return a - b; });
					document.getElementById('myTicketNums').innerHTML =
						mine.map(function (n) { return '<span class="myticket-chip">#' + n + '</span>'; }).join('');

					var res = document.getElementById('myTicketResult');
					if (goralDrawDone) {
						var won = mine.indexOf(String(goralWinningNo)) !== -1;
						res.className = 'myticket-result ' + (won ? 'is-win' : 'is-lose');
						res.innerHTML = '<span class="myticket-q">Did I win?</span> <span class="myticket-ans">' +
							(won ? 'YES \u2014 you won the pot!' : 'No, not this time') + '</span>';
						res.style.display = '';
					} else {
						// Before the draw: show only the number; "Did I win?" stays blank.
						res.innerHTML = '';
						res.style.display = 'none';
					}
					document.getElementById('myTicketPanel').style.display = '';
				}

				// A restrained, professional gold confetti burst from the winning stage
				// (brand colours, one burst, fades in ~2.5s). Skipped for reduced motion.
				function goralConfetti() {
					if (reduceMotion) return;
					var box = document.querySelector('.box-win');
					var rect = box ? box.getBoundingClientRect() : null;
					var vw = window.innerWidth, vh = window.innerHeight;
					var ox = rect ? (rect.left + rect.width / 2) : vw / 2;
					var oy = rect ? (rect.top + rect.height * 0.35) : vh * 0.4;
					var canvas = document.createElement('canvas');
					canvas.style.cssText = 'position:fixed;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:2000;';
					document.body.appendChild(canvas);
					var ctx = canvas.getContext('2d');
					var dpr = Math.min(window.devicePixelRatio || 1, 2);
					canvas.width = vw * dpr; canvas.height = vh * dpr; ctx.scale(dpr, dpr);
					var colors = ['#ffcc64', '#f5d9a0', '#ec8e37', '#ffffff'];
					var P = [];
					for (var i = 0; i < 120; i++) {
						var a = (-Math.PI / 2) + (Math.random() - 0.5) * 2.1;
						var sp = 5 + Math.random() * 9;
						P.push({ x: ox, y: oy, vx: Math.cos(a) * sp + (Math.random() - 0.5) * 2, vy: Math.sin(a) * sp,
							w: 5 + Math.random() * 5, h: 8 + Math.random() * 6, rot: Math.random() * 6.28,
							vr: (Math.random() - 0.5) * 0.3, c: colors[(Math.random() * colors.length) | 0] });
					}
					var start = Date.now();
					(function frame() {
						var t = Date.now() - start;
						ctx.clearRect(0, 0, vw, vh);
						var alive = false;
						for (var i = 0; i < P.length; i++) {
							var p = P[i];
							p.vy += 0.22; p.vx *= 0.99; p.x += p.vx; p.y += p.vy; p.rot += p.vr;
							var op = t < 1400 ? 1 : Math.max(0, 1 - (t - 1400) / 1000);
							if (op <= 0 || p.y > vh + 40) continue;
							alive = true;
							ctx.save(); ctx.globalAlpha = op; ctx.translate(p.x, p.y); ctx.rotate(p.rot);
							ctx.fillStyle = p.c; ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h); ctx.restore();
						}
						if (alive && t < 2600) { requestAnimationFrame(frame); }
						else if (canvas.parentNode) { canvas.parentNode.removeChild(canvas); }
					})();
				}

				function goralFinish() {
					var w = document.getElementById('winnerName');
					if (w) { w.style.visibility = 'visible'; w.classList.add('is-revealed'); }
					var stage = document.querySelector('.box-win');
					if (stage) { stage.classList.add('is-won'); }
					goralConfetti();
					revealMyTicket();
				}

				// Animate the balls present in .box-win-x (scramble -> decelerate ->
				// settle left to right over ~6.4s), scrolling the stage into view first.
				function goralPlay() {
					var cells = Array.prototype.slice.call(
						document.querySelectorAll('.box-win-x.is-spinning .box-win-number'));
					if (!cells.length) { revealMyTicket(); return; }
					cells.forEach(function (c) { c.textContent = Math.floor(Math.random() * 10); });

					var stageEl = document.querySelector('.box-win');
					if (stageEl) { try { stageEl.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' }); } catch (e) { stageEl.scrollIntoView(); } }

					var finals = cells.map(function (c) { return c.getAttribute('data-final'); });
					var SPIN_MS = 6400;
					var lockAt = cells.map(function (_, i) { return Math.round(SPIN_MS * (i + 1) / cells.length); });
					var started = Date.now();
					function tick() {
						var elapsed = Date.now() - started;
						cells.forEach(function (c, i) {
							if (c.dataset.locked) return;
							if (elapsed >= lockAt[i]) { c.dataset.locked = '1'; c.textContent = finals[i]; c.classList.add('is-locked'); }
							else { c.textContent = Math.floor(Math.random() * 10); }
						});
						if (elapsed >= SPIN_MS) { goralFinish(); return; }
						setTimeout(tick, 45 + 105 * (elapsed / SPIN_MS));
					}
					tick();
				}

				// Build the balls from a freshly-decided number (real-time path), then play.
				function goralReveal(num, name) {
					goralWinningNo = String(num);
					goralDrawDone = true;
					var x = document.querySelector('.box-win-x');
					if (!x) return;
					x.classList.add('is-spinning');
					x.innerHTML = String(num).split('').map(function (d) {
						return '<div class="box-win-number" data-final="' + d + '">' + (Math.floor(Math.random() * 10)) + '</div>';
					}).join('');
					var nm = document.getElementById('winnerName');
					if (!nm) {
						nm = document.createElement('div');
						nm.id = 'winnerName';
						nm.className = 'box-win-winner';
						var box = document.querySelector('.box-win');
						if (box) box.appendChild(nm);
					}
					if (nm) { nm.textContent = name ? ('Won by ' + name) : ''; nm.style.visibility = 'hidden'; }
					goralPlay();
				}

				// Real-time draw: the instant the clock is up, ask the server to pick the
				// winner (idempotent) and reveal it IN PLACE (no reload). Also covers a
				// cold load of an already-ended pot the cron hasn't processed yet.
				var goralEnsuring = false;
				window.goralEnsureDrawn = function () {
					if (goralDrawDone || goralEnsuring) return;
					goralEnsuring = true;
					$.ajax({
						url: 'functions/draw-now', type: 'POST',
						data: { campaignID: goralCampaignID }, dataType: 'JSON',
						success: function (r) {
							if (r && r.result === 'OK' && r.winningTicketNo != null) {
								$('#btnBuyNow').hide();
								goralReveal(r.winningTicketNo, r.winnerName || '');
							} else { goralEnsuring = false; }
						},
						error: function () { goralEnsuring = false; }
					});
				};

				if (goralDrawDone) { goralPlay(); }
				else if (goralDrawEnded) { window.goralEnsureDrawn(); }
				else { revealMyTicket(); }
			})();
			</script>
		  </div>
		</div>
		<div class="content-draw-b">
		  <div class="container">
			<div class="row">
			  <div class="col-md">
				<h5 style="padding-bottom: 10px;">
				  Thank you all for joining! If you&rsquo;re the winner, check your
				  email to claim your prize.
				</h5>
				<a href="/" class="btnBackHome" style="text-decoration: none;">
				  Back to home
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
		<div class="footer">
		  <div class="container">
			<div class="row">
			  <div class="social-media text-center">
				<span><img src="../assets/images/logo.svg" alt="" /></span>
				<i class="fa-brands fa-facebook-f"></i>
				<i class="fa-brands fa-twitter"></i>
				<i class="fa-brands fa-linkedin-in"></i>
				<i class="fa-brands fa-instagram"></i>
			  </div>
			</div>
			<div class="row">
			  <div class="menu-footer">
				<a href="/">Home</a>
				<a class="active" href="/drawing">Drawing Page</a>
				<a href="/contact">Contact Us</a>
			  </div>
			</div>
			<div class="row">
			  <div class="text-desc">
				The Goral is a weekly community split-the-pot drawing. One ticket can win the pot &mdash; and a share of every pot goes to tzedakah. Winners are drawn and posted publicly each week.
			  </div>
			</div>
			<div class="row">
			  <div class="copyright">© <?php echo date('Y'); ?> The Goral</div>
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
						
						
						<div class="row mb-1 mt-4">
							<div class="col-md text-pay">
								Payment Method
							</div>
						</div>
						
<!-- Saved-card vault returns in the wallet/token phase. -->
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
							<!-- PayArc hosted fields -->
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