<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StorefrontLogin extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $showModal = false;

    protected $listeners = ['open-login-modal' => 'openModal'];

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:8',
    ];

    public function openModal()
    {
        $this->showModal = true;
        $this->resetValidation();
        $this->reset(['email', 'password', 'remember']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            // Merge guest cart if exists
            $this->mergeGuestCart();

            $this->closeModal();

            // Redirect to intended page or home
            return redirect()->intended(route('home'));
        }

        $this->addError('email', 'Las credenciales proporcionadas no son correctas.');
    }

    private function mergeGuestCart()
    {
        app(\App\Services\CartService::class)->mergeGuestCart();
    }

    public function render()
    {
        return view('livewire.storefront-login');
    }
}
