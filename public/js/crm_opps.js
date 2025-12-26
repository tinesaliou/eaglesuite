// crm_opps.js (vanilla + jQuery used in project)
document.addEventListener('DOMContentLoaded', function() {
  // helper fetch JSON
  async function postForm(formData) {
    const res = await fetch('/eaglesuite/pages/crm/actions.php', { method:'POST', body: formData });
    return res.json();
  }

  // ---------- Drag & Drop simple ----------
  const columns = document.querySelectorAll('.opp-column');
  let dragged = null;

  document.addEventListener('dragstart', function(e) {
    const card = e.target.closest('.opp-card');
    if (!card) return;
    dragged = card;
    e.dataTransfer.effectAllowed = 'move';
    card.classList.add('dragging');
  });

  document.addEventListener('dragend', function(e) {
    if (dragged) dragged.classList.remove('dragging');
    dragged = null;
  });

  columns.forEach(col => {
    col.addEventListener('dragover', function(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
    });

    col.addEventListener('drop', async function(e) {
      e.preventDefault();
      if (!dragged) return;
      this.appendChild(dragged);
      const oppId = dragged.dataset.id;
      const newState = this.dataset.state;
      // notify server
      const fd = new FormData();
      fd.append('action','update_opportunity_state');
      fd.append('id', oppId);
      fd.append('etat', newState);
      const json = await postForm(fd);
      if (!json.success) {
        alert('Erreur mise à jour : ' + (json.error||''));
        location.reload();
      }
    });
  });

  // ---------- Create opportunity ----------
  $('#formAddOpportunity').on('submit', function(e){
    e.preventDefault();

    $.post('/eaglesuite/pages/crm/actions.php', $(this).serialize(), function(r){

        if (r.success) {
            $('#modalAddOpportunity').modal('hide');

            showAlert('success', 'Opportunité ajoutée avec succès !');

            setTimeout(() => location.reload(), 900);

        } else {
            showAlert('danger', r.error || 'Erreur lors de l’enregistrement');
        }

    }, 'json');
});

 // ---------- Edit opportunity (load via AJAX) ----------
$(document).on("click", ".btnEditOpp", function () {

    const id = $(this).data("id");

    $.get('/eaglesuite/pages/crm/actions.php', {
        action: 'get_opportunity',
        id: id
    }, function (r) {

        if (!r.success || !r.data) {
            alert("Impossible de charger l'opportunité");
            return;
        }

        const o = r.data;

        // Remplissage sécurisé
        $("#editOppId").val(o.id ?? '');
        $("#editOppTitre").val(o.titre ?? '');
        $("#editOppMontant").val(o.montant ?? '');
        $("#editOppDevise").val(o.devise_id ?? '');
        $("#editOppEtat").val(o.etat ?? '');
        $("#editOppProb").val(o.probabilite ?? '');
        $("#editOppDate").val(o.date_cloture_prevue ?? '');
        $("#editOppUser").val(o.utilisateur_id ?? '');
        $("#editOppDesc").val(o.description ?? '');

    }, 'json')
    .fail(function () {
        alert("Erreur serveur lors du chargement");
    });

});


  // ---------- Delete from list ----------

 document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalDeleteOpportunity");

    modal.addEventListener("show.bs.modal", function (event) {

        const button = event.relatedTarget; // bouton qui ouvre le modal

        const id = button.getAttribute("data-id");
        const nom = button.getAttribute("data-nom");

        document.getElementById("deleteOppId").value = id;
        document.getElementById("delete-opp-name").textContent = nom;
    });
});


 

  // ---------- Manage stages ----------
  $('#formAddStage').on('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    postForm(fd).then(res => {
      if (res.success) location.reload(); else alert(res.error || 'Erreur');
    });
  });

  // draggable reorder for stages list (native)
  const stagesList = document.getElementById('stagesList');
  if (stagesList) {
    let dragEl = null;
    stagesList.addEventListener('dragstart', function(e){ dragEl = e.target.closest('li'); e.dataTransfer.effectAllowed='move'; }, true);
    stagesList.addEventListener('dragover', function(e){ e.preventDefault(); const li = e.target.closest('li'); if (!li || li === dragEl) return; li.parentNode.insertBefore(dragEl, li.nextSibling); }, false);
    stagesList.querySelectorAll('li').forEach(li => li.setAttribute('draggable', true));

    document.getElementById('saveStageOrder').addEventListener('click', async function(){
      const ids = Array.from(stagesList.querySelectorAll('li')).map(li => li.dataset.id).join(',');
      const fd = new FormData();
      fd.append('action','reorder_stages');
      fd.append('order', ids);
      const res = await postForm(fd);
      if (res.success) location.reload(); else alert(res.error||'Erreur');
    });
  }

  // edit/delete stage buttons
  $(document).on('click', '.btnDeleteStage', function(){
    if (!confirm('Supprimer étape ? Les opportunités seront rebasculées.')) return;
    const id = $(this).data('id');
    const fd = new FormData();
    fd.append('action','delete_stage');
    fd.append('id', id);
    postForm(fd).then(r => { if (r.success) location.reload(); else alert(r.error||'Erreur'); });
  });

  // load opportunity details endpoint for edit
  // Add server-side action 'get_opportunity' in actions.php returning {success:true, item: {...}}
});
