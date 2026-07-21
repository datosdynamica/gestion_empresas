<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Controlador de autenticacion
|--------------------------------------------------------------------------
| Administra el ingreso y la salida del panel interno. Su responsabilidad es
| validar credenciales contra `sec_users`, crear la sesion del modulo y
| redirigir al usuario a la pantalla adecuada segun el resultado.
*/

/**
 * Maneja el flujo de login del modulo.
 */
class AuthController
{
    private $secUserModel;

    /**
     * Prepara el acceso al modelo que consulta usuarios del panel.
     */
    public function __construct()
    {
        $this->secUserModel = new SecUserModel();
    }

    /**
     * Muestra el formulario de acceso o redirige al panel si ya existe sesion.
     */
    public function login(): void
    {
        if (Auth::check()) {
            Response::redirect('panel');
        }

        $pageTitle = APP_NAME;
        $rememberedLogin = Auth::rememberedLogin();
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Valida el usuario y la contrasena enviados por el formulario.
     */
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

    /**
     * Cierra la sesion actual y devuelve al formulario de ingreso.
     */
    public function logout(): void
    {
        Auth::logout();
        Response::flash('success', 'Sesion finalizada correctamente.');
        Response::redirect('login');
    }
}
