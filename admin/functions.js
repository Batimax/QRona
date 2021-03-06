// FUNCTIONS

// Quick and simple export target #table_id into a csv
function download_table_as_csv(table_id) {
	// Select rows from table_id
	var rows = document.querySelectorAll("table#" + table_id + " tr");
	// Construct csv
	var csv = [];
	for (var i = 0; i < rows.length; i++) {
		var row = [],
			cols = rows[i].querySelectorAll("td, th");
		for (var j = 0; j < cols.length; j++) {
			// Clean innertext to remove multiple spaces and jumpline (break csv)
			// Let /n as it is for multiple dates cells (!!!!!!!!!)
			var data = cols[j].innerText
				.replace(/(\r\n|\n|\r)/gm, " | ")
				.replace(/(\s\s)/gm, " ");
			data = data.replace(/"/g, '""');
			// Let + in csv (regex for date: ^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} )
			data = data.replace(/\+/g, "'+");
			// Push escaped string
			row.push('"' + data + '"');
		}
		csv.push(row.join(";"));
	}
	var csv_string = csv.join("\n");
	// Download it
	var filename =
		"export_" + table_id + "_" + new Date().toLocaleDateString() + ".csv";
	var link = document.createElement("a");
	link.style.display = "none";
	link.setAttribute("target", "_blank");
	link.setAttribute(
		"href",
		"data:text/csv;charset=utf-8," + encodeURIComponent(csv_string)
	);
	link.setAttribute("download", filename);
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
}

function redirectUrl(time) {
	setTimeout(function () {
		window.location.replace("https://satellite.bar/");
	}, time);
}

function Export() {
	html2canvas(document.getElementById("Contaminated_people"), {
		onrendered: function (canvas) {
			var data = canvas.toDataURL();
			var docDefinition = {
				pageOrientation: "landscape",
				content: [
					{
						image: data,
						width: 750,
					},
				],
			};
			pdfMake.createPdf(docDefinition).download("Table.pdf");
		},
	});
}

function getDateAgo(days) {
	var now = new Date(); //"now"
	now.setDate(now.getDate() - days);

	m = now.getMonth() + 1;
	if (m == 1) {
		M = "Janvier";
	} else if (m == 2) {
		M = "Février";
	} else if (m == 3) {
		M = "Mars";
	} else if (m == 4) {
		M = "Avril";
	} else if (m == 5) {
		M = "Mai";
	} else if (m == 6) {
		M = "Juin";
	} else if (m == 7) {
		M = "Juillet";
	} else if (m == 8) {
		M = "Août";
	} else if (m == 9) {
		M = "Septembre";
	} else if (m == 10) {
		M = "Octobre";
	} else if (m == 11) {
		M = "Novembre";
	} else if (m == 12) {
		M = "Décembre";
	}
	d = now.getDate();
	D = now.getDay() + 1;
	if (D == 1) {
		D = "Lundi";
	} else if (D == 2) {
		D = "Mardi";
	} else if (D == 3) {
		D = "Mercredi";
	} else if (D == 4) {
		D = "Jeudi";
	} else if (D == 5) {
		D = "Vendredi";
	} else if (D == 6) {
		D = "Samedi";
	} else if (D == 7) {
		D = "Dimanche";
	}
	var date = '<h1>' + D + " " + d + " " + M + '</h1>';
	return date;
}



function clearTable() {
	$("#my_table").html("");
	$("#my_table_header").html("");
}

// DELETE user in db
function deleteUser() {
	// Extract selected name id
	var selected_name_id = $("#selected_name").val();
	var selected_id = selected_name_id.split(":")[1];
	var selected_name = selected_name_id.split("id:")[0];

	if (
		selected_name_id.length > 1 &&
		typeof selected_id !== "undefined" &&
		selected_id.length > 0
	) {
		var msg_delete_user =
			"La suppresion du compte de <b>" +
			selected_name +
			"</b> sera définitive. Êtes-vous sûr de vouloir continuer?";

		$("#msg_delete_user").html(msg_delete_user);

		$("#modal_delete_user").modal("show");

		$("#btn_deny_delete_user").on("click", function () {
			$("#modal_delete_user").modal("hide");
		});

		$("#btn_accept_delete_user").on("click", function () {
			$("#modal_delete_user").modal("hide");

			console.warn("Deleting user");

			$.ajax({
				url: "api.php",
				type: "post",
				dataType: "json",
				data: {
					action: "delete_user",
					selected_id: selected_id,
				},
				success: function (response) {
					console.log(response);
					if (response.status == "success") {
						$("#modal_user_deleted").modal("show");

						$("#modal_user_deleted_ok").on("click", function () {
							$("#modal_user_deleted").modal("hide");
						});
					} else if (response.error == "no_user") {
						clearTable();
						$("#title_tab").html("Utilisateur non trouvé!");
					}
				},
			});
		});
	} else {
		clearTable();
		$("#title_tab").html("Sélection non valide!");
	}
}

// Show user data
function getUserData() {
	// Extract selected name id
	var selected_name_id = $("#selected_name").val();
	var selected_id = selected_name_id.split(":")[1];

	if (
		selected_name_id.length > 1 &&
		typeof selected_id !== "undefined" &&
		selected_id.length > 0
	) {
		clearTable();
		$.ajax({
			url: "api.php",
			type: "post",
			dataType: "json",
			data: {
				action: "get_user_data",
				selected_id: selected_id,
			},
			success: function (response) {
				if (response.error == "no_user") {
					clearTable();
					$("#title_tab").html("Utilisateur non trouvé!");
				} else {
					var user_data = response.status[0];
					console.log(user_data);
					var table_header =
						" <tr><th>Nom</th> <th>Rue</th> <th>Ville</th> <th>Zipcode</th> <th>Telephone</th> <th>Verif</th> <th>Email</th> <th>Scans</th> <th>Table</th></tr>";

					var name = user_data.lastname + " " + user_data.firstname;
					var adress = user_data.adress;
					var city = user_data.city;
					var zipcode = user_data.zipcode;
					var phone = user_data.phone;
					var validated = user_data.nbr_verified;
					var email = user_data.email;
					var logs = user_data.logs;

					var table_content =
						'<tr><th class="no_colapse"> ' +
						name +
						"</td> " +
						"<td> " +
						adress +
						"</td>" +
						"<td> " +
						city +
						"</td>" +
						"<td> " +
						zipcode +
						"</td>" +
						"<td> " +
						phone +
						"</td>" +
						"<td> " +
						validated +
						"</td>" +
						"<td> " +
						email +
						"</td>" +
						'<td class="no_colapse" > ' +
						logs +
						"</td></tr>";

					$("#my_table_header").html(table_header);
					$("#my_table").html(table_content);
				}
			},
		});
	} else {
		clearTable();
		$("#title_tab").html("Sélection non valide!");
	}
}
