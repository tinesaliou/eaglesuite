import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppDrawer extends StatefulWidget {
  const AppDrawer({super.key});

  @override
  State<AppDrawer> createState() => _AppDrawerState();
}

class _AppDrawerState extends State<AppDrawer> {
  String userEmail = '';
  String userName = '';

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      userEmail = prefs.getString('user_email') ?? 'Utilisateur';
      userName = prefs.getString('user_name') ?? 'Bienvenue 👋';
    });
  }

  void _navigate(BuildContext context, String route) {
    Navigator.pop(context); // ferme le drawer
    if (ModalRoute.of(context)?.settings.name != route) {
      Navigator.pushNamed(context, route);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: Colors.grey[100],
      child: Column(
        children: [
          // 🔹 En-tête du Drawer (avatar + infos utilisateur)
          UserAccountsDrawerHeader(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Colors.blueAccent, Colors.lightBlue],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            currentAccountPicture: CircleAvatar(
              backgroundColor: Colors.white,
              child: Text(
                userName.isNotEmpty ? userName[0].toUpperCase() : '?',
                style: const TextStyle(
                  fontSize: 32,
                  color: Colors.blueAccent,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            accountName: Text(
              userName,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            accountEmail: Text(
              userEmail,
              style: const TextStyle(fontSize: 14, color: Colors.white70),
            ),
          ),

          // 🔹 Liste des options
          Expanded(
            child: ListView(
              children: [
                _menuItem(
                  icon: Icons.dashboard,
                  text: 'Tableau de bord',
                  color: Colors.blueAccent,
                  onTap: () => _navigate(context, '/dashboard'),
                ),
                _menuItem(
                  icon: Icons.inventory_2,
                  text: 'Produits',
                  color: Colors.orangeAccent,
                  onTap: () => _navigate(context, '/produits'),
                ),
                _menuItem(
                  icon: Icons.business,
                  text: 'Fournisseurs',
                  color: Colors.indigo,
                  onTap: () => _navigate(context, '/fournisseurs'),
                ),
                _menuItem(
                  icon: Icons.shopping_basket,
                  text: 'Achats',
                  color: Colors.green,
                  onTap: () => _navigate(context, '/achats'),
                ),
                _menuItem(
                  icon: Icons.people,
                  text: 'Clients',
                  color: Colors.purple,
                  onTap: () => _navigate(context, '/clients'),
                ),
                _menuItem(
                  icon: Icons.point_of_sale,
                  text: 'Ventes',
                  color: Colors.redAccent,
                  onTap: () => _navigate(context, '/ventes'),
                ),
                _menuItem(
                  icon: Icons.account_balance_wallet,
                  text: 'Trésorerie',
                  color: Colors.teal,
                  onTap: () => _navigate(context, '/tresorerie'),
                ),

                const Divider(),

                _menuItem(
                  icon: Icons.settings,
                  text: 'Paramètres',
                  color: Colors.grey,
                  onTap: () => _navigate(context, '/parametres'),
                ),

                _menuItem(
                  icon: Icons.logout,
                  text: 'Déconnexion',
                  color: Colors.red,
                  onTap: () async {
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.remove('token');
                    await prefs.remove('user_email');
                    await prefs.remove('user_name');
                    if (context.mounted) {
                      Navigator.pop(context);
                      Navigator.pushReplacementNamed(context, '/login');
                    }
                  },
                ),
              ],
            ),
          ),

          // 🔹 Pied de menu
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 10),
            child: Text(
              "ERP Quincaillerie v1.0",
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// 🔸 Widget personnalisé pour chaque item du menu
  Widget _menuItem({
    required IconData icon,
    required String text,
    required Color color,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(
        text,
        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
      ),
      onTap: onTap,
      horizontalTitleGap: 8,
      dense: true,
    );
  }
}
