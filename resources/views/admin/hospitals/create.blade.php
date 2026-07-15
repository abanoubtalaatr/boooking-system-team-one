<x-layouts.dashboard title="إضافة مستشفى" role="admin">

    <div class="max-w-3xl mx-auto">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b border-gray-200 px-8 py-6">

                <h1 class="text-2xl font-bold text-gray-900">
                    إضافة مستشفى جديدة
                </h1>

                <p class="mt-2 text-gray-500">
                    أدخل بيانات المستشفى لإضافتها إلى النظام.
                </p>

            </div>

            <form
                action="{{ route('admin.hospitals.store') }}"
                method="POST"
                class="p-8 space-y-6">

                @csrf

                {{-- اسم المستشفى --}}
                <div>

                    <label class="block mb-2 font-medium text-gray-700">
                        اسم المستشفى
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500">

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                </div>

                {{-- العنوان --}}
                <div>

                    <label class="block mb-2 font-medium text-gray-700">
                        العنوان
                    </label>

                    <input
                        type="text"
                        name="address"
                        value="{{ old('address') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-indigo-500">

                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                </div>

                <div class="grid md:grid-cols-2 gap-6">

                    {{-- Latitude --}}
                    <div>

                        <label class="block mb-2 font-medium text-gray-700">
                            خط العرض (Latitude)
                        </label>

                        <input
                            type="number"
                            step="0.0000001"
                            name="latitude"
                            value="{{ old('latitude') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3">

                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                    </div>

                    {{-- Longitude --}}
                    <div>

                        <label class="block mb-2 font-medium text-gray-700">
                            خط الطول (Longitude)
                        </label>

                        <input
                            type="number"
                            step="0.0000001"
                            name="longitude"
                            value="{{ old('longitude') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3">

                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                    </div>

                </div>

                <div class="flex justify-end gap-3 pt-4">

                    <a
                        href="{{ route('admin.hospitals.index') }}"
                        class="rounded-lg border border-gray-300 px-6 py-2 hover:bg-gray-50">

                        إلغاء

                    </a>

                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700">

                        حفظ المستشفى

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-layouts.dashboard>