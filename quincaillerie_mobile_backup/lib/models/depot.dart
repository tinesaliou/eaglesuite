class Depot {
  final int id;
  final String nom;
  final String? description;
  final String? createdAt;

  Depot({
    required this.id,
    required this.nom,
    this.description,
    this.createdAt,
  });

  factory Depot.fromJson(Map<String, dynamic> json) {
    return Depot(
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
