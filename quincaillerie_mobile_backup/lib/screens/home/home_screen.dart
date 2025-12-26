import 'package:flutter/material.dart';
import '../dashboard/dashboard_screen.dart';
import '../produits/produits_screen.dart';
import '../clients/clients_screen.dart';
import '../ventes/ventes_screen.dart';
import '../fournisseurs/fournisseurs_screen.dart';
import '../achats/achats_screen.dart';
import '../../widgets/app_drawer.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  // Pages affichées par onglet (ordre = items bottom nav)
  final List<Widget> _pages = [
  const DashboardScreen(),
  const ProduitsScreen(),
  ClientsScreen(),       
  const VentesScreen(),
  const FournisseursScreen(),
  const AchatsScreen(),
];

  void _onItemTapped(int index) {
    setState(() => _selectedIndex = index);
  }

  // labels dynamiques pour AppBar
  String _titleForIndex(int i) {
    switch (i) {
      case 0:
        return 'Tableau de bord';
      case 1:
        return 'Produits';
      case 2:
        return 'Clients';
      case 3:
        return 'Ventes';
      case 4:
        return 'Fournisseurs';
      case 5:
        return 'Achats';
      default:
        return 'Quincaillerie';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_titleForIndex(_selectedIndex)),
      ),
      drawer: const AppDrawer(),
      body: _pages[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        selectedItemColor: Colors.blue,
        unselectedItemColor: Colors.grey,
        type: BottomNavigationBarType.fixed,
        onTap: _onItemTapped,
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
