<?php

declare(strict_types=1);

class AuthController
{
    private $secUserModel;

    public function __construct()
    {
        $this->secUserModel = new SecUserModel();
    }

    public function login(): void
    {
        if (Auth::check()) {
            Response::redirect('panel');
        }

        $pageTitle = 'Acceso Seguro';
        $rememberedLogin = Auth::rememberedLogin();
        require __DIR__ . '/../views/auth/login.php';
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('login');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $rememberLogin = !empty($_POST['remember_login']);

        if ($login === '' || $password === '') {
            Response::flash('error', 'Ingrese usuario y contrasena para continuar.');
            Response::redirect('login');
        }

        $user = $this->secUserModel->findByLoginAndEmpresa($login, ID_EMPRESA_MASTER);
        if (!$user) {
            Response::flash('error', 'Usuario o contrasena incorrectos.');
            Response::redirect('login');
        }

        if (strtoupper((string) ($user['active'] ?? 'N')) !== 'Y') {
            Response::flash('error', 'El usuario existe pero esta inactivo en sec_users.');
            Response::redirect('login');
        }

        if (!hash_equals((string) $user['pswd'], $password)) {
            Response::flash('error', 'Usuario o contrasena incorrectos.');
            Response::redirect('login');
        }

        Auth::login($user);

        if ($rememberLogin) {
            Auth::rememberLogin($login);
        } else {
            Auth::forgetRememberedLogin();
        }

        Response::flash('success', 'Sesion iniciada correctamente.');
        Response::redirect('panel');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::flash('success', 'Sesion finalizada correctamente.');
        Response::redirect('login');
    }
}
