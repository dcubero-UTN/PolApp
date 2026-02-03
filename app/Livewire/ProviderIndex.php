<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Provider;
use Livewire\WithPagination;

class ProviderIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $provider_id;
    public $showDeleteModal = false;
    public $providerIdToDelete = null;

    public $name, $contact_name, $phone, $address;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['name', 'contact_name', 'phone', 'address', 'provider_id', 'editMode']);

        if ($id) {
            $this->editMode = true;
            $this->provider_id = $id;
            $provider = Provider::find($id);
            $this->name = $provider->name;
            $this->contact_name = $provider->contact_name;
            $this->phone = $provider->phone;
            $this->address = $provider->address;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if ($this->editMode) {
            Provider::find($this->provider_id)->update([
                'name' => $this->name,
                'contact_name' => $this->contact_name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Proveedor actualizado.');
        } else {
            Provider::create([
                'name' => $this->name,
                'contact_name' => $this->contact_name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Proveedor creado.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->providerIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->providerIdToDelete = null;
    }

    public function deleteConfirmed()
    {
        if (!$this->providerIdToDelete)
            return;

        $provider = Provider::withCount('purchases')->find($this->providerIdToDelete);

        if ($provider) {
            if ($provider->purchases_count > 0) {
                session()->flash('error', 'No se puede eliminar el proveedor porque tiene compras registradas. Elimine primero las compras o manténgalo como referencia.');
            } else {
                $provider->delete();
                session()->flash('message', 'Proveedor eliminado correctamente.');
            }
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $providers = Provider::where('name', 'like', "%{$this->search}%")
            ->orWhere('contact_name', 'like', "%{$this->search}%")
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.provider-index', [
            'providers' => $providers,
        ])->layout('layouts.app');
    }
}
