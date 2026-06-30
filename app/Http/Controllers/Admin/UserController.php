<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Список пользователей + форма создания
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    // Создание нового пользователя
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,organizer,jury,participant,visitor',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'Учетная запись успешно создана!');
    }

    // Смена роли
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,organizer,jury,participant,visitor']);
        $user->update(['role' => $request->role]);
        return back()->with('success', 'Роль пользователя изменена!');
    }

    // Блокировка / Разблокировка
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $msg = $user->is_active ? 'разблокирован' : 'заблокирован';
        return back()->with('success', "Пользователь {$msg}!");
    }

    // Удаление
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя удалить собственную учетную запись.');
        }
        $user->delete();
        return back()->with('success', 'Пользователь удален!');
    }
}