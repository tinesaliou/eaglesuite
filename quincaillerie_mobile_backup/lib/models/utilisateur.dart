class Utilisateur {
  final String email;
  final String? nom;
  final String? role;

  Utilisateur({
    required this.email,
    this.nom,
    this.role,
  });

  factory Utilisateur.fromJson(Map<String, dynamic> json) {
    return Utilisateur(
      email: json['email'] ?? '',
      nom: json['nom'],
      role: json['role'],
    );
  }

  Map<String, dynamic> toJson() => {
        'email': email,
        'nom': nom,
        'role': role,
      };
}
