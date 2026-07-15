<x-layouts.dashboard title="إضافة طبيب" role="admin">

<div class="breadcrumb mb-4">
    الرئيسية / إدارة الأطباء / إضافة طبيب
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <h2 class="text-2xl font-bold mb-6">
        إضافة طبيب جديد
    </h2>

    <form action="{{ route('admin.doctors.store') }}" method="POST">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block mb-2 font-medium">الاسم</label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="block mb-2 font-medium">البريد الإلكتروني</label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="block mb-2 font-medium">كلمة المرور</label>

                <input
                    type="password"
                    name="password"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="block mb-2 font-medium">تأكيد كلمة المرور</label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="block mb-2 font-medium">التخصص</label>

                <select
                    name="specialization_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
>

                    @foreach($specializations as $specialization)
                        <option value="{{ $specialization->id }}">
                            {{ $specialization->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">المستشفى</label>

                <select
                    name="hospital_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
>

                    @foreach($hospitals as $hospital)
                        <option value="{{ $hospital->id }}">
                            {{ $hospital->name }}
                        </option>
                    @endforeach

                </select>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-3">

            <a
                href="{{ route('admin.doctors.index') }}"
                class="px-5 py-2 rounded-lg border">
                إلغاء
            </a>

            <button
                class="px-6 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                إنشاء الحساب وإرسال البريد الإلكتروني
            </button>

        </div>

    </form>

</div>

</x-layouts.dashboard>