function seeStockByArticle(articleId) {
	if (articleId == null) articleId = -1;

	if (articleId > 0) {
		modalMessage('articles/stock/'+articleId,"Resumen de Stock",null,null,null,null,'closeModalMessage()',true,null,null,700);		
	}
}

function seeOutOfStockByArticle(articleId,warehouseId) {
	if (articleId == null) articleId = -1;
	if (warehouseId == null) warehouseId = -1;

	if (articleId > 0 && warehouseId > 0) {
		modalMessage('articles/outOfStock/'+articleId+'/'+warehouseId,"Stock Faltante",null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}

function seeNotAvailableStockByArticle(articleId,warehouseId) {
	if (articleId == null) articleId = -1;
	if (warehouseId == null) warehouseId = -1;

	if (articleId > 0 && warehouseId > 0) {
		modalMessage('articles/notAvailableStock/'+articleId+'/'+warehouseId,"Stock Reservado",null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}

function seeArticleLastUnitPrice(articleId) {
	if (articleId == null) articleId = -1;		

	if (articleId > 0 ) {
		modalMessage('articles/detailedUnitPrice/'+articleId+'/0/0',"Detalle de Último Costo",null,null,null,null,'closeModalMessage()',true,null,null,600);		
	}
}