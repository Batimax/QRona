$(document).ready(function () {
	// Agreement admin
	$("#terms").modal("show");
	$("#btn_deny").on("click", function () {
		redirectUrl(0);
	});

	// Agree
	$("#btn_accept").on("click", function () {
		$("#terms").modal("hide");

		// Get all "name" of database
		$.ajax({
			url: "api.php",
			type: "post",
			dataType: "json",
			data: {
				action: "get_names",
			},
			success: function (response) {
				console.log(response);
				var list_names = response.status;
				list_names.forEach(myFunction);

				function myFunction(value) {
					$("#list_name").append(
						$("<option>", {
							value: value,
						})
					);
				}
			},
		});

		// Get all tables from db
		$.ajax({
			url: "api.php",
			type: "post",
			dataType: "json",
			data: {
				action: "get_tables",
			},
			success: function (response) {
				console.log(response);
				var list_tables = response.status;
				list_tables.forEach(myFunction);

				function myFunction(value) {
					console.log(value);
					$("#selected_table").append(new Option(value));
				}
			},
		});

		// DELETE user in db
		$("#btn_delete_account").on("click", function (e) {
			e.preventDefault();
			deleteUser();
		});

		// Show user data
		$("#btn_get_user_data").on("click", function (e) {
			e.preventDefault();
			getUserData();
		});

		// Get dayly info
		$("#btn_submit_daily_info").on("click", function (e) {
			e.preventDefault();
			var selected_daily_info_days = $("#selected_daily_info_days").val();
			$.ajax({
				url: "api.php",
				type: "post",
				dataType: "json",
				data: {
					action: "print_table_daily",
					selected_daily_info_days: selected_daily_info_days,
				},
				success: function (response) {
					console.log(response);
					clearTable();
					var table_header = "";

					table_header = getDateAgo(selected_daily_info_days);

					$("#my_table_header").html(table_header);
					$("#my_table").html(response.status);
				},
			});
		});

		// Send selected name to server
		$("#btn_submit_names").on("click", function (e) {
			e.preventDefault();

			var selected_name_id = $("#selected_name").val();
			var selected_hour = $("#selected_hour").val();
			var selected_days_ago = $("#selected_days_ago").val();
			var selected_table = $("#selected_table").val();

			// Extract selected name id
			var selected_id = selected_name_id.split(":")[1];
			var selected_name = selected_name_id.split("id:")[0];

			// Verify data and send data to backend
			$.ajax({
				url: "api.php",
				type: "post",
				dataType: "json",
				data: {
					action: "send_selected_id",
					selected_id: selected_id,
					selected_hour: selected_hour,
					selected_table: selected_table,
					selected_days_ago: selected_days_ago,
				},
				success: function (response) {
					if (response.error === "no_id_selected") {
						console.warn("No user selected!");
						$("#title_tab").html("No user selected!");
						clearTable();
					} else if (
						response.error ===
						"no_users_in_contact_with_wanted_user"
					) {
						clearTable();
						$("#title_tab").html("No contact with other clients!");
					} else if (response.error === "no_scan_date_for_user") {
						clearTable();
						$("#title_tab").html(
							"No log registered for this user!"
						);
					} else {
						var user_contaminated_infos = response.status;
						console.log(user_contaminated_infos);

						var table_content, date_scan_wanted, table_header;
						table_content = "";
						table_header =
							" <tr><th>Date Contamination</th> <th>Nom</th> <th>Rue</th> <th>Zipcode</th> <th>Ville</th> <th>Telephone</th> <th>Email</th> <th>Date Scan</th></tr>";

						user_contaminated_infos.forEach(myFunction);

						function myFunction(value) {
							if (jQuery.type(value) !== "array") {
								// Create a new table for each date_scan of the suspect person
								date_scan_wanted = value;
							} else {
								value.forEach(children_myFunction);

								function children_myFunction(children_value) {
									// Treat all persons data
									var name =
										children_value.lastname +
										" " +
										children_value.firstname;
									var adress = children_value.adress;
									var zipcode = children_value.zipcode;
									var city = children_value.city;
									var phone = children_value.phone;
									var email = children_value.email;
									var date_scan = children_value.date_scan;

									var line =
										'<tr><th class="no_colapse"> ' +
										date_scan_wanted +
										"</th><td>" +
										name +
										"</td> " +
										"<td> " +
										adress +
										"</td>" +
										"<td> " +
										zipcode +
										"</td>" +
										"<td> " +
										city +
										"</td>" +
										"<td> " +
										phone +
										"</td>" +
										"<td> " +
										email +
										"</td>" +
										'<td class="no_colapse" > ' +
										date_scan +
										"</td></tr>";

									table_content = table_content + line;
								}
								table_content =
									table_content +
									'<tr><td colspan=7 class="table-dark">  </td></tr>';
							}
						}
						$("#my_table_header").html(table_header);
						$("#title_tab").html(selected_name);
						$("#my_table").html(table_content);
					}
				},
			});
		});

		// Send selected uniq name to server
		$("#btn_submit_unique_names").on("click", function (e) {
			e.preventDefault();

			var selected_name_id = $("#selected_name").val();
			var selected_hour = $("#selected_hour").val();
			var selected_table = $("#selected_table").val();
			var selected_days_ago = $("#selected_days_ago").val();

			// Extract selected name id
			var selected_id = selected_name_id.split(":")[1]; // returns 'two'
			var selected_name = selected_name_id.split("id:")[0]; // returns 'two'

			// Verify data and send data to backend
			$.ajax({
				url: "api.php",
				type: "post",
				dataType: "json",
				data: {
					action: "send_selected_unique_id",
					selected_id: selected_id,
					selected_hour: selected_hour,
					selected_table: selected_table,
					selected_days_ago: selected_days_ago,
				},
				success: function (response) {
					if (response.error === "no_id_selected") {
						console.warn("No user selected!");
						$("#title_tab").html("No user selected!");
						clearTable();
					} else if (
						response.error ===
						"no_users_in_contact_with_wanted_user"
					) {
						clearTable();
						$("#title_tab").html("No contact with other clients!");
					} else if (response.error === "no_scan_date_for_user") {
						clearTable();
						$("#title_tab").html(
							"No log registered for this user!"
						);
					} else {
						var user_contaminated_infos = response.status;
						console.log(user_contaminated_infos);

						var table_content, table_header;
						table_content = "";
						table_header =
							" <tr><th>Nom</th> <th>Rue</th> <th>Zipcode</th> <th>Ville</th> <th>Telephone</th> <th>Email</th>";

						user_contaminated_infos.forEach(myFunction);

						function myFunction(value) {
							if (jQuery.type(value) !== "array") {
								// When array element is the scan_date
							} else {
								value.forEach(children_myFunction);

								function children_myFunction(children_value) {
									// Treat all persons data
									var name =
										children_value.lastname +
										" " +
										children_value.firstname;
									var adress = children_value.adress;
									var zipcode = children_value.zipcode;
									var city = children_value.city;
									var phone = children_value.phone;
									var email = children_value.email;

									var line =
										"<tr><td>" +
										name +
										"</td> " +
										"<td> " +
										adress +
										"</td>" +
										"<td> " +
										zipcode +
										"</td>" +
										"<td> " +
										city +
										"</td>" +
										"<td> " +
										phone +
										"</td>" +
										"<td> " +
										email +
										"</td></tr>";

									table_content = table_content + line;
								}
							}
						}
						$("#my_table_header").html(table_header);
						$("#title_tab").html(selected_name);
						$("#my_table").html(table_content);
					}
				},
			});
		});
	});
});
