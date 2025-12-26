import '../services/api_service.dart';
import '../models/client.dart';

class ClientRepository {
  final ApiService api;
  ClientRepository(this.api);

  Future<List<Client>> fetchClients() async {
    final res = await api.get('clients', 'list');
    if (res['success'] == true) {
      return (res['data'] as List).map((e) => Client.fromJson(e)).toList();
    }
    return [];
  }
}
