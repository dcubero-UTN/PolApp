<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status_filter = '';
    public $showImageModal = false;
    public $selectedImageUrl = '';
    public $showDeleteModal = false;
    public $expenseIdToDelete = null;

    protected $queryString = ['search', 'status_filter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function approve($id)
    {
        if (!Auth::user()->hasRole('admin'))
            return;

        $expense = Expense::find($id);
        $expense->update(['status' => 'aprobado']);
        session()->flash('message', 'Gasto aprobado correctamente.');
    }

    public function reject($id)
    {
        if (!Auth::user()->hasRole('admin'))
            return;

        $expense = Expense::find($id);
        $expense->update(['status' => 'rechazado']);
        session()->flash('message', 'Gasto rechazado.');
    }

    public function toggleReimbursed($id)
    {
        if (!Auth::user()->hasRole('admin'))
            return;

        $expense = Expense::find($id);
        $expense->update(['reimbursed' => !$expense->reimbursed]);
        session()->flash('message', $expense->reimbursed ? 'Marcado como reembolsado.' : 'Marcado como no reembolsado.');
    }

    public function confirmDelete($id)
    {
        $this->expenseIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->expenseIdToDelete = null;
    }

    public function deleteConfirmed()
    {
        if (!$this->expenseIdToDelete)
            return;

        $expense = Expense::find($this->expenseIdToDelete);

        if ($expense) {
            if ($expense->liquidation_id) {
                session()->flash('error', 'No se puede eliminar un gasto que ya ha sido liquidado.');
                $this->cancelDelete();
                return;
            }
            // Security check: only admin can delete anything, user can delete their own if pending
            if (Auth::user()->hasRole('admin') || ($expense->user_id === Auth::id() && $expense->status === 'pendiente')) {
                // Delete attachment if exists
                if ($expense->attachment_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->attachment_path);
                }

                $expense->delete();
                session()->flash('message', 'Gasto eliminado correctamente.');
            } else {
                session()->flash('error', 'No tiene permisos para eliminar este gasto.');
            }
        }

        $this->cancelDelete();
    }

    public function openImage($url)
    {
        $this->selectedImageUrl = $url;
        $this->showImageModal = true;
    }

    public function closeImageModal()
    {
        $this->showImageModal = false;
    }

    public function render()
    {
        $expenses = Expense::with('user')
            ->accessibleBy(Auth::user())
            ->when($this->status_filter, function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('concept', 'like', "%{$this->search}%")
                        ->orWhere('provider', 'like', "%{$this->search}%")
                        ->orWhere('place', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.expense-index', [
            'expenses' => $expenses
        ])->layout('layouts.app');
    }
}
