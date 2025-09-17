<?php
session_start();

require("config/session.php");
require("config/db.php"); // Ensure the database connection is included

if($getUserID == "") {
	header("Location: /sign-in");
    exit(); // Always exit after a header redirect
}

// === DATABASE HELPER FUNCTIONS (SECURE) ===

/**
 * Fetches a single value from the database using a prepared statement.
 * @param mysqli $con The database connection.
 * @param string $sql The SQL query with a '?' placeholder.
 * @param string $types A string containing the types of the bind parameters (e.g., 'i' for integer).
 * @param mixed ...$params The parameters to bind.
 * @return mixed The result value, or 0/null on failure.
 */
function fetchSingleValue(mysqli $con, string $sql, string $types, ...$params) {
    $stmt = $con->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ? array_values($result)[0] : 0;
}

// === DATA PREPARATION ===

$userIdInt = (int)$getUserID;

// Get Money Spent
$moneySpent = fetchSingleValue($con, "SELECT COALESCE(SUM(total_price), 0) FROM tbl_ticket WHERE purchased_by = ?", 'i', $userIdInt);

// Get Tickets Bought
$ticketsBought = fetchSingleValue($con, "SELECT COUNT(*) FROM tbl_ticket WHERE purchased_by = ?", 'i', $userIdInt);

// Get Raffles Joined
$rafflesJoined = fetchSingleValue($con, "SELECT COUNT(DISTINCT campaignid_fk) FROM tbl_ticket WHERE purchased_by = ?", 'i', $userIdInt);

// Get Money Won
$moneyWon = fetchSingleValue($con, "SELECT COALESCE(SUM(total_price), 0) FROM tbl_ticket WHERE purchased_by = ? AND win = 'Y'", 'i', $userIdInt);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Dashboard - The Goral</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

    <link rel="stylesheet" href="../assets/css/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/font/fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/sweetalert.css" />

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sweetalert.min.js"></script>
    <script src="../assets/js/index.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/font/fontawesome/js/all.min.js"></script>
  </head>
  <body>
    <div class="header-ac-bg" style="background: white">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img class="logo" src="../assets/images/logo-dark.svg" alt="logo" />
            </a>
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
				<button class="btn dropdown-toggle btn-filter" type="button" id="dropdownMenuClickableInside" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: #e7e7e7; padding: 0px; font-size: 12px;">
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
                <span class="money-spent">$<?= number_format((float)$moneySpent, 2) ?></span>
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
                <span class="ticket-bought"><?= $ticketsBought ?></span>
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
                <span class="raffles-joined"><?= $rafflesJoined ?></span>
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
                <span class="money-won">$<?= number_format((float)$moneyWon, 2) ?></span>
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
            $allcount = (int)fetchSingleValue($con, "SELECT count(*) FROM tbl_ticket WHERE purchased_by = ?", 'i', $userIdInt);
			
			if($allcount > 0) {
                $sql = "
                    SELECT t.campaignid_fk, DATE_FORMAT(t.purchased_date,'%m/%d/%Y') AS purchased_date, t.total_price, 
                           RIGHT(t.card_number,4) as card_number, t.payment_status, c.campaign_name, c.page_url
                    FROM tbl_ticket t
                    JOIN tbl_campaign c ON t.campaignid_fk = c.campaign_id
                    WHERE t.purchased_by = ? 
                    ORDER BY t.purchased_date DESC 
                    LIMIT ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param('ii', $userIdInt, $rowperpage);
                $stmt->execute();
                $result = $stmt->get_result();

				while($dTicketPurchased = $result->fetch_assoc()) {
					
					$paymentStatus = "Failed";
                    if ($dTicketPurchased['payment_status'] == 0) $paymentStatus = "Process";
                    if ($dTicketPurchased['payment_status'] == 1) $paymentStatus = "Success";
					
					$paymentMethod = !empty($dTicketPurchased['card_number']) ? "Card " . $dTicketPurchased['card_number'] : "";
					
					$url = $dTicketPurchased['page_url'];
					?>
						<tr>
							<td><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($dTicketPurchased['campaign_name']) ?></a></td>
							<td><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($dTicketPurchased['purchased_date']) ?></a></td>
							<td><a href="<?= htmlspecialchars($url) ?>">$<?= htmlspecialchars($dTicketPurchased['total_price']) ?></a></td>
							<td><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($paymentMethod) ?></a></td>
							<td><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($paymentStatus) ?></a></td>
						</tr>
					<?php
					}
                $stmt->close();
			} else {
				?>
					<tr><td colspan="5" class="text-center">No raffle history found.</td></tr>
				<?php
			}
			?>
			</tbody>
        </table>
		
		<?php
		if($allcount > 5) {
		?>
			<h2 class="load-more-raffle"><i class="fa-solid fa-chevron-down"></i></h2>
			<input type="hidden" id="filter-raffle" value="">
			<input type="hidden" id="row-raffle" value="0">
			<input type="hidden" id="all-raffle" value="<?= $allcount; ?>">
			<input type="hidden" id="currentNo-raffle" value="<?= $rowperpage; ?>">
		<?php
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
            <a class="active" href="/">Home</a>
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
  </body>
</html>