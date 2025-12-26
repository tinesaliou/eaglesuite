import '../services/api_service.dart';
import '../models/vente.dart';

class VenteRepository {
  final ApiService api;
  VenteRepository(this.api);

  Future<List<Vente>> fetchVentes() async {
    final res = await api.get('ventes', 'list');
    if (res['success'] == true) {
      return (res['data'] as List).map((e) => Vente.fromJson(e)).toList();
    }
    return [];
  }
}
