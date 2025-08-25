$(document).ready(function () {
  $("#btnBuyNow").click(function () {
    $("#checkoutModal").modal("show");
  });
  $(".cancel").click(function () {
    $("#checkoutModal").modal("hide");
  });
  $(".closeBtn").click(function (e) {
    e.preventDefault();
    $(
      "#checkoutModal,#createCampModal,#editCampModal,#thanksModal,#addCardModal,#editCardModal"
    ).modal("hide");
  });
  $("#btnGetStarted").click(function () {
    $("#createCampModal").modal("show");
  });
  $("#btnBuyTicket").click(function () {
    $("#thanksModal").modal("show");
  });
  $("#btnAddCard").click(function () {
    $("#addCardModal").modal("show");
  });
  $("#btnEditCard").click(function () {
    $("#editCardModal").modal("show");
  });

  $("#btnGetStarted2").click(function () {
    $("#btnGetStarted").click();
  });
  $("#btnGetStarted3").click(function () {
    $("#btnGetStarted").click();
  });
  $("#btnGetStarted4").click(function () {
    $("#btnGetStarted").click();
  });
  $("#btnGetStarted5").click(function () {
    $("#btnGetStarted").click();
  });
  $("#btnGetStarted6").click(function () {
    $("#btnGetStarted").click();
  });

  var today = new Date(
    new Date().getFullYear(),
    new Date().getMonth(),
    new Date().getDate()
  );
  
  
	$(document).on('click', '.card-list', function (e) {
		var id = $(this).attr('id');
		var cardNumber = $(this).data("card-number");
		var cardNumber2 = $(this).data("card-number2");
		var cardName = $(this).data("card-name");
		var cardExpired = $(this).data("card-expired");
		var cardCVV = $(this).data("card-cvv");
		var zip = $(this).data("zip");
		
		$(".btn-filter").text("Card " + cardNumber);
		
		if(cardNumber2 != "") {
			$("#cardHolderName").val(cardName);
			$("#cardNumber").val(cardNumber2);
			$("#expiry").val(cardExpired);
			$("#cvv").val(cardCVV);
			$("#zip").val(zip);
			$("#cc-valid").val(1);
		}
	});
});
