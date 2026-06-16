<?php
session_start();

require("config/session.php");
require("config/payarc.php");

if($getUserID == "") {
	header("Location: /");
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wallet - The Goral</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

    <link href="assets/css/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/font/fontawesome/css/all.min.css" rel="stylesheet" />
    <link href="assets/css/sweetalert.css" rel="stylesheet" />
    <!-- Utility-pages layer (overrides style.css) -->
    <link href="assets/css/site.css" rel="stylesheet" />

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sweetalert.min.js"></script>
    <script src="/assets/js/index.js"></script>
	<script src="/assets/js/wallet.js"></script>
    <script src="assets/font/fontawesome/js/all.min.js"></script>
    <!-- PayArc hosted-fields tokenizer (PCI-safe). Card data never touches our server. -->
    <script src="<?php echo GORAL_PAYARC_IFRAME_JS; ?>" defer></script>
    <style id="payarc-styles">
      .payarc-fields { width: 100%; }
      .payarc-row-2 { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
      .payarc-row-2 > div { flex: 1; min-width: 90px; min-height: 48px; overflow: hidden; }
      #card-token-container > div { width: 100%; min-height: 48px; }
      /* PayArc injects its iframes at a fixed 300x150 — constrain them. */
      #card-token-container iframe { width: 100% !important; max-width: 100% !important; height: 48px !important; display: block; }
      #addCardModal .modal-body { overflow-x: hidden; }
      .payarc-input { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
      .payarc-label { display: none; }
      .payarc-container { background: transparent; }
      .payarc-input-error { border-color: #d9534f; color: #d9534f; }
      /* PayArc marks fields "success" just for being filled in — a green
         border on a card that later fails tokenization is a lie. Neutral. */
      .payarc-input-success { border-color: #ced4da; }
    </style>
    <script>
    $(document).ready(function () {
      var PAYARC_CLIENT_ID = "<?php echo htmlspecialchars(GORAL_PAYARC_CLIENT_ID, ENT_QUOTES, 'UTF-8'); ?>";

      function resetSaveBtn() { $("#btnSaveCardWallet").text("Save Card").prop("disabled", false); }

      function payarcLast4(response) {
        if (response.last_four) return String(response.last_four);
        if (response.last4) return String(response.last4);
        if (response.card_number) return String(response.card_number).replace(/\D/g, "").slice(-4);
        return "";
      }

      var PAYARC_SETTINGS = {
        FIELDS_CONTAINER: "card-token-container",
        INITIATE_PAYMENT: "initiate-payment",
        TOKEN_CALLBACK: {
          success: function (obj) {
            try {
              var response = JSON.parse(obj.response);
              if (response && response.token) {
                $.ajax({
                  url: "functions/save-card",
                  type: "POST",
                  data: {
                    cardHolderName: $("#walletCardName").val(),
                    paymentToken: response.token,
                    cardLast4: payarcLast4(response),
                    cardBrand: response.card_type || response.brand || ""
                  },
                  dataType: "JSON",
                  success: function (jsonStr) {
                    if (jsonStr.result == "OK") {
                      location.reload();
                    } else if (jsonStr.result == "duplicate") {
                      Swal.fire({ text: "That card is already saved.", icon: "error", confirmButtonText: "OK" });
                      resetSaveBtn();
                    } else {
                      Swal.fire({ text: jsonStr.message || "Could not save the card.", icon: "error", confirmButtonText: "OK" });
                      resetSaveBtn();
                    }
                  },
                  error: function () {
                    Swal.fire({ text: "Something went wrong. Please try again.", icon: "error", confirmButtonText: "OK" });
                    resetSaveBtn();
                  }
                });
                return;
              }
            } catch (e) {}
            Swal.fire({ text: "Could not read your card. Please re-check the details.", icon: "error", confirmButtonText: "OK" });
            resetSaveBtn();
          },
          error: function (obj) {
            var msg = "We couldn’t verify this card — please check the number and expiry date.";
            try {
              var r = JSON.parse(obj.response);
              if (r && (r.message || r.error)) { msg = r.message || r.error; }
            } catch (e) {}
            $(".ccNotValid").text(msg).show();
            resetSaveBtn();
          },
          paymentWindowClosed: function () { resetSaveBtn(); }
        }
      };

      if (PAYARC_CLIENT_ID && typeof initPayarcTokenizer === "function") {
        initPayarcTokenizer(PAYARC_CLIENT_ID, PAYARC_SETTINGS);
      }

      $("#initiate-payment").click(function () {
        if (typeof getPayarcToken === "function") { getPayarcToken(this); }
      });

      $("#btnSaveCardWallet").click(function () {
        if ($("#walletCardName").val() == "") { $("#walletCardName").focus(); return; }
        if (!PAYARC_CLIENT_ID || typeof initPayarcTokenizer === "undefined") {
          Swal.fire({ text: "Card saving isn't switched on yet. Please try again shortly.", icon: "error", confirmButtonText: "OK" });
          return;
        }
        $(".ccNotValid").hide();
        $("#btnSaveCardWallet").text("Saving").prop("disabled", true);
        $("#initiate-payment").click();
      });
    });
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
      <div class="row">
        <div class="col-md">
          <h1 class="page-head">Wallet</h1>
          <p class="page-sub">Your saved cards, for a faster checkout.</p>
        </div>
      </div>
      <div class="row">
        <div class="col-md">
          <?php
          $qCardList = mysqli_query($con, "SELECT card_id, card_name, card_last4, card_brand, expired, zip FROM tbl_card WHERE userid_fk = '" . intval($getUserID) . "'");
          if($qCardList && mysqli_num_rows($qCardList) > 0) {
          ?>
          <div style="overflow-y: auto">
            <table class="tbl-card">
				<thead>
				  <tr>
					<th>Name</th>
					<th>Number</th>
					<th>Expiry</th>
					<th>Action</th>
				  </tr>
				</thead>

				<tbody>
					<?php
					while($dCardList = mysqli_fetch_array($qCardList)) {
					?>
						<tr class="card_<?php echo $dCardList['card_id']; ?>">
							<td class="card-name<?php echo $dCardList['card_id']; ?>"><?php echo htmlspecialchars($dCardList['card_name'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td class="card-number<?php echo $dCardList['card_id']; ?>"><?php echo htmlspecialchars(($dCardList['card_brand'] ?: 'Card') . ' •••• ' . $dCardList['card_last4'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td class="card-expired<?php echo $dCardList['card_id']; ?>"><?php echo htmlspecialchars($dCardList['expired'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td style="display: none;"></td>
							<td class="zip<?php echo $dCardList['card_id']; ?>" style="display: none;"><?php echo htmlspecialchars($dCardList['zip'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td style="display: flex; justify-content: center; gap: 10px;">
							  <div class="btn-delete-card-wrap">
								<button type="button" class="btn btn-light delete-card" id="<?php echo $dCardList['card_id']; ?>">Remove</button>
							  </div>
							</td>
						</tr>
					<?php
					}
					?>
				</tbody>
            </table>
          </div>
          <?php } else { ?>
          <div class="empty-state">Save a card here, or check &ldquo;Save my card&rdquo; at checkout &mdash; for faster checkout next time.</div>
          <?php } ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md">
          <button type="button" id="btnAddCard" class="btn-add-card">Add a card</button>
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
    <!-- Add-a-card modal: PayArc hosted fields only — raw card numbers never touch this server. -->
    <div aria-hidden="true" aria-labelledby="addCardModalLabel" class="modal fade" id="addCardModal" role="dialog" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Add a Card<button class="closeBtn"><i class="fa-solid fa-circle-xmark"></i></button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="walletCardName">Card Holder Name<small style="color: red">*</small></label>
                  <input autocomplete="off" class="form-control" id="walletCardName" placeholder="Jack" type="text" />
                </div>
              </div>
              <div class="col-md-12 mt-2">
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
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
                <button class="btnBuyTicket" type="button" id="btnSaveCardWallet" style="font-weight: bold;">Save Card</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
