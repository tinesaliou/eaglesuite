class Unite {
  final int id;
  final String nom;
  final String? createdAt;

  Unite({
    required this.id,
    required this.nom,
    this.createdAt,
  });

  factory Unite.fromJson(Map<String, dynamic> json) {
    return Unite(
      id: int.tryParse(json['id'].toString()) ?? 0,
      nom: json['nom'] ?? '',
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom': nom,
      'created_at': createdAt,
    };
  }
}
