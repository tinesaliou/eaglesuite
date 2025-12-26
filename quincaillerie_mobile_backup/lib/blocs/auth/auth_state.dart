import '../../models/utilisateur.dart';

abstract class AuthState {}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class AuthAuthenticated extends AuthState {
  final Utilisateur utilisateur;
  AuthAuthenticated(this.utilisateur);
}

class AuthError extends AuthState {
  final String message;
  AuthError(this.message);
}
