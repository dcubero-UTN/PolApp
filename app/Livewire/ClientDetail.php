<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Livewire\Attributes\On;

class ClientDetail extends Component
{
    public Client $client;
    public $selected_user_id;

    public function mount(Client $client)
    {
        $this->client = $client->load(['user', 'sales.items.product', 'sales.returns']);
        $this->selected_user_id = $client->user_id;
    }

    public function updatedSelectedUserId($value)
    {
        if (!auth()->user()->hasRole('admin')) {
            return;
        }

        $this->client->update(['user_id' => $value]);
        $this->client->load('user');
        session()->flash('message', 'Vendedor reasignado correctamente.');
    }

    #[On('visit-completed')]
    #[On('return-completed')]
    public function refreshClient()
    {
        $this->client->refresh();
        $this->client->load(['user', 'sales.items.product', 'sales.returns.product']);
    }

    public function render()
    {
        return view('livewire.client-detail', [
            'sellers' => auth()->user()->hasRole('admin') ? \App\Models\User::role('vendedor')->get() : []
        ])->layout('layouts.app');
    }
}
