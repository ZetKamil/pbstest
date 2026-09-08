<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\CurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class OrderDashboard
 * 
 * Livewire component handling real-time order searching, eager-loading,
 * pagination, and currency conversion calculations.
 */
class OrderDashboard extends Component
{
    use WithPagination;

    /**
     * Search query string for filtering orders by user name.
     * Bound in Blade using wire:model.live.debounce.300ms="search".
     *
     * @var string
     */
    public string $search = '';

    /**
     * Reset pagination page whenever the search query is updated.
     * Prevents empty state when searching while on page 2+.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Render the order dashboard component view.
     * 
     * Eager loads 'user' relationship using with('user') to eliminate N+1 queries.
     * Filters orders to only include users matching the search query.
     *
     * @param CurrencyService $currencyService Injected currency conversion service
     * @return \Illuminate\Contracts\View\View
     */
    public function render(CurrencyService $currencyService)
    {
        // Eager loading with('user') prevents the N+1 query problem by loading all users in 1 query.
        // whereHas('user') ensures only orders belonging to matching users are retrieved.
        $orders = Order::with('user')
            ->when(!empty(trim($this->search)), function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->latest('ordered_at')
            ->paginate(10);

        // Retrieve current rates to display in information header
        $rates = $currencyService->getRates();

        return view('livewire.order-dashboard', [
            'orders'          => $orders,
            'currencyService' => $currencyService,
            'rates'           => $rates,
        ]);
    }
}
