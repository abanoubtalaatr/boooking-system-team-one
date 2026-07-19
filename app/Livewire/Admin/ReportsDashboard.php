<?php

namespace App\Livewire\Admin;

use App\Services\Reports\AdminReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class ReportsDashboard extends Component
{
    #[Url(as: 'period', except: 'week')]
    public string $period = 'week';

    public function mount(): void
    {
        Gate::authorize('reports.view');
        $this->normalizePeriod();
    }

    public function setPeriod(string $period): void
    {
        abort_unless(in_array($period, ['week', 'forty_days', 'year'], true), 404);

        $this->period = $period;
    }

    public function render(AdminReportService $reports): View
    {
        Gate::authorize('reports.view');
        $this->normalizePeriod();

        return view('livewire.admin.reports-dashboard', [
            'report' => $reports->build($this->period),
        ]);
    }

    private function normalizePeriod(): void
    {
        if (! in_array($this->period, ['week', 'forty_days', 'year'], true)) {
            $this->period = 'week';
        }
    }
}
