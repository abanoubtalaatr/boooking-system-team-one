<x-layouts.dashboard title="تفاصيل الموعد" role="doctor">

    <div class="max-w-4xl mx-auto">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b px-8 py-6">

                <h1 class="text-2xl font-bold">

                    تفاصيل الموعد

                </h1>

            </div>

            <div class="p-8 grid md:grid-cols-2 gap-6">

                <div>

                    <h3 class="text-sm text-gray-500">

                        الطبيب

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ $availabilitySlot->doctor->name }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        البريد الإلكتروني

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ $availabilitySlot->doctor->email }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        التخصص

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ $availabilitySlot->doctor->doctorProfile?->specialization?->name ?? '-' }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        المستشفى

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ $availabilitySlot->doctor->doctorProfile?->hospital?->name ?? '-' }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        اليوم

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ \Carbon\Carbon::parse($availabilitySlot->day)->format('d/m/Y') }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        من

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ \Carbon\Carbon::parse($availabilitySlot->start_time)->format('h:i A') }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        إلى

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ \Carbon\Carbon::parse($availabilitySlot->end_time)->format('h:i A') }}

                    </p>

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        الحالة

                    </h3>

                    @if($availabilitySlot->is_booked)

                        <span class="mt-2 inline-block rounded-full bg-red-100 text-red-700 px-3 py-1">

                            محجوز

                        </span>

                    @else

                        <span class="mt-2 inline-block rounded-full bg-green-100 text-green-700 px-3 py-1">

                            متاح

                        </span>

                    @endif

                </div>

                <div>

                    <h3 class="text-sm text-gray-500">

                        تاريخ الإنشاء

                    </h3>

                    <p class="font-semibold mt-1">

                        {{ $availabilitySlot->created_at->format('d/m/Y h:i A') }}

                    </p>

                </div>

            </div>

            <div class="border-t px-8 py-5 flex justify-end">

                <a
                    href="{{ route('doctor.availability-slots.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2 hover:bg-gray-50">

                    رجوع

                </a>

            </div>

        </div>

    </div>

</x-layouts.dashboard>