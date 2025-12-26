$(document).ready(function () {
    // === ENTREPRISE ===
    $("#formEntreprise").on("submit", function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        formData.append("action", "update");
        formData.append("table", "entreprise");
        formData.append("id", "1"); // entreprise unique

        $.ajax({
            url: "/quincaillerie/api/settings.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === "success") {
                    alert("Entreprise mise à jour avec succès !");
                } else {
                    alert("Erreur: " + res.data);
                }
            },
            error: function () {
                alert("Erreur AJAX entreprise");
            },
        });
    });

    // === LISTE UNITE / DEVISE / TVA ===
    function loadList(table, container) {
        $.get("/quincaillerie/api/settings.php", { action: "list", table: table }, function (res) {
            if (res.status === "success") {
                let html = `<table class="table table-bordered">
                                <thead><tr>`;
                if (table === "unites") html += "<th>ID</th><th>Libellé</th>";
                if (table === "devises") html += "<th>ID</th><th>Code</th><th>Symbole</th>";
                if (table === "tva") html += "<th>ID</th><th>Taux (%)</th>";
                html += "<th>Actions</th></tr></thead><tbody>";

                res.data.forEach(function (item) {
                    html += "<tr>";
                    if (table === "unites") html += `<td>${item.id}</td><td>${item.libelle}</td>`;
                    if (table === "devises") html += `<td>${item.id}</td><td>${item.code}</td><td>${item.symbole}</td>`;
                    if (table === "tva") html += `<td>${item.id}</td><td>${item.taux}</td>`;
                    html += `<td>
                                <button class="btn btn-sm btn-warning edit" data-id="${item.id}" data-table="${table}">Modifier</button>
                                <button class="btn btn-sm btn-danger delete" data-id="${item.id}" data-table="${table}">Supprimer</button>
                             </td>`;
                    html += "</tr>";
                });
                html += "</tbody></table>";

                $(container).html(html);
            }
        });
    }

    // Chargement initial
    loadList("unites", "#listUnites");
    loadList("devises", "#listDevises");
    loadList("tva", "#listTva");

    // === AJOUT / EDITION ===
    $("#formUnite, #formDevise, #formTva").on("submit", function (e) {
        e.preventDefault();
        let form = $(this);
        let table = form.find("input[name=table]").val();
        let id = form.find("input[name=id]").val();

        let action = id ? "update" : "create";
        let data = form.serialize() + "&action=" + action;

        $.post("/quincaillerie/api/settings.php", data, function (res) {
            if (res.status === "success") {
                alert("Enregistré avec succès !");
                if (table === "unites") loadList("unites", "#listUnites");
                if (table === "devises") loadList("devises", "#listDevises");
                if (table === "tva") loadList("tva", "#listTva");
                form[0].reset();
                form.closest(".modal").modal("hide");
            } else {
                alert("Erreur: " + res.data);
            }
        });
    });

    // === SUPPRESSION ===
    $(document).on("click", ".delete", function () {
        if (!confirm("Supprimer cet élément ?")) return;
        let id = $(this).data("id");
        let table = $(this).data("table");

        $.post("/quincaillerie/api/settings.php", { action: "delete", table: table, id: id }, function (res) {
            if (res.status === "success") {
                if (table === "unites") loadList("unites", "#listUnites");
                if (table === "devises") loadList("devises", "#listDevises");
                if (table === "tva") loadList("tva", "#listTva");
            } else {
                alert("Erreur: " + res.data);
            }
        });
    });

    // === EDITION ===
    $(document).on("click", ".edit", function () {
        let id = $(this).data("id");
        let table = $(this).data("table");

        $.get("/quincaillerie/api/settings.php", { action: "list", table: table }, function (res) {
            if (res.status === "success") {
                let item = res.data.find((row) => row.id == id);
                if (!item) return;

                if (table === "unites") {
                    $("#formUnite [name=id]").val(item.id);
                    $("#formUnite [name=libelle]").val(item.libelle);
                    $("#modalUnite").modal("show");
                }

                if (table === "devises") {
                    $("#formDevise [name=id]").val(item.id);
                    $("#formDevise [name=code]").val(item.code);
                    $("#formDevise [name=symbole]").val(item.symbole);
                    $("#modalDevise").modal("show");
                }

                if (table === "tva") {
                    $("#formTva [name=id]").val(item.id);
                    $("#formTva [name=taux]").val(item.taux);
                    $("#modalTva").modal("show");
                }
            }
        });
    });
});
