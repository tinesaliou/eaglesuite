// lib/repositories/utilisateur_repository.dart
import '../services/api_service.dart';

class UtilisateurRepository {
  final ApiService api;

  UtilisateurRepository(this.api);

  Future<Map<String, dynamic>> login(String email, String password) async {
    return await api.post('auth', 'login', {'email': email, 'password': password});
  }

  Future<Map<String, dynamic>> getProfile() async {
    return await api.get('auth', 'me');
  }
}
