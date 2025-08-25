<?php
session_start();

require("config/session.php");

if($getUserID == "") {
	header("Location: /sign-in");
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
  <body>
    <div class="header-ac-bg" style="background: white">
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
          <div class="title-dash">Dashboard</div>
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
					$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(COUNT(*), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
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
				$qRafflesWon = mysqli_query($con, "SELECT COALESCE(SUM(total_price),0) AS total_won FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND win = 'Y'");
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
					SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' ORDER BY purchased_date DESC LIMIT 5
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
					<td colspan="5" align="center">No data</td>
				<?php
				}
			}
			?>
			</tbody>
        </table>
		
		<?php
		if($allcount > 0) {
			if($allcount > 5) {
			?>
				<h2 class="load-more-raffle"><i class="fa-solid fa-chevron-down"></i></h2>
				<input type="hidden" id="filter-raffle" value="<?php echo $_GET['filter']; ?>">
				<input type="hidden" id="row-raffle" value="0">
				<input type="hidden" id="all-raffle" value="<?php echo $allcount; ?>">
				<input type="hidden" id="currentNo-raffle" value="<?php echo $rowperpage; ?>">
			<?php
			}
		}
		?>
      </div>
	  <div class="row">
        <div class="col-md">
          <div class="title-dash">Campaigns</div>
        </div>
      </div>
      <div class="row tbl-rh" style="overflow-x: auto;">
        <table class="tbl-campaign" id="customers">
			<thead>
			<tr>
            <th>Name</th>
            <th>Date</th>
			<th>Participants</th>
            <th>Pot</th>
            <th>Status</th>
			<th>
				<div class="filter-by-container2">
					<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; padding 0px; font-size: 12px;">
						Sort by
					</button>
					
					<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
						<li>
							<div class="label-radio"><label for="pmmtl"> Pot: Most Money - Least</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter" id="pmmtl" value="pmmtl" <?php if($_GET['filter'] == "" || $_GET['filter'] == "pmmtl") { echo "checked"; } ?>/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="clltm"> Countdown: least time left - most</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter" id="clltm" value="clltm" <?php if($_GET['filter'] == "clltm") { echo "checked"; } ?>/></div>
						</li>
					</ul>
				</div>
			</th>
			</tr>
			</thead>
		  
		  <tbody>
			<?php
			$rowperpage = 5;

            $allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE added_by = '$getUserID'";
            $allcount_result = mysqli_query($con,$allcount_query);
            $allcount_fetch = mysqli_fetch_array($allcount_result);
            $allcount = $allcount_fetch['allcount'];

			if($allcount > 0) {
				$query = "
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
					DATE_FORMAT(tbl_campaign.start_date,'%Y-%m-%d %H:%i') AS start_date_f,
					DATE_FORMAT(tbl_campaign.end_date,'%d') AS end_date,
					DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d %H:%i') AS end_date_f,
					COALESCE(SUM(total_price),0) as rating
					FROM tbl_campaign
					LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
					WHERE
					tbl_campaign.added_by = '$getUserID'
					GROUP BY tbl_campaign.campaign_id 
					ORDER BY tbl_campaign.status DESC, rating DESC limit 0,$rowperpage";
				$result = mysqli_query($con,$query);
			
				$i = 1;
				while($data = mysqli_fetch_array($result)){
					
					if($data['status'] == "open") {
						$url = $data['page_url'];
					}
					else {
						$url = $data['campaign_id'];
					}
					?>
					
					<tr class="post" id="post_<?php echo $data['campaign_id']; ?>">
						<td class="label-campaign-name<?php echo $data['campaign_id']; ?>"><a href="<?php echo $url; ?>"><?php echo $data['campaign_name']; ?></a></td>
						<td class="label-sd-ed<?php echo $data['campaign_id']; ?>"><a href="<?php echo $url; ?>"><?php echo $data['start_date']; ?> / <?php echo $data['end_date']; ?></a></td>
						<td><a href="<?php echo $url; ?>">
							<?php
							$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
							
							echo mysqli_num_rows($qAccumulateParticipant);
							?>
						</a></td>
						<td><a href="<?php echo $url; ?>">
							$<?php
							$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
							$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
							
							echo $dAccumulateTicket['total_accumulate'];
							?>
						</a></td>
						<td align="center">
							<?php
							if($data['status'] == "open") {
							?>
								<div class="blinking-green"></div>
							<?php
							}
							else {
							?>
								<div class="blinking-red"></div>
							<?php
							}
							?>
							
							<input type="hidden" class="label-public<?php echo $data['campaign_id']; ?>"/>
						</td>
						<td>
							<?php
							if($data['status'] == "open") {
							?>
								<button class="btn editCampaign editCampaign<?php echo $data['campaign_id']; ?>" id="<?php echo $data['campaign_id']; ?>" type="button" style="background: #e7e7e7; font-size: 12px;"
								data-first-name="<?php echo $data['first_name']; ?>"
											data-last-name="<?php echo $data['last_name']; ?>"
											data-email="<?php echo $data['email_address']; ?>"
											data-phone="<?php echo $data['phone']; ?>"
											data-start-date="<?php echo $data['start_date_f']; ?>"
											data-end-date="<?php echo $data['end_date_f']; ?>"
											data-campaign-name="<?php echo $data['campaign_name']; ?>"
											data-page-url="<?php echo $data['page_url']; ?>"
											data-public="<?php echo $data['public']; ?>">
									Edit
								</button>
							<?php
							}
							?>
						</td>
					</tr>
					<?php
					$i++;
				}
			}
			else {
			?>
				<td colspan="7" align="center">No data</td>
			<?php
			}
			?>
		</tbody>
        </table>
		
		<?php
		if($allcount > 0) {
			if($allcount > 5) {
			?>
				<h2 class="load-more"><i class="fa-solid fa-chevron-down"></i></h2>
				<input type="hidden" id="filter" value="<?php echo $_GET['filter']; ?>">
				<input type="hidden" id="row" value="0">
				<input type="hidden" id="all" value="<?php echo $allcount; ?>">
				<input type="hidden" id="currentNo" value="<?php echo $rowperpage; ?>">
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
            <a class="active" href="">Home</a>
            <a href="/live-campaign.html">Live Campaigns</a>
            <a href="/all-campaign.html">All Campaigns</a>
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
	
	<div
      class="modal fade bd-example-modal-lg"
      id="editCampModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="createCampModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Edit My Campaign <button class='closeBtn'><i class="fa-solid fa-circle-xmark"></i></button>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md">
                <div class="form-group">
                  <label for="country"
                    >Start Date & Time<small style="color: red">*</small></label
                  >
				  <input type="hidden" id="edtCampaignID"/>
                  <input
				  	type="datetime-local"
                    class="form-control"
                    id="edtStartDate"
                    autocomplete="off"
                  />
                </div>
              </div>
              <div class="col-md">
                <div class="form-group">
                  <label for="state"
                    >End Date & Time<small style="color: red">*</small></label
                  >
                  <input
				  	type="datetime-local"
                    class="form-control"
                    id="edtEndDate"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                <div class="form-group">
                  <label for="campaignName"
                    >Campaign Name<small style="color: red">*</small></label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="edtCampaignName"
                    autocomplete="off"
                  />
                </div>
              </div>
            </div>
           
            <div class="row">
              <div class="col-md">
                <div class="text-mmc">Make my campaign</div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                <div class="form-check form-switch">
				<label class="switch">
						<input type="checkbox" checked id="edtPublic">
						<span class="slider"></span>
						<span class="text on">Public</span>
						<span class="text off">Private</span>
					</label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
                <button type="submit" class="btnCreateCamp" id="btnUpdateCamp" style="font-weight: bold;">
                  Update My Campaign
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
