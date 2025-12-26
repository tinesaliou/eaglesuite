import 'package:flutter/material.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:shimmer/shimmer.dart';
import '../../services/api_service.dart';
import '../../locator.dart';
import '../../models/dashboard.dart';
import '../produits/produits_screen.dart';
import '../clients/clients_screen.dart';
import '../ventes/ventes_screen.dart';
import '../fournisseurs/fournisseurs_screen.dart';
import '../achats/achats_screen.dart';
import '../../widgets/app_drawer.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiService api = sl<ApiService>();
  int _selectedIndex = 0;

  final List<Widget> _pages = [];

  @override
  void initState() {
    super.initState();
    _pages.add(_DashboardContent(api: api));
    _pages.add(const ProduitsScreen());
    _pages.add(ClientsScreen());
    _pages.add(const VentesScreen());
    _pages.add(const FournisseursScreen());
    _pages.add(const AchatsScreen());
  }

  void _onItemTapped(int index) {
    setState(() => _selectedIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("ERP Mobile - Quincaillerie")),
      drawer: const AppDrawer(),
      body: _pages[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: _onItemTapped,
        type: BottomNavigationBarType.fixed,
        selectedItemColor: Colors.blueAccent,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.dashboard), label: 'Accueil'),
          BottomNavigationBarItem(icon: Icon(Icons.shopping_bag), label: 'Produits'),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Clients'),
          BottomNavigationBarItem(icon: Icon(Icons.point_of_sale), label: 'Ventes'),
          BottomNavigationBarItem(icon: Icon(Icons.business), label: 'Fournisseurs'),
          BottomNavigationBarItem(icon: Icon(Icons.shopping_basket), label: 'Achats'),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------
//  DASHBOARD CONTENT
// ---------------------------------------------------------------------
class _DashboardContent extends StatelessWidget {
  final ApiService api;
  const _DashboardContent({required this.api});

  Future<DashboardData?> _loadDashboardData() async {
    return await api.fetchDashboard();
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<DashboardData?>(
      future: _loadDashboardData(),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const _DashboardShimmer();
        } else if (snapshot.hasError) {
          return Center(child: Text("❌ Erreur : ${snapshot.error}"));
        } else if (!snapshot.hasData || snapshot.data == null) {
          return const Center(child: Text("Aucune donnée disponible."));
        }

        final data = snapshot.data!;
        return RefreshIndicator(
          onRefresh: () async => _loadDashboardData(),
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                _buildKpiCards(context, data),
                const SizedBox(height: 20),
                _buildSalesChart(data.months, data.sales),
                const SizedBox(height: 20),
                _buildTopProducts(data.topProducts),
                const SizedBox(height: 20),
                _buildStockAlerts(data.stockAlerts),
                const SizedBox(height: 20),
                _buildCashSummary(data.caisses, data.recentOps),
              ],
            ),
          ),
        );
      },
    );
  }

  // ---------------------------------------------------------------------
  // 🔹 KPI CARDS (cliquables)
  // ---------------------------------------------------------------------
  Widget _buildKpiCards(BuildContext context, DashboardData data) {
    return GridView.count(
      crossAxisCount: 2,
      childAspectRatio: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 10,
      crossAxisSpacing: 10,
      children: [
        _kpiCard(context, "Ventes du jour", "${data.ventesJour.toStringAsFixed(0)} FCFA", Colors.blue, const VentesScreen()),
        _kpiCard(context, "Ventes du mois", "${data.ventesMois.toStringAsFixed(0)} FCFA", Colors.teal, const VentesScreen()),
        _kpiCard(context, "Produits", "${data.produitsTotal}", Colors.orange, const ProduitsScreen()),
        _kpiCard(context, "Clients", "${data.clientsTotal}", Colors.purple, ClientsScreen()),
      ],
    );
  }

  Widget _kpiCard(BuildContext context, String title, String value, Color color, Widget destination) {
    return InkWell(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => destination)),
      child: Card(
        elevation: 3,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        child: Container(
          padding: const EdgeInsets.all(12),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(title, style: const TextStyle(fontSize: 14, color: Colors.grey)),
              const SizedBox(height: 5),
              Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            ],
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------
  //  SALES CHART
  // ---------------------------------------------------------------------
  Widget _buildSalesChart(List<String> months, List<double> sales) {
    if (sales.isEmpty) return const Text("Aucune donnée de vente récente.");
    return Card(
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: [
            const Text("Ventes (12 derniers mois)", style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 10),
            SizedBox(
              height: 200,
              child: LineChart(
                LineChartData(
                  gridData: const FlGridData(show: true),
                  titlesData: FlTitlesData(
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (v, meta) {
                          int i = v.toInt();
                          return i >= 0 && i < months.length
                              ? Text(months[i].substring(5), style: const TextStyle(fontSize: 10))
                              : const SizedBox();
                        },
                      ),
                    ),
                  ),
                  borderData: FlBorderData(show: false),
                  lineBarsData: [
                    LineChartBarData(
                      isCurved: true,
                      color: Colors.blueAccent,
                      barWidth: 3,
                      belowBarData: BarAreaData(show: true, color: Colors.blueAccent.withValues(alpha: 51)),
                      spots: List.generate(sales.length, (i) => FlSpot(i.toDouble(), sales[i])),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------
  // 🏆 TOP PRODUITS
  // ---------------------------------------------------------------------
  Widget _buildTopProducts(List<Map<String, dynamic>> topProducts) {
    return Card(
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("Top produits vendus", style: TextStyle(fontWeight: FontWeight.bold)),
            const Divider(),
            if (topProducts.isEmpty)
              const Text("Aucun produit vendu récemment."),
            ...topProducts.map((p) => ListTile(
                  title: Text(p['nom']),
                  trailing: Text("${p['qte']} u."),
                )),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------
  // ⚠️ STOCK ALERTS
  // ---------------------------------------------------------------------
  Widget _buildStockAlerts(List<Map<String, dynamic>> stockAlerts) {
    return Card(
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("Produits sous le seuil d’alerte", style: TextStyle(fontWeight: FontWeight.bold)),
            const Divider(),
            if (stockAlerts.isEmpty)
              const Text("Aucun produit en alerte de stock."),
            ...stockAlerts.map((s) => ListTile(
                  title: Text(s['nom']),
                  subtitle: Text("Stock: ${s['quantite']} | Seuil: ${s['seuil_alerte']}"),
                  trailing: const Icon(Icons.warning, color: Colors.orange),
                )),
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------
  // 💰 CASH SUMMARY
  // ---------------------------------------------------------------------
  Widget _buildCashSummary(List<Map<String, dynamic>> caisses, List<Map<String, dynamic>> recentOps) {
    return Card(
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("Caisses & dernières opérations", style: TextStyle(fontWeight: FontWeight.bold)),
            const Divider(),
            ...caisses.map((c) => Text("💰 ${c['nom']} — Solde: ${c['solde_actuel']} FCFA")),
            const SizedBox(height: 8),
            const Text("Opérations récentes:", style: TextStyle(fontWeight: FontWeight.w600)),
            ...recentOps.map((o) => ListTile(
                  dense: true,
                  title: Text("${o['type_operation']} - ${o['montant']} FCFA"),
                  subtitle: Text(o['date_operation'] ?? ''),
                )),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------
// ✨ SHIMMER LOADER
// ---------------------------------------------------------------------
class _DashboardShimmer extends StatelessWidget {
  const _DashboardShimmer();

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade300,
      highlightColor: Colors.grey.shade100,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: List.generate(6, (i) => _placeholderCard()).toList(),
        ),
      ),
    );
  }

  Widget _placeholderCard() {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 8),
      height: 100,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }
}
