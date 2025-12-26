import 'package:flutter/material.dart';
import '../../models/produit.dart';
import '../../services/api_service.dart';

class ProduitDetailScreen extends StatefulWidget {
  final Produit produit;

  const ProduitDetailScreen({super.key, required this.produit});

  @override
  State<ProduitDetailScreen> createState() => _ProduitDetailScreenState();
}

class _ProduitDetailScreenState extends State<ProduitDetailScreen> {
  late TextEditingController _nomCtl;
  late TextEditingController _prixAchatCtl;
  late TextEditingController _prixVenteCtl;
  late TextEditingController _stockCtl;
  late TextEditingController _descriptionCtl;
  bool _isEditing = false;
  bool _isLoading = false;

  final ApiService _api = ApiService();

  @override
  void initState() {
    super.initState();
    _nomCtl = TextEditingController(text: widget.produit.nom);
    _prixAchatCtl = TextEditingController(text: widget.produit.prixAchat.toString());
    _prixVenteCtl = TextEditingController(text: widget.produit.prixVente.toString());
    _stockCtl = TextEditingController(text: widget.produit.stockTotal.toString());
    _descriptionCtl = TextEditingController(text: widget.produit.description ?? '');
  }

  Future<void> _updateProduit() async {
    setState(() => _isLoading = true);

    final res = await _api.post('produits', 'update', {
      'id': widget.produit.id,
      'nom': _nomCtl.text,
      'prix_achat': _prixAchatCtl.text,
      'prix_vente': _prixVenteCtl.text,
      'stock_total': _stockCtl.text,
      'description': _descriptionCtl.text,
    });

    setState(() => _isLoading = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Produit mis à jour avec succès')),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message'] ?? 'Erreur de mise à jour')),
      );
    }
  }

  Future<void> _deleteProduit() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Confirmer la suppression'),
        content: const Text('Voulez-vous vraiment supprimer ce produit ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          ElevatedButton(onPressed: () => Navigator.pop(context, true), child: const Text('Supprimer')),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isLoading = true);
    final res = await _api.post('produits', 'delete', {'id': widget.produit.id});
    setState(() => _isLoading = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Produit supprimé avec succès')),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message'] ?? 'Erreur de suppression')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_isEditing ? 'Modifier le produit' : 'Détails du produit'),
        actions: [
          if (!_isEditing)
            IconButton(
              icon: const Icon(Icons.edit),
              onPressed: () => setState(() => _isEditing = true),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  TextFormField(
                    controller: _nomCtl,
                    enabled: _isEditing,
                    decoration: const InputDecoration(labelText: 'Nom du produit'),
                  ),
                  TextFormField(
                    controller: _prixAchatCtl,
                    enabled: _isEditing,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Prix d\'achat'),
                  ),
                  TextFormField(
                    controller: _prixVenteCtl,
                    enabled: _isEditing,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Prix de vente'),
                  ),
                  TextFormField(
                    controller: _stockCtl,
                    enabled: _isEditing,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Stock total'),
                  ),
                  TextFormField(
                    controller: _descriptionCtl,
                    enabled: _isEditing,
                    decoration: const InputDecoration(labelText: 'Description'),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 20),
                  if (_isEditing)
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      children: [
                        ElevatedButton.icon(
                          onPressed: _updateProduit,
                          icon: const Icon(Icons.save),
                          label: const Text('Enregistrer'),
                        ),
                        OutlinedButton.icon(
                          onPressed: () => setState(() => _isEditing = false),
                          icon: const Icon(Icons.cancel),
                          label: const Text('Annuler'),
                        ),
                      ],
                    ),
                  const SizedBox(height: 20),
                  ElevatedButton.icon(
                    onPressed: _deleteProduit,
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                    icon: const Icon(Icons.delete_forever),
                    label: const Text('Supprimer'),
                  ),
                ],
              ),
            ),
    );
  }
}
