class VenteDetail {
  final int id;
  final int venteId;
  final int produitId;
  final String produitNom;
  final int quantite;
  final double prixUnitaire;
  final int depotId;

  VenteDetail({
    required this.id,
    required this.venteId,
    required this.produitId,
    required this.produitNom,
    required this.quantite,
    required this.prixUnitaire,
    required this.depotId,
  });

  factory VenteDetail.fromJson(Map<String, dynamic> json) {
    return VenteDetail(
      id: int.tryParse((json['id'] ?? 0).toString()) ?? 0,
      venteId: int.tryParse((json['vente_id'] ?? 0).toString()) ?? 0,
      produitId: int.tryParse((json['produit_id'] ?? 0).toString()) ?? 0,
      produitNom: json['produit_nom'] ?? '',
      quantite: int.tryParse((json['quantite'] ?? 0).toString()) ?? 0,
      prixUnitaire: double.tryParse((json['prix_unitaire'] ?? 0).toString()) ?? 0,
      depotId: int.tryParse((json['depot_id'] ?? 0).toString()) ?? 0,
    );
  }
}
