<x-layouts.dashboard title="تعديل بيانات الطبيب" role="admin">

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">
            تعديل بيانات الطبيب
        </h1>

        <p class="mt-2 text-gray-500">
            يمكنك تغيير حالة الطبيب أو تحديث التخصص والمستشفى.
        </p>
    </div>

    <form method="POST"
          action="{{ route('admin.doctors.update', $doctor) }}">

        @csrf
        @method('PUT')

        {{-- الاسم --}}
        <div class="mb-6">
            <label class="block mb-2 font-medium text-gray-700">
                اسم الطبيب
            </label>

            <input
                type="text"
                value="{{ $doctor->name }}"
                disabled
                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2">
        </div>

        {{-- البريد الإلكترونى --}}
        <div class="mb-6">
            <label class="block mb-2 font-medium text-gray-700">
                البريد الإلكتروني
            </label>

            <input
                type="email"
                value="{{ $doctor->email }}"
                disabled
                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2">
        </div>

        {{-- التخصص --}}
        <div class="mb-6">
            <label class="block mb-2 font-medium text-gray-700">
                التخصص
            </label>

            <select
                name="specialization_id"
                class="w-full rounded-lg border border-gray-300 px-4 py-2">

                @foreach($specializations as $specialization)

                    <option
                        value="{{ $specialization->id }}"
                        @selected(optional($doctor->doctorProfile)->specialization_id == $specialization->id)>

                        {{ $specialization->name }}

                    </option>

                @endforeach

            </select>

        </div>

        {{-- المستشفى --}}
        <div class="mb-6">
            <label class="block mb-2 font-medium text-gray-700">
                المستشفى
            </label>

            <select
                name="hospital_id"
                class="w-full rounded-lg border border-gray-300 px-4 py-2">

                @foreach($hospitals as $hospital)

                    <option
                        value="{{ $hospital->id }}"
                        @selected(optional($doctor->doctorProfile)->hospital_id == $hospital->id)>

                        {{ $hospital->name }}

                    </option>

                @endforeach

            </select>

        </div>

        {{-- الحالة --}}
        <div class="mb-8">

            <label class="block mb-2 font-medium text-gray-700">
                حالة الحساب
            </label>

            <select
                name="is_active"
                class="w-full rounded-lg border border-gray-300 px-4 py-2">

                <option
                    value="1"
                    @selected(optional($doctor->doctorProfile)->is_active)>
                    نشط
                </option>

                <option
                    value="0"
                    @selected(!optional($doctor->doctorProfile)->is_active)>
                    غير نشط
                </option>

            </select>

        </div>

        <div class="flex justify-end gap-3">

            <a
                href="{{ route('admin.doctors.index') }}"
                class="rounded-lg border border-gray-300 px-6 py-2 hover:bg-gray-50">

                إلغاء

            </a>

            <button
                type="submit"
                class="rounded-lg bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700">

                حفظ التعديلات

            </button>

        </div>

    </form>

</div>

</x-layouts.dashboard>