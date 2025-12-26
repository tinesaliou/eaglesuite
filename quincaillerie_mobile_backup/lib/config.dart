/* // lib/config.dart
class Config {
  // Si tu testes sur l'émulateur Android: utiliser 10.0.2.2 pour localhost
  // Pour appareil réel, utiliser l'IP de ta machine: ex: http://192.168.1.20/quincaillerie/api_rest/index.php
  static const baseUrl = 'http://10.0.2.2/quincaillerie/api_rest/index.php';
  
} */

const String BASE_URL = 'http://localhost/quincaillerie/api_rest/';
const String API_BASE_URL = '${BASE_URL}index.php';
const String UPLOADS_URL = '${BASE_URL}uploads/produits/';
// Config.dart (par exemple)
const String baseUrl = "http://localhost/quincaillerie/public/uploads/produits/";
