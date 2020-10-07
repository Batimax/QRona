$(document).ready(function () {
	console.log("document ready");
	// Hide containers
	hideContainers();

	//Check if cookie exists
	var userID = Cookies.get("userID");

	// Extract GET variables (zones)
	var $_GET = {},
		user_table;

	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
		function decode(s) {
			return decodeURIComponent(s.split("+").join(" "));
		}

		$_GET[decode(arguments[1])] = decode(arguments[2]);
	});

	user_table = $_GET["table"];

	// USER WITH COOKIE
	if (userID != null) {
		console.log("Cookie found");
		$.ajax({
			url: "static/api/api.php",
			type: "post",
			data: {
				userID: userID,
				user_table: user_table,
			},
			success: function (response) {
				if (response.status == "succes_login") {
					console.log("Successfull login");
					console.log(response);
					// show success container
					$("#success_login").fadeIn();
					redirectUrl(5000);
				} else if (response.error == "last_log_too_soon") {
					console.warn(
						"User has already logged less than 30 minutes ago in this zone."
					);
					$("#error_already_logged").fadeIn();
					redirectUrl(5000);
				} else if (response.error == "user_not_found") {
					// Error: Cannot find userID in db -> create new account
					console.error("Cannot find userID in db");
					$("#error_changed_cookie").fadeIn();
					Cookies.set("userID", "", { expires: -1 });
					redirectUrl(15000);
				} else if (response.error == "nbr_not_verified") {
					console.log("Phone number not verified!");
					var url =
						"verify_nbr/index.html?userID=" +
						userID +
						"&table=" +
						user_table;
					// var url =
					// 	// "http://localhost:8888/Satellite/QRona/verify_nbr/index.html?userID=" +
					// 	"https://satellite.bar/qr/verify_nbr/index.html?userID=" +
					// 	userID +
					// 	"&table=" +
					// 	user_table;
					window.location.replace(url);
				}
			},
		});
	} else {
		// NEW USER
		createNewUser(user_table);
	}
});
