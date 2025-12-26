import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../locator.dart';
import '../../models/client.dart';

class ClientsScreen extends StatefulWidget {
  const ClientsScreen({super.key});

  @override
  State<ClientsScreen> createState() => _ClientsScreenState();
}

class _ClientsScreenState extends State<ClientsScreen> {
  final ApiService api = sl<ApiService>();
  List<Client> clients = [];
  List<Client> filteredClients = [];
  bool loading = true;
  String search = "";

  @override
  void initState() {
    super.initState();
    _loadClients();
  }

  Future<void> _loadClients() async {
    setState(() => loading = true);
    final res = await api.get('clients', 'list');
    if (res['success'] == true && res['data'] != null) {
      final list = (res['data'] as List)
          .map((c) => Client.fromJson(Map<String, dynamic>.from(c)))
          .toList();
      setState(() {
        clients = list;
        filteredClients = list;
        loading = false;
      });
    } else {
      setState(() => loading = false);
    }
  }

  void _filterClients(String value) {
    setState(() {
      search = value;
      filteredClients = clients
          .where((c) =>
              c.nom.toLowerCase().contains(value.toLowerCase()) ||
              (c.telephone ?? '').contains(value))
          .toList();
    });
  }

  Future<void> _deleteClient(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text("Confirmation"),
        content: const Text("Supprimer ce client ?"),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text("Annuler")),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, true), child: const Text("Supprimer")),
        ],
      ),
    );

    if (confirm == true) {
      final res = await api.get('clients', 'delete', {'id': id});
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Client supprimé.")),
        );
        _loadClients();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Erreur lors de la suppression.")),
        );
      }
    }
  }

  void _openClientForm([Client? client]) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ClientFormScreen(client: client, onSaved: _loadClients),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Clients"),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadClients,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _openClientForm(),
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
                      hintText: "Rechercher un client...",
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide.none,
                      ),
                    ),
                    onChanged: _filterClients,
                  ),
                ),
                Expanded(
                  child: filteredClients.isEmpty
                      ? const Center(child: Text("Aucun client trouvé"))
                      : ListView.builder(
                          itemCount: filteredClients.length,
                          itemBuilder: (context, i) {
                            final c = filteredClients[i];
                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              child: ListTile(
                                leading: const Icon(Icons.person, color: Colors.blueAccent),
                                title: Text(c.nom, style: const TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Text(
                                  "${c.telephone ?? '—'} | ${c.type}",
                                  style: const TextStyle(color: Colors.grey),
                                ),
                                trailing: PopupMenuButton<String>(
                                  onSelected: (value) {
                                    if (value == 'edit') _openClientForm(c);
                                    if (value == 'delete') _deleteClient(c.id);
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
class ClientFormScreen extends StatefulWidget {
  final Client? client;
  final Function onSaved;

  const ClientFormScreen({super.key, this.client, required this.onSaved});

  @override
  State<ClientFormScreen> createState() => _ClientFormScreenState();
}

class _ClientFormScreenState extends State<ClientFormScreen> {
  final ApiService api = sl<ApiService>();
  final _formKey = GlobalKey<FormState>();

  late TextEditingController nomCtrl;
  late TextEditingController telCtrl;
  late TextEditingController emailCtrl;
  late TextEditingController adresseCtrl;
  bool exonere = false;
  String type = 'Particulier';
  bool saving = false;

  @override
  void initState() {
    super.initState();
    final c = widget.client;
    nomCtrl = TextEditingController(text: c?.nom ?? '');
    telCtrl = TextEditingController(text: c?.telephone ?? '');
    emailCtrl = TextEditingController(text: c?.email ?? '');
    adresseCtrl = TextEditingController(text: c?.adresse ?? '');
    exonere = c?.exonere ?? false;
    type = c?.type ?? 'Particulier';
  }

  Future<void> _saveClient() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => saving = true);

    final data = {
      'nom': nomCtrl.text,
      'telephone': telCtrl.text,
      'email': emailCtrl.text,
      'adresse': adresseCtrl.text,
      'exonere': exonere ? 1 : 0,
      'type': type,
      if (widget.client != null) 'idClient': widget.client!.id,
    };

    final res = widget.client == null
        ? await api.post('clients', 'create', data)
        : await api.post('clients', 'update', data);

    setState(() => saving = false);

    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(widget.client == null ? "Client ajouté" : "Client modifié")),
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
      appBar: AppBar(title: Text(widget.client == null ? "Ajouter un client" : "Modifier le client")),
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
              const SizedBox(height: 10),
              DropdownButtonFormField<String>(
                initialValue: type,
                items: const [
                  DropdownMenuItem(value: 'Particulier', child: Text("Particulier")),
                  DropdownMenuItem(value: 'Entreprise', child: Text("Entreprise")),
                  DropdownMenuItem(value: 'Passager', child: Text("Passager")),
                ],
                onChanged: (v) => setState(() => type = v!),
                decoration: const InputDecoration(labelText: "Type de client"),
              ),
              const SizedBox(height: 30),
              saving
                  ? const CircularProgressIndicator()
                  : ElevatedButton.icon(
                      onPressed: _saveClient,
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
