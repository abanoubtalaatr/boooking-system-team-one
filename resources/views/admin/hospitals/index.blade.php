<x-layouts.dashboard title="إدارة المستشفيات" role="admin">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-2xl font-bold text-gray-900">
                    إدارة المستشفيات
                </h1>

                <p class="mt-1 text-gray-500">
                    عرض وإدارة جميع المستشفيات المسجلة بالنظام.
                </p>

            </div>

            <a
                href="{{ route('admin.hospitals.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700">

                <x-ui-icon name="plus" class="w-5 h-5"/>

                إضافة مستشفى

            </a>

        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">
                                #
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">
                                اسم المستشفى
                            </th>

                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">
                                العنوان
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">
                                عدد الأطباء
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">
                                الإجراءات
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                    @forelse($hospitals as $hospital)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4">

                                {{ $loop->iteration }}

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">

                                        <x-ui-icon name="clinic" class="w-5 h-5 text-indigo-600"/>

                                    </div>

                                    <div>

                                        <div class="font-semibold text-gray-900">

                                            {{ $hospital->name }}

                                        </div>

                                    </div>

                                </div>

                            </td>

                            <td class="px-6 py-4 text-gray-600">

                                {{ $hospital->address }}

                            </td>

                            <td class="px-6 py-4 text-center">

                                <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-indigo-700 text-sm">

                                    {{ $hospital->doctor_profiles_count }}

                                </span>

                            </td>

                            <td class="px-6 py-4">

                                <div class="flex items-center justify-center gap-2">

                                    {{-- Show --}}
                                    <a
                                        href="{{ route('admin.hospitals.show',$hospital) }}"
                                        class="p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200"
                                        title="عرض">

                                        <x-ui-icon name="eye" class="w-5 h-5"/>

                                    </a>

                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('admin.hospitals.edit',$hospital) }}"
                                        class="p-2 rounded-lg bg-yellow-100 text-yellow-600 hover:bg-yellow-200"
                                        title="تعديل">

                                        <x-ui-icon name="edit" class="w-5 h-5"/>

                                    </a>

                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('admin.hospitals.destroy',$hospital) }}"
                                        method="POST"
                                        onsubmit="return confirm('هل أنت متأكد من حذف المستشفى؟')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200">

                                            <x-ui-icon name="trash" class="w-5 h-5"/>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">

                                لا توجد مستشفيات حتى الآن.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="border-t px-6 py-4">

                {{ $hospitals->links() }}

            </div>

        </div>

    </div>

</x-layouts.dashboard>