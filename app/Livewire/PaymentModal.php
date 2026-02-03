<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Payment;
use App\Models\DailyVisit;
use App\Models\CollectionAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class PaymentModal extends Component
{
    public $showModal = false;
    public ?Client $client = null;
    public $sale;
    public $amount = 0;
    public $payment_method = 'efectivo';
    public $reference_number = null;
    public $whatsappLink = null;
    public $last_note = '';
    public $payment_mode = 'payment'; // 'payment' or 'failure'

    // Collection Attempt (No-Payment) fields
    public $attempt_reason = '';
    public $attempt_notes = '';
    public $attempt_latitude = null;
    public $attempt_longitude = null;
    public $next_visit_date = null;

    public function mount()
    {
        \Illuminate\Support\Facades\Log::info('PaymentModal MOUNTED [ID: ' . $this->id() . ']');
    }

    #[On('openPaymentModal')]
    public function openPaymentModal($clientId, $saleId = null)
    {
        \Illuminate\Support\Facades\Log::info('!!! OPEN PAYMENT MODAL CALLED FOR ID: ' . $clientId . ' [Sale ID: ' . ($saleId ?? 'latest') . ']');
        $this->reset(['whatsappLink', 'amount', 'attempt_reason', 'attempt_notes', 'reference_number', 'payment_method']);
        $this->client = Client::find($clientId);

        // Fetch last note recorded
        $lastAttempt = CollectionAttempt::where('client_id', $clientId)
            ->latest()
            ->first();
        $this->last_note = $lastAttempt ? ($lastAttempt->reason . ': ' . $lastAttempt->notes) : 'Sin registros previos';

        // Find specific sale or fall back to oldest unpaid one
        if ($saleId) {
            $this->sale = Sale::find($saleId);
        } else {
            $this->sale = Sale::where('client_id', $clientId)
                ->where('current_balance', '>', 0)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        $this->showModal = true;

        if ($this->sale) {
            $this->payment_mode = 'payment';
            $this->amount = $this->sale->suggested_quota > 0
                ? min($this->sale->suggested_quota, $this->sale->current_balance)
                : $this->sale->current_balance;

            $this->dispatch('capture-gps');
        } else {
            $this->payment_mode = 'failure';
            $this->amount = 0;
        }
    }

    public function updatedPaymentMode($value)
    {
        if ($value === 'failure') {
            $this->amount = 0;
        } elseif ($this->sale) {
            $this->amount = $this->sale->suggested_quota > 0
                ? min($this->sale->suggested_quota, $this->sale->current_balance)
                : $this->sale->current_balance;
        }
    }

    public function finalizeVisit()
    {
        \Illuminate\Support\Facades\Log::info('finalizeVisit called', [
            'amount' => $this->amount,
            'client_id' => $this->client?->id,
            'sale_id' => $this->sale?->id
        ]);

        // Validation logic
        if ($this->payment_mode === 'payment') {
            if ($this->sale && ($this->sale->liquidation_id || $this->sale->status === 'liquidado')) {
                session()->flash('error', 'Esta venta está vinculada a una liquidación cerrada.');
                return;
            }
            $this->validate([
                'amount' => 'required|numeric|min:1|max:' . ($this->sale->current_balance ?? 0),
                'payment_method' => 'required|in:efectivo,sinpe',
            ]);
        } else {
            $this->validate([
                'attempt_reason' => 'required|string',
            ], [
                'attempt_reason.required' => 'Debe indicar el motivo del incumplimiento para guardar este registro.'
            ]);
        }

        $paymentId = null;

        DB::transaction(function () use (&$paymentId) {
            $visitResult = 'incumplimiento';

            // 1. Process Payment if amount > 0
            if ($this->amount > 0) {
                $before = $this->sale->current_balance;
                $after = $before - $this->amount;

                $payment = Payment::create([
                    'sale_id' => $this->sale->id,
                    'user_id' => Auth::id(),
                    'amount' => $this->amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'payment_method' => $this->payment_method,
                    'reference_number' => $this->reference_number,
                ]);
                $paymentId = $payment->id;

                $this->sale->current_balance = $after;
                if ($after <= 0) {
                    $this->sale->status = 'pagado';
                }
                $this->sale->save();

                $this->client->current_balance -= $this->amount;
                $this->client->next_visit_date = null;
                $this->client->next_visit_notes = null;
                $this->client->save();

                $visitResult = 'abono';
            }

            // 2. Record Activity (Collection Attempt / Visit Log)
            CollectionAttempt::create([
                'client_id' => $this->client->id,
                'sale_id' => $this->sale?->id,
                'user_id' => Auth::id(),
                'reason' => $this->amount > 0 ? 'pago_realizado' : $this->attempt_reason,
                'notes' => $this->attempt_notes,
                'latitude' => $this->attempt_latitude,
                'longitude' => $this->attempt_longitude,
            ]);

            // 3. Mark as Visited (Daily Visit)
            DailyVisit::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'client_id' => $this->client->id,
                    'visit_date' => date('Y-m-d'),
                ],
                [
                    'completed' => true,
                    'result' => $visitResult,
                ]
            );

            // 4. Update Next Visit if requested
            if ($this->next_visit_date) {
                $this->client->next_visit_date = $this->next_visit_date;
                $this->client->next_visit_notes = $this->attempt_notes;
                $this->client->save();
            }
        });

        // 5. Generate WhatsApp Receipt Link
        if ($this->amount > 0) {
            $date = date('d/m/Y H:i');
            $message = "✅ *RECIBO DE PAGO #{$paymentId}*\n\n" .
                "Cliente: *{$this->client->name}*\n" .
                "Monto Recibido: *₡" . number_format($this->amount) . "*\n" .
                "Saldo Pendiente: *₡" . number_format($this->sale->current_balance) . "*\n\n" .
                "Método: " . ucfirst($this->payment_method) . "\n" .
                "Fecha: {$date}\n\n" .
                "💬 _Gracias por su preferencia._";

            $phone = preg_replace('/[^0-9]/', '', $this->client->phone_primary);
            $this->whatsappLink = "https://wa.me/506{$phone}?text=" . urlencode($message);
        } else {
            // Confirmation if no payment but visited
            $this->showModal = false;
        }

        $this->dispatch('visit-completed');
    }

    public function close()
    {
        $this->showModal = false;
        $this->reset(['client', 'sale', 'amount', 'whatsappLink', 'last_note', 'attempt_reason', 'attempt_notes', 'reference_number', 'payment_method', 'next_visit_date']);
    }

    public function render()
    {
        \Illuminate\Support\Facades\Log::info('PaymentModal rendering [ID: ' . $this->id() . ']. showModal: ' . ($this->showModal ? 'true' : 'false'));
        return view('livewire.payment-modal');
    }
}
