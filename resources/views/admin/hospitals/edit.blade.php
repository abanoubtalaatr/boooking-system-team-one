<x-layouts.dashboard title="تعديل المستشفى" role="admin">

    <div class="max-w-3xl mx-auto">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b border-gray-200 px-8 py-6">

                <h1 class="text-2xl font-bold">
                    تعديل بيانات المستشفى
                </h1>

                <p class="mt-2 text-gray-500">
                    يمكنك تعديل بيانات المستشفى.
                </p>

            </div>

            <form
                action="{{ route('admin.hospitals.update',$hospital) }}"
                method="POST"
                class="p-8 space-y-6">

                @csrf
                @method('PUT')

                <div>

                    <label class="block mb-2 font-medium">
                        اسم المستشفى
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name',$hospital->name) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3">

                </div>

                <div>

                    <label class="block mb-2 font-medium">
                        العنوان
                    </label>

                    <input
                        type="text"
                        name="address"
                        value="{{ old('address',$hospital->address) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3">

                </div>

                <div class="grid md:grid-cols-2 gap-6">

                    <div>

                        <label class="block mb-2 font-medium">
                            Latitude
                        </label>

                        <input
                            type="number"
                            step="0.0000001"
                            name="latitude"
                            value="{{ old('latitude',$hospital->latitude) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3">

                    </div>

                    <div>

                        <label class="block mb-2 font-medium">
                            Longitude
                        </label>

                        <input
                            type="number"
                            step="0.0000001"
                            name="longitude"
                            value="{{ old('longitude',$hospital->longitude) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3">

                    </div>

                </div>

                <div class="flex justify-end gap-3">

                    <a
                        href="{{ route('admin.hospitals.index') }}"
                        class="rounded-lg border border-gray-300 px-6 py-2">

                        رجوع

                    </a>

                    <button
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-white">

                        حفظ التعديلات

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-layouts.dashboard>