<x-layouts.dashboard :title="'بروفايل '.$patient->name" role="admin">
    <div class="breadcrumb">الرئيسية / المرضى / {{ $patient->name }}</div>
    <livewire:admin.patient-profile :patient="$patient" />
</x-layouts.dashboard>
