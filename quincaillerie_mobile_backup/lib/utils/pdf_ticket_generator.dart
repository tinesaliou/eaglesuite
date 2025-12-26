// lib/utils/pdf_ticket_generator.dart
//import 'dart:typed_data';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:http/http.dart' as http;
import '../services/api_service.dart';
import 'package:flutter/foundation.dart'; // pour debugPrint


class PdfTicketGenerator {
  /// Génère le PDF du ticket thermique 80mm
  static Future<Uint8List> generateTicketPdf({
    required ApiService api,
    required Map<String, dynamic> vente,
    required List<dynamic> details,
  }) async {
    final pdf = pw.Document();

    // Format thermique : largeur 80mm (≈226.8 points)
    const double pageWidth = 226.8;
    const double padding = 8.0;

    // 🔹 Charger le logo si disponible
    Uint8List? logoBytes;
    try {
      final logoPath = vente['entreprise_logo'] ??
          vente['logo'] ??
          (vente['entreprise']?['logo']);
      if (logoPath != null && logoPath.toString().isNotEmpty) {
        final baseUrl = api.baseUrl.replaceAll('index.php', '');
        final logoUrl = logoPath.toString().startsWith('http')
            ? logoPath.toString()
            : '$baseUrl$logoPath';
        final response = await http.get(Uri.parse(logoUrl));
        if (response.statusCode == 200) logoBytes = response.bodyBytes;
      }
    } catch (_) {
      logoBytes = null;
    }

    // 🔹 Infos entreprise
    final entreprise = vente['entreprise'] ?? {
      'nom': vente['entreprise_nom'] ?? 'Mon Entreprise',
      'adresse': vente['entreprise_adresse'] ?? '',
      'telephone': vente['entreprise_telephone'] ?? '',
      'email': vente['entreprise_email'] ?? '',
    };

    // 🔹 Helper format
    String fmt(dynamic v) {
      final d = double.tryParse(v.toString()) ?? 0.0;
      return d.toStringAsFixed(0);
    }

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat(pageWidth, double.infinity,
            marginAll: padding),
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.center,
            children: [
              if (logoBytes != null)
                pw.Container(
                  height: 50,
                  child: pw.Image(pw.MemoryImage(logoBytes),
                      fit: pw.BoxFit.contain),
                ),
              pw.SizedBox(height: 4),
              pw.Text(entreprise['nom'] ?? '',
                  style: pw.TextStyle(
                      fontWeight: pw.FontWeight.bold, fontSize: 12)),
              if ((entreprise['adresse'] ?? '').toString().isNotEmpty)
                pw.Text(entreprise['adresse'], style: const pw.TextStyle(fontSize: 8)),
              if ((entreprise['telephone'] ?? '').toString().isNotEmpty)
                pw.Text('Tél : ${entreprise['telephone']}',
                    style: const pw.TextStyle(fontSize: 8)),
              if ((entreprise['email'] ?? '').toString().isNotEmpty)
                pw.Text(entreprise['email'],
                    style: const pw.TextStyle(fontSize: 8)),
              pw.Divider(),

              // 🔹 En-tête facture
              pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                children: [
                  pw.Column(
                      crossAxisAlignment: pw.CrossAxisAlignment.start,
                      children: [
                        pw.Text('Facture : ${vente['numero'] ?? ''}',
                            style: pw.TextStyle(
                                fontSize: 9, fontWeight: pw.FontWeight.bold)),
                        pw.Text('Date : ${vente['date_vente'] ?? ''}',
                            style: const pw.TextStyle(fontSize: 8)),
                      ]),
                  pw.Column(
                      crossAxisAlignment: pw.CrossAxisAlignment.end,
                      children: [
                        pw.Text('Client : ${vente['client_nom'] ?? ''}',
                            style: const pw.TextStyle(fontSize: 9)),
                      ]),
                ],
              ),
              pw.Divider(),

              // 🔹 Détails produits
              pw.Column(
                children: details.map((d) {
                  final nom = d['produit_nom'] ?? d['nom'] ?? '';
                  final qte = d['quantite'] ?? 1;
                  final prix = double.tryParse(
                          (d['prix_unitaire'] ?? d['prix'] ?? '0').toString()) ??
                      0;
                  final total = prix * (qte is int ? qte : int.tryParse(qte.toString()) ?? 1);
                  return pw.Padding(
                    padding: const pw.EdgeInsets.symmetric(vertical: 2),
                    child: pw.Row(
                        mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                        children: [
                          pw.Expanded(
                              child: pw.Text(nom,
                                  style: const pw.TextStyle(fontSize: 9))),
                          pw.Text('$qte x ${fmt(prix)}', style: const pw.TextStyle(fontSize: 8)),
                          pw.Text(fmt(total),
                              style: pw.TextStyle(
                                  fontWeight: pw.FontWeight.bold,
                                  fontSize: 9)),
                        ]),
                  );
                }).toList(),
              ),
              pw.Divider(),

              // 🔹 Totaux
              _row('Total HT', fmt(vente['totalHT']), bold: false),
              _row('Taxe', fmt(vente['taxe']), bold: false),
              _row('Remise', fmt(vente['remise']), bold: false),
              pw.Divider(),
              _row('Total TTC', fmt(vente['totalTTC']), bold: true),
              pw.SizedBox(height: 6),
              _row('Montant versé', fmt(vente['montant_verse'])),
              _row('Monnaie rendue',
                  fmt(((double.tryParse(vente['montant_verse'].toString()) ?? 0) -
                          (double.tryParse(vente['totalTTC'].toString()) ?? 0))
                      .clamp(0, double.infinity))),

              pw.SizedBox(height: 8),
              pw.Text(
                  'Mode : ${vente['mode_paiement'] ?? ''} ',
                  style: const pw.TextStyle(fontSize: 9)),
              pw.SizedBox(height: 8),
              pw.Text('Merci pour votre visite!',
                  style: pw.TextStyle(
                      fontWeight: pw.FontWeight.bold, fontSize: 10)),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  /// Génère une ligne résumé alignée à droite
  static pw.Widget _row(String label, String value, {bool bold = false}) {
    return pw.Row(
        mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
        children: [
          pw.Text(label, style: pw.TextStyle(fontSize: 9)),
          pw.Text(value,
              style: pw.TextStyle(
                  fontSize: 9,
                  fontWeight: bold ? pw.FontWeight.bold : pw.FontWeight.normal)),
        ]);
  }

  /// 🔹 Télécharge les données du ticket et ouvre la prévisualisation PDF
  static Future<void> printTicketFromApi({
    required ApiService api,
    required int venteId,
  }) async {
    try {
      final resp = await api.fetchTicket(venteId);
      if (resp['success'] != true) {
        throw Exception(resp['message'] ?? 'Erreur lecture ticket');
      }

      final data = resp['data'] as Map<String, dynamic>;
      final vente = data['vente'] as Map<String, dynamic>;
      final details = data['details'] as List<dynamic>;

      final pdfBytes =
          await generateTicketPdf(api: api, vente: vente, details: details);

      // 🔹 Ouvre automatiquement la prévisualisation / impression
      await Printing.layoutPdf(
        onLayout: (format) async => pdfBytes,
        name: 'Ticket_${vente['numero'] ?? venteId}',
      );
    } catch (e) {
      debugPrint('Erreur impression ticket: $e');
      rethrow;
    }
  }
}
