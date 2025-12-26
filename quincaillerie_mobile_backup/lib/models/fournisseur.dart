class Fournisseur {
  final int id;
  final String nom;
  final String? telephone;
  final String? email;
  final String? adresse;
  final bool exonere;
  final DateTime? createdAt;

  Fournisseur({
    required this.id,
    required this.nom,
    this.telephone,
    this.email,
    this.adresse,
    this.exonere = false,
    this.createdAt,
  });

  ///  Création d’un objet fournisseur à partir du JSON renvoyé par l’API
  factory Fournisseur.fromJson(Map<String, dynamic> json) {
    return Fournisseur(
      id: (json['id'] ?? json['id'] ?? 0) as int,
      nom: json['nom'] ?? '',
      telephone: json['telephone'],
      email: json['email'],
      adresse: json['adresse'],
      exonere: (json['exonere']?.toString() ?? '0') == '1',
      createdAt: json['created_at'] != null && json['created_at'] != ''
          ? DateTime.tryParse(json['created_at'])
          : null,
    );
  }

  ///  Conversion en JSON pour envoi (create/update)
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom': nom,
      'telephone': telephone,
      'email': email,
      'adresse': adresse,
      'exonere': exonere ? 1 : 0,
    };
  }
}
