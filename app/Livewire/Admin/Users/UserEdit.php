<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserEdit extends Component
{
    public User $user;
    public $password = '';
    public $password_confirmation = '';

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function boot()
    {
        $this->withValidator(function ($validator) {
            if ($validator->fails()) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => '¡Error!',
                    'text' => 'El formulario tiene errores.'
                ]);
            }
        });
    }

    public function update()
    {
        $this->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'user.role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_CUSTOMER])],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $oldRole = $this->user->getOriginal('role');

        $this->user->update([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'role' => $this->user->role,
        ]);

        if ($this->password) {
            $this->user->update(['password' => Hash::make($this->password)]);
        }

        // Log activity
        Log::info('User updated', [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
            'action' => 'update',
            'target_user_id' => $this->user->id,
            'target_user_email' => $this->user->email,
            'old_role' => $oldRole,
            'new_role' => $this->user->role,
            'password_changed' => !empty($this->password),
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => '¡Bien hecho!',
            'text' => 'Usuario actualizado correctamente.'
        ]);

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('livewire.admin.users.user-edit');
    }
}