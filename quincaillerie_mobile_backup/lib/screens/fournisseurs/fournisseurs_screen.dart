import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../locator.dart';
import '../../models/fournisseur.dart';

class FournisseursScreen extends StatefulWidget {
  const FournisseursScreen({super.key});

  @override
  State<FournisseursScreen> createState() => _FournisseursScreenState();
}

class _FournisseursScreenState extends State<FournisseursScreen> {
  final ApiService api = sl<ApiService>();
  List<Fournisseur> fournisseurs = [];
  List<Fournisseur> filteredFournisseurs = [];
  bool loading = true;
  String search = "";

  @override
  void initState() {
    super.initState();
    _loadFournisseurs();
  }

  Future<void> _loadFournisseurs() async {
    setState(() => loading = true);
    final res = await api.get('fournisseurs', 'list');
    if (res['success'] == true && res['data'] != null) {
      final list = (res['data'] as List)
          .map((f) => Fournisseur.fromJson(Map<String, dynamic>.from(f)))
          .toList();
      setState(() {
        fournisseurs = list;
        filteredFournisseurs = list;
        loading = false;
      });
    } else {
      setState(() => loading = false);
    }
  }

  void _filterFournisseurs(String value) {
    setState(() {
      search = value;
      filteredFournisseurs = fournisseurs
          .where((f) =>
              f.nom.toLowerCase().contains(value.toLowerCase()) ||
              (f.telephone ?? '').contains(value))
          .toList();
    });
  }

  Future<void> _deleteFournisseur(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text("Confirmation"),
        content: const Text("Supprimer ce fournisseur ?"),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text("Annuler")),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text("Supprimer")),
        ],
      ),
    );

    if (confirm == true) {
      final res = await api.get('fournisseurs', 'delete', {'id': id});
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Fournisseur supprimé.")),
        );
        _loadFournisseurs();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Erreur lors de la suppression.")),
        );
      }
    }
  }

  void _openFournisseurForm([Fournisseur? fournisseur]) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => FournisseurFormScreen(fournisseur: fournisseur, onSaved: _loadFournisseurs),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Fournisseurs"),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadFournisseurs,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _openFournisseurForm(),
        child: const Icon(Icons.add),
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(12),
                  child: TextField(
                    decoration: InputDecoration(
                      prefixIcon: const Icon(Icons.search),
                      hintText: "Rechercher un fournisseur...",
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide.none,
                      ),
                    ),
                    onChanged: _filterFournisseurs,
                  ),
                ),
                Expanded(
                  child: filteredFournisseurs.isEmpty
                      ? const Center(child: Text("Aucun fournisseur trouvé"))
                      : ListView.builder(
                          itemCount: filteredFournisseurs.length,
                          itemBuilder: (context, i) {
                            final f = filteredFournisseurs[i];
                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              child: ListTile(
                                leading: const Icon(Icons.person, color: Colors.blueAccent),
                                title: Text(f.nom, style: const TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Text(
                                  "${f.telephone ?? '—'} ",
                                  style: const TextStyle(color: Colors.grey),
                                ),
                                trailing: PopupMenuButton<String>(
                                  onSelected: (value) {
                                    if (value == 'edit') _openFournisseurForm(f);
                                    if (value == 'delete') _deleteFournisseur(f.id);
                                  },
                                  itemBuilder: (context) => [
                                    const PopupMenuItem(value: 'edit', child: Text("Modifier")),
                                    const PopupMenuItem(value: 'delete', child: Text("Supprimer")),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                ),
              ],
            ),
    );
  }
}

// ----------------------------------------------------------------------
// 🧾 Formulaire d’ajout / modification
// ----------------------------------------------------------------------
class FournisseurFormScreen extends StatefulWidget {
  final Fournisseur? fournisseur;
  final Function onSaved;

  const FournisseurFormScreen({super.key, this.fournisseur, required this.onSaved});

  @override
  State<FournisseurFormScreen> createState() => _FournisseurFormScreenState();
}

class _FournisseurFormScreenState extends State<FournisseurFormScreen> {
  final ApiService api = sl<ApiService>();
  final _formKey = GlobalKey<FormState>();

  late TextEditingController nomCtrl;
  late TextEditingController telCtrl;
  late TextEditingController emailCtrl;
  late TextEditingController adresseCtrl;
  bool exonere = false;
  bool saving = false;

  @override
  void initState() {
    super.initState();
    final f = widget.fournisseur;
    nomCtrl = TextEditingController(text: f?.nom ?? '');
    telCtrl = TextEditingController(text: f?.telephone ?? '');
    emailCtrl = TextEditingController(text: f?.email ?? '');
    adresseCtrl = TextEditingController(text: f?.adresse ?? '');
    exonere = f?.exonere ?? false;
  }

  Future<void> _saveFournisseur() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => saving = true);

    final data = {
      'nom': nomCtrl.text,
      'telephone': telCtrl.text,
      'email': emailCtrl.text,
      'adresse': adresseCtrl.text,
      'exonere': exonere ? 1 : 0,
      if (widget.fournisseur != null) 'id': widget.fournisseur!.id,
    };

    final res = widget.fournisseur == null
        ? await api.post('fournisseurs', 'create', data)
        : await api.post('fournisseurs', 'update', data);

    setState(() => saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(widget.fournisseur == null ? "Fournisseur ajouté" : "Fournisseur modifié")),
      );
      widget.onSaved();
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Erreur: ${res['message'] ?? 'Opération échouée'}")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.fournisseur == null ? "Ajouter un fournisseur" : "Modifier le fournisseur")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                controller: nomCtrl,
                decoration: const InputDecoration(labelText: "Nom complet *"),
                validator: (v) => v == null || v.isEmpty ? "Nom obligatoire" : null,
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: telCtrl,
                decoration: const InputDecoration(labelText: "Téléphone"),
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: "Email"),
                keyboardType: TextInputType.emailAddress,
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: adresseCtrl,
                decoration: const InputDecoration(labelText: "Adresse"),
                maxLines: 2,
              ),
              const SizedBox(height: 10),
              SwitchListTile(
                title: const Text("Exonéré de TVA ?"),
                value: exonere,
                onChanged: (v) => setState(() => exonere = v),
              ),
              const SizedBox(height: 30),
              saving
                  ? const CircularProgressIndicator()
                  : ElevatedButton.icon(
                      onPressed: _saveFournisseur,
                      icon: const Icon(Icons.save),
                      label: const Text("Enregistrer"),
                    ),
            ],
          ),
        ),
      ),
    );
  }
}
