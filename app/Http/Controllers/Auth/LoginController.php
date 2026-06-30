<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Показать форму авторизации
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Обработать вход
     */
        public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password) && $user->is_active) {
            Auth::login($user);
            
            // Редирект в зависимости от роли
            switch ($user->role) {
                case 'admin':
                    return redirect('/admin/users');
                case 'organizer':
                    return redirect('/organizer/events');
                case 'participant':
                    return redirect('/participant/films');
                case 'jury':
                    return redirect('/jury/films');
                case 'visitor':  // ← Добавили явную обработку роли
                    return redirect('/visitor');  // ← Правильный маршрут!
                default:
                    return redirect('/visitor');  // ← Фоллбэк тоже на /visitor
            }
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Неверный логин или пароль');
    }
}