<x-layouts.dashboard title="إدارة الأطباء" role="admin">

    <div class="breadcrumb mb-4">
        الرئيسية / إدارة الأطباء
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                إدارة الأطباء
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                عرض وإدارة بيانات الأطباء ومتابعة المحادثات الخاصة بهم.
            </p>
        </div>
{{-- Validation Errors --}}
        @if ($errors->any())

            <div class="rounded-lg border border-red-200 bg-red-50 p-4">

                <ul class="list-disc list-inside space-y-1 text-sm text-red-600">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif
        <a
            href="{{ route('admin.doctors.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
        >
            <x-ui-icon name="plus" />
            <span>إضافة طبيب</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>

                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                            #
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                            الطبيب
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                            التخصص
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                            المستشفى
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                            الحالة
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                            الإجراءات
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($doctors as $doctor)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700">
                                        {{ strtoupper(substr($doctor->name, 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $doctor->name }}
                                        </p>

                                        <p class="text-sm text-gray-500">
                                            {{ $doctor->email }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                {{ $doctor->doctorProfile?->specialization?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $doctor->doctorProfile?->hospital?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-center">

                                @if($doctor->doctorProfile?->is_active)
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                        نشط
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                        غير نشط
                                    </span>
                                @endif

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex items-center justify-center gap-2">

                                    {{-- عرض --}}
                                    <a
                                        href="{{ route('admin.doctors.show', $doctor) }}"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-blue-100 hover:text-blue-600 transition"
                                        title="عرض"
                                    >
                                        <x-ui-icon name="eye" />
                                    </a>

                                    {{-- تعديل --}}
                                    <a
                                        href="{{ route('admin.doctors.edit', $doctor) }}"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-amber-100 hover:text-amber-600 transition"
                                        title="تعديل"
                                    >
                                        <x-ui-icon name="edit" />
                                    </a>

                                    {{-- المحادثات --}}
                                    <a
                                        href="{{ route('admin.doctors.conversations', $doctor) }}"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-indigo-100 hover:text-indigo-600 transition"
                                        title="المحادثات"
                                    >
                                        <x-ui-icon name="chat" />
                                    </a>

                                    {{-- حذف --}}
                                    <form
                                        action="{{ route('admin.doctors.destroy', $doctor) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبيب؟')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg p-2 text-slate-500 hover:bg-red-100 hover:text-red-600 transition"
                                            title="حذف"
                                        >
                                            <x-ui-icon name="trash" />
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                لا يوجد أطباء لعرضهم حالياً.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="border-t px-6 py-4">
            {{ $doctors->links() }}
        </div>

    </div>

</x-layouts.dashboard>