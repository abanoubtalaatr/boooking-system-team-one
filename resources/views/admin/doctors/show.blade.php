<x-layouts.dashboard title="بيانات الطبيب" role="admin">

    <div class="breadcrumb mb-4">
        الرئيسية / إدارة الأطباء / بيانات الطبيب
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="flex items-center justify-between border-b px-6 py-5">

            <div class="flex items-center gap-4">

                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-700">
                    {{ strtoupper(substr($doctor->name,0,1)) }}
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $doctor->name }}
                    </h2>

                    <p class="text-gray-500">
                        {{ $doctor->email }}
                    </p>
                </div>

            </div>

            <a
                href="{{ route('admin.doctors.edit',$doctor) }}"
                class="rounded-lg bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700"
            >
                تعديل
            </a>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">

            <div class="space-y-5">

                <div>
                    <p class="text-sm text-gray-500">التخصص</p>
                    <p class="font-semibold">
                        {{ $doctor->doctorProfile?->specialization?->name ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">المستشفى</p>
                    <p class="font-semibold">
                        {{ $doctor->doctorProfile?->hospital?->name ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">سعر الكشف</p>
                    <p class="font-semibold">
                        {{ $doctor->doctorProfile?->price ?? '-' }} جنيه
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">سنوات الخبرة</p>
                    <p class="font-semibold">
                        {{ $doctor->doctorProfile?->experience_years ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">التقييم</p>
                    <p class="font-semibold">
                        ⭐ {{ number_format($doctor->doctorProfile?->rating ?? 0,1) }}
                    </p>
                </div>

            </div>

            <div class="space-y-5">

                <div>
                    <p class="text-sm text-gray-500">المؤهل</p>
                    <p class="font-semibold">
                        {{ $doctor->doctorProfile?->education ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">اللغة</p>
                    <p class="font-semibold">
                        {{ $doctor->doctorProfile?->language ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">الحالة</p>

                    @if($doctor->doctorProfile?->is_active)
                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">
                            نشط
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm">
                            غير نشط
                        </span>
                    @endif

                </div>

            </div>

        </div>

        <div class="border-t p-6">

            <h3 class="font-bold text-lg mb-3">
                نبذة عن الطبيب
            </h3>

            <p class="leading-8 text-gray-700">
                {{ $doctor->doctorProfile?->bio ?: 'لا توجد نبذة.' }}
            </p>

        </div>

    </div>

</x-layouts.dashboard>