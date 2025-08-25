<?php
error_reporting(0);
session_start();

require("../config/session.php");

if($getUserRole == 1 || $getUserRole == 3) {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="robots" content="noindex, nofollow">
	<link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>Raffles Done - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link
      href="../assets/font/fontawesome/css/all.min.css"
      rel="stylesheet"
      type="text/css"
    />
    <link rel="stylesheet" href="../assets/css/bootstrap/css/bootstrap.css" />

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet" />
	
	<script src="../assets/js/jquery.min.js"></script>
	<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="/admin/js/raffles-done.js"></script>
  </head>

  <body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
      <!-- Sidebar -->
      <ul
        class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion toggled"
        id="accordionSidebar"
      >
        <!-- Sidebar - Brand -->
        <a
          class="sidebar-brand d-flex align-items-center justify-content-center"
          href="/admin"
        >
          <div class="sidebar-brand-icon rotate-n-15"></div>
          <img
            style="width: 100px; height: 25px"
            src="../assets/images/logo-dark.png"
            alt=""
          />
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0" />

        <!-- Nav Item - Dashboard -->
        <li class="nav-item active">
          <a class="nav-link" href="/admin">
            <i class="fa-solid fa-signal"></i>
            <span>Dashboard</span></a
          >
        </li>

        <li class="nav-item">
          <a class="nav-link" href="account-information">
            <i class="fa-solid fa-user"></i>
            <span>Account Information</span></a
          >
        </li>
        <li class="nav-item">
          <a class="nav-link" href="delegate-access">
            <i class="fa-solid fa-users-gear"></i>
            <span>Delegate Access</span></a
          >
        </li>
        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
          <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
      </ul>
      <!-- End of Sidebar -->

      <!-- Content Wrapper -->
      <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
          <!-- Topbar -->
          <nav
            class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"
          >
            <!-- Sidebar Toggle (Topbar) -->
            <button
              id="sidebarToggleTop"
              class="btn btn-link d-md-none rounded-circle mr-3"
            >
              <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
              <!-- Nav Item - User Information -->
              <li class="nav-item dropdown no-arrow">
				
			  
                <a
                  class="nav-link dropdown-toggle"
                  href="#"
                  id="userDropdown"
                  role="button"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                >
                  <span
                    class="mr-2 d-none d-lg-inline text-gray-600 small"
                    style="color: #fff !important"
                    ><?php echo $getFirstName." ".$getLastName; ?></span
                  >
                  <img
                    class="img-profile rounded-circle"
                    src="../assets/images/user-icon.png"
                  />
                </a>
                <!-- Dropdown - User Information -->
                <div
                  class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                  aria-labelledby="userDropdown"
                >
                  <div class="dropdown-divider"></div>
				  <a class="dropdown-item" href="../user-dashboard">
					<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
					Back to User Dashboard
				  </a>
				  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="../functions/logout">
                    <i
                      class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"
                    ></i>
                    Logout
                  </a>
                </div>
              </li>
            </ul>
          </nav>
          <!-- End of Topbar -->

          <!-- Begin Page Content -->
          <div class="container-fluid">
            <!-- Page Heading -->
            <div
              class="d-sm-flex align-items-center justify-content-between mb-4" style="display: flex;">
				<div><h1 class="h3 mb-0 text-gray-800">Raffles Done</h1></div>
				
				<div class="filter-by-container">
					<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
						Sort by
					</button>
					
					<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
						<li>
							<div class="label-radio"><label for="at"> All Time</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="at" value="at" checked/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="lm"> This Month</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="lm" value="lm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="ltm"> Last 3 Months</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="ltm" value="ltm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="loy"> Last 1 Year</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-dashboard" id="loy" value="loy"/></div>
						</li>
					</ul>
				</div>
            </div>

            <!-- Content Row -->
            <div class="row">
              <!-- Earnings (Monthly) Card Example -->
              <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div
                          class="text-xs font-weight-bold text-info text-uppercase mb-1"
                        >
                          Raffles Done
                        </div>
                        <div class="row no-gutters align-items-center">
                          <div class="col-auto">
                            <div
                              class="h5 mb-0 mr-3 font-weight-bold text-gray-800 raffles-done"
                            >
                              
							<?php
							$qClosedCampaign = mysqli_query($con, "SELECT COUNT(*) AS total_campaign FROM tbl_campaign");
							$dClosedCampaign = mysqli_fetch_array($qClosedCampaign);
							
							echo $dClosedCampaign['total_campaign'];
							?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i
                          class="fas fa-clipboard-list fa-2x text-gray-300"
                        ></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div
                          class="text-xs font-weight-bold text-primary text-uppercase mb-1"
                        >
                          Weekly Raffles
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 weekly-raffles">
							<?php
							$qWeeklyRaffles = mysqli_query($con, "SELECT COUNT(*) AS total_weekly_raffles FROM tbl_campaign WHERE category = 'weekly'");
							$dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles);
							
							echo $dWeeklyRaffles['total_weekly_raffles'];
							?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i
                          class="fas fa-clipboard-list fa-2x text-gray-300"
                        ></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Earnings (Monthly) Card Example -->
              <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div
                          class="text-xs font-weight-bold text-success text-uppercase mb-1"
                        >
                          User Campaigns
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 user-campaigns">
							<?php
							$qUserCampaigns = mysqli_query($con, "SELECT COUNT(*) AS total_user_campaign FROM tbl_campaign WHERE category = ''");
							$dUserCampaigns = mysqli_fetch_array($qUserCampaigns);
							
							echo $dUserCampaigns['total_user_campaign'];
							?>
                        </div>
                      </div>
                      <div class="col-auto">
                        <i
                          class="fas fa-clipboard-list fa-2x text-gray-300"
                        ></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Content Row -->
            <div class="row">
              <!-- Project Card Example -->
              <div class="col-xl-6">
                <div class="card shadow mb-4">
                  <div class="card-header py-3" style="display: flex">
                    <h6 class="m-0 font-weight-bold text-primary">
                      Weekly Raffles
                    </h6>
                    <div class="filter-by-container" style="margin-left: auto">
                      <button
                        class="btn dropdown-toggle btn-filter"
                        type="button"
                        id="dropdownMenuClickableInside"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        style="background: #e7e7e7"
                      >
                        Sort by
                      </button>

                      <ul
                        class="dropdown-menu"
                        aria-labelledby="dropdownMenuClickableInside"
                        style="
                          box-shadow: 0 5px 5px -5px #333;
                          min-width: 250px;
                        "
                      >
                        <li>
							<div class="label-radio"><label for="wr-at"> All Time</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-weekly-raffle" id="wr-at" value="wr-at" checked/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="wr-lm"> This Month</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-weekly-raffle" id="wr-lm" value="wr-lm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="wr-ltm"> Last 3 Months</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-weekly-raffle" id="wr-ltm" value="wr-ltm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="wr-loy"> Last 1 Year</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-weekly-raffle" id="wr-loy" value="wr-loy"/></div>
						</li>
                      </ul>
                    </div>
                  </div>
                  <div class="card-body">
					<div style="width: 100%; overflow-x: auto;">
                    <table class="tbl-weekly-raffless">
						<thead>
							<tr>
								<th>No</th>
								<th>Date</th>
								<th>Pot</th>
								<th>Winner</th>
								<th>Payment</th>
							</tr>
						</thead>
						
						<tbody>
							<?php
							$i = 1;
							$rowperpage = 5;

							$allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE category = 'weekly'";
							$allcount_result = mysqli_query($con,$allcount_query);
							$allcount_fetch = mysqli_fetch_array($allcount_result);
							$allcount = $allcount_fetch['allcount'];
							
							if($allcount > 0) {
								$qWeeklyRaffles = mysqli_query($con, "
								SELECT
								tbl_campaign.weekly_no,
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
								tbl_campaign.category = 'weekly'
								GROUP BY tbl_campaign.campaign_id 
								ORDER BY tbl_campaign.weekly_no DESC, rating DESC limit 0,$rowperpage
								");
								
								while($dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles)) {
									$campaignID = $dWeeklyRaffles['campaign_id'];
									$endDate = $dWeeklyRaffles['end_date'];
									$weeklyNo = $dWeeklyRaffles['weekly_no'];
									$status = $dWeeklyRaffles['status'];
									$pageURL = $dWeeklyRaffles['page_url'];
									
									if($status == "open") {
										$elementStatus = "<div class='blinking-green'></div>";
									}
									else {
										$elementStatus = "<div class='blinking-red'></div>";
									}
								  ?>
								  <tr>
									<td align="center">
										<div style="display: inline-block; vertical-align: middle;"><?php
										echo $elementStatus;
										?>
										</div>
										
										<div style="display: inline-block; vertical-align: middle;"><span style="padding-left: 3px;"><a href="../<?php echo $pageURL; ?>"><?php echo $weeklyNo; ?></a></span>
										</div>
									</td>
									<td><a href="../<?php echo $pageURL; ?>"><?php echo $endDate; ?></a></td>
									<td align="center">
										<a href="../<?php echo $pageURL; ?>">
											$<?php
											$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
											$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
											
											$totalPrice = $dAccumulateTicket['total_accumulate'];
											
											echo $totalPrice;
											?>
										</a>
									</td>
									<td>
										<a href="../<?php echo $pageURL; ?>">
											<?php
											$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE win = 'Y' AND campaignid_fk = '" . $campaignID . "'");
											$dWinner = mysqli_fetch_array($qWinner);
											
											echo $dWinner['first_name']." ".$dWinner['last_name'];
											?>
										</a>
									</td>
									<td align="center">
										<?php
										$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE win = 'Y' AND campaignid_fk = '" . $campaignID . "'");
										if(mysqli_num_rows($qWinner) > 0) {
											$qChk = mysqli_query($con, "SELECT * FROM tbl_payment WHERE campaignid_fk = '" . $campaignID . "'");
											
											$dChk = mysqli_fetch_array($qChk);
											
											$paymentOption = $dChk['payment_option'];
											
											if($dChk['total'] == "") {
												$total = "";
											}
											else {
												$total = "$".$dChk['total'];
											}
											
											if($paymentOption == "Zelle") {
												$checkedZelle = "checked";
											}
											else {
												$checkedZelle = "";
											}
											
											if($paymentOption == "Paypal") {
												$checkedPaypal = "checked";
											}
											else {
												$checkedPaypal = "";
											}
											
											if($paymentOption != "Zelle" && $paymentOption != "Paypal" && $paymentOption != "") {
												$checkedCustom = "checked";
												$elementInput = "";
											}
											else {
												$checkedCustom = "";
												$elementInput = "style='display: none;'";
											}
											?>
											<span class="pay-option-text pay-option-text<?php echo $campaignID; ?>"><?php echo $paymentOption. " ".$total ?></span>
										
											<?php
											if($getUserRole == 3) {}
											else {
											?>
												<div class="filter-by-container filter-by-container<?php echo $campaignID; ?>">
													<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
														Pay Option
													</button>
													
													<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
														<li>
															<div class="label-radio"><label for="zelle<?php echo $campaignID; ?>"> Zelle $<?php echo $totalPrice / 2; ?></label></div>
															<div class="input-radio"><input type="radio" class="radio-pay-option" name="pay-option<?php echo $campaignID; ?>" data-campaign-id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" id="zelle<?php echo $campaignID; ?>" value="Zelle" <?php echo $checkedZelle; ?>/></div>
														</li>
														
														<li>
															<div class="label-radio"><label for="paypal<?php echo $campaignID; ?>"> Paypal $<?php echo $totalPrice / 2; ?></label></div>
															<div class="input-radio"><input type="radio" class="radio-pay-option" name="pay-option<?php echo $campaignID; ?>" data-campaign-id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" id="paypal<?php echo $campaignID; ?>" value="Paypal" <?php echo $checkedPaypal; ?>/></div>
														</li>
														
														<li>
															<div class="label-radio"><label for="custom<?php echo $campaignID; ?>"> Custom $<?php echo $totalPrice / 2; ?></label></div>
															<div class="input-radio"><input type="radio" class="radio-pay-option" name="pay-option<?php echo $campaignID; ?>" data-campaign-id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" id="custom<?php echo $campaignID; ?>" value="Custom" <?php echo $checkedCustom; ?>/></div>
															
															<div class="container-custom-text<?php echo $campaignID; ?>" <?php echo $elementInput; ?>>
																<input type="text" class="custom-text custom-text<?php echo $campaignID; ?>" id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" style="width: 100%; box-sizing: border-box;" value="<?php echo $paymentOption; ?>"/>
															</div>
														</li>
													</ul>
												</div>
											<?php
											}
										}
										?>
									</td>
								  </tr>
								  <?php
								  $i++;
								}
							}
							?>
						</tbody>
						<?php
                      ?>
                    </table>
					</div>
					
					<?php
					if($allcount > 0) {
						if($allcount > 5) {
						?>
							<h2 class="load-more" style="text-align: center; font-size: 18px; margin-top: 5px;"><i class="fa-solid fa-chevron-down"></i></h2>
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
              </div>
              <div class="col-xl-6">
                <div class="card shadow mb-4">
                  <div class="card-header py-3" style="display: flex">
                    <h6 class="m-0 font-weight-bold" style="color: #1cc88a">
                      User Campaigns
                    </h6>
                    <div class="filter-by-container" style="margin-left: auto">
                      <button
                        class="btn dropdown-toggle btn-filter"
                        type="button"
                        id="dropdownMenuClickableInside"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        style="background: #e7e7e7"
                      >
                        Sort by
                      </button>

                      <ul
                        class="dropdown-menu"
                        aria-labelledby="dropdownMenuClickableInside"
                        style="
                          box-shadow: 0 5px 5px -5px #333;
                          min-width: 250px;
                        "
                      >
                        <li>
							<div class="label-radio"><label for="at-cu"> All Time</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-campaign-user" id="at-cu" value="at" checked/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="lm-cu"> This Month</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-campaign-user" id="lm-cu" value="lm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="ltm-cu"> Last 3 Months</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-campaign-user" id="ltm-cu" value="ltm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="loy-cu"> Last 1 Year</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter-campaign-user" id="loy-cu" value="loy"/></div>
						</li>
                      </ul>
                    </div>
                  </div>
                  <div class="card-body">
				  
				  <div style="width: 100%; overflow-x: auto;">
					  <table class="tbl-user-campaigns">
						<thead>
							<tr>
								<th>No</th>
								<th>Date</th>
								<th>Pot</th>
								<th>Winner</th>
								<th>Payment</th>
							</tr>
						</thead>
						
						<tbody>
							<?php
							$rowperpage = 5;

							$allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE category = ''";
							$allcount_result = mysqli_query($con,$allcount_query);
							$allcount_fetch = mysqli_fetch_array($allcount_result);
							$allcount = $allcount_fetch['allcount'];
							
							if($allcount > 0) {
								$i = 1;
								
								$qWeeklyRaffles = mysqli_query($con, "
								  SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, status, page_url FROM tbl_campaign WHERE category = '' ORDER BY status DESC, end_date DESC limit 0,$rowperpage
								");
								
								while($dWeeklyRaffles = mysqli_fetch_array($qWeeklyRaffles)) {
									$campaignID = $dWeeklyRaffles['campaign_id'];
									$endDate = $dWeeklyRaffles['end_date'];
									$status = $dWeeklyRaffles['status'];
									
									if($status == "open") {
										$elementStatus = "<div class='blinking-green'></div>";
										
										$pageURL = $dWeeklyRaffles['page_url']; 
									}
									else {
										$elementStatus = "<div class='blinking-red'></div>";
										
										$pageURL = $dWeeklyRaffles['campaign_id']; 
									}
								  ?>
								  <tr>
									<td align="center">
										<div style="display: inline-block; vertical-align: middle;">
											<a href="../<?php echo $pageURL; ?>">
												<?php
												echo $elementStatus;
												?>
											</a>
										</div>
										
										<div style="display: inline-block; vertical-align: middle;">
											<a href="../<?php echo $pageURL; ?>">
												<span style="padding-left: 3px;"><?php echo $i; ?></span>
											</a>
										</div>
									</td>
									<td><a href="../<?php echo $pageURL; ?>"><?php echo $endDate; ?></a></td>
									<td align="center">
										<a href="../<?php echo $pageURL; ?>">
											$<?php
											$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
											$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
											
											$totalPrice = $dAccumulateTicket['total_accumulate'];
											
											echo $totalPrice;
											?>
										</a>
									</td>
									<td>
										<a href="../<?php echo $pageURL; ?>">
											<?php
											$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE win = 'Y' AND campaignid_fk = '" . $campaignID . "'");
											$dWinner = mysqli_fetch_array($qWinner);
											
											echo $dWinner['first_name']." ".$dWinner['last_name'];
											?>
										</a>
									</td>
									<td align="center">
										<?php
										$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE win = 'Y' AND campaignid_fk = '" . $campaignID . "'");
										if(mysqli_num_rows($qWinner) > 0) {
											$qChk = mysqli_query($con, "SELECT * FROM tbl_payment WHERE campaignid_fk = '" . $campaignID . "'");
											if(mysqli_num_rows($qChk) > 0) {
												$dChk = mysqli_fetch_array($qChk);
												
												if($dChk['total'] == "") {
													$total = "";
												}
												else {
													$total = "$".$dChk['total'];
												}
												
												$paymentOption = $dChk['payment_option'];
												
												if($paymentOption == "Zelle") {
													$checkedZelle = "checked";
												}
												else {
													$checkedZelle = "";
												}
												
												if($paymentOption == "Paypal") {
													$checkedPaypal = "checked";
												}
												else {
													$checkedPaypal = "";
												}
												
												if($paymentOption != "Zelle" && $paymentOption != "Paypal") {
													$checkedCustom = "checked";
													$elementInput = "";
												}
												else {
													$checkedCustom = "";
													$elementInput = "style='display: none;'";
												}
											}
											else {
												$paymentOption = "";
												$total = "";
												$checkedZelle = "";
												$checkedPaypal = "";
												$checkedCustom = "";
												$elementInput = "style='display: none;'";
											}
											?>
											<span class="pay-option-text pay-option-text<?php echo $campaignID; ?>"><?php echo $paymentOption. " ".$total ?></span>
										
											<?php
											if($getUserRole == 3) {}
											else {
											?>
												<div class="filter-by-container filter-by-container<?php echo $campaignID; ?>">
													<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
														Pay Option
													</button>
													
													<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
														<li>
															<div class="label-radio"><label for="zelle<?php echo $campaignID; ?>"> Zelle $<?php echo $totalPrice / 2; ?></label></div>
															<div class="input-radio"><input type="radio" class="radio-pay-option" name="pay-option<?php echo $campaignID; ?>" data-campaign-id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" id="zelle<?php echo $campaignID; ?>" value="Zelle" <?php echo $checkedZelle; ?>/></div>
														</li>
														
														<li>
															<div class="label-radio"><label for="paypal<?php echo $campaignID; ?>"> Paypal $<?php echo $totalPrice / 2; ?></label></div>
															<div class="input-radio"><input type="radio" class="radio-pay-option" name="pay-option<?php echo $campaignID; ?>" data-campaign-id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" id="paypal<?php echo $campaignID; ?>" value="Paypal" <?php echo $checkedPaypal; ?>/></div>
														</li>
														
														<li>
															<div class="label-radio"><label for="custom<?php echo $campaignID; ?>"> Custom $<?php echo $totalPrice / 2; ?></label></div>
															<div class="input-radio"><input type="radio" class="radio-pay-option" name="pay-option<?php echo $campaignID; ?>" data-campaign-id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" id="custom<?php echo $campaignID; ?>" value="Custom" <?php echo $checkedCustom; ?>/></div>
															
															<div class="container-custom-text<?php echo $campaignID; ?>" <?php echo $elementInput; ?>>
																<input type="text" class="custom-text custom-text<?php echo $campaignID; ?>" id="<?php echo $campaignID; ?>" data-pay="<?php echo $totalPrice / 2; ?>" style="width: 100%; box-sizing: border-box;" value="<?php echo $paymentOption; ?>"/>
															</div>
														</li>
													</ul>
												</div>
											<?php
											}
										}
										?>
									</td>
								  </tr>
								  <?php
								$i++;
								}
							}
							?>
						</tbody>
						</table>
					</div>
					
					<?php
					if($allcount > 0) {
						if($allcount > 5) {
						?>
							<h2 class="load-more-user" style="text-align: center; font-size: 18px; margin-top: 5px;"><i class="fa-solid fa-chevron-down"></i></h2>
							<input type="hidden" id="filter-user" value="<?php echo $_GET['filter']; ?>">
							<input type="hidden" id="row-user" value="0">
							<input type="hidden" id="all-user" value="<?php echo $allcount; ?>">
							<input type="hidden" id="currentNo-user" value="<?php echo $rowperpage; ?>">
						<?php
						}
					}
					?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
          <div class="container my-auto">
            <div class="copyright text-center my-auto">
              <span>Copyright &copy; Thegoral.com 2021</span>
            </div>
          </div>
        </footer>
        <!-- End of Footer -->
      </div>
      <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
  </body>
</html>
<?php
}
else if($getUserRole == 2) {
	header("Location: ../sign-in");
}
else {
	header("Location: ../sign-in");
}
?>