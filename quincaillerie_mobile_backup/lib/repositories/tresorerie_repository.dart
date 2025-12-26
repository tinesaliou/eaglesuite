import '../services/api_service.dart';
import '../models/caisse.dart';

class TresorerieRepository {
  final ApiService api;
  TresorerieRepository(this.api);

  Future<List<Caisse>> fetchCaisses() async {
    final res = await api.get('caisses', 'list');
    if (res['success'] == true) {
      return (res['data'] as List).map((e) => Caisse.fromJson(e)).toList();
    }
    return [];
  }
}
