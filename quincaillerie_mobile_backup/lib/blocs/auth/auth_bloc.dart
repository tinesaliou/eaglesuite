import 'package:flutter_bloc/flutter_bloc.dart';
import '../../repositories/utilisateur_repository.dart';
import '../../models/utilisateur.dart';
import 'auth_event.dart';
import 'auth_state.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final UtilisateurRepository utilisateurRepository;

  AuthBloc(this.utilisateurRepository) : super(AuthInitial()) {
    on<AuthLoginRequested>(_onLoginRequested);
    on<AuthLogoutRequested>(_onLogoutRequested);
  }

  Future<void> _onLoginRequested(
      AuthLoginRequested event, Emitter<AuthState> emit) async {
    emit(AuthLoading());

    final result =
        await utilisateurRepository.login(event.email, event.password);

    if (result['success'] == true) {
      final user = result['utilisateur'] as Utilisateur;
      emit(AuthAuthenticated(user)); // ✅ envoie l’utilisateur
    } else {
      emit(AuthError(result['message'] ?? 'Erreur de connexion'));
    }
  }

  Future<void> _onLogoutRequested(
      AuthLogoutRequested event, Emitter<AuthState> emit) async {
    await utilisateurRepository.logout();
    emit(AuthInitial());
  }
}
