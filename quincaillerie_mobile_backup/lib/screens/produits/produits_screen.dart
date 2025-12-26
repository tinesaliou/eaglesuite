import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../models/produit.dart';
import '../../config.dart';
import 'produit_edit_screen.dart';
import 'add_produit_screen.dart';

class ProduitsScreen extends StatefulWidget {
  const ProduitsScreen({super.key});

  @override
  State<ProduitsScreen> createState() => _ProduitsScreenState();
}

class _ProduitsScreenState extends State<ProduitsScreen> {
  final ApiService _api = ApiService();
  late Future<List<Produit>> _futureProduits;

  @override
  void initState() {
    super.initState();
    _futureProduits = _loadProduits();
  }

  Future<List<Produit>> _loadProduits() async {
    final res = await _api.get('produits', 'list');
    if (res['success'] == true && res['data'] is List) {
      return (res['data'] as List)
          .map((e) => Produit.fromJson(e))
          .toList();
    }
    return [];
  }

  Future<void> _refresh() async {
    setState(() => _futureProduits = _loadProduits());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Produits')),
      body: FutureBuilder<List<Produit>>(
        future: _futureProduits,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text('Aucun produit disponible.'));
          }

          final produits = snapshot.data!;
          return RefreshIndicator(
            onRefresh: _refresh,
            child: GridView.builder(
              padding: const EdgeInsets.all(12),
              itemCount: produits.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                childAspectRatio: 0.8,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
              ),
              itemBuilder: (context, index) {
                final p = produits[index];
                return GestureDetector(
                  onTap: () async {
                    final updated = await Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => ProduitEditScreen(produit: p),
                      ),
                    );
                    if (updated == true) _refresh();
                  },
                  child: Card(
                    elevation: 4,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Column(
                      children: [
                        Expanded(
                          child: p.image != null && p.image!.isNotEmpty
                              ? Image.network(
                                  '${baseUrl}${p.image}',
                                  fit: BoxFit.cover,
                                  //width: double.infinity,
                                  errorBuilder: (context, error, stackTrace) {
                                    print('⚠️ Erreur de chargement image: $error');
                                    return Container(
                                      color: Colors.grey[200],
                                      child: const Icon(Icons.broken_image, size: 60),
                                    );
                                  },
                                )
                              : Container(
                                  color: Colors.grey[200],
                                  child: const Icon(Icons.image, size: 60),
                                ),
                        ),
                        Padding(
                          padding: const EdgeInsets.all(8),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                p.nom,
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${p.prixVente} FCFA',
                                style: const TextStyle(color: Colors.green, fontSize: 14),
                              ),
                              Text(
                                'Stock: ${p.stockTotal}',
                                style: const TextStyle(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final added = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const ProduitFormScreen()),
          );
          if (added == true) _refresh();
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}
