$(document).on("click", ".btnEditProduit", function() {
    $("#editIdProduit").val($(this).data("id"));
    $("#editNomProduit").val($(this).data("nom"));
    $("#editReferenceProduit").val($(this).data("reference"));
    $("#editCategorieProduit").val($(this).data("categorie"));
    $("#editPrixAchatProduit").val($(this).data("prixachat"));
    $("#editPrixVenteProduit").val($(this).data("prixvente"));
    $("#editStockProduit").val($(this).data("stock"));
});
