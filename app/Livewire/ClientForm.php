<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ClientForm extends Component
{
    public ?Client $client = null;

    public $user_id;
    public $name;
    public $phone_primary;
    public $phone_secondary;
    public $email;
    public $address_details;
    public $collection_day;
    public $collection_frequency;
    public $hora_cobro; // keeping optional logic

    public function mount(Client $client = null)
    {
        if ($client && $client->exists) {
            $this->client = $client;
            $this->user_id = $client->user_id;
            $this->name = $client->name;
            $this->phone_primary = $client->phone_primary;
            $this->phone_secondary = $client->phone_secondary;
            $this->email = $client->email;
            $this->address_details = $client->address_details;
            $this->collection_day = $client->collection_day;
            $this->collection_frequency = $client->collection_frequency;
            $this->hora_cobro = $client->hora_cobro;
        } else {
            // Default logic
            $this->client = new Client();
            $this->collection_frequency = 'Semanal';
            $this->collection_day = 'Lunes';

            // If seller, auto-assign
            if (!Auth::user()->hasRole('admin') && Auth::user()->hasRole('vendedor')) {
                $this->user_id = Auth::id();
            }
        }
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone_primary' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clients', 'phone_primary')->ignore($this->client->id)
            ],
            'address_details' => 'required|string',
            'collection_frequency' => 'required|in:Diario,Semanal,Quincenal,Mensual',
            'user_id' => 'required|exists:users,id',
            'email' => 'nullable|email',
            'phone_secondary' => 'nullable|string',
            'hora_cobro' => 'nullable|date_format:H:i',
        ];

        if ($this->collection_frequency === 'Semanal') {
            $rules['collection_day'] = 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo';
        } elseif ($this->collection_frequency === 'Diario') {
            $rules['collection_day'] = 'nullable';
        } else {
            // For Quincenal and Mensual we might store a specific day or date string
            $rules['collection_day'] = 'required';
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        if (!$this->client->exists) {
            $this->client = new Client();
        }

        $this->client->fill([
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone_primary' => $this->phone_primary,
            'phone_secondary' => $this->phone_secondary,
            'email' => $this->email,
            'address_details' => $this->address_details,
            'collection_day' => $this->collection_day,
            'collection_frequency' => $this->collection_frequency,
            'hora_cobro' => $this->hora_cobro,
        ]);

        $this->client->save();

        session()->flash('message', 'Cliente guardado exitosamente.');

        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.client-form', [
            'sellers' => Auth::user()->hasRole('admin') ? User::role('vendedor')->get() : collect(),
        ])->layout('layouts.app');
    }
}
