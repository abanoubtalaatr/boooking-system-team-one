<x-layouts.dashboard title="محادثات الطبيب" role="admin">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    محادثات الطبيب
                </h1>

                <p class="mt-1 text-gray-500">
                    جميع المحادثات الخاصة بالدكتور
                    <span class="font-semibold text-indigo-600">
                        {{ $doctor->name }}
                    </span>
                </p>
            </div>

            <a
                href="{{ route('admin.doctors.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50">

                ← الرجوع إلى قائمة الأطباء

            </a>

        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            المريض
                        </th>

                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                            آخر رسالة
                        </th>

                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            آخر نشاط
                        </th>

                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                            الإجراء
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-100">

                @forelse($conversations as $conversation)

                    <tr class="hover:bg-gray-50">

                        {{-- Patient --}}
                        <td class="px-6 py-4">

                            <div>

                                <div class="font-semibold text-gray-900">

                                    {{ $conversation->patient->name }}

                                </div>

                                <div class="text-sm text-gray-500">

                                    {{ $conversation->patient->email }}

                                </div>

                            </div>

                        </td>

                        {{-- Last Message --}}
                        <td class="px-6 py-4 text-gray-700">

                            {{ Str::limit($conversation->latestMessage?->body ?? 'لا توجد رسائل', 40) }}

                        </td>

                        {{-- Last Activity --}}
                        <td class="px-6 py-4 text-center text-gray-600">

                            {{ optional($conversation->last_message_at)->diffForHumans() ?? '-' }}

                        </td>

                        {{-- Action --}}
                        <td class="px-6 py-4 text-center">

                            <a
                                href="{{ route('admin.conversations.show', $conversation) }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">

                                <x-ui-icon name="chat" class="w-4 h-4" />

                                عرض المحادثة

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="py-12 text-center text-gray-500">

                            لا توجد محادثات لهذا الطبيب.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="flex justify-center">

            {{ $conversations->links() }}

        </div>

    </div>

</x-layouts.dashboard>