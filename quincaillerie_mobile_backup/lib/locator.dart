// lib/locator.dart
import 'package:get_it/get_it.dart';
import 'services/api_service.dart';

final sl = GetIt.instance;

void setupLocator() {
  sl.registerLazySingleton<ApiService>(() => ApiService());
}
