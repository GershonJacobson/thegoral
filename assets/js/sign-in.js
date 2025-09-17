$(document).ready(function () {
    // Modal handlers (keeping these as they might be used elsewhere)
    $("#btnBuyNow").click(function () {
        $("#checkoutModal").modal("show");
    });
    
    $(".cancel, .fa-circle-xmark").click(function () {
        $("#checkoutModal").modal("hide");
    });
    
    // Enhanced email field handler with validation
    $("#email").on('keypress', function(e) {
        if(e.which === 13) { // Enter key
            e.preventDefault();
            const email = $(this).val().trim();
            
            if(email === "") {
                showFieldError($(this), "Please enter your email address");
            } else if(!isValidEmail(email)) {
                showFieldError($(this), "Please enter a valid email address");
            } else {
                clearFieldError($(this));
                $("#password").focus();
            }
        }
    });
    
    // Enhanced password field handler
    $("#password").on('keypress', function(e) {
        if(e.which === 13) { // Enter key
            e.preventDefault();
            const password = $(this).val();
            
            if(password === "") {
                showFieldError($(this), "Please enter your password");
            } else {
                clearFieldError($(this));
                $("#btnLogin").trigger("click");
            }
        }
    });
    
    // Main login handler with improved validation and error handling
    $("#btnLogin").click(function(e) {
        e.preventDefault();
        
        // Get form values
        const email = $("#email").val().trim();
        const password = $("#password").val();
        const rememberMe = $('#remember-me').is(':checked') ? 1 : 0;
        const csrfToken = $('input[name="csrf_token"]').val();
        
        // Clear any previous errors
        clearAllErrors();
        
        // Validate inputs
        let hasError = false;
        
        if(email === "") {
            showFieldError($("#email"), "Email address is required");
            hasError = true;
        } else if(!isValidEmail(email)) {
            showFieldError($("#email"), "Please enter a valid email address");
            hasError = true;
        }
        
        if(password === "") {
            showFieldError($("#password"), "Password is required");
            hasError = true;
        }
        
        if(hasError) {
            return false;
        }
        
        // Disable button and show loading state
        const $btn = $(this);
        const originalText = $btn.text();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Logging in...').prop('disabled', true);
        
        // Make AJAX request with correct parameters
        $.ajax({
            url: "functions/login.php", // FIXED: Added .php extension
            type: "POST",
            data: {
                emailAddress: email, // FIXED: Changed from 'email' to 'emailAddress'
                password: password,
                rememberMe: rememberMe,
                csrf_token: csrfToken // ADDED: CSRF token for security
            },
            dataType: "JSON",
            success: function (response) {
                if(response.result === "OK") {
                    // Success - show success message then redirect
                    Swal.fire({
                        title: "Welcome back!",
                        text: "Login successful. Redirecting...",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        // Redirect to dashboard or homepage
                        window.location.href = response.redirect || "/user-dashboard";
                    });
                } 
                else if(response.result === "NOTOK") {
                    // Invalid credentials
                    Swal.fire({
                        title: "Login Failed",
                        text: response.message || "Invalid email or password. Please try again.",
                        icon: "error",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#dc3545"
                    });
                    $btn.html(originalText).prop('disabled', false);
                } 
                else if(response.result === "blank") {
                    // Empty fields (shouldn't happen with client-side validation)
                    Swal.fire({
                        title: "Missing Information",
                        text: response.message || "Please enter both email and password.",
                        icon: "warning",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ffc107"
                    });
                    $btn.html(originalText).prop('disabled', false);
                } 
                else if(response.result === "error") {
                    // Server error
                    Swal.fire({
                        title: "System Error",
                        text: response.message || "A system error occurred. Please try again later.",
                        icon: "error",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#dc3545"
                    });
                    $btn.html(originalText).prop('disabled', false);
                }
                else {
                    // Unexpected response
                    Swal.fire({
                        title: "Unexpected Error",
                        text: "An unexpected error occurred. Please try again.",
                        icon: "error",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#dc3545"
                    });
                    $btn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                // Network or server error
                console.error("Login error:", error);
                
                let errorMessage = "Unable to connect to the server. ";
                
                if(xhr.status === 403) {
                    errorMessage += "Security verification failed. Please refresh the page and try again.";
                } else if(xhr.status === 404) {
                    errorMessage += "Login service not found. Please contact support.";
                } else if(xhr.status === 500) {
                    errorMessage += "Server error occurred. Please try again later.";
                } else if(status === "timeout") {
                    errorMessage += "Request timed out. Please check your connection.";
                } else {
                    errorMessage += "Please check your internet connection and try again.";
                }
                
                Swal.fire({
                    title: "Connection Error",
                    text: errorMessage,
                    icon: "error",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#dc3545"
                });
                
                $btn.html(originalText).prop('disabled', false);
            },
            timeout: 30000 // 30 second timeout
        });
    });
    
    // Helper function to validate email format
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Helper function to show field-specific error
    function showFieldError($field, message) {
        $field.addClass('is-invalid');
        
        // Remove any existing error message
        $field.siblings('.invalid-feedback').remove();
        
        // Add new error message
        $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
    }
    
    // Helper function to clear field error
    function clearFieldError($field) {
        $field.removeClass('is-invalid');
        $field.siblings('.invalid-feedback').remove();
    }
    
    // Helper function to clear all errors
    function clearAllErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }
    
    // Clear errors when user starts typing
    $("#email, #password").on('input', function() {
        clearFieldError($(this));
    });
    
    // Auto-focus on email field when page loads
    $("#email").focus();
});