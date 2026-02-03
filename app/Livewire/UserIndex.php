<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Sale;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $userIdToDelete = null;

    public function confirmDelete($id)
    {
        $this->userIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->userIdToDelete = null;
    }

    public function deleteConfirmed()
    {
        if (!$this->userIdToDelete) {
            $this->cancelDelete();
            return;
        }

        $user = User::find($this->userIdToDelete);

        if ($user) {
            // Prevent deleting self
            if ($user->id == auth()->id()) {
                session()->flash('error', 'No puedes eliminarte a ti mismo.');
                $this->cancelDelete();
                return;
            }

            // Check for assigned clients
            if ($user->clients()->exists()) {
                session()->flash('error', 'No se puede eliminar al vendedor porque tiene clientes asignados. Por favor, reasigna los clientes primero.');
                $this->cancelDelete();
                return;
            }

            // Check if user has sales or other relations...
            $salesCount = Sale::where('user_id', $user->id)->count();
            if ($salesCount > 0) {
                session()->flash('error', 'No se puede eliminar un vendedor con ventas registradas. Reasigne sus clientes o inactive la cuenta.');
            } else {
                $user->delete();
                session()->flash('message', 'Usuario eliminado correctamente.');
            }
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $users = User::role('vendedor')
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.user-index', [
            'users' => $users
        ])->layout('layouts.app');
    }
}
