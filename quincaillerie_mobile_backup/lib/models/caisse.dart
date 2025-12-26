class Caisse {
  final int id;
  final String type;
  final double soldeActuel;

  Caisse({
    required this.id,
    required this.type,
    required this.soldeActuel,
  });

  factory Caisse.fromJson(Map<String, dynamic> json) => Caisse(
        id: int.parse(json['id'].toString()),
        type: json['type'] ?? '',
        soldeActuel: double.tryParse(json['solde_actuel'].toString()) ?? 0,
      );
}
