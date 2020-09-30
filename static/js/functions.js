// FUNCTIONS

function validate_form() {
	var firstname_success,
		lastname_success,
		phone_success,
		mail_success,
		address_success,
		city_success,
		zipcode_success;

	// Names
	var reg_firstname = /^ ?[A-Za-z]{1}[a-zâäàéèùêëîïôöçñ]{2,20}([ -][A-Za-z]{1}[a-zâäàéèùêëîïôöçñ]{2,20}){0,2} ?$/;

	if (!reg_firstname.test(firstname.value)) {
		$("#firstname").removeClass("is-valid");
		$("#firstname").addClass("is-invalid");
	} else {
		$("#firstname").addClass("is-valid");
		$("#firstname").removeClass("is-invalid");
		firstname_success = true;
	}

	reg_lastname = /^ ?[A-Za-z]{1}[a-zâäàéèùêëîïôöçñ]{1,20}([ -][A-Za-z]{1}[a-zâäàéèùêëîïôöçñ]{1,20}){0,3} ?$/;

	if (!reg_lastname.test(lastname.value)) {
		$("#lastname").removeClass("is-valid");
		$("#lastname").addClass("is-invalid");
	} else {
		$("#lastname").addClass("is-valid");
		$("#lastname").removeClass("is-invalid");
		lastname_success = true;
	}

	// Address
	if (address.value.length < 6) {
		$("#address").removeClass("is-valid");
		$("#address").addClass("is-invalid");
	} else {
		$("#address").addClass("is-valid");
		$("#address").removeClass("is-invalid");
		address_success = true;
	}

	// City
	if (city.value.length < 4) {
		$("#city").removeClass("is-valid");
		$("#city").addClass("is-invalid");
	} else {
		$("#city").addClass("is-valid");
		$("#city").removeClass("is-invalid");
		city_success = true;
	}

	// Zipcode
	reg_zipcode = /^[0-9]{4,6}?$/;

	if (!reg_zipcode.test(zipcode.value)) {
		$("#zipcode").removeClass("is-valid");
		$("#zipcode").addClass("is-invalid");
	} else {
		$("#zipcode").addClass("is-valid");
		$("#zipcode").removeClass("is-invalid");
		zipcode_success = true;
	}

	// Mail
	// var reg_mail = /^[\+a-zA-Z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$/;
	var reg_mail = /^[a-zA-Z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$/;


	if (!reg_mail.test(email.value)) {
		$("#email").removeClass("is-valid");
		$("#email").addClass("is-invalid");
	} else {
		$("#email").addClass("is-valid");
		$("#email").removeClass("is-invalid");
		mail_success = true;
	}

	if (
		firstname_success &&
		lastname_success &&
		address_success &&
		city_success &&
		zipcode_success &&
		mail_success
	) {
		return true;
	} else {
		return false;
	}
}

function hideContainers() {
	$("#formulaire").hide();
	$("#success_login").hide();
	$("#success_new_account").hide();
	$("#success_departure").hide();
	$("#error_cookie_deleted").hide();
	$("#error_changed_cookie").hide();
	$("#error_already_logged").hide();
}

function redirectUrl(time) {
	setTimeout(function () {
		window.location.replace("https://satellite.bar/");
	}, time);
}

function createNewUser(user_table) {
	console.log("creating new account");
	// Opens Popup for terms and conditions when page is loaded
	console.log("modal opening");
	$("#terms").modal("show");
	console.log("modal opened!");
	// Deny
	$("#btn_deny").on("click", function () {
		redirectUrl(0);
	});

	// Agree
	$("#btn_accept").on("click", function () {
		$("#terms").modal("hide");
		$("#formulaire").fadeIn();
	});

	// Register
	$("#btn_register").on("click", function (e) {
		e.preventDefault();

		var firstname = $("#firstname").val().trim();
		var lastname = $("#lastname").val().trim();
		var address = $("#address").val().trim();
		var city = $("#city").val().trim();
		var zipcode = $("#zipcode").val().trim();
		// var phone = $("#phone").val().trim();

		var email = $("#email").val().trim();

		// Verify data and send data to backend
		if (validate_form()) {
			console.log("asaasas");
			$.ajax({
				url: "static/api/api.php",
				type: "post",
				dataType: "json",
				data: {
					firstname: firstname,
					lastname: lastname,
					address: address,
					city: city,
					zipcode: zipcode,
					email: email,
					user_table: user_table,
				},
				success: function (response) {
					if (response.status == "succes_login") {
						console.log(response);

						// show success container
						$("#formulaire").hide();
						$("#success_new_account").fadeIn();

						// Create cookie during 6 months
						Cookies.set("userID", response.userID, {
							expires: 180,
						});
						console.log("Cookie saved");

						if (response.error == "cookie_deleted") {
							$("#error_cookie_deleted").fadeIn();
						}
						redirectUrl(5000);
					} else if (response.error == "last_log_too_soon") {
						console.warn(
							"User has already logged less than 30 minutes ago."
						);
						$("#formulaire").hide();
						$("#error_already_logged").fadeIn();
						redirectUrl(5000);
					} else if (response.error == "nbr_not_verified") {
						console.log("Phone number not verified!");
						// Create cookie during 6 months
						Cookies.set("userID", response.userID, {
							expires: 180,
						});
						console.log("Cookie saved");

						var userID = response.status;
						// Relative path
						var url =
							"verify_nbr/index.html?userID=" +
							userID +
							"&table=" +
							user_table;
						window.location.replace(url);
					}
				},
				error: function () {}, //!!!!!!!!!!!!!!!!!!!!!
			});
		}
	});
}
