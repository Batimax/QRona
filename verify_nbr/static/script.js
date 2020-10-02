$(document).ready(function () {
	// Hide containers
	hideContainers();

	// Extract GET variables
	var $_GET = {},
		userID;

	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
		function decode(s) {
			return decodeURIComponent(s.split("+").join(" "));
		}
		$_GET[decode(arguments[1])] = decode(arguments[2]);
	});

	userID = $_GET["userID"];
	user_table = $_GET["table"];

	// Send sms
	$("#send_sms").on("click", function (e) {
		e.preventDefault();

		if (validate_form()) {
			var phone = $("#phone").val().trim();

			phone = formatePhoneNumber(phone);

			if (phone === false) {
				$("#phone").removeClass("is-valid");
				$("#phone").addClass("is-invalid");
			} else {
				console.log(phone);

				sessionStorage.setItem("phone_number", phone);

				console.log("Sending SMS...");
				$.ajax({
					url: "static/api.php",
					type: "post",
					data: {
						action: "send_sms",
						phone: phone,
						userID: userID,
					},
					success: function (response) {
						console.log(response);
						if (response.status == "success_sms") {
							console.log("SMS sent!");
							// Set Timeout for sendSMS buttons
							smsTimeOut();

							waitForCode();
						} else if (response.error == "twilio_phone_not_valid") {
							console.log("Not valid phone for the API!");
							$("#phone").addClass("is-invalid");
							$("#error_nbr_not_valid").show();
						} else if (
							response.error == "twilio_unauthorized_country"
						) {
							console.log("Not authorized country code for API!");
							$("#phone").addClass("is-invalid");
							$("#error_unauthorized_country").fadeIn();
						} else {
							console.log("An error occured with the API!");
							$("#error_occured").show();
						}
					},
					error: function (response) {
						console.log(response);
					},
				});
			}
		}
	});

	// Check entered code
	$("#check_code").on("click", function (e) {
		e.preventDefault();
		checkCodeTimeOut();
		if (validate_code()) {
			var code = $("#code").val();
			console.log("Checking code...");

			var phone = sessionStorage.getItem("phone_number");

			$.ajax({
				url: "static/api.php",
				type: "post",
				data: {
					action: "check_code",
					userID: userID,
					code: code,
					phone: phone,
				},
				success: function (response) {
					if (response.status == "success_check_code") {
						$("#code").removeClass("is-invalid");
						$("#code").addClass("is-valid");
						console.log("Code valid!");
						// Send-back user to log page
						$("#success_nbr_verified").fadeIn();
						$("#form_phone").hide();
						$("#form_code").hide();

						// Redirect user to logs
						var url = "../index.html?table=" + user_table;
						redirectLogTimeOut(url);
					} else if (response.error == "wrong_code") {
						$("#code").removeClass("is-valid");
						$("#code").addClass("is-invalid");
						console.log("Wrong code!");
					}
				},
			});
		}
	});

	// Send sms again
	$("#send_sms_again").on("click", function (e) {
		console.log("Sending other code");
		e.preventDefault();

		// Set Timeout for sendSMS buttons
		smsTimeOut();

		$("#code").val("");
		$("#code").removeClass("is-invalid");

		var phone = sessionStorage.getItem("phone_number");

		$.ajax({
			url: "static/api.php",
			type: "post",
			data: {
				action: "send_sms",
				phone: phone,
				userID: userID,
			},
			success: function (response) {
				if (response.status == "success_sms") {
					console.log("SMS sent again!");
				}
			},
		});
	});

	// Change phone number
	$("#change_phone_number").on("click", function (e) {
		console.log("Change phone number");
		e.preventDefault();

		$("#form_phone").show();
		$("#code").val("");
		$("#code").removeClass("is-invalid");
	});
});
