class Client {
  final int id;
  final String nom;
  final String? telephone;
  final String? email;
  final String? adresse;
  final bool exonere;
  final String type; // 'Particulier', 'Entreprise' ou 'Passager'
  final DateTime? createdAt;

  Client({
    required this.id,
    required this.nom,
    this.telephone,
    this.email,
    this.adresse,
    this.exonere = false,
    this.type = 'Particulier',
    this.createdAt,
  });

  ///  Création d’un objet Client à partir du JSON renvoyé par l’API
  factory Client.fromJson(Map<String, dynamic> json) {
    return Client(
      id: (json['idClient'] ?? json['id'] ?? 0) as int,
      nom: json['nom'] ?? '',
      telephone: json['telephone'],
      email: json['email'],
      adresse: json['adresse'],
      exonere: (json['exonere']?.toString() ?? '0') == '1',
      type: json['type'] ?? 'Particulier',
      createdAt: json['created_at'] != null && json['created_at'] != ''
          ? DateTime.tryParse(json['created_at'])
          : null,
    );
  }

  /// 🔄 Conversion en JSON pour envoi (create/update)
  Map<String, dynamic> toJson() {
    return {
      'idClient': id,
      'nom': nom,
      'telephone': telephone,
      'email': email,
      'adresse': adresse,
      'exonere': exonere ? 1 : 0,
      'type': type,
    };
  }
}
