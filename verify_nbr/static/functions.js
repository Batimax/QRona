// FUNCTIONS

function hideContainers() {
	$("#form_code").hide();
	$("#error_nbr_not_valid").hide();
	$("#error_occured").hide();
	$("#success_nbr_verified").hide();
	$("#error_unauthorized_country").hide();
}

function smsTimeOut() {
	var enableSubmit = function () {
		$("#send_sms").removeAttr("disabled");
		$("#send_sms_again").removeAttr("disabled");
	};

	$("#send_sms").attr("disabled", true);
	$("#send_sms_again").attr("disabled", true);

	setTimeout(function () {
		enableSubmit();
	}, 15000);
}

function validate_form() {
	var phone_success = false;

	var reg_phone = /^([+][(]?[0-9]{1,3}[)]?)\s*[-\s\.]?[(]?[0-9]{1,3}[)]?([-\s\.]?[0-9]{3})([-\s\.]?[0-9]{3,5})$/g;

	if (!reg_phone.test(phone.value)) {
		$("#phone").removeClass("is-valid");
		$("#phone").addClass("is-invalid");
	} else {
		$("#phone").addClass("is-valid");
		$("#phone").removeClass("is-invalid");
		phone_success = true;
	}

	if (phone_success) {
		return true;
	} else {
		return false;
	}
}

function validate_code() {
	var code_success = false;
	var reg_code = /^[0-9]{6}$/;
	if (!reg_code.test(code.value)) {
		$("#code").addClass("is-invalid");
	} else {
		$("#code").removeClass("is-invalid");
		code_success = true;
	}
	if (code_success) {
		return true;
	} else {
		return false;
	}
}

function waitForCode() {
	$("#form_phone").hide();
	$("#form_code").show();
}

function redirectLogTimeOut(url) {
	setTimeout(function () {
		redirect();
	}, 3000);

	function redirect() {
		window.location.replace(url);
	}
}
