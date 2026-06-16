/**
 * Participants modal: click any .js-participants element (data-campaign-id)
 * to list everyone in that raffle — alphabetical, searchable, each name
 * linking to their Account Information (and back again via ?back=).
 */
$(document).ready(function () {

	function participantRow(p, campaignID) {
		// where Account Information should send the admin back to on close:
		// this page, with the same participants modal reopened
		var back = location.pathname + '?participants=' + encodeURIComponent(campaignID);

		var $row = $('<a class="participant-row"></a>')
			.attr('href', 'account-information?q=' + encodeURIComponent(p.email || (p.firstName + ' ' + p.lastName)) + '&back=' + encodeURIComponent(back));

		var name = ((p.firstName || '') + ' ' + (p.lastName || '')).trim() || '(no name)';
		$('<div class="participant-name"></div>').text(name).appendTo($row);

		var contact = [p.email, p.phone].filter(Boolean).join(' · ');
		$('<div class="participant-contact"></div>').text(contact).appendTo($row);

		(p.purchases || []).forEach(function (b) {
			var line = 'Ticket #' + b.ticketNo + ' · ' + b.qty + (b.qty == 1 ? ' ticket' : ' tickets') +
				' · $' + b.price + ' · bought ' + b.at;
			$('<div class="participant-purchase"></div>').text(line).appendTo($row);
		});

		// for the client-side search
		$row.attr('data-search', (name + ' ' + contact).toLowerCase());
		return $row;
	}

	function openParticipants(campaignID) {
		$('#participantsList').html('<div class="participant-loading">Loading…</div>');
		$('#participantsCount').text('');
		$('#participantsSearch').val('');
		$('#participantsModal').modal('show');

		$.ajax({
			url: 'functions/get-participants',
			type: 'POST',
			data: { campaignID: campaignID },
			dataType: 'JSON',
			success: function (jsonStr) {
				var $list = $('#participantsList').empty();

				if (jsonStr.result !== 'OK' || !jsonStr.participants.length) {
					$list.html('<div class="participant-loading">No participants yet.</div>');
					return;
				}

				$('#participantsCount').text('(' + jsonStr.participants.length + ')');
				jsonStr.participants.forEach(function (p) {
					$list.append(participantRow(p, campaignID));
				});
			},
			error: function () {
				$('#participantsList').html('<div class="participant-loading">Could not load participants.</div>');
			}
		});
	}

	$(document).on('click', '.js-participants', function (e) {
		e.preventDefault();
		openParticipants($(this).data('campaign-id'));
	});

	$(document).on('input', '#participantsSearch', function () {
		var q = $(this).val().toLowerCase().trim();
		$('#participantsList .participant-row').each(function () {
			$(this).toggle(q === '' || ($(this).attr('data-search') || '').indexOf(q) !== -1);
		});
	});

	// Coming back from Account Information (?participants=<id>): reopen the list
	var returnTo = new URLSearchParams(window.location.search).get('participants');
	if (returnTo && /^[A-Za-z0-9-]+$/.test(returnTo)) {
		openParticipants(returnTo);
	}
});
