$(document).ready(function () {
    // Password strength requirements
    const PASSWORD_MIN_LENGTH = 8; // Increased from 6 for better security
    const PASSWORD_REQUIREMENTS = {
        minLength: 8,
        requireUppercase: true,
        requireLowercase: true,
        requireNumber: true,
        requireSpecial: false // Optional but recommended
    };
    
    // Email validation regex - more comprehensive
    function validateEmail(email) {
        const expr = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        return expr.test(email);
    }
    
    // Phone validation - accepts various formats
    function validatePhone(phone) {
        // Remove all non-digit characters for validation
        const cleanPhone = phone.replace(/\D/g, '');
        
        // Check if it's 10 digits (US) or between 10-15 (international)
        if (cleanPhone.length === 10) {
            return cleanPhone; // US number without country code
        } else if (cleanPhone.length === 11 && cleanPhone[0] === '1') {
            return cleanPhone.substring(1); // US number with country code
        } else if (cleanPhone.length >= 10 && cleanPhone.length <= 15) {
            return cleanPhone; // International number
        }
        return false;
    }
    
    // Format phone number for display
    function formatPhoneNumber(phone) {
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 10) {
            return cleaned.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        return phone;
    }
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        const feedback = [];
        
        if (password.length >= PASSWORD_MIN_LENGTH) {
            strength++;
        } else {
            feedback.push(`At least ${PASSWORD_MIN_LENGTH} characters`);
        }
        
        if (/[a-z]/.test(password)) {
            strength++;
        } else if (PASSWORD_REQUIREMENTS.requireLowercase) {
            feedback.push("One lowercase letter");
        }
        
        if (/[A-Z]/.test(password)) {
            strength++;
        } else if (PASSWORD_REQUIREMENTS.requireUppercase) {
            feedback.push("One uppercase letter");
        }
        
        if (/[0-9]/.test(password)) {
            strength++;
        } else if (PASSWORD_REQUIREMENTS.requireNumber) {
            feedback.push("One number");
        }
        
        if (/[^a-zA-Z0-9]/.test(password)) {
            strength++;
        } else if (PASSWORD_REQUIREMENTS.requireSpecial) {
            feedback.push("One special character");
        }
        
        return {
            score: strength,
            feedback: feedback,
            isValid: feedback.length === 0 || (password.length >= PASSWORD_MIN_LENGTH && strength >= 3)
        };
    }
    
    // Show field error with Bootstrap styling
    function showFieldError($field, message) {
        $field.addClass('is-invalid');
        
        // Remove existing error message
        $field.siblings('.invalid-feedback').remove();
        
        // Add new error message
        $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
    }
    
    // Clear field error
    function clearFieldError($field) {
        $field.removeClass('is-invalid is-valid');
        $field.siblings('.invalid-feedback').remove();
        $field.siblings('.valid-feedback').remove();
    }
    
    // Show field success
    function showFieldSuccess($field, message = '') {
        $field.removeClass('is-invalid').addClass('is-valid');
        $field.siblings('.invalid-feedback').remove();
        
        if (message) {
            $field.siblings('.valid-feedback').remove();
            $field.after('<div class="valid-feedback d-block">' + message + '</div>');
        }
    }
    
    // Real-time validation for all fields
    $("#firstName, #lastName").on('blur input', function() {
        const $field = $(this);
        const value = $field.val().trim();
        const fieldName = $field.attr('id') === 'firstName' ? 'First name' : 'Last name';
        
        if (value === '') {
            clearFieldError($field);
        } else if (value.length < 2) {
            showFieldError($field, `${fieldName} must be at least 2 characters`);
        } else if (!/^[a-zA-Z\s'-]+$/.test(value)) {
            showFieldError($field, `${fieldName} contains invalid characters`);
        } else {
            showFieldSuccess($field);
        }
    });
    
    // Email validation on input
    $("#email").on('blur input', function() {
        const email = $(this).val().trim();
        
        if (email === '') {
            clearFieldError($(this));
        } else if (!validateEmail(email)) {
            showFieldError($(this), 'Please enter a valid email address');
        } else {
            showFieldSuccess($(this));
            // Check if email already exists (optional - requires backend endpoint)
            // checkEmailAvailability(email);
        }
    });
    
    // Phone validation on input
    $("#phone").on('blur input', function() {
        const phone = $(this).val().trim();
        
        if (phone === '') {
            clearFieldError($(this));
        } else {
            const validPhone = validatePhone(phone);
            if (!validPhone) {
                showFieldError($(this), 'Please enter a valid phone number');
            } else {
                // Auto-format the phone number
                $(this).val(formatPhoneNumber(phone));
                showFieldSuccess($(this));
            }
        }
    });
    
    // Password strength indicator
    $("#password").on('input focus', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        
        if (password === '') {
            clearFieldError($(this));
            $('.password-strength').remove();
        } else {
            // Remove existing strength indicator
            $('.password-strength').remove();
            
            // Add strength indicator
            let strengthClass = 'danger';
            let strengthText = 'Weak';
            
            if (strength.score >= 4) {
                strengthClass = 'success';
                strengthText = 'Strong';
            } else if (strength.score >= 3) {
                strengthClass = 'warning';
                strengthText = 'Medium';
            }
            
            if (strength.feedback.length > 0) {
                showFieldError($(this), 'Password needs: ' + strength.feedback.join(', '));
            } else {
                showFieldSuccess($(this));
                $(this).after(`<small class="password-strength text-${strengthClass}">Password strength: ${strengthText}</small>`);
            }
        }
        
        // Check confirm password match if it has value
        const confirmPassword = $("#confirmPassword").val();
        if (confirmPassword !== '') {
            checkPasswordMatch();
        }
    });
    
    // Confirm password validation
    function checkPasswordMatch() {
        const password = $("#password").val();
        const confirmPassword = $("#confirmPassword").val();
        
        if (confirmPassword === '') {
            clearFieldError($("#confirmPassword"));
        } else if (password !== confirmPassword) {
            showFieldError($("#confirmPassword"), 'Passwords do not match');
        } else {
            showFieldSuccess($("#confirmPassword"), 'Passwords match');
        }
    }
    
    $("#confirmPassword").on('input', checkPasswordMatch);
    
    // Handle Enter key navigation
    $("input").on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const $inputs = $('input:visible');
            const index = $inputs.index(this);
            
            if (index < $inputs.length - 1) {
                $inputs.eq(index + 1).focus();
            } else {
                $("#btnSignup").trigger('click');
            }
        }
    });
    
    // Main signup handler
    $("#btnSignup").click(function(e) {
        e.preventDefault();
        
        // Get all values
        const firstName = $("#firstName").val().trim();
        const lastName = $("#lastName").val().trim();
        const email = $("#email").val().trim();
        const phone = $("#phone").val().trim();
        const password = $("#password").val();
        const confirmPassword = $("#confirmPassword").val();
        const tosChecked = $('input[name="tos"]').is(':checked');
        const csrfToken = $('input[name="csrf_token"]').val();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validation flags
        let isValid = true;
        let firstErrorField = null;
        
        // Validate all fields
        if (firstName === '' || firstName.length < 2) {
            showFieldError($("#firstName"), firstName === '' ? 'First name is required' : 'First name must be at least 2 characters');
            if (!firstErrorField) firstErrorField = $("#firstName");
            isValid = false;
        }
        
        if (lastName === '' || lastName.length < 2) {
            showFieldError($("#lastName"), lastName === '' ? 'Last name is required' : 'Last name must be at least 2 characters');
            if (!firstErrorField) firstErrorField = $("#lastName");
            isValid = false;
        }
        
        if (email === '') {
            showFieldError($("#email"), 'Email address is required');
            if (!firstErrorField) firstErrorField = $("#email");
            isValid = false;
        } else if (!validateEmail(email)) {
            showFieldError($("#email"), 'Please enter a valid email address');
            if (!firstErrorField) firstErrorField = $("#email");
            isValid = false;
        }
        
        if (phone === '') {
            showFieldError($("#phone"), 'Phone number is required');
            if (!firstErrorField) firstErrorField = $("#phone");
            isValid = false;
        } else if (!validatePhone(phone)) {
            showFieldError($("#phone"), 'Please enter a valid phone number');
            if (!firstErrorField) firstErrorField = $("#phone");
            isValid = false;
        }
        
        const passwordStrength = checkPasswordStrength(password);
        if (password === '') {
            showFieldError($("#password"), 'Password is required');
            if (!firstErrorField) firstErrorField = $("#password");
            isValid = false;
        } else if (!passwordStrength.isValid) {
            showFieldError($("#password"), 'Password needs: ' + passwordStrength.feedback.join(', '));
            if (!firstErrorField) firstErrorField = $("#password");
            isValid = false;
        }
        
        if (confirmPassword === '') {
            showFieldError($("#confirmPassword"), 'Please confirm your password');
            if (!firstErrorField) firstErrorField = $("#confirmPassword");
            isValid = false;
        } else if (password !== confirmPassword) {
            showFieldError($("#confirmPassword"), 'Passwords do not match');
            if (!firstErrorField) firstErrorField = $("#confirmPassword");
            isValid = false;
        }
        
        if (!tosChecked) {
            Swal.fire({
                title: 'Terms of Service',
                text: 'Please agree to our Terms of Service and Privacy Policy to continue',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#17a2b8'
            });
            isValid = false;
        }
        
        // Focus first error field
        if (firstErrorField) {
            firstErrorField.focus();
        }
        
        // Stop if validation failed
        if (!isValid) {
            return false;
        }
        
        // Show loading state
        const $btn = $(this);
        const originalText = $btn.text();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Creating your account...').prop('disabled', true);
        
        // Submit to server
        $.ajax({
            url: "functions/register.php", // FIXED: Added .php extension
            type: "POST",
            data: {
                firstName: firstName,
                lastName: lastName,
                emailAddress: email, // Changed to match backend expectation
                phone: validatePhone(phone), // Send clean phone number
                password: password,
                confirmPassword: confirmPassword,
                csrf_token: csrfToken // Added CSRF token
            },
            dataType: "JSON",
            success: function(response) {
                if (response.result === "OK") {
                    // Success
                    Swal.fire({
                        title: 'Welcome to The Goral!',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success" style="font-size: 48px; margin-bottom: 20px;"></i>
                                <p>Your account has been created successfully!</p>
                                <p class="text-muted">Please check your email at <strong>${email}</strong> to verify your account.</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Go to Login',
                        confirmButtonColor: '#28a745',
                        showCancelButton: true,
                        cancelButtonText: 'Go to Homepage',
                        cancelButtonColor: '#6c757d',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/sign-in";
                        } else {
                            window.location.href = "/";
                        }
                    });
                } else if (response.result === "EXISTS") {
                    // Email already registered
                    Swal.fire({
                        title: 'Email Already Registered',
                        html: `
                            <p>This email address is already associated with an account.</p>
                            <p>Would you like to login instead?</p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Go to Login',
                        cancelButtonText: 'Try Another Email',
                        confirmButtonColor: '#17a2b8',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/sign-in";
                        } else {
                            $("#email").val('').focus();
                        }
                    });
                    $btn.html(originalText).prop('disabled', false);
                } else {
                    // Other errors
                    Swal.fire({
                        title: 'Registration Failed',
                        text: response.message || 'An error occurred during registration. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545'
                    });
                    $btn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error("Registration error:", error);
                
                let errorMessage = "Unable to complete registration. ";
                
                if (xhr.status === 403) {
                    errorMessage += "Security verification failed. Please refresh the page and try again.";
                } else if (xhr.status === 404) {
                    errorMessage += "Registration service not found. Please contact support.";
                } else if (xhr.status === 500) {
                    errorMessage += "Server error occurred. Please try again later.";
                } else if (status === "timeout") {
                    errorMessage += "Request timed out. Please check your connection.";
                } else {
                    errorMessage += "Please check your internet connection and try again.";
                }
                
                Swal.fire({
                    title: 'Connection Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                
                $btn.html(originalText).prop('disabled', false);
            },
            timeout: 30000 // 30 second timeout
        });
    });
    
    // Auto-focus first field on load
    $("#firstName").focus();
});