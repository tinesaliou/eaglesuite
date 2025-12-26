import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'app.dart';
import 'locator.dart';
import 'blocs/auth/auth_bloc.dart';
import 'repositories/utilisateur_repository.dart';
import 'services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  setupLocator();

  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString('token');
  final initialRoute = (token != null && token.isNotEmpty) ? '/dashboard' : '/login';

  runApp(MultiBlocProvider(
    providers: [
      BlocProvider<AuthBloc>(
        create: (_) => AuthBloc(UtilisateurRepository(sl<ApiService>())),
      ),
    ],
    child: QuincaillerieApp(initialRoute: initialRoute),
  ));
}
