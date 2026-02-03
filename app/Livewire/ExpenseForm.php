<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseForm extends Component
{
    use WithFileUploads;

    public $date;
    public $place;
    public $provider;
    public $concept;
    public $amount;
    public $category = '';
    public $payment_method = '';
    public $justification;
    public $attachment;

    public $expenseId;
    public $isEdit = false;
    public $existingAttachment;

    protected $rules = [
        'date' => 'required|date',
        'place' => 'required|string|max:255',
        'provider' => 'required|string|max:255',
        'concept' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0.01',
        'category' => 'required|string',
        'payment_method' => 'required|string',
        'justification' => 'nullable|string',
        'attachment' => 'nullable|image|max:5120', // Max 5MB
    ];

    public function mount(Expense $expense = null)
    {
        if ($expense && $expense->exists) {
            if ($expense->status !== 'pendiente') {
                return redirect()->route('expenses.index')->with('error', 'Solo se pueden editar gastos en estado pendiente.');
            }

            // Security check: only owner or admin can edit
            if (!Auth::user()->hasRole('admin') && $expense->user_id !== Auth::id()) {
                abort(403);
            }

            $this->expenseId = $expense->id;
            $this->isEdit = true;
            $this->date = $expense->date->format('Y-m-d');
            $this->place = $expense->place;
            $this->provider = $expense->provider;
            $this->concept = $expense->concept;
            $this->amount = $expense->amount;
            $this->category = $expense->category;
            $this->payment_method = $expense->payment_method;
            $this->justification = $expense->justification;
            $this->existingAttachment = $expense->attachment_path;
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'date' => $this->date,
            'place' => $this->place,
            'provider' => $this->provider,
            'concept' => $this->concept,
            'amount' => $this->amount,
            'category' => $this->category,
            'payment_method' => $this->payment_method,
            'justification' => $this->justification,
        ];

        if ($this->attachment) {
            $data['attachment_path'] = $this->attachment->store('receipts', 'public');
        }

        if ($this->isEdit) {
            $expense = Expense::findOrFail($this->expenseId);

            if ($expense->liquidation_id) {
                session()->flash('error', 'Este gasto ya ha sido liquidado y no puede ser modificado.');
                return redirect()->route('expenses.index');
            }

            if ($expense->status !== 'pendiente') {
                session()->flash('error', 'Este gasto ya no puede ser editado.');
                return redirect()->route('expenses.index');
            }

            $expense->update($data);
            session()->flash('message', 'Gasto actualizado correctamente.');
        } else {
            $data['status'] = 'pendiente';
            Expense::create($data);
            session()->flash('message', 'Gasto registrado correctamente y pendiente de aprobación.');
        }

        return redirect()->route('expenses.index');
    }

    public function render()
    {
        return view('livewire.expense-form')->layout('layouts.app');
    }
}
