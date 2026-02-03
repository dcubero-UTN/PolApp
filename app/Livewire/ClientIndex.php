<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\DailyVisit;
use Illuminate\Support\Facades\Auth;

class ClientIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $collection_day = '';
    public $seller_id = '';
    public $only_with_balance = false;

    // UI State for Tabs
    public $days = [
        'Lunes' => 'L',
        'Martes' => 'M',
        'Miércoles' => 'M',
        'Jueves' => 'J',
        'Viernes' => 'V',
        'Sábado' => 'S',
        'Domingo' => 'D'
    ];

    public function openPaymentModal($clientId)
    {
        \Illuminate\Support\Facades\Log::info('!!! CLIENT INDEX DISPATCHING openPaymentModal FOR ID: ' . $clientId);
        $this->dispatch('openPaymentModal', clientId: $clientId);
    }

    protected $listeners = ['payment-processed' => '$refresh', 'visit-completed' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'collection_day' => ['except' => ''],
        'seller_id' => ['except' => ''],
        'only_with_balance' => ['except' => false],
    ];

    public function mount()
    {
        \Illuminate\Support\Facades\Log::info('ClientIndex MOUNTED [ID: ' . $this->id() . ']');

        // Default to current day in Spanish ONLY if not provided via query string
        if (empty($this->collection_day) && empty($this->search)) {
            $daysMap = [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                0 => 'Domingo'
            ];
            $today = date('w');
            $this->collection_day = $daysMap[$today] ?? 'Lunes';
        }
    }

    public function setDay($day)
    {
        $this->collection_day = $day;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        if ($this->search) {
            $this->collection_day = '';
        }
    }

    public function delete($id)
    {
        $client = Client::find($id);

        if (Auth::user()->can('delete', $client)) {
            $client->delete();
            session()->flash('message', 'Cliente eliminado correctamente.');
        } else {
            session()->flash('error', 'No tienes permiso para eliminar este cliente.');
        }
    }

    public function render()
    {
        $todayStr = date('Y-m-d');
        $userId = Auth::id();

        $query = Client::query()
            ->search($this->search)
            ->where(function ($q) use ($todayStr) {
                $q->forDay($this->collection_day);

                $daysMap = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];
                if ($this->collection_day == ($daysMap[date('w')] ?? '')) {
                    $q->orWhere('next_visit_date', $todayStr);
                }
            })
            ->when($this->seller_id && Auth::user()->hasRole('admin'), function ($q) {
                $q->where('clients.user_id', $this->seller_id);
            })
            ->when($this->only_with_balance, function ($q) {
                $q->where('clients.current_balance', '>', 0);
            })
            ->addSelect([
                    'is_completed' => DailyVisit::select('completed')
                        ->whereColumn('client_id', 'clients.id')
                        ->where('visit_date', $todayStr)
                        ->limit(1)
                ])
            ->orderBy('is_completed', 'asc')
            ->orderByRaw('hora_cobro IS NULL, hora_cobro ASC')
            ->orderBy('name');

        // Create a base query for stats to avoid duplication and ensure synchronization
        $statsQuery = Client::query()
            ->where(function ($q) use ($todayStr) {
                $q->forDay($this->collection_day);
                $daysMap = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];
                if ($this->collection_day == ($daysMap[date('w')] ?? '')) {
                    $q->orWhere('next_visit_date', $todayStr);
                }
            })
            ->when($this->seller_id && Auth::user()->hasRole('admin'), function ($q) {
                $q->where('clients.user_id', $this->seller_id);
            });

        $statsCount = (clone $statsQuery)->count();

        // Count only completed visits for clients in the current filtered route (any user)
        $completedCount = (clone $statsQuery)
            ->whereHas('dailyVisits', function ($q) use ($todayStr) {
                $q->where('visit_date', $todayStr)
                    ->where('completed', true);
            })
            ->count();

        return view('livewire.client-index', [
            'clients' => $query->paginate(20),
            'sellers' => Auth::user()->hasRole('admin') ? \App\Models\User::role('vendedor')->get() : [],
            'totalRoute' => $statsCount,
            'completedRoute' => $completedCount,
        ])->layout('layouts.app');
    }
}
