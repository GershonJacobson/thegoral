<?php
session_start();

require("config/session.php");

if($getUserID == "") {
	header("Location: /sign-in");
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Dashboard - The Goral</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

    <link
      rel="stylesheet"
      href="../assets/css/bootstrap/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/font/fontawesome/css/all.min.css" />
	<link rel="stylesheet" href="../assets/css/sweetalert.css" />
	<!-- Utility-pages layer (overrides style.css) -->
	<link rel="stylesheet" href="../assets/css/site.css" />

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="../assets/js/sweetalert.min.js"></script>
    <script src="../assets/js/index.js"></script>
	<script src="../assets/js/dashboard.js"></script>
    <script src="../assets/font/fontawesome/js/all.min.js"></script>
	<script>
		$(document).ready(function () {
			$('#edtStartDate').change(function() {
				var currentDate = "<?php echo date('Y-m-d'); ?>";
				var startDate = $("#edtStartDate").val();
				$("#edtEndDate").val("");
				if(startDate < currentDate) {
				$("#edtStartDate").val("");
				}
			});

		

			$('#endDate').change(function() {
				var currentDate = "<?php echo date('Y-m-d'); ?>";
				var startDate = $("#edtStartDate").val();
				var endDate = $("#edtEndDate").val();

				if(endDate < startDate) {
					$("#edtEndDate").val("");
				}

				if(endDate < currentDate){
					$("#edtEndDate").val("");
				}
			});
		})
	
	</script>
  </head>
  <body class="page-account">
    <div class="header-ac-bg header-light">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img
                class="logo"
                src="../assets/images/logo-dark.svg"
                alt="logo"
              />
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
      </div>
    </div>
    <div class="container">
      <div class="row" style="align-items: center;">
        <div class="col-md col-50-bn">
          <h1 class="page-head">Welcome back, <?php echo htmlspecialchars($getFirstName, ENT_QUOTES, 'UTF-8'); ?></h1>
          <p class="page-sub">Your tickets, raffles and winnings at a glance.</p>
        </div>
		
		<div class="col-md col-50-bn">
			 <div class="filter-by-container2" style="float: right;">
				<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; padding 0px; font-size: 12px;">
					Sort by
				</button>
				
				<ul class="dropdown-menu dropdown-menu-dashboard" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
					<li>
						<div class="label-radio"><label for="at"> All Time</label></div>
						<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="at" value="at" checked/></div>
					</li>
					
					<li>
						<div class="label-radio"><label for="tm"> This Month</label></div>
						<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="tm" value="tm"/></div>
					</li>
					
					<li>
						<div class="label-radio"><label for="ltm"> Last 3 Months</label></div>
						<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="ltm" value="ltm"/></div>
					</li>
					
					<li>
						<div class="label-radio"><label for="ty"> This Year</label></div>
						<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="ty" value="ty"/></div>
					</li>
				</ul>
			</div>
		</div>
      </div>
      <div class="row">
        <div class="col-md col-50-bn">
          <div class="card-dash">
            <div class="row">
              <div class="col-md-3">
                <div class="icon-dash">
                  <img src="../assets/images/af-icon.png" alt="" />
                </div>
              </div>
              <div class="col-md">
                <span class="money-spent">
					$<?php
					$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
					$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
					echo $totalTicket = $dAccumulateTicket['total_ticket'];
					?>
				</span>
                <p>Money Spent</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md col-50-bn">
          <div class="card-dash">
            <div class="row">
              <div class="col-md-3">
                <div class="icon-dash">
                  <img src="../assets/images/tb-icon.png" alt="" />
                </div>
              </div>
              <div class="col-md">
                <span class="ticket-bought">
					<?php
					// SUM(total_ticket), not COUNT(*): a 2-ticket purchase is 2 tickets.
					$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_ticket), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
					$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
					echo $totalTicket = $dAccumulateTicket['total_ticket'];
					?>
				</span>
                <p>Tickets Bought</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md col-50-bn">
          <div class="card-dash">
            <div class="row">
              <div class="col-md-3">
                <div class="icon-dash">
                  <img src="../assets/images/esj-icon.png" alt="" />
                </div>
              </div>
              <div class="col-md">
                <span class="raffles-joined"">
					<?php
					$qRafflesJoined = mysqli_query($con, "SELECT DISTINCT campaignid_fk FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
					echo $totalRafflesJoined = mysqli_num_rows($qRafflesJoined);
					?>
				</span>
                <p>Raffles Joined</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md col-50-bn">
          <div class="card-dash">
            <div class="row">
              <div class="col-md-3">
                <div class="icon-dash">
                  <img src="../assets/images/rw-icon.png" alt="" />
                </div>
              </div>
              <div class="col-md">
                <span class="money-won">
				$<?php
				// The prize is HALF THE POT of each raffle this user won —
				// not the price they paid for the winning ticket.
				$qRafflesWon = mysqli_query($con, "
					SELECT ROUND(COALESCE(SUM(pot.total), 0) / 2, 2) AS total_won
					FROM tbl_ticket w
					JOIN (SELECT campaignid_fk, SUM(total_price) AS total FROM tbl_ticket GROUP BY campaignid_fk) pot
					  ON pot.campaignid_fk = w.campaignid_fk
					WHERE w.purchased_by = '" . $getUserID . "' AND w.win = 'Y'
				");
				$dRafflesWon = mysqli_fetch_array($qRafflesWon);
				echo $dRafflesWon['total_won'];
				?>
				</span>
                <p>Money Won</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row" style="margin-top: 30px;">
        <div class="col-md">
          <div class="title-dash">Raffle history</div>
        </div>
      </div>
      <div class="row tbl-rh" style="overflow-x: auto;">
        <table id="customers" class="tbl-raffle">
			<thead>
				<tr>
					<th>Campaign Name</th>
					<th>Ticket #</th>
					<th>Date</th>
					<th>Amount</th>
					<th>Payment</th>
					<th>Status</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			$rowperpage = 5;

            $allcount_query = "SELECT count(*) as allcount FROM tbl_ticket WHERE purchased_by = '$getUserID'";
            $allcount_result = mysqli_query($con,$allcount_query);
            $allcount_fetch = mysqli_fetch_array($allcount_result);
            $allcount = $allcount_fetch['allcount'];
			
			if($allcount > 0) {
				$qTicketPurchased = mysqli_query($con, "
					SELECT campaignid_fk, ticket_no, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, card_last4 AS card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' ORDER BY purchased_date DESC LIMIT 5
				");
				if(mysqli_num_rows($qTicketPurchased) > 0) {
				while($dTicketPurchased = mysqli_fetch_array($qTicketPurchased)) {
					$campaignID = $dTicketPurchased['campaignid_fk'];
					
					if($dTicketPurchased['payment_status'] == 0) {
						$paymentStatus = "Process";
					}
					else if($dTicketPurchased['payment_status'] == 1) {
						$paymentStatus = "Success";
					}
					else {
						$paymentStatus = "Failed";
					}
					
					if($dTicketPurchased['card_number'] != "") {
						$paymentMethod = "Card ".$dTicketPurchased['card_number'];
					}
					else {
						$paymentMethod = "";
					}
					
					$qCampaignName = mysqli_query($con, "SELECT campaign_id, campaign_name, status, page_url FROM tbl_campaign WHERE campaign_id = '$campaignID'");
					$dCampaignName = mysqli_fetch_array($qCampaignName);
					
					$url = $dCampaignName['page_url'];
					?>
						<tr>
							<td><a href="<?php echo $url; ?>"><?php echo $dCampaignName['campaign_name']; ?></a></td>
							<td><a href="<?php echo $url; ?>">#<?php echo $dTicketPurchased['ticket_no']; ?></a></td>
							<td><a href="<?php echo $url; ?>"><?php echo $dTicketPurchased['purchased_date']; ?></a></td>
							<td><a href="<?php echo $url; ?>">$<?php echo $dTicketPurchased['total_price']; ?></a></td>
							<td><a href="<?php echo $url; ?>"><?php echo $paymentMethod; ?></a></td>
							<td><a href="<?php echo $url; ?>"><?php echo $paymentStatus; ?></a></td>
						</tr>
					<?php
					}
				}
				else {
				?>
					<tr><td colspan="6" align="center" class="empty-cards">No tickets in this period.</td></tr>
				<?php
				}
			}
			else {
			?>
				<tr><td colspan="6" align="center" class="empty-cards">No tickets yet &mdash; <a href="/drawing">grab one for this week&rsquo;s pot</a>.</td></tr>
			<?php
			}
			?>
			</tbody>
        </table>
		
		<?php
		if($allcount > 0) {
			if($allcount > 5) {
			?>
				<h2 class="load-more-raffle"><i class="fa-solid fa-chevron-down"></i></h2>
				<input type="hidden" id="filter-raffle" value="<?php echo htmlspecialchars($_GET['filter'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
				<input type="hidden" id="row-raffle" value="0">
				<input type="hidden" id="all-raffle" value="<?php echo $allcount; ?>">
				<input type="hidden" id="currentNo-raffle" value="<?php echo $rowperpage; ?>">
			<?php
			}
		}
		?>
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
            <a href="/drawing">Drawing Page</a>
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
	
	
  </body>
</html>
