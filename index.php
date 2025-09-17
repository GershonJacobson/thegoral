<?php
// index.php (Corrected: Restores Hero Section)
//
// Features:
// - Modern, secure, and performant practices.
// - CSRF token protection for the payment form.
// - Prepared statements for all database queries to prevent SQL injection.
// - Separation of concerns: PHP logic, HTML presentation, and JS are distinct.
// - Vanilla JavaScript for core logic, reducing jQuery dependency.

declare(strict_types=1);

// === ERROR HANDLING & CONFIGURATION ===
// (In a production environment, display_errors should be Off)
ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);
// Ensure the 'logs' directory exists and is writable by the web server.
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// === SESSION & DEPENDENCIES ===
require __DIR__ . '/config/session.php'; // Handles session_start() and security
require __DIR__ . '/config/db.php';     // Creates the $con mysqli connection object

// === UTILITIES & SECURITY ===
/**
 * Escapes a string for safe HTML output to prevent XSS.
 * @param mixed $value The value to escape.
 * @return string The escaped string.
 */
function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generates and stores a CSRF token in the session if one doesn't exist.
 * @return string The CSRF token.
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// === DATABASE FUNCTIONS (using prepared statements) ===

function getWeeklyCampaign(mysqli $con): ?array {
    $sql = "SELECT campaign_id, campaign_name, page_url, end_date, status FROM tbl_campaign WHERE category = 'weekly' AND status = 'open' LIMIT 1";
    $result = $con->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

function getParticipantsCount(mysqli $con, int $campaignId): int {
    $sql = "SELECT COUNT(DISTINCT email) as total FROM tbl_ticket WHERE campaignid_fk = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) return 0;
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result['total'] ?? 0);
}

function getTotalPot(mysqli $con, int $campaignId): float {
    $sql = "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) return 0.0;
    $stmt->bind_param('i', $campaignId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float)($result['total_accumulate'] ?? 0.0);
}

function getLastWeeklyRaffles(mysqli $con, int $limit = 5): array {
    $sql = "
        SELECT c.campaign_id, c.campaign_name, DATE_FORMAT(c.end_date, '%m/%d/%Y') AS end_date,
               COALESCE(SUM(t.total_price), 0) as pot
        FROM tbl_campaign c
        LEFT JOIN tbl_ticket t ON c.campaign_id = t.campaignid_fk
        WHERE c.category = 'weekly' AND t.total_price != 0
        GROUP BY c.campaign_id
        ORDER BY c.end_date DESC
        LIMIT ?";
    $stmt = $con->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getSavedCards(mysqli $con, int $userId): array {
    $sql = "SELECT card_id, card_name, RIGHT(card_number, 4) AS card_last4, card_number AS card_number_full, expired, cvv, zip FROM tbl_card WHERE userid_fk = ? ORDER BY card_id DESC";
    $stmt = $con->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// === PAGE STATE & DATA PREPARATION ===
$page            = basename($_SERVER['PHP_SELF'], '.php');
$getUserID       = $_SESSION['user_id'] ?? '';
$getFirstName    = $_SESSION['first_name'] ?? '';
$getLastName     = $_SESSION['last_name'] ?? '';
$getEmailAddress = $_SESSION['email'] ?? '';
$getPhone        = $_SESSION['phone'] ?? '';
$csrfToken       = getCsrfToken();

$weeklyCampaign = getWeeklyCampaign($con);
$campaignId     = $weeklyCampaign ? (int)$weeklyCampaign['campaign_id'] : 0;
$pageURL        = $weeklyCampaign ? $weeklyCampaign['page_url'] : '';
$participants   = $campaignId ? getParticipantsCount($con, $campaignId) : 0;
$totalPot       = $campaignId ? getTotalPot($con, $campaignId) : 0.0;
$lastRaffles    = getLastWeeklyRaffles($con, 5);
$savedCards     = $getUserID ? getSavedCards($con, (int)$getUserID) : [];

// Ticket prices with defaults
$price1Ticket = 2.0;
$price2Ticket = 3.0;
if ($campaignId) {
    $stmt = $con->prepare("SELECT `1ticket_price`, `2ticket_price` FROM tbl_ticket_price WHERE campaignid_fk = ?");
    if ($stmt) {
        $stmt->bind_param('i', $campaignId);
        $stmt->execute();
        $priceResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($priceResult) {
            $price1Ticket = (float)($priceResult['1ticket_price'] ?? $price1Ticket);
            $price2Ticket = (float)($priceResult['2ticket_price'] ?? $price2Ticket);
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Homepage - The Goral</title>

    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.css">
    <link rel="stylesheet" href="assets/font/fontawesome/css/all.min.css">
</head>
<body>

    <header class="header-ac-bg">
        <div class="container">
            <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
                <div class="container-fluid">
                    <a class="navbar-brand" href="/"><img alt="The Goral Logo" class="logo" src="assets/images/logo.svg"></a>
                    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="icon-bar top-bar"></span>
                        <span class="icon-bar middle-bar"></span>
                        <span class="icon-bar bottom-bar"></span>
                    </button>
                    
                    <?php require __DIR__ . '/header.php'; ?>
                </div>
            </nav>

            <?php if ($weeklyCampaign): ?>
            <div class="row">
                <div class="text-dp"><?= h($weeklyCampaign['campaign_name']) ?></div>
                <div class="weeks-pot" style="width:200px;">
                    <div class="blinking-green" style="margin-right:10px;"></div>
                    <?= h($participants) ?> Participant<?= $participants !== 1 ? 's' : '' ?>
                </div>
            </div>
            <div class="row">
                <h1>$<?= h(number_format($totalPot, 2)) ?></h1>
            </div>
            <div style="border:0.5px solid #707070"></div>
            <div class="row">
                <div class="col-md" style="display:flex;flex-flow:column;align-items:center;">
                    <div class="title-draw" style="color:#fff;">Draw in :</div>
                    <div id="countdown" class="countdown-<?= h($campaignId) ?>">
                        <div id="tiles"><span>00</span><span>00</span><span>00</span><span>00</span></div>
                        <div class="labels"><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>
                    </div>
                </div>
                <div class="col-md text-center" style="margin-bottom:30px">
                    <div class="illus-1">
                        <lottie-player src="assets/animation/Anim-01.json" background="Transparent" speed="1" loop autoplay></lottie-player>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php if ($weeklyCampaign['status'] === 'open'): ?>
                    <button class="btnBuyNow" data-bs-toggle="modal" data-bs-target="#checkoutModal" id="btnBuyNow" data-campaign-id="<?= h($campaignId) ?>" type="button">Buy Ticket Now</button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col text-center" style="padding: 80px 0;">
                    <h1 class="text-dp" style="color: white;">No Weekly Campaign Running</h1>
                    <p style="color: white;">Please check back later for the next raffle!</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="section-two">
            <div class="container">
                <div class="row"><div class="title">3 simple steps !</div></div>
                <div class="row">
                    <div class="col-md">
                        <div class="illus-2 text-center">
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
        </section>

        <section class="section-three">
            <div class="container">
                <div class="row row-check">
                    <div class="col-md">
                        <div class="text-subtitle">2. Check
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
        </section>

        <section class="section-four">
            <div class="container">
                <div class="row">
                    <div class="col-md">
                        <div class="illus-4 text-center">
                            <lottie-player src="assets/animation/Anim-04.json" background="Transparent" speed="1" loop autoplay></lottie-player>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="text-subtitle">3. Collect
                            <p>If you are the winner, you can easily collect your prize through our platform. Congratulations, you've won!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-five">
            <div class="container">
                <div class="row">
                    <div class="col-md">
                        <div class="illus-5 text-center">
                            <lottie-player src="assets/animation/Anim-05.json" background="Transparent" speed="1" loop autoplay></lottie-player>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="text-subtitle" style="margin-top:10px">Last 5 weekly raffles</div>
                        <div style="margin-top:15px;">
                            <table class="tbl-raffle">
                                <thead>
                                    <tr style="font-size:12px; font-family:Suwannaphum;">
                                        <th>#</th>
                                        <th></th>
                                        <th>Date</th>
                                        <th>Participants</th>
                                        <th>Pot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lastRaffles)): $i = 1; foreach ($lastRaffles as $raffle):
                                        $pCount = getParticipantsCount($con, (int)$raffle['campaign_id']);
                                    ?>
                                    <tr style="border-top:1px solid #646464;">
                                        <td><div class="text-number"><?= $i ?></div></td>
                                        <td><img style="width:28px" alt="User Icon" src="assets/images/user-icon.png"></td>
                                        <td><span class="text-date"><?= h($raffle['end_date']) ?></span></td>
                                        <td><span class="text-date"><?= h($pCount) ?></span></td>
                                        <td><div class="text-price">$<?= h(number_format((float)$raffle['pot'], 2)) ?></div></td>
                                    </tr>
                                    <?php $i++; endforeach; else: ?>
                                    <tr><td colspan="5" class="text-center p-3">No past raffles to display.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="social-media text-center">
                    <span><img alt="The Goral Logo" src="assets/images/logo.svg"></span>
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
                <div class="text-desc">Lörem ipsum od ohet dilogi. Bell trabel, samuligt, ohöbel utom diska. Jinesade bel när feras redorade i belogi.</div>
            </div>
            <div class="row">
                <div class="copyright">© <?= date('Y') ?> The Goral</div>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col modal-title">
                            Buy Now
                            <button type="button" class="closeBtn" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-circle-xmark"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md col-50-bn">
                            <div class="box-bn purchase-amount" data-purchase="1" data-price="<?= h($price1Ticket) ?>">
                                <h5>1 Ticket for</h5>
                                <h3>$<?= h(number_format($price1Ticket, 2)) ?></h3>
                            </div>
                        </div>
                        <div class="col-md col-50-bn">
                            <div class="box-bn box-bn-b purchase-amount" data-purchase="2" data-price="<?= h($price2Ticket) ?>">
                                <h5>2 Tickets for</h5>
                                <h3>$<?= h(number_format($price2Ticket, 2)) ?></h3>
                            </div>
                        </div>
                    </div>
                    <form id="checkoutForm" novalidate>
                        <input type="hidden" id="input-purchase" name="inputPurchase" value="1" />
                        <input type="hidden" id="input-price" name="inputPrice" value="<?= h($price1Ticket) ?>" />
                        <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>" />

                        <div class="row">
                            <div class="col-md col-50-bn">
                                <div class="form-group">
                                    <label for="firstNameC">First Name <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="firstNameC" name="firstName" type="text" value="<?= h($getFirstName) ?>" required>
                                </div>
                            </div>
                            <div class="col-md col-50-bn">
                                <div class="form-group">
                                    <label for="lastnameC">Last Name <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="lastnameC" name="lastName" type="text" value="<?= h($getLastName) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md col-50-bn">
                                <div class="form-group">
                                    <label for="emailC">Email <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="emailC" name="email" type="email" value="<?= h($getEmailAddress) ?>" required>
                                    <div class="emailNotValid text-danger d-none" style="font-size:12px;">Email is not valid</div>
                                </div>
                            </div>
                            <div class="col-md col-50-bn">
                                <div class="form-group">
                                    <label for="phoneC">Phone <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="phoneC" name="phone" type="tel" value="<?= h($getPhone) ?>" required>
                                    <div class="phoneNotValid text-danger d-none" style="font-size:12px;">Phone is not valid</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1 mt-4">
                            <div class="col-md text-pay">Payment Method</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md text-end">
                                <div class="btn-group">
                                    <button class="btn btn-secondary dropdown-toggle btn-filter" type="button" data-bs-toggle="dropdown" aria-expanded="false">Saved Card</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if (!empty($savedCards)): foreach ($savedCards as $card): ?>
                                        <li><button type="button" class="dropdown-item card-list" data-card-number="<?= h($card['card_number_full']) ?>" data-card-name="<?= h($card['card_name']) ?>" data-card-expired="<?= h($card['expired']) ?>" data-card-cvv="<?= h($card['cvv']) ?>" data-zip="<?= h($card['zip']) ?>">Card ending in <?= h($card['card_last4']) ?></button></li>
                                        <?php endforeach; else: ?>
                                        <li><span class="dropdown-item text-muted">No saved cards</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row credit-card-option">
                            <div class="col-md-6 col-50-bn">
                                <div class="form-group">
                                    <label for="cardHolderName">Card Holder Name <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="cardHolderName" name="cardHolderName" type="text" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-50-bn">
                                <div class="form-group">
                                    <label for="cardNumber">Card Number <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="cardNumber" name="cardNumber" type="text" required>
                                    <input type="hidden" id="cc-valid" value="0">
                                    <div class="ccNotValid text-danger d-none" style="font-size:12px;">Card number is not valid</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-25-bn">
                                <div class="form-group">
                                    <label for="expiry">Expiry <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="expiry" name="expiry" placeholder="MM/YY" type="text" maxlength="5" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-25-bn">
                                <div class="form-group">
                                    <label for="cvv">CVV <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="cvv" name="cvv" type="text" maxlength="4" required>
                                </div>
                            </div>
                            <div class="col-md-3 col-50-bn">
                                <div class="form-group">
                                    <label for="zip">Zip <small class="text-danger">*</small></label>
                                    <input autocomplete="off" class="form-control" id="zip" name="zip" type="text" required>
                                </div>
                            </div>
                        </div>

                        <div class="summary-purchase">
                            <div class="row">
                                <div class="col-md text-pay mt-4 mb-4">Summary</div>
                            </div>
                            <div class="row">
                                <div class="col-md text-sum col-50-bn total-ticket">1 Ticket</div>
                                <div class="col-md text-sum col-50-bn ticket-price">$<?= h(number_format($price1Ticket, 2)) ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md mt-4 mb-2 text-center">
                                <button class="btnBuyTicket" type="submit" style="font-weight:bold;">Buy Ticket</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="assets/font/fontawesome/js/all.min.js" defer></script>
    <script src="assets/js/jquery.creditCardValidator.js" defer></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <script src="assets/js/sweetalert.min.js" defer></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($weeklyCampaign && !empty($weeklyCampaign['end_date'])): ?>
        (function initCountdown() {
            const countdownDate = new Date("<?= date('c', strtotime($weeklyCampaign['end_date'])) ?>").getTime();
            const campaignId = <?= (int)$weeklyCampaign['campaign_id'] ?>;
            const countdownEl = document.querySelector('.countdown-' + campaignId);

            if (!countdownEl) return;

            const timer = setInterval(function() {
                const now = new Date(new Date().toLocaleString("en-US", { timeZone: "America/New_York" })).getTime();
                const distance = countdownDate - now;

                if (distance < 0) {
                    countdownEl.innerHTML = "<h4>DRAW COMPLETED</h4>";
                    const countdownTqEl = document.getElementById('countdown-tq');
                    if(countdownTqEl) countdownTqEl.innerHTML = "<h4>DRAW COMPLETED</h4>";
                    clearInterval(timer);
                    return;
                }

                const days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                const hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                const minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                const seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');

                const html = `<div id='tiles'><span>${days}</span><span>${hours}</span><span>${minutes}</span><span>${seconds}</span></div><div class='labels'><li>Days</li><li>Hours</li><li>Mins</li><li>Secs</li></div>`;
                countdownEl.innerHTML = html;
                
                const countdownTqEl = document.getElementById('countdown-tq');
                if(countdownTqEl) countdownTqEl.innerHTML = html.replace(/tiles/g, 'tiles-tq').replace(/labels/g, 'labels-tq');

            }, 1000);
        })();
        <?php endif; ?>

        const checkoutModalEl = document.getElementById('checkoutModal');
        if (checkoutModalEl) {
            const form = document.getElementById('checkoutForm');
            const btnBuyTicket = form.querySelector('.btnBuyTicket');
            const checkoutModal = new bootstrap.Modal(checkoutModalEl);

            document.querySelectorAll('.purchase-amount').forEach(el => {
                el.addEventListener('click', () => {
                    document.querySelectorAll('.purchase-amount').forEach(i => i.classList.remove('box-bn-active'));
                    el.classList.add('box-bn-active');
                    const purchase = el.dataset.purchase;
                    const price = el.dataset.price;
                    form.inputPurchase.value = purchase;
                    form.inputPrice.value = price;
                    document.querySelector('.total-ticket').textContent = `${purchase} Ticket${purchase > 1 ? 's' : ''}`;
                    document.querySelector('.ticket-price').textContent = '$' + parseFloat(price).toFixed(2);
                });
            });
            
            const firstPurchase = document.querySelector('.purchase-amount[data-purchase="1"]');
            if (firstPurchase) firstPurchase.classList.add('box-bn-active');

            document.querySelectorAll('.card-list').forEach(button => {
                button.addEventListener('click', function() {
                    form.cardHolderName.value = this.dataset.cardName || '';
                    form.cardNumber.value = this.dataset.cardNumber || '';
                    form.expiry.value = this.dataset.cardExpired || '';
                    form.cvv.value = this.dataset.cardCvv || '';
                    form.zip.value = this.dataset.zip || '';
                    $('#cardNumber').trigger('input');
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                form.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        input.focus();
                        isValid = false;
                    }
                });

                if (!isValid) return;

                btnBuyTicket.textContent = 'Processing...';
                btnBuyTicket.disabled = true;

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());
                payload.campaignID = document.getElementById('btnBuyNow')?.dataset.campaignId || '';

                fetch('functions/buy-ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': payload.csrf_token },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.result === 'OK') {
                        checkoutModal.hide();
                        Swal.fire({
                            width: '800px',
                            html: `<div class="row"> <div class="col-md-6"> <div class="illus-8"> <img src="assets/images/illu-8.png" alt="Success Illustration" /> </div> </div> <div class="col-md-6"> <div class="row"> <div class="col-md"> <div class="text-tq"> Thanks! <p> Check your email to receive a receipt! <br /> And to see when the raffle will be drawn </p> </div> </div> </div> <div class="row"> <div class="col-md"> <div id="countdown-tq"></div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-ticnum"> Ticket Number <p>${h(data.ticketNo)}</p> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="btn-drawing-page"> <a href="/<?php echo $pageURL; ?>">Go to Drawing Page</a> </div> </div> </div> <div class="row"> <div class="col-md"> <div class="text-raffle">Share This Raffle</div> </div> </div> <div class="row"> <div class="col-md"> <div class="sosmed"> <img src="assets/images/fb.png" alt="Facebook" /><img src="assets/images/ig.png" alt="Instagram" /><img src="assets/images/wa.png" alt="WhatsApp" /> </div> </div> </div> </div> </div>`,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            showCloseButton: true
                        });
                        form.reset();
                        firstPurchase?.classList.add('box-bn-active');
                    } else {
                        Swal.fire({ text: data.message || "An error occurred. Please try again.", icon: "error" });
                    }
                })
                .catch(error => {
                    console.error('Submission Error:', error);
                    Swal.fire({ text: "A network error occurred. Please check your connection and try again.", icon: "error" });
                })
                .finally(() => {
                    btnBuyTicket.textContent = 'Buy Ticket';
                    btnBuyTicket.disabled = false;
                });
            });

            if (window.jQuery && $.fn.validateCreditCard) {
                $('#cardNumber').on('input', function() {
                    const info = $(this).validateCreditCard();
                    const isValid = !!info.valid;
                    $('#cc-valid').val(isValid ? '1' : '0');
                    $('.ccNotValid').toggleClass('d-none', isValid);
                });
            }
        }
        function h(str) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return str.replace(/[&<>"']/g, m => map[m]);
        }
    });
    </script>

</body>
</html>