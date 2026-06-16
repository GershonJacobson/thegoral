/**
 * Account Information Module
 * Handles user search, profile viewing, editing, and refunds
 */

$(document).ready(function() {
    // ============================================
    // MODAL MANAGEMENT
    // ============================================

    /**
     * When a user card is clicked, load and display their information
     */
    $(document).on('click', '.card-info-edit', function(e) {
        const $card = $(this);
        const userData = {
            firstName: $card.data('first-name'),
            lastName: $card.data('last-name'),
            email: $card.data('email'),
            phone: $card.data('phone'),
            number: $card.data('number')
        };

        // Store for later use
        $('#id-number').val(userData.number);

        // Populate modal with user data
        populateUserModal(userData);

        // Load payment history
        loadPaymentHistory(userData.email);

        // Show modal
        $('#accountInformationModal').modal('show');
    });

    /**
     * Populate the account information modal with user data
     */
    function populateUserModal(userData) {
        const fullName = userData.firstName + ' ' + userData.lastName;
        
        $('.profile-name').text(fullName);
        $('.first-name-text').text(userData.firstName);
        $('.last-name-text').text(userData.lastName);
        $('.email-text').text(userData.email);
        $('.phone-text').text(userData.phone);
    }

    /**
     * Load payment history for a user
     */
    function loadPaymentHistory(email) {
        const $tbody = $('.tbl-payment-history tbody');
        
        // Clear and show loading state
        $tbody.empty().append('<tr><td colspan="4" align="center">Loading...</td></tr>');

        $.ajax({
            url: 'functions/account-information-api.php?action=payment-history',
            type: 'POST',
            data: { email: email },
            dataType: 'JSON',
            success: function(payments) {
                $tbody.empty();

                if (payments.length === 0) {
                    $tbody.append('<tr><td colspan="4" align="center">No payment history</td></tr>');
                    return;
                }

                // Build payment rows
                payments.forEach(function(payment) {
                    const refundAmount = payment.totalRefund ? '$' + payment.totalRefund : '';
                    const userRole = payment.userRole;

                    // Only show refund button for admins
                    let refundCell = '';
                    if (userRole == 1 || userRole == 2) {
                        refundCell = `
                            <div class="text-refund text-refund${payment.ticketID}">${refundAmount}</div>
                            <button type="button" class="btn-refund" id="${payment.ticketID}">Refund</button>
                        `;
                    }

                    const row = `
                        <tr>
                            <td>${payment.purchased_date}</td>
                            <td>$${payment.total_price}</td>
                            <td>${payment.payment_method || '—'}</td>
                            <td>${refundCell}</td>
                        </tr>
                    `;

                    $tbody.append(row);
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'Failed to load payment history';
                $tbody.empty().append(`<tr><td colspan="4" align="center" style="color: red;">${errorMsg}</td></tr>`);
            }
        });
    }

    // ============================================
    // REFUND FUNCTIONALITY
    // ============================================

    $(document).on('click', '.btn-refund', function(e) {
        const ticketID = $(this).attr('id');
        $('#ticketID').val(ticketID);
        $('#refundModal').modal('show');
    });

    $('#btnRefund').click(function() {
        const ticketID = $('#ticketID').val();
        const edtAmount = $('#edtAmount').val();

        // Validate amount
        if (!edtAmount || edtAmount.trim() === '') {
            alert('Please enter a refund amount');
            $('#edtAmount').focus();
            return;
        }

        if (isNaN(edtAmount) || parseFloat(edtAmount) < 0) {
            alert('Please enter a valid amount');
            $('#edtAmount').focus();
            return;
        }

        // Process refund
        const $btn = $(this);
        $btn.text('Processing').prop('disabled', true);

        $.ajax({
            url: 'functions/refund.php',
            type: 'POST',
            data: {
                ticketID: ticketID,
                edtAmount: edtAmount
            },
            dataType: 'JSON',
            success: function(response) {
                if (response.result === 'OK') {
                    Swal.fire('Success', 'Refund processed successfully', 'success');
                    $('#edtAmount').val('');
                    $('.text-refund' + ticketID).text('$' + edtAmount);
                    $('#refundModal').modal('hide');
                } else {
                    Swal.fire('Error', response.error || 'Failed to process refund', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while processing the refund', 'error');
            },
            complete: function() {
                $btn.text('Refund').prop('disabled', false);
            }
        });
    });

    // ============================================
    // EDIT PROFILE FUNCTIONALITY
    // ============================================

    $('#btnEditProfile').click(function() {
        // Populate edit form with current data
        $('#edtFirstName').val($('.first-name-text').text());
        $('#edtLastName').val($('.last-name-text').text());
        $('#edtEmail').val($('.email-text').text());
        $('#edtPhone').val($('.phone-text').text());

        $('#updateProfile').modal('show');
    });

    $('#btnUpdateProfile').click(function() {
        const firstName = $('#edtFirstName').val().trim();
        const lastName = $('#edtLastName').val().trim();
        const email = $('#edtEmail').val().trim();
        const phone = $('#edtPhone').val().trim();
        const number = $('#id-number').val();

        // Validate required fields
        if (!firstName) {
            alert('First name is required');
            $('#edtFirstName').focus();
            return;
        }
        if (!lastName) {
            alert('Last name is required');
            $('#edtLastName').focus();
            return;
        }
        if (!email) {
            alert('Email is required');
            $('#edtEmail').focus();
            return;
        }

        // Validate email format
        if (!isValidEmail(email)) {
            alert('Please enter a valid email address');
            $('#edtEmail').focus();
            return;
        }

        // Send update request
        const $btn = $(this);
        $btn.text('Updating...').prop('disabled', true);

        $.ajax({
            url: 'functions/account-information-api.php?action=update',
            type: 'POST',
            data: {
                firstName: firstName,
                lastName: lastName,
                email: email,
                phone: phone
            },
            dataType: 'JSON',
            success: function(response) {
                if (response.result === 'OK') {
                    // Update modal display
                    const fullName = firstName + ' ' + lastName;
                    $('.profile-name').text(fullName);
                    $('.first-name-text').text(firstName);
                    $('.last-name-text').text(lastName);
                    $('.phone-text').text(phone);

                    // Update card display
                    $('.card-info-edit' + number + ' .text-info-ds').text(fullName);
                    $('.card-info-edit' + number).data('first-name', firstName);
                    $('.card-info-edit' + number).data('last-name', lastName);
                    $('.card-info-edit' + number).data('email', email);
                    $('.card-info-edit' + number).data('phone', phone);

                    Swal.fire('Success', 'Profile updated successfully', 'success');
                    $('#updateProfile').modal('hide');
                } else {
                    Swal.fire('Error', response.error || 'Failed to update profile', 'error');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'An error occurred';
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                $btn.text('Update Profile').prop('disabled', false);
            }
        });
    });

    // ============================================
    // SEARCH FUNCTIONALITY
    // ============================================

    let searchTimeout;

    $('#search').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchVal = $(this).val().trim();

        // Clear search if input is empty
        if (!searchVal) {
            $('.data-account').html(getInitialAccountsHTML());
            return;
        }

        // Debounce search to avoid too many requests
        searchTimeout = setTimeout(function() {
            performSearch(searchVal);
        }, 300);
    });

    function performSearch(searchVal, autoOpenSingle) {
        const $dataContainer = $('.data-account');
        $dataContainer.empty().append('<div class="loading">Searching...</div>');

        $.ajax({
            url: 'functions/account-information-api.php?action=search',
            type: 'POST',
            data: { searchVal: searchVal },
            dataType: 'JSON',
            success: function(accounts) {
                $dataContainer.empty();

                if (accounts.length === 0) {
                    $dataContainer.append('<div style="padding: 20px; text-align: center; color: #999;">No accounts found</div>');
                    return;
                }

                // Build account cards
                accounts.forEach(function(account, index) {
                    const card = `
                        <div class="col-xl-12 mb-2">
                            <div class="card-info-edit card-info-edit${index}" style="text-align: start !important"
                                 data-first-name="${escapeHtml(account.firstName)}"
                                 data-last-name="${escapeHtml(account.lastName)}"
                                 data-email="${escapeHtml(account.emailAddress)}"
                                 data-phone="${escapeHtml(account.phone)}"
                                 data-number="${index}">
                                <span class="user-icon"><img src="../assets/images/user-icon.png" alt="User"/></span>
                                <span class="text-info-ds" style="font-weight: 700">${escapeHtml(account.firstName + ' ' + account.lastName)}</span>
                                <span class="account-sub">${escapeHtml([account.emailAddress, account.phone].filter(Boolean).join(' · '))}</span>
                            </div>
                        </div>
                    `;
                    $dataContainer.append(card);
                });

                // came from a deep link and there's exactly one match: open it
                if (autoOpenSingle && accounts.length === 1) {
                    $('.card-info-edit0').trigger('click');
                }
            },
            error: function() {
                $dataContainer.empty().append('<div style="padding: 20px; text-align: center; color: red;">Search failed</div>');
            }
        });
    }

    // Deep link: account-information?q=<email or name> (used by the participants modal)
    const deepLinkQ = new URLSearchParams(window.location.search).get('q');
    if (deepLinkQ) {
        $('#search').val(deepLinkQ);
        performSearch(deepLinkQ, true);
    }

    // Arrived from a participants list (?back=/admin/...?participants=<id>):
    // closing the profile returns to that list, reopened on the same raffle.
    const backTo = new URLSearchParams(window.location.search).get('back');
    const backToSafe = (backTo && /^\/admin\/(raffles-done)?\?participants=[A-Za-z0-9-]+$/.test(backTo)) ? backTo : null;
    if (backToSafe) {
        $(document).on('click', '#closeBtn', function () {
            window.location.href = backToSafe;
        });
    }

    // ============================================
    // MODAL CLOSE BUTTONS
    // ============================================

    $('#closeBtn, #closeBtn2, #closeBtn3').click(function() {
        $('#accountInformationModal, #updateProfile, #refundModal').modal('hide');
    });

    // ============================================
    // REFUND AMOUNT INPUT - NUMERIC ONLY
    // ============================================

    $('#edtAmount').on('keydown', function(e) {
        // Allow: backspace, delete, tab, escape, enter, decimal point, minus
        const allowedKeys = [8, 9, 27, 13, 46, 110, 190]; // backspace, tab, escape, enter, delete, ., .
        
        if (allowedKeys.includes(e.keyCode) || 
            (e.keyCode === 189 && e.shiftKey === false) || // minus
            (e.keyCode >= 48 && e.keyCode <= 57) || // numbers
            (e.keyCode >= 96 && e.keyCode <= 105)) { // numpad
            return;
        }

        e.preventDefault();
    });

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    /**
     * Validate email format
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Get initial accounts HTML (empty state)
     */
    function getInitialAccountsHTML() {
        // In production, this would load from server
        // For now, return empty state
        return '<div style="padding: 20px; text-align: center; color: #999;">Enter search term above</div>';
    }
});