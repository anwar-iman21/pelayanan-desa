<?php
// csrf.php
// WAJIB dipanggil setelah session_start().
// Cara pakai di form: panggil csrf_field() di dalam tag <form>...</form>
// Cara cek pas proses POST: kalau csrf_verify() hasilnya false, tolak requestnya

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify() {
    if (!isset($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}