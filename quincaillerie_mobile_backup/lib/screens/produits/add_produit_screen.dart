import 'dart:io' show File;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../services/api_service.dart';

class ProduitFormScreen extends StatefulWidget {
  const ProduitFormScreen({super.key});

  @override
  State<ProduitFormScreen> createState() => _ProduitFormScreenState();
}

class _ProduitFormScreenState extends State<ProduitFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nomCtl = TextEditingController();
  final _prixAchatCtl = TextEditingController();
  final _prixVenteCtl = TextEditingController();
  final _stockCtl = TextEditingController();
  final _descriptionCtl = TextEditingController();

  final ApiService _api = ApiService();
  XFile? _pickedFile;

  // 🔹 Listes dynamiques
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _unites = [];
  List<Map<String, dynamic>> _depots = [];

  int? _selectedCategorie;
  int? _selectedUnite;
  int? _selectedDepot;

  @override
  void initState() {
    super.initState();
    _loadDropdownData();
  }

  /// 🔹 Charge les listes depuis l’API
  Future<void> _loadDropdownData() async {
    final catRes = await _api.get('categories', 'list');
    final uniRes = await _api.get('unites', 'list');
    final depRes = await _api.get('depots', 'list');

    setState(() {
      if (catRes['success'] == true && catRes['data'] is List) {
        _categories = List<Map<String, dynamic>>.from(catRes['data']);
      }
      if (uniRes['success'] == true && uniRes['data'] is List) {
        _unites = List<Map<String, dynamic>>.from(uniRes['data']);
      }
      if (depRes['success'] == true && depRes['data'] is List) {
        _depots = List<Map<String, dynamic>>.from(depRes['data']);
      }

      // Sélectionne la première valeur par défaut si dispo
      if (_categories.isNotEmpty && _selectedCategorie == null) {
        _selectedCategorie = int.tryParse(_categories.first['id'].toString());
      }
      if (_unites.isNotEmpty && _selectedUnite == null) {
        _selectedUnite = int.tryParse(_unites.first['id'].toString());
      }
      if (_depots.isNotEmpty && _selectedDepot == null) {
        _selectedDepot = int.tryParse(_depots.first['id'].toString());
      }
    });
  }

  /// 📸 Choisir une image
  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery);
    if (picked != null) setState(() => _pickedFile = picked);
  }

  /// 💾 Sauvegarde du produit
  Future<void> _saveProduit() async {
    if (!_formKey.currentState!.validate()) return;

    String? imageFileName;
    if (_pickedFile != null) {
      final upload = await _api.uploadImage(_pickedFile!);
      if (upload['success'] == true) {
        imageFileName = upload['file'];
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(upload['message'] ?? 'Erreur upload')),
        );
        return;
      }
    }

    final data = {
      'nom': _nomCtl.text,
      'prix_achat': _prixAchatCtl.text,
      'prix_vente': _prixVenteCtl.text,
      'stock_total': 0,
      'description': _descriptionCtl.text,
      'categorie_id': _selectedCategorie,
      'unite_id': _selectedUnite,
      'depot_id': _selectedDepot,
      'image': imageFileName,
    };

    final res = await _api.post('produits', 'create', data);
    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Produit ajouté avec succès ✅')),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message'] ?? 'Erreur d’ajout ❌')),
      );
    }
  }

  /// ➕ Ajout d’une nouvelle catégorie, unité ou dépôt
  void _ajouterNouvelElement(String type) async {
    final nomController = TextEditingController();

    await showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text('Ajouter $type'),
        content: TextField(
          controller: nomController,
          decoration: InputDecoration(labelText: 'Nom du $type'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () async {
              final nom = nomController.text.trim();
              if (nom.isEmpty) return;

              final res = await _api.post(type, 'create', {'nom': nom});
              if (res['success'] == true) {
                Navigator.pop(context);
                await _loadDropdownData();
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('$type ajouté avec succès ✅')),
                );
              } else {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(res['message'] ?? 'Erreur d’ajout')),
                );
              }
            },
            child: const Text('Enregistrer'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    Widget imagePreview = _pickedFile != null
        ? (kIsWeb
            ? Image.network(_pickedFile!.path, fit: BoxFit.cover)
            : Image.file(File(_pickedFile!.path), fit: BoxFit.cover))
        : const Icon(Icons.add_a_photo, size: 50);

    return Scaffold(
      appBar: AppBar(title: const Text('Nouveau produit')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              // 📸 Image
              GestureDetector(
                onTap: _pickImage,
                child: Container(
                  height: 150,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.grey[200],
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: imagePreview,
                ),
              ),
              const SizedBox(height: 16),

              // 🏷️ Champs texte
              TextFormField(
                controller: _nomCtl,
                decoration: const InputDecoration(labelText: 'Nom'),
                validator: (v) => v!.isEmpty ? 'Nom requis' : null,
              ),
              TextFormField(
                controller: _prixAchatCtl,
                decoration: const InputDecoration(labelText: 'Prix d’achat'),
                keyboardType: TextInputType.number,
              ),
              TextFormField(
                controller: _prixVenteCtl,
                decoration: const InputDecoration(labelText: 'Prix de vente'),
                keyboardType: TextInputType.number,
              ),
              TextFormField(
                controller: _stockCtl,
                decoration: const InputDecoration(labelText: 'Stock initial',
                helperText: 'Ce champ est géré automatiquement (achats/ventes)',
                ),
                keyboardType: TextInputType.number,
                readOnly: true,
              ),
              TextFormField(
                controller: _descriptionCtl,
                decoration: const InputDecoration(labelText: 'Description'),
                maxLines: 2,
              ),
              const SizedBox(height: 20),

              // 🔽 Catégorie
              Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<int>(
                      initialValue: _selectedCategorie,
                      decoration: const InputDecoration(labelText: 'Catégorie'),
                      items: _categories
                          .map((c) => DropdownMenuItem<int>(
                                value: int.tryParse(c['id'].toString()),
                                child: Text(c['nom']),
                              ))
                          .toList(),
                      onChanged: (v) => setState(() => _selectedCategorie = v),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.add_circle, color: Colors.blue),
                    onPressed: () => _ajouterNouvelElement('categories'),
                  ),
                ],
              ),

              // 🔽 Unité
              Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<int>(
                      initialValue: _selectedUnite,
                      decoration: const InputDecoration(labelText: 'Unité'),
                      items: _unites
                          .map((u) => DropdownMenuItem<int>(
                                value: int.tryParse(u['id'].toString()),
                                child: Text(u['nom']),
                              ))
                          .toList(),
                      onChanged: (v) => setState(() => _selectedUnite = v),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.add_circle, color: Colors.blue),
                    onPressed: () => _ajouterNouvelElement('unites'),
                  ),
                ],
              ),

              // 🔽 Dépôt
              Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<int>(
                      initialValue: _selectedDepot,
                      decoration: const InputDecoration(labelText: 'Dépôt'),
                      items: _depots
                          .map((d) => DropdownMenuItem<int>(
                                value: int.tryParse(d['id'].toString()),
                                child: Text(d['nom']),
                              ))
                          .toList(),
                      onChanged: (v) => setState(() => _selectedDepot = v),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.add_circle, color: Colors.blue),
                    onPressed: () => _ajouterNouvelElement('depots'),
                  ),
                ],
              ),

              const SizedBox(height: 25),

              // 💾 Bouton d’enregistrement
              ElevatedButton.icon(
                onPressed: _saveProduit,
                icon: const Icon(Icons.save),
                label: const Text('Enregistrer'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
