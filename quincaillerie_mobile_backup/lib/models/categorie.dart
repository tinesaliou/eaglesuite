class Categorie {
  final int id;
  final String nom;
  final String? description;
  final String? createdAt;

  Categorie({
    required this.id,
    required this.nom,
    this.description,
    this.createdAt,
  });

  factory Categorie.fromJson(Map<String, dynamic> json) {
    return Categorie(
      id: int.tryParse(json['id'].toString()) ?? 0,
      nom: json['nom'] ?? '',
      description: json['description'],
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom': nom,
      'description': description,
      'created_at': createdAt,
    };
  }
}
