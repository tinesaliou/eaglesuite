// lib/screens/ventes/vente_detail_screen.dart
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../locator.dart';
import '../../utils/pdf_ticket_generator.dart'; // <-- Import du générateur PDF

class VenteDetailScreen extends StatefulWidget {
  final int venteId;
  const VenteDetailScreen({super.key, required this.venteId});

  @override
  State<VenteDetailScreen> createState() => _VenteDetailScreenState();
}

class _VenteDetailScreenState extends State<VenteDetailScreen> {
  final ApiService api = sl<ApiService>();
  bool loading = true;
  Map<String, dynamic>? vente;
  List<dynamic> details = [];

  @override
  void initState() {
    super.initState();
    _loadVente();
  }

  Future<void> _loadVente() async {
    setState(() => loading = true);
    final res = await api.fetchVente(widget.venteId);
    if (res['success'] == true && res['data'] != null) {
      setState(() {
        vente = res['data'];
        details = res['data']['details'] ?? [];
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message'] ?? 'Erreur de chargement')),
      );
    }
    setState(() => loading = false);
  }

  Future<void> _cancelVente() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Annuler la vente'),
        content: const Text('Voulez-vous vraiment annuler cette vente ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Non'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Oui'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final res = await api.cancelVente(widget.venteId);
    if (res['success'] == true) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Vente annulée')));
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message'] ?? 'Erreur d’annulation')),
      );
    }
  }

  Future<void> _reprintTicket() async {
    try {
      await PdfTicketGenerator.printTicketFromApi(
        api: api,
        venteId: widget.venteId,
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur impression : $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final fmt = NumberFormat("#,##0", "fr_FR");
    return Scaffold(
      appBar: AppBar(
        title: const Text('Détails vente'),
        actions: [
          IconButton(
            icon: const Icon(Icons.print),
            tooltip: 'Réimprimer ticket',
            onPressed: _reprintTicket,
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadVente,
          ),
        ],
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : vente == null
              ? const Center(child: Text('Aucune donnée'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Card(
                        elevation: 2,
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'N° ${vente!['numero']}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 18,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text('Client : ${vente!['client_nom']}'),
                              Text('Date : ${vente!['date_vente']}'),
                              Text('Mode paiement : ${vente!['mode_paiement']}'),
                              Text(
                                'Statut : ${vente!['statut']}',
                                style: TextStyle(
                                  color: vente!['statut'] == 'Payé'
                                      ? Colors.green
                                      : Colors.orange,
                                ),
                              ),
                              const Divider(),
                              Text(
                                'Total TTC : ${fmt.format(double.tryParse(vente!['totalTTC'].toString()) ?? 0)} FCFA',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text('Produits',
                          style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      ...details.map(
                        (d) => Card(
                          child: ListTile(
                            title: Text(d['produit_nom'] ?? ''),
                            subtitle: Text(
                              'Qté : ${d['quantite']} • Prix : ${fmt.format(double.tryParse(d['prix_unitaire'].toString()) ?? 0)} FCFA',
                            ),
                            trailing: Text(
                              '${fmt.format((double.tryParse(d['prix_unitaire'].toString()) ?? 0) * (int.tryParse(d['quantite'].toString()) ?? 0))} FCFA',
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      if ((vente!['annule'] == 0 || vente!['annule'] == false))
                        Center(
                          child: ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.redAccent,
                            ),
                            onPressed: _cancelVente,
                            icon: const Icon(Icons.cancel),
                            label: const Text('Annuler la vente'),
                          ),
                        )
                      else
                        const Center(
                          child: Text(
                            'Cette vente est annulée',
                            style: TextStyle(
                              color: Colors.redAccent,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}
