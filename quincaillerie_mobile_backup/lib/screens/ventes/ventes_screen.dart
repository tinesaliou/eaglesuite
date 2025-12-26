// lib/screens/ventes/ventes_screen.dart

import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../locator.dart';
import '../../models/vente.dart';
import 'vente_detail_screen.dart';
import 'vente_form_screen.dart';

class VentesScreen extends StatefulWidget {
  const VentesScreen({super.key});

  @override
  State<VentesScreen> createState() => _VentesScreenState();
}

class _VentesScreenState extends State<VentesScreen> {
  final ApiService api = sl<ApiService>();
  List<Vente> ventes = [];
  bool loading = false;
  String query = '';
  final _controller = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadVentes();
  }

  Future<void> _loadVentes({bool refresh = false}) async {
    setState(() => loading = true);
    final res = await api.fetchVentes(q: query, page: 1, perPage: 200);
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List)
          .map((e) => Vente.fromJson(Map<String, dynamic>.from(e)))
          .toList();
      setState(() => ventes = list);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Erreur chargement ventes')),
        );
      }
    }
    setState(() => loading = false);
  }

  Future<void> _onRefresh() async => await _loadVentes(refresh: true);

  void _onSearch(String q) {
    query = q;
    _loadVentes();
  }

  Future<void> _confirmCancel(Vente v) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Annuler la vente'),
        content: Text('Voulez-vous vraiment annuler la vente ${v.numero} ?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Non')),
          ElevatedButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Oui')),
        ],
      ),
    );
    if (ok == true) {
      final res = await api.cancelVente(v.id);
      if (res['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context)
              .showSnackBar(const SnackBar(content: Text('Vente annulée')));
          _loadVentes(refresh: true);
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(res['message'] ?? 'Erreur')),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ventes'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final created = await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const VenteFormScreen()),
              );
              if (created == true) _loadVentes(refresh: true);
            },
          )
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(60),
          child: Padding(
            padding: const EdgeInsets.all(8.0),
            child: TextField(
              controller: _controller,
              onSubmitted: _onSearch,
              decoration: InputDecoration(
                hintText: 'Rechercher (numéro, client)...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    _controller.clear();
                    _onSearch('');
                  },
                ),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _onRefresh,
        child: loading && ventes.isEmpty
            ? const Center(child: CircularProgressIndicator())
            : ListView.separated(
                padding: const EdgeInsets.all(12),
                itemCount: ventes.length,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, i) {
                  final v = ventes[i];
                  return Card(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: ListTile(
                      onTap: () async {
                        final updated = await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => VenteDetailScreen(venteId: v.id),
                          ),
                        );
                        if (updated == true) _loadVentes(refresh: true);
                      },
                      leading: CircleAvatar(
                        child: Text(v.numero.split('-').last),
                      ),
                      title: Text(v.numero),
                      subtitle: Text(
                        '${v.clientNom} • ${v.dateVente.toLocal().toString().split(' ')[0]}',
                      ),
                      trailing: SizedBox(
                        height: 50,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              '${v.totalTTC.toStringAsFixed(0)} FCFA',
                              style:
                                  const TextStyle(fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 4),
                            if (v.annule)
                              const Text(
                                'Annulée',
                                style: TextStyle(
                                    color: Colors.red, fontSize: 12),
                              )
                            else
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    v.statut,
                                    style: TextStyle(
                                      color: v.statut == 'Payé'
                                          ? Colors.green
                                          : Colors.orange,
                                      fontSize: 12,
                                    ),
                                  ),
                                  IconButton(
                                    padding: EdgeInsets.zero,
                                    constraints: const BoxConstraints(),
                                    icon: const Icon(Icons.cancel,
                                        color: Colors.redAccent, size: 18),
                                    onPressed: () => _confirmCancel(v),
                                  ),
                                ],
                              ),
                          ],
                        ),
                      ),
                      isThreeLine: true,
                    ),
                  );
                },
              ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final created = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const VenteFormScreen()),
          );
          if (created == true) _loadVentes(refresh: true);
        },
        child: const Icon(Icons.add_shopping_cart),
      ),
    );
  }
}
