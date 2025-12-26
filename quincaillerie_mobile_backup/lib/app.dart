import 'package:flutter/material.dart';
import 'screens/splash/splash_screen.dart';
import 'screens/login/login_screen.dart';
import 'screens/dashboard/dashboard_screen.dart';
//import 'screens/home/home_screen.dart';
import 'screens/ventes/ventes_screen.dart';
import 'screens/achats/achats_screen.dart';
import 'screens/fournisseurs/fournisseurs_screen.dart';
import 'screens/parametres/parametres_screen.dart';
import 'screens/produits/produits_screen.dart';
import 'screens/clients/clients_screen.dart';
import 'screens/tresorerie/tresorerie_screen.dart';

class QuincaillerieApp extends StatelessWidget {
  final String initialRoute;

  const QuincaillerieApp({super.key, required this.initialRoute});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Quincaillerie ERP Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(primarySwatch: Colors.blue),
      initialRoute: '/splash', // 👈 démarre ici
      routes: {
      '/splash': (_) => const SplashScreen(),
      '/login': (_) =>  LoginScreen(),
      '/dashboard': (_) => const DashboardScreen(),
      //'/home': (_) => const HomeScreen(),            // si tu utilises HomeScreen
      '/produits': (_) => const ProduitsScreen(),
      '/clients': (_) => ClientsScreen(),
      '/ventes': (_) => const VentesScreen(),
      '/fournisseurs': (_) => const FournisseursScreen(),
      '/achats': (_) => const AchatsScreen(),
      '/tresorerie': (_) => const TresorerieScreen(),
      '/parametres': (_) => const ParametresScreen(),
},
    );
  }
}
