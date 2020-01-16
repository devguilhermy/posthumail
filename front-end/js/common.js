function getFormData(form) {
	var unindexed_array = form.serializeArray();
	var indexed_array = {};

	$.map(unindexed_array, function (n, i) {
		indexed_array[n['name']] = n['value'];
	});

	return indexed_array;
}


$('.money').mask('0000000000.00', { reverse: true });


$(document).ready(function () {
	var $telefone4 = $("#telefone4");
	$telefone4.mask('(00) 00000-0000', {
		reverse: false
	});
});

