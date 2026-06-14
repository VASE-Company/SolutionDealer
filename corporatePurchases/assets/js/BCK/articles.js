function initializeEditArticle() {
	enableAllowDiscountEditArticle();
}

function enableAllowDiscountEditArticle() {
	if ($('#allowDiscount').prop("checked")) {
		$('#discountPercent').prop("disabled",false);
	} else {
		$('#discountPercent').val('0.00');
		$('#discountPercent').prop("disabled",true);
	}
}