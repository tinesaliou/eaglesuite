// lib/models/dashboard.dart

class DashboardData {
  // KPIs simples
  final double ventesJour;
  final double ventesMois;
  final int produitsTotal;
  final int clientsTotal;

  // Graphique ventes
  final List<String> months;
  final List<double> sales;

  // Top products, stock alerts, cash
  final List<Map<String, dynamic>> topProducts;
  final List<Map<String, dynamic>> stockAlerts;
  final List<Map<String, dynamic>> caisses;
  final List<Map<String, dynamic>> recentOps;

  DashboardData({
    required this.ventesJour,
    required this.ventesMois,
    required this.produitsTotal,
    required this.clientsTotal,
    required this.months,
    required this.sales,
    required this.topProducts,
    required this.stockAlerts,
    required this.caisses,
    required this.recentOps,
  });

  /// Construit à partir des réponses distinctes d'API (kpis, sales_months, ...)
  factory DashboardData.fromApiResponses({
    required Map<String, dynamic> kpisResp,
    required Map<String, dynamic> salesResp,
    required Map<String, dynamic> topResp,
    required Map<String, dynamic> alertResp,
    required Map<String, dynamic> cashResp,
  }) {
    final kpis = kpisResp['data'] ?? {};
    final months = List<String>.from(salesResp['labels'] ?? []);
    final sales = (salesResp['values'] ?? []).map<double>((e) {
      if (e is num) return e.toDouble();
      if (e is String) return double.tryParse(e) ?? 0.0;
      return 0.0;
    }).toList();

    final topProducts = List<Map<String, dynamic>>.from(topResp['data'] ?? []);
    final stockAlerts = List<Map<String, dynamic>>.from(alertResp['data'] ?? []);
    final cashData = cashResp['data'] ?? {};
    final caisses = List<Map<String, dynamic>>.from(cashData['caisses'] ?? []);
    final recentOps = List<Map<String, dynamic>>.from(cashData['recent_ops'] ?? []);

    return DashboardData(
      ventesJour: (kpis['ventes_jour'] is num) ? (kpis['ventes_jour'] as num).toDouble() : double.tryParse(kpis['ventes_jour']?.toString() ?? '0') ?? 0.0,
      ventesMois: (kpis['ventes_mois'] is num) ? (kpis['ventes_mois'] as num).toDouble() : double.tryParse(kpis['ventes_mois']?.toString() ?? '0') ?? 0.0,
      produitsTotal: int.tryParse(kpis['produits_total']?.toString() ?? '') ?? (kpis['produits_total'] is num ? (kpis['produits_total'] as num).toInt() : 0),
      clientsTotal: int.tryParse(kpis['clients_total']?.toString() ?? '') ?? (kpis['clients_total'] is num ? (kpis['clients_total'] as num).toInt() : 0),
      months: months,
      sales: sales,
      topProducts: topProducts,
      stockAlerts: stockAlerts,
      caisses: caisses,
      recentOps: recentOps,
    );
  }
}
