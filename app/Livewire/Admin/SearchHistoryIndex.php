<?php

namespace App\Livewire\Admin;

use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

class SearchHistoryIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    /** @var list<int> */
    public array $perPageOptions = [15, 30, 50, 100];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setPerPage(int $perPage): void
    {
        if (! in_array($perPage, $this->perPageOptions, true)) {
            return;
        }

        $this->perPage = $perPage;
        $this->resetPage();
    }

    public function render()
    {
        $patients = Patient::withCount('searchHistories')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.search-history-index', [
            'patients' => $patients,
        ]);
    }
}
