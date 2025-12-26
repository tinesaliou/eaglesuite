import 'dart:io' show File;
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../models/produit.dart';
import '../../services/api_service.dart';
import '../../config.dart';

class ProduitEditScreen extends StatefulWidget {
  final Produit produit;
  const ProduitEditScreen({super.key, required this.produit});

  @override
  State<ProduitEditScreen> createState() => _ProduitEditScreenState();
}

class _ProduitEditScreenState extends State<ProduitEditScreen> {
  final _formKey = GlobalKey<FormState>();
  final ApiService _api = ApiService();

  XFile? _pickedFile;

  // 🧱 Controllers
  late TextEditingController _nomCtl;
  late TextEditingController _prixAchatCtl;
  late TextEditingController _prixVenteCtl;
  late TextEditingController _stockCtl;
  late TextEditingController _descriptionCtl;

  // 🔽 Dropdowns dynamiques
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _unites = [];
  List<Map<String, dynamic>> _depots = [];

  int? _selectedCategorie;
  int? _selectedUnite;
  int? _selectedDepot;

  @override
  void initState() {
    super.initState();

    _nomCtl = TextEditingController(text: widget.produit.nom);
    _prixAchatCtl = TextEditingController(text: widget.produit.prixAchat.toString());
    _prixVenteCtl = TextEditingController(text: widget.produit.prixVente.toString());
    _stockCtl = TextEditingController(text: widget.produit.stockTotal.toString());
    _descriptionCtl = TextEditingController(text: widget.produit.description ?? '');

    _selectedCategorie = widget.produit.categorieId;
    _selectedUnite = widget.produit.uniteId;
    _selectedDepot = widget.produit.depotId;

    _loadDropdownData();
  }

  Future<void> _loadDropdownData() async {
    final catRes = await _api.get('categories', 'list');
    final uniRes = await _api.get('unites', 'list');
    final depRes = await _api.get('depots', 'list');

    setState(() {
      if (catRes['success'] == true) _categories = List<Map<String, dynamic>>.from(catRes['data']);
      if (uniRes['success'] == true) _unites = List<Map<String, dynamic>>.from(uniRes['data']);
      if (depRes['success'] == true) _depots = List<Map<String, dynamic>>.from(depRes['data']);
    });
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery);
    if (picked != null) setState(() => _pickedFile = picked);
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    String? imageFileName = widget.produit.image;

    // 🖼️ Upload si nouvelle image sélectionnée
    if (_pickedFile != null) {
      final upload = await _api.uploadImage(_pickedFile!);
      if (upload['success'] == true) {
        imageFileName = upload['file'];
      } else {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(upload['message'] ?? 'Erreur d’upload')));
        return;
      }
    }

    final data = {
      'id': widget.produit.id,
      'nom': _nomCtl.text,
      'prix_achat': _prixAchatCtl.text,
      'prix_vente': _prixVenteCtl.text,
      //'stock_total': _stockCtl.text,
      'description': _descriptionCtl.text,
      'categorie_id': _selectedCategorie,
      'unite_id': _selectedUnite,
      'depot_id': _selectedDepot,
      'image': imageFileName,
    };

    final res = await _api.post('produits', 'update', data);
    if (res['success'] == true) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Produit mis à jour avec succès')));
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(res['message'] ?? 'Erreur de mise à jour')));
    }
  }

  Future<void> _delete() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Confirmer la suppression'),
        content: const Text('Voulez-vous vraiment supprimer ce produit ?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final res = await _api.post('produits', 'delete', {'id': widget.produit.id});
    if (res['success'] == true) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('Produit supprimé avec succès')));
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(res['message'] ?? 'Erreur suppression')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final imageWidget = _pickedFile != null
        ? (kIsWeb
            ? Image.network(_pickedFile!.path, fit: BoxFit.cover)
            : Image.file(File(_pickedFile!.path), fit: BoxFit.cover))
        : (widget.produit.image != null && widget.produit.image!.isNotEmpty
            ? Image.network(
                '${baseUrl}${widget.produit.image}',
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) =>
                    const Icon(Icons.broken_image, size: 80, color: Colors.grey),
              )
            : const Icon(Icons.image, size: 80, color: Colors.grey));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Modifier le produit'),
        actions: [
          IconButton(
            icon: const Icon(Icons.delete, color: Colors.red),
            onPressed: _delete,
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              GestureDetector(
                onTap: _pickImage,
                child: Container(
                  height: 160,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.grey[200],
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Center(child: imageWidget),
                ),
              ),
              const SizedBox(height: 16),
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
                decoration: const InputDecoration(
                  labelText: 'Stock total',
                  helperText: 'Ce champ est mis à jour automatiquement (achats/ventes)',
                ),
                keyboardType: TextInputType.number,
                readOnly: true, // 🔹 Lecture seule
              ),

              TextFormField(
                controller: _descriptionCtl,
                decoration: const InputDecoration(labelText: 'Description'),
                maxLines: 2,
              ),
              const SizedBox(height: 20),

              // Catégorie
              DropdownButtonFormField<int>(
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

              // Unité
              DropdownButtonFormField<int>(
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

              // Dépôt
              DropdownButtonFormField<int>(
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

              const SizedBox(height: 25),
              ElevatedButton.icon(
                onPressed: _save,
                icon: const Icon(Icons.save),
                label: const Text('Enregistrer les modifications'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
