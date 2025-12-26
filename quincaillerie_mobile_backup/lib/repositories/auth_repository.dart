import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../models/utilisateur.dart';

class AuthRepository {
  final ApiService api;

  AuthRepository(this.api);

  Future<Map<String, dynamic>> login(String email, String password) async {
    final data = await api.login(email, password); // ✅ on récupère déjà un Map

    if (data['success'] == true && data['token'] != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', data['token']);
    }

    return data;
  }

  Future<void> logout() async {
    await api.logout();
  }

  Future<Utilisateur?> me() async {
    // Optionnel : appeler un endpoint user si nécessaire
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    if (token == null) return null;
    return null;
  }
}
