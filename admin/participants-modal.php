<!-- Participants modal — shared by the dashboard and Raffles Done.
     Opened by any element with class="js-participants" data-campaign-id="...".
     Requires admin/js/participants.js. -->
<div class="modal fade" id="participantsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="row">
          <div class="col-md modal-title" style="color: #d98b2b; font-weight: 700;">
            Participants <span id="participantsCount" style="color: #8a93a3; font-weight: 600;"></span>
            <button class="closeBtn" data-bs-dismiss="modal" style="position: absolute; right: 14px; top: 14px; border: none; background: transparent;"><i class="fa-solid fa-circle-xmark" style="font-size: 26px; color: #ec8e37;"></i></button>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-md-12">
            <input type="text" id="participantsSearch" class="form-control" placeholder="Search by name, email, or phone" autocomplete="off" />
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-md-12">
            <div id="participantsList"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
