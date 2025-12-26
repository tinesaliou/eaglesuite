import '../services/api_service.dart';
import '../models/utilisateur.dart';

class UtilisateurRepository {
  final ApiService api;

  UtilisateurRepository(this.api);

  /// 🔐 Connexion utilisateur
  Future<Map<String, dynamic>> login(String email, String password) async {
    final data = await api.login(email, password);

    // Si succès, on peut retourner un utilisateur simplifié
    if (data['success'] == true && data['token'] != null) {
      return {
        'success': true,
        'token': data['token'],
        'utilisateur': Utilisateur(email: email),
      };
    }

    return {
      'success': false,
      'message': data['message'] ?? 'Échec de la connexion',
    };
  }

  /// 🚪 Déconnexion
  Future<void> logout() async {
    await api.logout();
  }

  /// 👥 Récupérer la liste des utilisateurs
  Future<List<Utilisateur>> fetchUtilisateurs() async {
    final res = await api.get('utilisateurs', 'list');
    if (res['success'] == true && res['data'] is List) {
      return (res['data'] as List)
          .map((e) => Utilisateur.fromJson(e))
          .toList();
    }
    return [];
  }
}
