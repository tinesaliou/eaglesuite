import 'dart:convert';
import 'package:dio/dio.dart';
import 'dart:io';
import '../config.dart';
import '../models/dashboard.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:image_picker/image_picker.dart';

class ApiService {
  final Dio _dio = Dio();
  late final String baseUrl;

  ApiService() {
    if (kIsWeb) {
      baseUrl = API_BASE_URL;
    } else {
      baseUrl = 'http://10.0.2.2/quincaillerie/api_rest/index.php';
    }
  }

  ///  Méthode POST générique
  Future<Map<String, dynamic>> post(
    String module,
    String action,
    Map<String, dynamic> data, {
    bool withAuth = true,
  }) async {
    String? token;
    if (withAuth) token = await getToken();

    try {
      final response = await _dio.post(
        '$baseUrl?module=$module&action=$action',
        data: data,
        options: Options(headers: {
          'Content-Type': 'application/json',
          if (withAuth && token != null) 'Authorization': 'Bearer $token',
        }),
      );

      dynamic body = response.data;
      if (body is String) {
        try {
          body = json.decode(body);
        } catch (e) {
          return {'success': false, 'message': 'Erreur JSON: $e'};
        }
      }

      if (body is Map<String, dynamic>) return body;
      return {'success': false, 'message': 'Réponse invalide du serveur'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur: $e'};
    }
  }

  ///  Méthode GET générique
  Future<Map<String, dynamic>> get(String module, String action,
      [Map<String, dynamic>? params]) async {
    final token = await getToken();

    try {
      final response = await _dio.get(
        '$baseUrl?module=$module&action=$action',
        queryParameters: params,
        options: Options(headers: {
          if (token != null) 'Authorization': 'Bearer $token',
        }),
      );

      dynamic body = response.data;
      if (body is String) body = json.decode(body);

      return body is Map<String, dynamic>
          ? body
          : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur: $e'};
    }
  }

  ///  Récupération du token local
  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  ///  Authentification utilisateur
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final data = await post(
        'auth',
        'login',
        {'email': email, 'password': password},
        withAuth: false,
      );

      if (data['success'] == true && data['token'] != null) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', data['token']);
        await prefs.setString('user_email', email);
      }

      return data;
    } catch (e) {
      return {'success': false, 'message': 'Erreur: $e'};
    }
  }

  ///  Upload image produit
  Future<Map<String, dynamic>> uploadImage(XFile file) async {
    try {
      final formData = FormData.fromMap({
        'file': await MultipartFile.fromFile(file.path, filename: file.name),
      });

      final response = await _dio.post(
        '${API_BASE_URL.replaceAll("index.php", "")}modules/produits_upload.php',
        data: formData,
        options: Options(headers: {'Content-Type': 'multipart/form-data'}),
      );

      dynamic body = response.data;
      if (body is String) body = json.decode(body);

      return body is Map<String, dynamic>
          ? body
          : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': 'Erreur upload: $e'};
    }
  }

  Future<Map<String, dynamic>> uploadImageProduit(
      int produitId, File imageFile) async {
    final formData = FormData.fromMap({
      'id': produitId,
      'file': await MultipartFile.fromFile(imageFile.path),
    });

    final response = await _dio.post(
      '$baseUrl?module=produits&action=upload_image',
      data: formData,
      options: Options(headers: {'Content-Type': 'multipart/form-data'}),
    );

    return response.data is Map<String, dynamic>
        ? response.data
        : {'success': false, 'message': 'Réponse invalide'};
  }

  /// 👥 Liste clients
  Future<Map<String, dynamic>> getClients() async {
    return await get('clients', 'list');
  }

  /// 👥 Liste fournisseurs
  Future<Map<String, dynamic>> getFournisseurs() async {
    return await get('fournisseurs', 'list');
  }

  ///  Déconnexion
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user_email');
  }

  ///  Vérifie si utilisateur connecté
  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    return token != null && token.isNotEmpty;
  }

  ///  Chargement complet du tableau de bord
  Future<DashboardData> fetchDashboard() async {
    final baseNoIndex = baseUrl.replaceAll('index.php', '');
    final token = await getToken();

    try {
      final options = Options(headers: {
        if (token != null) 'Authorization': 'Bearer $token',
      });

      final futures = await Future.wait([
        _dio.get('$baseNoIndex/modules/dashboard.php',
            queryParameters: {'action': 'kpis'}, options: options),
        _dio.get('$baseNoIndex/modules/dashboard.php',
            queryParameters: {'action': 'sales_months'}, options: options),
        _dio.get('$baseNoIndex/modules/dashboard.php',
            queryParameters: {'action': 'top_products'}, options: options),
        _dio.get('$baseNoIndex/modules/dashboard.php',
            queryParameters: {'action': 'stock_alerts'}, options: options),
        _dio.get('$baseNoIndex/modules/dashboard.php',
            queryParameters: {'action': 'cash_summary'}, options: options),
      ]);

      // Decode les données
      dynamic decode(dynamic data) {
        if (data is String) return json.decode(data);
        return data;
      }

      return DashboardData.fromApiResponses(
        kpisResp: decode(futures[0].data),
        salesResp: decode(futures[1].data),
        topResp: decode(futures[2].data),
        alertResp: decode(futures[3].data),
        cashResp: decode(futures[4].data),
      );
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        //  Token expiré ou non valide
        await logout();
        throw Exception("Session expirée. Veuillez vous reconnecter.");
      }
      throw Exception(
          "Impossible de charger le dashboard: ${e.message ?? e.toString()}");
    } catch (e) {
      throw Exception("Erreur inattendue: $e");
    }
  }

    /// --- VENTES API ---

  /// Fetch ventes (liste)
  Future<Map<String, dynamic>> fetchVentes({String? q, int page = 1, int perPage = 50}) async {
    // Utilise endpoint module=ventes action=list (ici baseUrl sans index.php pour modules)
    final baseNoIndex = baseUrl.replaceAll('index.php', '');
    try {
      final resp = await _dio.get('$baseNoIndex/modules/ventes.php', queryParameters: {
        'action': 'list',
        if (q != null && q.isNotEmpty) 'q': q,
        'page': page,
        'perPage': perPage,
      }, options: Options(headers: {
        if ((await getToken()) != null) 'Authorization': 'Bearer ${await getToken()}',
      }));
      dynamic body = resp.data;
      if (body is String) body = json.decode(body);
      return body is Map<String, dynamic> ? body : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Fetch detail vente
  Future<Map<String, dynamic>> fetchVente(int id) async {
    final baseNoIndex = baseUrl.replaceAll('index.php', '');
    try {
      final resp = await _dio.get('$baseNoIndex/modules/ventes.php', queryParameters: {'action': 'get', 'id': id}, options: Options(headers: {
        if ((await getToken()) != null) 'Authorization': 'Bearer ${await getToken()}',
      }));
      dynamic body = resp.data;
      if (body is String) body = json.decode(body);
      return body is Map<String, dynamic> ? body : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Create vente: data must contain client_id, produits array etc.
  Future<Map<String, dynamic>> createVente(Map<String, dynamic> data) async {
    final baseNoIndex = baseUrl.replaceAll('index.php', '');
    try {
      final resp = await _dio.post('$baseNoIndex/modules/ventes.php?action=create', data: data, options: Options(headers: {
        'Content-Type': 'application/json',
        if ((await getToken()) != null) 'Authorization': 'Bearer ${await getToken()}',
      }));
      dynamic body = resp.data;
      if (body is String) body = json.decode(body);
      return body is Map<String, dynamic> ? body : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Cancel vente
  Future<Map<String, dynamic>> cancelVente(int id) async {
    final baseNoIndex = baseUrl.replaceAll('index.php', '');
    try {
      final resp = await _dio.post('$baseNoIndex/modules/ventes.php?action=cancel', data: {'id': id}, options: Options(headers: {
        'Content-Type': 'application/json',
        if ((await getToken()) != null) 'Authorization': 'Bearer ${await getToken()}',
      }));
      dynamic body = resp.data;
      if (body is String) body = json.decode(body);
      return body is Map<String, dynamic> ? body : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// Ticket
  Future<Map<String, dynamic>> fetchTicket(int id) async {
    final baseNoIndex = baseUrl.replaceAll('index.php', '');
    try {
      final resp = await _dio.get('$baseNoIndex/modules/ventes.php', queryParameters: {'action': 'ticket', 'id': id}, options: Options(headers: {
        if ((await getToken()) != null) 'Authorization': 'Bearer ${await getToken()}',
      }));
      dynamic body = resp.data;
      if (body is String) body = json.decode(body);
      return body is Map<String, dynamic> ? body : {'success': false, 'message': 'Réponse invalide'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }


}
