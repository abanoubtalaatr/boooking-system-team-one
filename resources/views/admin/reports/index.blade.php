<x-layouts.dashboard title="التقارير والإحصائيات" role="admin">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    التقارير والإحصائيات
                </h1>

                <p class="text-gray-500 mt-1">
                    متابعة الإيرادات والحجوزات والتخصصات الأكثر نشاطاً.
                </p>
            </div>

        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            <form method="GET"
                  action="{{ route('admin.reports') }}"
                  class="grid md:grid-cols-3 gap-4">

                <div>

                    <label class="block mb-2 font-medium">
                        من تاريخ
                    </label>

                    <input
                        type="date"
                        name="from"
                        value="{{ optional($from)->format('Y-m-d') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2">

                </div>

                <div>

                    <label class="block mb-2 font-medium">
                        إلى تاريخ
                    </label>

                    <input
                        type="date"
                        name="to"
                        value="{{ optional($to)->format('Y-m-d') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2">

                </div>

                <div class="flex items-end">

                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">

                        عرض التقرير

                    </button>

                </div>

            </form>

        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b px-6 py-4">

                <h2 class="font-bold text-lg">

                    الإيرادات الشهرية

                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-3 text-right">
                                الشهر
                            </th>

                            <th class="px-6 py-3 text-right">
                                الإيراد
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($revenueByMonth as $row)

                        <tr class="border-t">

                            <td class="px-6 py-4">

                                {{ $row->month }}

                            </td>

                            <td class="px-6 py-4 font-semibold text-green-700">

                                {{ number_format($row->total,2) }} جنيه

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="2"
                                class="py-10 text-center text-gray-500">

                                لا توجد بيانات.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Booking Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b px-6 py-4">

                <h2 class="font-bold text-lg">

                    الحجوزات حسب الحالة

                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-3 text-right">
                                الحالة
                            </th>

                            <th class="px-6 py-3 text-right">
                                العدد
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($bookingsByStatus as $status)

                        <tr class="border-t">

                            <td class="px-6 py-4">

                                @switch($status->status)

                                    @case('pending')
                                        قيد الانتظار
                                        @break

                                    @case('confirmed')
                                        مؤكد
                                        @break

                                    @case('completed')
                                        مكتمل
                                        @break

                                    @case('cancelled')
                                        ملغي
                                        @break

                                    @default
                                        {{ $status->status }}

                                @endswitch

                            </td>

                            <td class="px-6 py-4">

                                {{ $status->total }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="2"
                                class="text-center py-10 text-gray-500">

                                لا توجد بيانات.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Top Specializations --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b px-6 py-4">

                <h2 class="font-bold text-lg">

                    أكثر التخصصات نشاطاً

                </h2>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-3 text-right">

                                التخصص

                            </th>

                            <th class="px-6 py-3 text-right">

                                عدد الأطباء

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($topSpecializations as $specialization)

                        <tr class="border-t">

                            <td class="px-6 py-4">

                                {{ $specialization->name }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $specialization->doctors_count }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="2"
                                class="py-10 text-center text-gray-500">

                                لا توجد تخصصات.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-layouts.dashboard>