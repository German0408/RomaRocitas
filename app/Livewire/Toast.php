<?php

namespace App\Livewire;

use Livewire\Component;

class Toast extends Component
{
    public $message = '';
    public $type = 'info'; // success, error, warning, info
    public $show = false;

    protected $listeners = ['show-toast' => 'showToast'];

    public function showToast($data)
    {
        $this->message = $data['message'];
        $this->type = $data['type'] ?? 'info';
        $this->show = true;

        // Auto-hide after 3 seconds
        $this->dispatch('hide-toast');
    }

    public function hideToast()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.toast');
    }
}