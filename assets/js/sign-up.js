$(document).ready(function () {
    function validateEmail(email) {
        var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        return expr.test(email);
    }

    function getValidNumber(value) {
        value = $.trim(value).replace(/\D/g, '');
        if (value.substring(0, 1) == '1') {
            value = value.substring(1);
        }
        if (value.length == 10) {
            return value;
        }
        return false;
    }

    // Real-time validation
    $("#email, #phone, #password, #confirmPassword").bind("keyup change", function() {
        var email = $("#email").val();
        var phone = $("#phone").val();
        // 7-15 digits, allowing +, spaces, dots, dashes and parentheses
        var filter = /^(?=(?:\D*\d){7,15}\D*$)[+\d\s().-]+$/;
        var password = $("#password").val();
        var confirmPassword = $("#confirmPassword").val();

        // Email validation
        if(email != "") {
            if(validateEmail(email)) {
                $(".emailNotValid").hide();
            } else {
                $(".emailNotValid").fadeIn();
            }
        } else {
            $(".emailNotValid").hide();
        }

        // Phone validation
        if(phone != "") {
            if(filter.test(phone)) {
                $(".phoneNotValid").hide();
            } else {
                $(".phoneNotValid").fadeIn();
            }
        } else {
            $(".phoneNotValid").hide();
        }

        // Password validation
        if(password != "") {
            if(password.length >= 6) {
                $(".minPassword").hide();
            } else {
                $(".minPassword").fadeIn();
            }
        }

        // Password confirmation match
        if(password != "" && confirmPassword != "") {
            if(password == confirmPassword) {
                $(".mustSamePassword").hide();
            } else {
                $(".mustSamePassword").fadeIn();
            }
        }
    });

    // Form submission
    $("#btnSignup").click(function () {
        var firstName = $("#firstName").val().trim();
        var lastName = $("#lastName").val().trim();
        var email = $("#email").val().trim();
        var phone = $("#phone").val().trim();
        var password = $("#password").val();
        var confirmPassword = $("#confirmPassword").val();
        var filter = /^(?=(?:\D*\d){7,15}\D*$)[+\d\s().-]+$/;

        // Validate all fields are filled
        if(firstName == "" || lastName == "" || email == "" || phone == "" || password == "" || confirmPassword == "") {
            if(firstName == "") {
                $("#firstName").focus();
            } else if(lastName == "") {
                $("#lastName").focus();
            } else if(email == "") {
                $("#email").focus();
            } else if(phone == "") {
                $("#phone").focus();
            } else if(password == "") {
                $("#password").focus();
            } else if(confirmPassword == "") {
                $("#confirmPassword").focus();
            }
            return;
        }

        // Names must look like names — winners are announced publicly by name.
        var nameRule = /^\p{L}[\p{L} .'-]{0,48}\p{L}$/u;
        if(!nameRule.test(firstName) || !nameRule.test(lastName)) {
            Swal.fire({
                icon: "error",
                title: "Real Name Required",
                text: "Please enter your real first and last name — winners are announced by name."
            });
            $(!nameRule.test(firstName) ? "#firstName" : "#lastName").focus();
            return;
        }

        // Validate format and requirements
        if(!validateEmail(email)) {
            Swal.fire({
                icon: "error",
                title: "Invalid Email",
                text: "Please enter a valid email address"
            });
            $("#email").focus();
            return;
        }

        if(!filter.test(phone)) {
            Swal.fire({
                icon: "error",
                title: "Invalid Phone",
                text: "Please enter a valid phone number"
            });
            $("#phone").focus();
            return;
        }

        if(password.length < 6) {
            Swal.fire({
                icon: "error",
                title: "Weak Password",
                text: "Password must be at least 6 characters"
            });
            $("#password").focus();
            return;
        }

        if(password != confirmPassword) {
            Swal.fire({
                icon: "error",
                title: "Password Mismatch",
                text: "Passwords do not match"
            });
            $("#password").focus();
            return;
        }

        // Check terms of service
        if(!$('input[name="tos"]').is(':checked')) {
            Swal.fire({
                icon: "warning",
                title: "Terms Required",
                text: "Please agree to the Terms of Services"
            });
            return;
        }

        // Submit form
        $("#btnSignup").text("Signing you up").prop('disabled', true);

        $.ajax({
            url: "functions/register",
            type: "POST",
            data: {
                firstName: firstName,
                lastName: lastName,
                email: email,
                phone: phone,
                password: password
            },
            dataType: "JSON",
            success: function (response) {
                $("#btnSignup").text("Sign up").prop('disabled', false);

                if(response.result == "OK") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Created!',
                        text: "Your account has been successfully created. You can now log in.",
                        showConfirmButton: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        if(result.isConfirmed) {
                            window.location.href = "/sign-in";
                        }
                    });
                } else if(response.result == "userExisted") {
                    Swal.fire({
                        icon: "error",
                        title: "Email Already Registered",
                        text: "This email address is already registered. Please use a different email or try logging in."
                    });
                } else if(response.result == "invalidName") {
                    Swal.fire({
                        icon: "error",
                        title: "Real Name Required",
                        text: "Please enter your real first and last name — winners are announced by name."
                    });
                } else if(response.result == "invalidEmail") {
                    Swal.fire({
                        icon: "error",
                        title: "Invalid Email",
                        text: "Please enter a valid email address"
                    });
                } else if(response.result == "weakPassword") {
                    Swal.fire({
                        icon: "error",
                        title: "Weak Password",
                        text: "Password must be at least 6 characters"
                    });
                } else if(response.result == "missingFields") {
                    Swal.fire({
                        icon: "error",
                        title: "Missing Fields",
                        text: "Please fill in all required fields"
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Registration Failed",
                        text: response.message || "An error occurred during registration. Please try again."
                    });
                }
            },
            error: function() {
                $("#btnSignup").text("Sign up").prop('disabled', false);
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Could not connect to server. Please try again."
                });
            }
        });
    });
});