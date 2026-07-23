<x-layouts.dashboard title="المواعيد المتاحة" role="admin">

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                إدارة المواعيد
            </h1>

            <p class="mt-1 text-gray-500">
                عرض جميع المواعيد الخاصة بالأطباء.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-4 text-right text-xs font-semibold">
                                الطبيب
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold">
                                التخصص
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold">
                                المستشفى
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold">
                                اليوم
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold">
                                من
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold">
                                إلى
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold">
                                الحالة
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold">
                                الإجراءات
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                    @forelse($slots as $slot)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4">

                                <div class="font-semibold">
                                    {{ $slot->doctor->name }}
                                </div>

                                <div class="text-sm text-gray-500">
                                    {{ $slot->doctor->email }}
                                </div>

                            </td>

                            <td class="px-6 py-4">

                                {{ $slot->doctor->doctorProfile?->specialization?->name ?? '-' }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $slot->doctor->doctorProfile?->hospital?->name ?? '-' }}

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

                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs">

                                        محجوز

                                    </span>

                                @else

                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs">

                                        متاح

                                    </span>

                                @endif

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-center">

                                    <a
                                        href="{{ route('admin.availability-slots.show',$slot) }}"
                                        class="p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200">

                                        <x-ui-icon name="eye" class="w-5 h-5"/>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="py-12 text-center text-gray-500">

                                لا توجد مواعيد.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="border-t px-6 py-4">

                {{ $slots->links() }}

            </div>

        </div>

    </div>

</x-layouts.dashboard>