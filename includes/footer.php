    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <h5 class="mb-3 fw-semibold">Ingresar</h5>
                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label small">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div id="loginError" class="alert alert-danger d-none small py-2"></div>
                        <button type="submit" class="btn btn-primary w-100">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Ingresar
                        </button>
                    </form>
                    <p class="text-center small mt-3 mb-0">
                        &iquest;No tienes cuenta?
                        <a href="<?= BASE_URL ?>/index.php?page=register">Regístrate</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <?= $extraJs ?? '' ?>
</body>
</html>
