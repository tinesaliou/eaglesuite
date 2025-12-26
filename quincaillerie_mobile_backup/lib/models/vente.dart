// lib/models/vente.dart
class Vente {
  final int id;
  final String numero;
  final int clientId;
  final String clientNom;
  final DateTime dateVente;
  final double totalHT;
  final double taxe;
  final double remise;
  final double totalTTC;
  final double montantVerse;
  final double resteAPayer;
  final String statut;
  final String modePaiement;
  final bool annule;

  Vente({
    required this.id,
    required this.numero,
    required this.clientId,
    required this.clientNom,
    required this.dateVente,
    required this.totalHT,
    required this.taxe,
    required this.remise,
    required this.totalTTC,
    required this.montantVerse,
    required this.resteAPayer,
    required this.statut,
    required this.modePaiement,
    required this.annule,
  });

  factory Vente.fromJson(Map<String, dynamic> json) {
    return Vente(
      id: int.tryParse((json['id'] ?? json['id'].toString())?.toString() ?? '0') ?? 0,
      numero: json['numero'] ?? '',
      clientId: int.tryParse((json['client_id'] ?? '0').toString()) ?? 0,
      clientNom: json['client_nom'] ?? (json['clientName'] ?? ''),
      dateVente: DateTime.tryParse((json['date_vente'] ?? json['created_at'] ?? DateTime.now().toIso8601String()).toString()) ?? DateTime.now(),
      totalHT: double.tryParse((json['totalHT'] ?? '0').toString()) ?? 0,
      taxe: double.tryParse((json['taxe'] ?? '0').toString()) ?? 0,
      remise: double.tryParse((json['remise'] ?? '0').toString()) ?? 0,
      totalTTC: double.tryParse((json['totalTTC'] ?? '0').toString()) ?? 0,
      montantVerse: double.tryParse((json['montant_verse'] ?? '0').toString()) ?? 0,
      resteAPayer: double.tryParse((json['reste_a_payer'] ?? '0').toString()) ?? 0,
      statut: json['statut'] ?? '',
      modePaiement: json['mode_paiement'] ?? '',
      annule: (json['annule'] == 1 || json['annule'] == '1' || json['annule'] == true),
    );
  }
}
