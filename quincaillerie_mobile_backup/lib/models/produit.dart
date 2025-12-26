class Produit {
  final int id;
  final String nom;
  final String? reference;
  final String? description;
  final double prixAchat;
  final double prixVente;
  final int stockTotal;
  final int seuilAlerte;
  final int? categorieId;
  final int? depotId;
  final int? uniteId;
  final String? categorieNom;
  final String? depotNom;
  final String? uniteNom;
  final List<Map<String, dynamic>> stocksParDepot;
  final String? image;
  
  final String? createdAt;

  Produit({
    required this.id,
    required this.nom,
    this.reference,
    this.description,
    required this.prixAchat,
    required this.prixVente,
    required this.stockTotal,
    required this.seuilAlerte,
    this.categorieId,
    this.depotId,
    this.uniteId,
    this.categorieNom,
    this.depotNom,
    this.uniteNom,
    this.image,
    this.stocksParDepot = const [],
    this.createdAt,
  });

  factory Produit.fromJson(Map<String, dynamic> json) {
    return Produit(
      id: int.tryParse(json['id'].toString()) ?? 0,
      nom: json['nom'] ?? '',
      reference: json['reference'],
      description: json['description'],
      prixAchat: double.tryParse(json['prix_achat']?.toString() ?? '0') ?? 0,
      prixVente: double.tryParse(json['prix_vente']?.toString() ?? '0') ?? 0,
      stockTotal: int.tryParse(json['stock_total']?.toString() ?? '0') ?? 0,
      seuilAlerte: int.tryParse(json['seuil_alerte']?.toString() ?? '0') ?? 0,
      categorieId: json['categorie_id'] != null
          ? int.tryParse(json['categorie_id'].toString())
          : null,
      depotId: json['depot_id'] != null
          ? int.tryParse(json['depot_id'].toString())
          : null,
      uniteId: json['unite_id'] != null
          ? int.tryParse(json['unite_id'].toString())
          : null,
      categorieNom: json['categorie_nom'], // 
      depotNom: json['depot_nom'],
      uniteNom: json['unite_nom'],
      image: json['image'],
      stocksParDepot: json['stocks_par_depot'] != null
          ? List<Map<String, dynamic>>.from(json['stocks_par_depot'])
          : [],
      createdAt: json['created_at'],
    );
  }
}
