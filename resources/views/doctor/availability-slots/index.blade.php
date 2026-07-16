<x-layouts.dashboard title="المواعيد المتاحة" role="doctor">

    <div class="space-y-6">

        
        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    إدارة المواعيد
                </h1>

                <p class="mt-1 text-gray-500">
                    إدارة جميع المواعيد الخاصة بك.
                </p>
            </div>

            <a href="{{ route('doctor.availability-slots.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">

                <x-ui-icon name="plus" class="w-5 h-5"/>

                <span>إضافة موعد</span>

            </a>

        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

                <p class="text-sm text-gray-500">
                    إجمالي المواعيد
                </p>

                <h2 class="mt-2 text-3xl font-bold text-gray-900">
                    {{ $totalSlots }}
                </h2>

            </div>

            <div class="rounded-xl border border-green-200 bg-green-50 p-6 shadow-sm">

                <p class="text-sm text-green-700">
                    المواعيد المتاحة
                </p>

                <h2 class="mt-2 text-3xl font-bold text-green-700">
                    {{ $availableSlots }}
                </h2>

            </div>

            <div class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm">

                <p class="text-sm text-red-700">
                    المواعيد المحجوزة
                </p>

                <h2 class="mt-2 text-3xl font-bold text-red-700">
                    {{ $bookedSlots }}
                </h2>

            </div>

        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                #
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                اليوم
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                من
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                إلى
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                الحالة
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600">
                                الإجراءات
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">

                        @forelse($slots as $slot)

                            <tr class="hover:bg-gray-50">

                                <td class="px-6 py-4 text-center font-semibold text-gray-700">
                                    {{ $slots->firstItem() + $loop->index }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    {{ \Carbon\Carbon::parse($slot->day)->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                                </td>

                                <td class="px-6 py-4 text-center">

                                    @if($slot->is_booked)

                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                            محجوز
                                        </span>

                                    @else

                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                            متاح
                                        </span>

                                    @endif

                                </td>

                                <td class="px-6 py-4">

                                    <div class="flex justify-center gap-2">

                                        <a href="{{ route('doctor.availability-slots.show', $slot) }}"
                                           class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200">

                                            <x-ui-icon name="eye" class="h-5 w-5"/>

                                        </a>

                                        @if(!$slot->is_booked)

                                            <a href="{{ route('doctor.availability-slots.edit', $slot) }}"
                                               class="rounded-lg bg-yellow-100 p-2 text-yellow-600 hover:bg-yellow-200">

                                                <x-ui-icon name="edit" class="h-5 w-5"/>

                                            </a>

                                            <form action="{{ route('doctor.availability-slots.destroy', $slot) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('هل تريد حذف هذا الموعد؟')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200">

                                                    <x-ui-icon name="trash" class="h-5 w-5"/>

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">

                                    لا توجد مواعيد حتى الآن.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($slots->hasPages())

                <div class="border-t border-gray-200 px-6 py-4">

                    {{ $slots->links() }}

                </div>

            @endif

        </div>

    </div>

</x-layouts.dashboard>

