<?php
error_reporting(0);
session_start();

require("../config/session.php");

if($getUserRole != 0) {
?>
	<!DOCTYPE html>
	<html lang="en">
	  <head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="robots" content="noindex, nofollow">
		<link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<title>Admin - Dashboard</title>

		<!-- Custom fonts for this template-->
		<link
		  href="../assets/font/fontawesome/css/all.min.css"
		  rel="stylesheet"
		  type="text/css"
		/>
		<link rel="stylesheet" href="../assets/css/sweetalert.css" />
		<link
		  rel="stylesheet"
		  href="../assets/css/bootstrap/css/bootstrap.min.css"
		/>
		<link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />

		<!-- Custom styles for this template-->
		<link href="css/sb-admin-2.css" rel="stylesheet" />
		
		<!-- Bootstrap core JavaScript-->
		<script src="../assets/js/jquery.min.js"></script>
		<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="../assets/js/sweetalert.min.js"></script>
		<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
		<script src="/admin/js/dashboard.js"></script>
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

		

		$('#edtEndDate').change(function() {
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
						><?php echo $getFirstName." ".$getLastName; ?> </span
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
				  class="d-sm-flex align-items-center justify-content-between mb-4" style="display: flex;"
				>
				  <div><h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
					<span class="text-server-time">Server Time: </span>
					<span class="value-server-time" id="clock"><?php echo date("d F Y H:i:s"); ?></span>
					
					<script type="text/javascript">
						const monthNames = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
					
						<?php
						$tz_time = date("D, d M Y H:i:s");
						?>
                        var currenttime = '<?php echo $tz_time;?>'; // Timestamp of the timezone you want to use, in this case, it's server time
                        var servertime=new Date(currenttime);
                        function padlength(l){
                            var output=(l.toString().length==1)? "0"+l : l;
                            return output;
                        }
                        function digitalClock(){
                            servertime.setSeconds(servertime.getSeconds()+1);
                            var timestring=padlength(servertime.getDate() + " " + monthNames[servertime.getMonth()] + " " + servertime.getFullYear() + " " + padlength(servertime.getHours())) +":"+padlength(servertime.getMinutes())+":"+padlength(servertime.getSeconds());
                            document.getElementById("clock").innerHTML=timestring;
                        }
                        window.onload=function(){
                        setInterval("digitalClock()", 1000);
                        }
					</script> 
				  </div>
				  
				  <?php
				  if($getUserRole == 2) {}
				  else {
				  ?>
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
				<?php
				  }
				?>
				</div>

				<!-- Content Row -->
				<?php
				if($getUserRole == 2) {}
				else {
				?>
					<div class="row">
					  <!-- Earnings (Monthly) Card Example -->
					  <div class="col-xl-4 col-md-6 mb-4">
						<div class="card border-left-primary shadow h-100 py-2">
						  <div class="card-body">
							<div class="row no-gutters align-items-center">
							  <div class="col mr-2">
								<div
								  class="text-xs font-weight-bold text-primary text-uppercase mb-1"
								>
								  Total Earnings
								</div>
								<div class="h5 mb-0 font-weight-bold text-gray-800 total-earnings">
								  $<?php
									$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket");
									$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
									
									echo $dAccumulateTicket['total_accumulate'];
									?>
								</div>
							  </div>
							  <div class="col-auto">
								<i class="fas fa-calendar fa-2x text-gray-300"></i>
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
								  Profits
								</div>
								<div class="h5 mb-0 font-weight-bold text-gray-800 total-profits">
									$<?php
									$qProfit = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_profit FROM tbl_ticket WHERE win_ticket_id != 0");
									$dProfit = mysqli_fetch_array($qProfit);
									
									echo $dProfit['total_profit'];
									?>
								</div>
							  </div>
							  <div class="col-auto">
								<i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
							  </div>
							</div>
						  </div>
						</div>
					  </div>

					  <!-- Earnings (Monthly) Card Example -->
					  <div class="col-xl-4 col-md-6 mb-4">
						<a href="raffles-done" style="text-decoration: none">
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
						</a>
					  </div>
					</div>
				<?php
				}
				?>
				<!-- Content Row -->
				
				<div class="filter-by-container">
					<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; margin-bottom: 10px;">
						Sort by
					</button>
					
					<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333; min-width: 250px;">
						<li>
							<div class="label-radio"><label for="pmmtl"> Pot: Most Money - Least</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter" id="pmmtl" value="pmmtl" checked/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="plmtm"> Pot: Least Money - Most</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter" id="plmtm" value="plmtm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="clltm"> Countdown: least time left - most</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter" id="clltm" value="clltm"/></div>
						</li>
						
						<li>
							<div class="label-radio"><label for="cmltl"> Countdown: most time left - least</label></div>
							<div class="input-radio"><input type="radio" class="radio" name="filter" id="cmltl" value="cmltl"/></div>
						</li>
					</ul>
				</div>
				
				<script>
				var countdowns = [];
				</script>
				
				<?php
				$rowperpage = 5;

				$allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1)";
				$allcount_result = mysqli_query($con,$allcount_query);
				$allcount_fetch = mysqli_fetch_array($allcount_result);
				$allcount = $allcount_fetch['allcount'];
				?>
				<div class="campaign-list">
				<?php
				if($allcount > 0) {
					$query = mysqli_query($con, "
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
						tbl_campaign.category,
						DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
						DATE_FORMAT(tbl_campaign.start_date,'%m/%d/%Y') AS start_date_f,
						tbl_campaign.end_date,
						DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date_f,
						DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
						DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
						COALESCE(SUM(total_price),0) as rating
						FROM tbl_campaign
						LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
						WHERE
						(tbl_campaign.status = 'open' AND tbl_campaign.keep_show = 0) OR (tbl_campaign.status = 'closed' AND tbl_campaign.keep_show = 1)
						GROUP BY tbl_campaign.campaign_id 
						ORDER BY
						(CASE WHEN category='weekly' AND status='open' THEN status END) DESC,
						(CASE WHEN category='' AND status='open' THEN status END) DESC,
						rating DESC limit 0,$rowperpage");
					
					if(mysqli_num_rows($query) > 0) {
						while($data = mysqli_fetch_array($query)) {
							$pageURL = $data['page_url'];
							
							$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
							
							$totalParticipant = mysqli_num_rows($qAccumulateParticipant);
							?>
							<script>
							countdowns.push({
								campaignID: "<?php echo $data['campaign_id']; ?>",
								countdownDate: new Date("<?php echo $data['end_date']; ?>".replace(" ", "T")).getTime()
							  });
							</script>
							
							<div class="row post" id="post_<?php echo $data['campaign_id']; ?>">
							  <div class="col-xl-12 mb-4">
								<div class="card-info">
								  <div class="row text-info-ds">
									<div class="col-md-3 user-icon">
										<a href="../<?php echo $pageURL; ?>">
											<img src="../assets/images/user-icon.png" alt="" />
											<span class="campaign-name<?php echo $data['campaign_id']; ?>"><?php echo $data['campaign_name']; ?></span>
										</a>
									</div>
									<div class="col-md-2">
										<a href="../<?php echo $pageURL; ?>">
										<?php
										echo $totalParticipant;
										?>
										Participant
										</a>
									</div>
									<div class="col-md-3"><a href="../<?php echo $pageURL; ?>">Time Left <span class="countdown-<?php echo $data['campaign_id']; ?>">0d 0h 0m 0s</span></a></div>
									<div class="col-md-1" style="display: flex; align-items: center;">
										<a href="../<?php echo $pageURL; ?>" style="display: flex; align-items: center;">
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
											
											$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
											$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
											?>
											
											<span style="padding-left: 5px;"> $<?php echo $dAccumulateTicket['total_accumulate'];
											?>
											</span>
										</a>
									</div>
									
									<div>
										<?php
										if($getUserRole == 3) {}
										else {
											if($data['category'] == "weekly") {
												if($data['status'] == "open") {
												?>
													<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
														<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
															Action
														</button>
														
														<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
															<?php
															$qTicketPrice = mysqli_query($con, "SELECT * FROM tbl_ticket_price WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
															if(mysqli_num_rows($qTicketPrice) > 0) {
																$dTicketPrice = mysqli_fetch_array($qTicketPrice);
																
																$ticketPrice1 = $dTicketPrice['1ticket_price'];
																$ticketPrice2 = $dTicketPrice['2ticket_price'];
															}
															else {
																$ticketPrice1 = 2;
																$ticketPrice2 = 3;
															}
															?>
														
															<li>
																<a class="edit-campaign edit-campaign<?php echo $data['campaign_id']; ?>" id="<?php echo $data['campaign_id']; ?>"
																data-start-date="<?php echo $data['start_date_f']; ?>"
																data-end-date="<?php echo $data['end_date_f']; ?>"
																data-start-time="<?php echo $data['start_time']; ?>"
																data-end-time="<?php echo $data['end_time']; ?>"
																data-campaign-name="<?php echo $data['campaign_name']; ?>"
																data-category="<?php echo $data['category']; ?>"
																data-public="<?php echo $data['public']; ?>"
																data-ticket-price1="<?php echo $ticketPrice1; ?>"
																data-ticket-price2="<?php echo $ticketPrice2; ?>"
																>Edit</a>
															</li>
														</ul>
													</div>
												<?php
												}
											}
											else {
												if($data['status'] == "open") {
												?>
													<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
														<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
															Action
														</button>
														
														<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
															<?php
															$qTicketPrice = mysqli_query($con, "SELECT * FROM tbl_ticket_price WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
															if(mysqli_num_rows($qTicketPrice) > 0) {
																$dTicketPrice = mysqli_fetch_array($qTicketPrice);
																
																$ticketPrice1 = $dTicketPrice['1ticket_price'];
																$ticketPrice2 = $dTicketPrice['2ticket_price'];
															}
															else {
																$ticketPrice1 = 2;
																$ticketPrice2 = 3;
															}
															?>
														
															<li>
																<a class="edit-campaign edit-campaign<?php echo $data['campaign_id']; ?>" id="<?php echo $data['campaign_id']; ?>"
																data-start-date="<?php echo $data['start_date_f']; ?>"
																data-end-date="<?php echo $data['end_date_f']; ?>"
																data-start-time="<?php echo $data['start_time']; ?>"
																data-end-time="<?php echo $data['end_time']; ?>"
																data-campaign-name="<?php echo $data['campaign_name']; ?>"
																data-category="<?php echo $data['category']; ?>"
																data-public="<?php echo $data['public']; ?>"
																data-ticket-price1="<?php echo $ticketPrice1; ?>"
																data-ticket-price2="<?php echo $ticketPrice2; ?>"
																>Edit</a>
															</li>
															
															<?php
															if($totalParticipant > 0) {}
															else {
															?>
																<li>
																	<a class="deleteCampaign" id="<?php echo $data['campaign_id']; ?>">Delete</a>
																</li>
															<?php
															}
															?>
														</ul>
													</div>
												<?php
												}
												else {
													if($totalParticipant > 0) {}
													else {
													?>
														<div class="filter-by-container3" style="position: absolute; top: 5px; right: 20px;">
															<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7;">
																Action
															</button>
															
															<ul class="dropdown-menu" aria-labelledby="dropdownMenuClickableInside" style="box-shadow: 0 5px 5px -5px #333;">
																<li>
																	<a class="deleteCampaign" id="<?php echo $data['campaign_id']; ?>">Delete</a>
																</li>
															</ul>
														</div>
													<?php
													}
												}
											}
										}
										?>
									</div>
								  </div>
								</div>
							  </div>
							</div>
						<?php
						}
					}
					?>
					</div>
					<script>
					$(document).ready(function () {
						var timer = setInterval(function() {
							// Get todays date and time
							
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
								var abc = "<b><span>"+days+"d</span> " + "<span>"+hours+"</span>h " + "<span>"+minutes+"</span>m " + "<span>"+seconds+"</span>s</b>";
								$(".countdown-" + countdown.campaignID).html(abc);
								// If the count down is over, write some text
								if (distance < 0) {
									//timerElement.innerHTML = "EXPIRED";
									// this timer is done, remove it
									$(".countdown-" + countdown.campaignID).text("COMPLETED");
									countdowns.splice(index, 1);
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
					
				<?php
				}
				
				if($allcount > 0) {
					if($allcount > 5) {
					?>
						<div class="row" style="text-align: center;">
							<h2 class="load-more"><i class="fa-solid fa-chevron-down" style="font-size: 20px; cursor: pointer;"></i></h2>
							<input type="hidden" id="filter" value="<?php echo $_GET['filter']; ?>">
							<input type="hidden" id="row" value="0">
							<input type="hidden" id="all" value="<?php echo $allcount; ?>">
							<input type="hidden" id="currentNo" value="<?php echo $rowperpage; ?>">
						</div>
					<?php
					}
				}
				?>
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
					Edit Campaign <button class='closeBtn'><i class="fa-solid fa-circle-xmark"></i></button>
				  </div>
				</div>
				
				<div class="row">
				  <div class="col-md">
					<div class="form-group">
					  <label for="edtStartDate"
						>Start Date<small style="color: red">*</small></label
					  >
					  <input type="hidden" id="edtCampaignID"/>
					  <input type="hidden" id="edtCategory"/>
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
					  <label for="edtEndDate"
						>End Date<small style="color: red">*</small></label
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
					  <label for="edtCampaignName"
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
				  <div class="col-md col-50-bn">
					<div class="form-group">
					  <label for="1ticketPrice"
						>1 Ticket Price ($)<small style="color: red">*</small></label
					  >
					  <input
						class="form-control"
						id="1ticketPrice"
						autocomplete="off"
						value="2"
					  />
					</div>
				  </div>
				  <div class="col-md col-50-bn">
					<div class="form-group">
					  <label for="2ticketPrice"
						>2 Ticket Price ($)<small style="color: red">*</small></label
					  >
					  <input
						class="form-control"
						id="2ticketPrice"
						autocomplete="off"
						value="3"
					  />
					</div>
				  </div>
				</div>
			   
				<div class="switch-public" style="display: none;">
					<div class="row">
					  <div class="col-md">
						<div class="text-mmc">Make campaign</div>
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
				</div>
				<div class="row">
				  <div class="col-md mt-4 mb-2 text-center">
					<button type="submit" class="btnCreateCamp" id="btnUpdateCamp" style="font-weight: bold;">
					  Update Campaign
					</button>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
	  </body>
	  
		<!-- Custom scripts for all pages-->
		<script src="js/sb-admin-2.min.js"></script>
	</html>
<?php
}
else {
	header("Location: ../sign-in");
}
?>