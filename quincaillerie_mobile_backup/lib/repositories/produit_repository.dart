import '../services/api_service.dart';
import '../models/produit.dart';

class ProduitRepository {
  final ApiService api;
  ProduitRepository(this.api);

  Future<List<Produit>> fetchProduits() async {
    final res = await api.get('produits', 'list');
    if (res['success'] == true && res['data'] is List) {
      return (res['data'] as List)
          .map((e) => Produit.fromJson(e))
          .toList();
    }
    return [];
  }

  Future<Map<String, dynamic>> addProduit(Map<String, dynamic> produitData) async {
    return await api.post('produits', 'create', produitData);
  }
}
