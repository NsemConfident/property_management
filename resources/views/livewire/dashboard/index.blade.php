<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function mount(): void
    {
        // Redirect based on role
        $user = Auth::user();
        
        if ($user->isTenant()) {
            $this->redirect(route('dashboard.tenant'), navigate: true);
        } elseif ($user->isOwner()) {
            $this->redirect(route('dashboard.owner'), navigate: true);
        } elseif ($user->isManager()) {
            $this->redirect(route('dashboard.manager'), navigate: true);
        }
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col items-center justify-center">
    <p>Loading dashboard...</p>
</div>

