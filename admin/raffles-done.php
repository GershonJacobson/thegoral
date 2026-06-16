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
    <!-- The Goral brand layer (overrides sb-admin-2) -->
    <link href="css/goral-admin.css" rel="stylesheet" />
	
	<script src="../assets/js/jquery.min.js"></script>
	<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="/admin/js/raffles-done.js"></script>
	<script src="/admin/js/participants.js"></script>
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
            style="width: 64px; height: auto"
            src="../assets/images/logo.svg"
            alt=""
          />
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-0" />

        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
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
        <li class="nav-item active">
          <a class="nav-link" href="raffles-done">
            <i class="fa-solid fa-clipboard-check"></i>
            <span>Raffles Done</span></a
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
                    ><?php echo htmlspecialchars($getFirstName." ".$getLastName, ENT_QUOTES, 'UTF-8'); ?></span
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
				
				
            </div>

            <!-- Content Row -->
            <div class="row">
              <!-- Project Card Example -->
              <div class="col-xl-8">
                <div class="card shadow mb-4">
                  <div class="card-header py-3" style="display: flex">
                    <h6 class="m-0 font-weight-bold text-primary">
                      Weekly Raffles
                    </h6>
                    
                  </div>
                  <div class="card-body">
					<div style="width: 100%; overflow-x: auto;">
                    <table class="tbl-weekly-raffless">
						<thead>
							<tr>
								<th>No</th>
								<th>Date</th>
								<th>Pot</th>
								<th>Participants</th>
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
									<td align="center">
										<?php
										$qPCount = mysqli_query($con, "SELECT COUNT(DISTINCT email) AS c FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "' AND ticket_no IS NOT NULL");
										$pCount = mysqli_fetch_array($qPCount)['c'];
										?>
										<a href="#" class="js-participants" data-campaign-id="<?php echo $campaignID; ?>" title="View participants"><?php echo $pCount; ?></a>
									</td>
									<td>
										<a href="../<?php echo $pageURL; ?>">
											<?php
											$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE win = 'Y' AND campaignid_fk = '" . $campaignID . "'");
											$dWinner = mysqli_fetch_array($qWinner);

											echo htmlspecialchars($dWinner['first_name']." ".$dWinner['last_name'], ENT_QUOTES, 'UTF-8');
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
							<input type="hidden" id="filter" value="<?php echo htmlspecialchars($_GET['filter'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
              
            </div>
          </div>
          <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
          <div class="container my-auto">
            <div class="copyright text-center my-auto">
              <span>&copy; <?php echo date('Y'); ?> The Goral</span>
            </div>
          </div>
        </footer>
        <!-- End of Footer -->
      </div>
      <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <?php require("participants-modal.php"); ?>

    <!-- Custom scripts for all pages-->
    <script src="/admin/js/sb-admin-2.min.js"></script>
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