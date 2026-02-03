<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Purchase;
use App\Models\Provider;
use App\Models\PurchasePayment;
use Carbon\Carbon;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AccountsPayableReport extends Component
{
    use WithPagination;

    // Filters
    public $startDate;
    public $endDate;
    public $providerId = '';
    public $onlyPending = true;

    // Payment Modal
    public $showPaymentModal = false;
    public $selectedPurchaseId = null;
    public $selectedPurchase = null;
    public $paymentAmount = 0;
    public $paymentDate;
    public $paymentMethod = 'transferencia';
    public $referenceNumber = '';

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'providerId' => ['except' => ''],
        'onlyPending' => ['except' => false],
    ];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function updating()
    {
        $this->resetPage();
    }

    public function openPaymentModal($purchaseId)
    {
        $this->selectedPurchaseId = $purchaseId;
        $this->selectedPurchase = Purchase::with('payments')->find($purchaseId);
        $this->paymentAmount = $this->selectedPurchase->balance;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->reset(['selectedPurchaseId', 'selectedPurchase', 'paymentAmount', 'referenceNumber']);
    }

    public function recordPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01|max:' . ($this->selectedPurchase->balance ?? 1000000),
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required',
        ]);

        DB::transaction(function () {
            PurchasePayment::create([
                'purchase_id' => $this->selectedPurchaseId,
                'amount' => $this->paymentAmount,
                'payment_date' => $this->paymentDate,
                'payment_method' => $this->paymentMethod,
                'reference_number' => $this->referenceNumber,
            ]);

            $p = Purchase::find($this->selectedPurchaseId);
            $newBalance = $p->balance;

            if ($newBalance <= 0) {
                $p->status = 'paid';
            } else {
                $p->status = 'credit';
            }
            $p->save();
        });

        session()->flash('message', 'Pago registrado correctamente.');
        $this->closePaymentModal();
    }

    public function getTotalDebtProperty()
    {
        // Calculate the sum of balances for all pending/credit purchases
        // We need a more efficient way than mapping, but for now:
        return Purchase::where('status', '!=', 'paid')
            ->get()
            ->sum(fn($p) => $p->balance);
    }

    public function render()
    {
        $query = Purchase::with(['provider', 'payments'])
            ->when($this->startDate, fn($q) => $q->whereDate('purchase_date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('purchase_date', '<=', $this->endDate))
            ->when($this->providerId, fn($q) => $q->where('provider_id', $this->providerId))
            ->when($this->onlyPending, fn($q) => $q->where('status', '!=', 'paid'));

        $purchases = $query->orderBy('purchase_date', 'desc')->paginate(15);
        $providers = Provider::orderBy('name')->get();

        return view('livewire.reports.accounts-payable-report', [
            'purchases' => $purchases,
            'providers' => $providers,
            'totalDebt' => $this->totalDebt
        ])->layout('layouts.app');
    }
}
