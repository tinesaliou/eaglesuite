import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../locator.dart';
import '../../models/produit.dart';
import '../../models/client.dart';
import '../../utils/pdf_ticket_generator.dart';

class VenteFormScreen extends StatefulWidget {
  const VenteFormScreen({super.key});

  @override
  State<VenteFormScreen> createState() => _VenteFormScreenState();
}

class _VenteFormScreenState extends State<VenteFormScreen> {
  final ApiService api = sl<ApiService>();
  final _formKey = GlobalKey<FormState>();

  List<Client> clients = [];
  List<Produit> produits = [];
  int? selectedClientId;
  bool clientExonere = false;

  List<_LineItem> items = [];

  double remise = 0;
  double montantVerse = 0;
  String modePaiement = 'Espèces';
  String typeVente = 'Comptant';
  String commentaire = '';

  // Devise
  String selectedDevise = 'FCFA';
  double tauxChange = 1.0;
  int deviseId = 1;

  bool loading = false;

  @override
  void initState() {
    super.initState();
    _loadRefs();
  }

  Future<void> _loadRefs() async {
    final clientsRes = await api.get('clients', 'list');
    if (clientsRes['success'] == true) {
      setState(() {
        clients = (clientsRes['data'] as List)
            .map((e) => Client.fromJson(Map<String, dynamic>.from(e)))
            .toList();
      });
    }

    final prodRes = await api.get('produits', 'list');
    if (prodRes['success'] == true) {
      setState(() {
        produits = (prodRes['data'] as List)
            .map((e) => Produit.fromJson(Map<String, dynamic>.from(e)))
            .toList();
      });
    }
  }

  void _addLine() => setState(() => items.add(_LineItem()));
  void _removeLine(int idx) => setState(() => items.removeAt(idx));

  double get totalHT {
    double s = 0;
    for (var it in items) {
      if (it.produit != null) s += (it.produit!.prixVente) * it.quantite;
    }
    return s;
  }

  double get taxe {
    final t = clientExonere ? 0.0 : 0.18;
    return totalHT * t;
  }

  double get totalTTC => totalHT + taxe - remise;
  double get resteAPayer => (totalTTC - montantVerse).clamp(0, double.infinity);
  double get monnaieRendue =>
      montantVerse > totalTTC ? montantVerse - totalTTC : 0;

  Future<void> _onClientChange(int? id) async {
    setState(() {
      selectedClientId = id;
      clientExonere = false;
    });
    if (id != null) {
      final res = await api.get('clients', 'get', {'id': id});
      if (res['success'] == true && res['data'] != null) {
        final d = res['data'];
        setState(() {
          clientExonere = (d['exonere'] == 1 || d['exonere'] == '1');
        });
      }
    }
  }

  Future<void> _submit() async {
    if (selectedClientId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Veuillez sélectionner un client.')));
      return;
    }

    if (items.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Ajoutez au moins un produit.')));
      return;
    }

    // Vérifie que tous les dépôts sont bien définis
    for (var it in items) {
      if (it.depotId == null || it.depotId == 0) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(
              "Le produit '${it.produit?.nom ?? 'inconnu'}' n’a pas de dépôt associé."),
        ));
        return;
      }
    }

    final produitsPayload = items
        .where((it) => it.produit != null)
        .map((it) => {
              'id': it.produit!.id,
              'quantite': it.quantite,
              'depot_id': it.depotId,
            })
        .toList();

    final payload = {
      'client_id': selectedClientId,
      'produits': produitsPayload,
      'remise': remise,
      'montant_verse': montantVerse,
      'mode_paiement': modePaiement,
      'type_vente': typeVente,
      'commentaire': commentaire,
      'taux_change': tauxChange,
      'devise_id': deviseId,
    };

    setState(() => loading = true);
    final res = await api.createVente(payload);
    setState(() => loading = false);

    // ✅ Impression auto après enregistrement réussi
    if (res['success'] == true) {
      final venteId = res['id'];
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Vente enregistrée avec succès.')));

        try {
          await PdfTicketGenerator.printTicketFromApi(api: api, venteId: venteId);
        } catch (e) {
          ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Erreur impression: $e')));
        }

        Navigator.pop(context, true);
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Erreur lors de la création.')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouvelle vente')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(12),
              child: Form(
                key: _formKey,
                child: Column(
                  children: [
                    DropdownButtonFormField<int>(
                      decoration: const InputDecoration(labelText: 'Client'),
                      items: clients
                          .map((c) =>
                              DropdownMenuItem(value: c.id, child: Text(c.nom)))
                          .toList(),
                      onChanged: _onClientChange,
                      initialValue: selectedClientId,
                    ),
                    const SizedBox(height: 12),

                    if (clientExonere)
                      const Text('Client exonéré de taxe',
                          style: TextStyle(color: Colors.green)),

                    // --- PRODUITS ---
                    Column(
                      children: [
                        ...List.generate(items.length, (i) {
                          final it = items[i];
                          return Card(
                            margin: const EdgeInsets.symmetric(vertical: 6),
                            child: Padding(
                              padding: const EdgeInsets.all(8.0),
                              child: Column(
                                children: [
                                  DropdownButtonFormField<int>(
                                    decoration: const InputDecoration(
                                        labelText: 'Produit'),
                                    items: produits
                                        .map((p) => DropdownMenuItem(
                                            value: p.id, child: Text(p.nom)))
                                        .toList(),
                                    initialValue: it.produit?.id,
                                    onChanged: (val) {
                                      final pr = produits.firstWhere(
                                          (p) => p.id == val,
                                          orElse: () => produits.first);
                                      setState(() {
                                        it.produit = pr;
                                        it.depotId = pr.depotId ?? 1;
                                      });
                                    },
                                  ),
                                  const SizedBox(height: 6),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          'Dépôt : ${it.produit?.depotNom ?? 'Défaut'}',
                                          style: const TextStyle(
                                              fontSize: 13,
                                              color: Colors.grey),
                                        ),
                                      ),
                                      SizedBox(
                                        width: 70,
                                        child: TextFormField(
                                          initialValue:
                                              it.quantite.toString(),
                                          decoration: const InputDecoration(
                                              labelText: 'Qté'),
                                          keyboardType: TextInputType.number,
                                          onChanged: (v) => setState(() =>
                                              it.quantite =
                                                  int.tryParse(v) ?? 1),
                                        ),
                                      ),
                                      IconButton(
                                        icon:
                                            const Icon(Icons.delete_outline),
                                        onPressed: () => _removeLine(i),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        }),
                        const SizedBox(height: 6),
                        ElevatedButton.icon(
                            onPressed: _addLine,
                            icon: const Icon(Icons.add),
                            label: const Text('Ajouter produit')),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // --- INFOS VENTE ---
                    TextFormField(
                      decoration:
                          const InputDecoration(labelText: 'Remise (FCFA)'),
                      keyboardType:
                          const TextInputType.numberWithOptions(decimal: true),
                      onChanged: (v) =>
                          setState(() => remise = double.tryParse(v) ?? 0),
                    ),
                    const SizedBox(height: 8),

                    /* DropdownButtonFormField<String>(
                      decoration:
                          const InputDecoration(labelText: 'Type de vente'),
                      initialValue: typeVente,
                      items: ['Comptant', 'Crédit']
                          .map((t) =>
                              DropdownMenuItem(value: t, child: Text(t)))
                          .toList(),
                      onChanged: (v) => setState(() => typeVente = v ?? typeVente),
                    ),
                    const SizedBox(height: 8), */

                    DropdownButtonFormField<String>(
                      initialValue: modePaiement,
                      items: [
                        'Espèces',
                        'Banque',
                        'Mobile Money',
                        'Chèque',
                        'Virement'
                      ]
                          .map((m) =>
                              DropdownMenuItem(value: m, child: Text(m)))
                          .toList(),
                      onChanged: (v) =>
                          setState(() => modePaiement = v ?? modePaiement),
                      decoration:
                          const InputDecoration(labelText: 'Mode paiement'),
                    ),
                    const SizedBox(height: 8),

                    // --- DEVISE + TAUX ---
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: selectedDevise,
                            items: const [
                              DropdownMenuItem(
                                  value: 'FCFA', child: Text('FCFA')),
                              DropdownMenuItem(
                                  value: 'USD', child: Text('USD')),
                              DropdownMenuItem(
                                  value: 'EUR', child: Text('EUR')),
                            ],
                            onChanged: (v) {
                              setState(() {
                                selectedDevise = v!;
                                deviseId =
                                    v == 'FCFA' ? 1 : v == 'USD' ? 2 : 3;
                              });
                            },
                            decoration:
                                const InputDecoration(labelText: 'Devise'),
                          ),
                        ),
                        const SizedBox(width: 8),
                        SizedBox(
                          width: 100,
                          child: TextFormField(
                            initialValue: tauxChange.toString(),
                            decoration:
                                const InputDecoration(labelText: 'Taux'),
                            keyboardType: TextInputType.number,
                            onChanged: (v) => setState(() =>
                                tauxChange = double.tryParse(v) ?? 1),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 8),
                    TextFormField(
                      decoration:
                          const InputDecoration(labelText: 'Montant versé'),
                      keyboardType:
                          const TextInputType.numberWithOptions(decimal: true),
                      onChanged: (v) =>
                          setState(() => montantVerse = double.tryParse(v) ?? 0),
                    ),
                    const SizedBox(height: 8),

                    TextFormField(
                      decoration:
                          const InputDecoration(labelText: 'Commentaire'),
                      onChanged: (v) => commentaire = v,
                      maxLines: 2,
                    ),

                    const SizedBox(height: 12),
                    Card(
                      child: ListTile(
                        title: const Text('Résumé'),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Total HT : ${totalHT.toStringAsFixed(0)} FCFA'),
                            Text('Taxe : ${taxe.toStringAsFixed(0)} FCFA'),
                            Text('Remise : ${remise.toStringAsFixed(0)} FCFA'),
                            Text(
                                'Total TTC : ${totalTTC.toStringAsFixed(0)} FCFA',
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold)),
                            Text(
                                'Montant versé : ${montantVerse.toStringAsFixed(0)} FCFA'),
                            Text(
                                'Reste à payer : ${resteAPayer.toStringAsFixed(0)} FCFA'),
                            Text(
                                'Monnaie rendue : ${monnaieRendue.toStringAsFixed(0)} FCFA'),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 12),

                    ElevatedButton.icon(
                      onPressed: _submit,
                      icon: const Icon(Icons.save),
                      label: const Text('Valider la vente'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}

class _LineItem {
  Produit? produit;
  int quantite = 1;
  int? depotId;
}
