<?php
session_start();

require("config/session.php");

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
    <title>Add Card - The Goral</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

    <link href="assets/css/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/font/fontawesome/css/all.min.css" rel="stylesheet" />

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/index.js"></script>
	<script src="/assets/js/wallet.js"></script>
    <script src="assets/font/fontawesome/js/all.min.js"></script>
    <script src="../assets/js/jquery.creditCardValidator.js"></script>
  </head>
  <body>
    <div class="header-ac-bg" style="background: white">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img
                class="logo"
                src="../assets/images/logo-dark.png"
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
      <div class="row row-check">
        <div class="col-md">
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
					$qCardList = mysqli_query($con, "SELECT RIGHT(card_number,4) as card_number_f, card_id, card_name, card_number, expired, cvv, zip FROM tbl_card WHERE userid_fk = '$getUserID'");
					if(mysqli_num_rows($qCardList) > 0) {
						while($dCardList = mysqli_fetch_array($qCardList)) {
						?>
							<tr class="card_<?php echo $dCardList['card_id']; ?>">
								<td class="card-name<?php echo $dCardList['card_id']; ?>"><?php echo $dCardList['card_name']; ?></td>
								<td class="card-number<?php echo $dCardList['card_id']; ?>">Card <?php echo $dCardList['card_number_f']; ?></td>
								<td class="card-expired<?php echo $dCardList['card_id']; ?>"><?php echo $dCardList['expired']; ?></td>
								<td class="card-cvv<?php echo $dCardList['card_id']; ?>" style="display: none;"><?php echo $dCardList['cvv']; ?></td>
								<td class="zip<?php echo $dCardList['card_id']; ?>" style="display: none;"><?php echo $dCardList['zip']; ?></td>
								<td style="display: flex; justify-content: center; gap: 10px;">
								  <div class="btn-edit-card"
								  >
									<button type="submit" class="btnEditCards btnEditCards<?php echo $dCardList['card_id']; ?>" id="<?php echo $dCardList['card_id']; ?>"
									data-card-name="<?php echo $dCardList['card_name']; ?>"
									data-card-number="<?php echo $dCardList['card_number']; ?>"
									data-card-expired="<?php echo $dCardList['expired']; ?>"
									data-card-cvv="<?php echo $dCardList['cvv']; ?>"
									data-zip="<?php echo $dCardList['zip']; ?>">Edit</button>
								  </div>
								</td>
							</tr>
						<?php
						}
					}
					else {
					?>
						<td colspan="6" align="center">No data</td>
					<?php
					}
					?>
				</tbody>
            </table>
          </div>
        </div>
        <div class="col-md">
          <div class="img-card">
            <img src="../assets/images/credit-card.png" alt="" />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md">
          <div class="btn-add-new-card">
            <button type="button" id="btnAddCard">Add New Card</button>
          </div>
        </div>
      </div>
    </div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="social-media text-center">
            <span><img src="../assets/images/logo.png" alt="" /></span>
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-linkedin-in"></i>
            <i class="fa-brands fa-instagram"></i>
          </div>
        </div>
        <div class="row">
          <div class="menu-footer">
            <a href="/">Home</a>
            <a href="live-campaign">Live Campaigns</a>
            <a href="all-campaign">All Campaigns</a>
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
    <!-- modal Add new Card-->
    <div
      aria-hidden="true"
      aria-labelledby="addCardModalLabel"
      class="modal fade bd-example-modal-lg"
      id="addCardModal"
      role="dialog"
      tabindex="-1"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Add New Card<button class="closeBtn">
                  <i class="fa-solid fa-circle-xmark"></i>
                </button>
              </div>
            </div>
            <div class="row credit-card-option">
              <div class="col-md-6 col-50-bn">
                <div class="form-group">
                  <label for="cardHolderName"
                    >Card Holder Name<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="cardHolderName"
                    placeholder="Jack"
                    type="text"
                  />
                </div>
              </div>
              <div class="col-md-6 col-50-bn">
                <div class="form-group">
                  <label for="cardNumber"
                    >Card Number<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="cardNumber"
                    placeholder="1234"
                    type="text"
                  />

                  <input type="hidden" id="cc-valid2" value="0" />

                  <div
                    class="ccNotValid"
                    style="color: red; display: none; font-size: 12px"
                  >
                    Number does not exist
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-25-bn">
                <div class="form-group">
                  <label for="expiry"
                    >Expiry<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="expiry"
                    placeholder="MM/YY"
                    type="text"
                    maxlength="5"
                    onkeyup="formatString(event);"
                  />
                </div>
              </div>
              <div class="col-md-3 col-25-bn">
                <div class="form-group">
                  <label for="cvv"
                    >CVV<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="cvv"
                    placeholder="CVV"
                    type="text"
                    maxlength="5"
                  />
                </div>
              </div>
              <div class="col-md-3 col-50-bn">
                <div class="form-group">
                  <label for="zip"
                    >Zip<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="zip"
                    type="text"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
                <button
                  class="btnBuyTicket btnSaveCard"
                  type="submit"
                  style="font-weight: bold"
                >
                  Save Card
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- modal Edit Card-->
    <div
      aria-hidden="true"
      aria-labelledby="addCardModalLabel"
      class="modal fade bd-example-modal-lg"
      id="editCardModal"
      role="dialog"
      tabindex="-1"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="row">
              <div class="col-md modal-title">
                Edit Card<button class="closeBtn">
                  <i class="fa-solid fa-circle-xmark"></i>
                </button>
              </div>
            </div>
            <div class="row credit-card-option">
              <div class="col-md-6 col-50-bn">
                <div class="form-group">
                  <label for="edtCardHolderName"
                    >Card Holder Name<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="edtCardHolderName"
                    placeholder="Jack"
                    type="text"
                  />
                </div>
              </div>
              <div class="col-md-6 col-50-bn">
                <div class="form-group">
                  <label for="edtCardNumber"
                    >Card Number<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="edtCardNumber"
                    placeholder="1234"
                    type="text"
                  />

			      <input type="hidden" id="cc-id"/>
                  <input type="hidden" id="cc-valid" value="0" />

                  <div
                    class="ccNotValid"
                    style="color: red; display: none; font-size: 12px"
                  >
                    Number does not exist
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-25-bn">
                <div class="form-group">
                  <label for="edtExpiry"
                    >Expiry<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="edtExpiry"
                    placeholder="MM/YY"
                    type="text"
                    maxlength="5"
                    onkeyup="formatString(event);"
                  />
                </div>
              </div>
              <div class="col-md-3 col-25-bn">
                <div class="form-group">
                  <label for="edtCVV"
                    >CVV<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="edtCVV"
                    placeholder="CVV"
                    type="text"
                    maxlength="5"
                  />
                </div>
              </div>
              <div class="col-md-3 col-50-bn">
                <div class="form-group">
                  <label for="edtZIP"
                    >Zip<small style="color: red">*</small></label
                  >
                  <input
                    autocomplete="off"
                    class="form-control"
                    id="edtZIP"
                    type="text"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md mt-4 mb-2 text-center">
                <button
                  class="btnBuyTicket btnUpdateCard"
                  type="submit"
                  style="font-weight: bold"
                >
                  Update Card
                </button>
				
				<div>
				<button class="btn btn-light delete-card">Delete</button>
				</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
