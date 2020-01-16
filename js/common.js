function getFormData(form) {
	var unindexed_array = form.serializeArray();
	var indexed_array = {};

	$.map(unindexed_array, function (n, i) {
		indexed_array[n['name']] = n['value'];
	});

	return indexed_array;
}


//$('.money').mask('0000000000.00', { reverse: true });


//$(document).ready(function () {
//	var $telefone4 = $("#telefone4");
//	$telefone4.mask('(00) 00000-0000', {
//		reverse: false
//	});
//});


$(document).ready(function () {
	$.post(
		"API/act.php", {
		action: "retrieve_session_info"
	},
		function (data, status) {
			var obj = JSON.parse(data);
			if (obj.data.client_id == null && obj.data.client_email == null) {
				alert("n logado");
				if (window.location.href != "https://localhost/posthumail/") {
					window.location.replace("https://localhost/posthumail/");
				}
			} else {
				alert("logado");
				window.location.replace("https://localhost/posthumail/menu/menu.php");
			}
		}
	);
});