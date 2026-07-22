<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecializationRequest;
use App\Services\Dashboard\SpecializationService;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Specialization;

class SpecializationController extends Controller
{
    public function __construct(
        protected SpecializationService $service
    ) {}

    public function index(): View
    {
        return view(
            'admin.specializations.index',
            $this->service->index()
        );
    }

    public function create(): View
    {
        return view('admin.specializations.create');
    }

    public function store(SpecializationRequest $request): RedirectResponse {
        $this->service->store(
            $request->validated()
        );

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization created successfully.');
    }

    public function edit(Specialization $specialization) {
        return view(
            'admin.specializations.edit',
            ['specialization' => $this->service->edit($specialization),]
        );
    }

    public function update(SpecializationRequest $request, Specialization $specialization): RedirectResponse {

        $this->service->update($specialization, $request->validated());
        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization updated successfully.');
    }

    public function destroy(Specialization $specialization)
    {
        try {
            $this->service->destroy($specialization);

            return redirect()
                ->route('admin.specializations.index')
                ->with(
                    'success',
                    'Specialization deleted successfully.'
                );
        } catch (ValidationException $exception) {
            return redirect()
                ->back()
                ->withErrors(
                    $exception->errors()
                );
        }
    }
}
