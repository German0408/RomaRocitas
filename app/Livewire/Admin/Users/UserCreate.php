<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserCreate extends Component
{
    public $user = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => 'customer',
    ];

    public function boot()
    {
        $this->withValidator(function ($validator) {
            if ($validator->fails()) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => '¡Error!',
                    'text' => 'El formulario tiene errores.',
                ]);
            }
        });
    }

    public function store()
    {
        $this->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:8|confirmed',
            'user.role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
        ]);

        $user = User::create([
            'name' => $this->user['name'],
            'email' => $this->user['email'],
            'password' => Hash::make($this->user['password']),
            'role' => $this->user['role'],
        ]);

        // Log activity
        Log::info('User created', [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
            'action' => 'create',
            'target_user_email' => $this->user['email'],
            'target_user_role' => $this->user['role'],
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => '¡Bien hecho!',
            'text' => 'Usuario creado correctamente.',
        ]);

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('livewire.admin.users.user-create');
    }
}
