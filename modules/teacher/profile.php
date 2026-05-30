<?php $pageTitle = 'Mi Perfil'; ?>
<h2 class="fw-bold mb-4">Mi Perfil</h2>
<div class="row">
    <div class="col-md-4">
        <div class="card text-center p-4 mb-4">
            <div class="mb-3">
                <img id="profileAvatar" src="" alt="Avatar" class="rounded-circle border" style="width:120px;height:120px;object-fit:cover;">
            </div>
            <form id="avatarForm" enctype="multipart/form-data">
                <label class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-camera"></i> Cambiar foto
                    <input type="file" name="avatar" accept="image/*" class="d-none" id="avatarInput">
                </label>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <form id="profileForm">
                <div class="mb-3">
                    <label class="form-label small">Nombre completo</label>
                    <input type="text" name="full_name" class="form-control" id="profileName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Email</label>
                    <input type="email" class="form-control" id="profileEmail" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Rol</label>
                    <input type="text" class="form-control" id="profileRole" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Biografía</label>
                    <textarea name="bio" class="form-control" id="profileBio" rows="4"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>
<?php $extraJs .= '<script>
$(function() {
    function loadProfile() {
        $.getJSON(BASE_URL + "/api/profile.php?action=get", function(u) {
            $("#profileName").val(u.nombre_completo);
            $("#profileEmail").val(u.email);
            var roleMap = { admin: "Admin", moderador: "Moderador", profesor: "Profesor", estudiante: "Estudiante" };
            $("#profileRole").val(roleMap[u.rol] || u.rol);
            $("#profileBio").val(u.bio || "");
            if (u.avatar) {
                $("#profileAvatar").attr("src", BASE_URL + "/" + u.avatar);
            } else {
                $("#profileAvatar").attr("src", BASE_URL + "/assets/uploads/avatars/default.png");
            }
        });
    }
    loadProfile();
    $("#profileForm").on("submit", function(e) {
        e.preventDefault();
        var btn = $(this).find("button[type=submit]");
        btn.prop("disabled", true).text("Guardando...");
        $.post(BASE_URL + "/api/profile.php?action=update", $(this).serialize()).done(function(r) {
            if (r.success) { alert("Perfil actualizado"); loadProfile(); }
        }).always(function() { btn.prop("disabled", false).text("Guardar cambios"); });
    });
    $("#avatarInput").on("change", function() {
        var fd = new FormData();
        fd.append("avatar", this.files[0]);
        $.ajax({
            url: BASE_URL + "/api/profile.php?action=avatar",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            success: function(r) {
                if (r.success) { $("#profileAvatar").attr("src", BASE_URL + "/" + r.avatar); }
            }
        });
    });
});
</script>'; ?>
