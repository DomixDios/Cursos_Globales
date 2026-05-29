<?php
$pageTitle = 'Registro';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/database.php';
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES (?, ?, ?, ?)');
    try {
        $stmt->execute([
            $_POST['full_name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            'estudiante'
        ]);
        $user = currentUser();
        if (!$user) login($_POST['email'], $_POST['password']);
        redirect(BASE_URL . '/index.php?page=student-dashboard');
    } catch (PDOException $e) {
        $error = 'El email ya est? registrado.';
    }
}
?>
<section class="container py-5" style="max-width:480px;">
    <h2 class="mb-4 fw-bold">Crear cuenta</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label small">Nombre completo</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label small">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label small">Contraseña</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Registrarse</button>
        <p class="text-center small mt-3">?Ya tienes cuenta? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Ingresa</a></p>
    </form>
</section>
