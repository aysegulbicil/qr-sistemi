<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        // Brute-force korumasi: IP basina dakikada en fazla 5 deneme.
        $throttler = service('throttler');
        if ($throttler->check(md5('login-' . $this->request->getIPAddress()), 5, MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Cok fazla giris denemesi. Lutfen bir dakika sonra tekrar dene.');
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = (new UserModel())->findByUsername($username);

        if ($user === null || ! $user['is_active'] || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Kullanıcı adı veya parola hatalı.');
        }

        // Session fixation korumasi: giriste oturum kimligini yenile.
        session()->regenerate(true);

        session()->set([
            'user_id'   => (int) $user['id'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ]);

        $redirect = session()->get('redirect_url');
        session()->remove('redirect_url');

        return redirect()->to($redirect ?: '/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('message', 'Çıkış yapıldı.');
    }
}
