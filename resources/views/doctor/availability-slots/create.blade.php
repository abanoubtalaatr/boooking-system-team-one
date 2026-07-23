<x-layouts.dashboard title="إضافة موعد جديد" role="doctor">

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    إضافة موعد جديد
                </h1>

                <p class="mt-1 text-gray-500">
                    قم بإضافة موعد جديد ليكون متاحًا للحجز.
                </p>
            </div>

            <a href="{{ route('doctor.availability-slots.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100">

                رجوع

            </a>

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

        {{-- Form --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <form action="{{ route('doctor.availability-slots.store') }}" method="POST" class="p-6 space-y-6">

                @csrf

                {{-- Day --}}
                <div>

                    <label for="day" class="mb-2 block text-sm font-medium text-gray-700">

                        اليوم

                    </label>

                    <input type="date" id="day" name="day" value="{{ old('day') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">

                </div>

                {{-- Start Time --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        وقت البداية
                    </label>

                    <select name="start_time" class="w-full rounded-lg border border-gray-300 px-4 py-2">

                        @for ($hour = 0; $hour < 24; $hour++)
                            @php
                                $time = sprintf('%02d:00', $hour);
                            @endphp

                            <option value="{{ $time }}" @selected(old('start_time', isset($availabilitySlot) ? \Carbon\Carbon::parse($availabilitySlot->start_time)->format('H:i') : '') == $time)>

                                {{ date('h:i A', strtotime($time)) }}

                            </option>
                        @endfor

                    </select>

                </div>

                {{-- End Time --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        وقت النهاية
                    </label>

                    <select name="end_time" class="w-full rounded-lg border border-gray-300 px-4 py-2">

                        @for ($hour = 1; $hour <= 24; $hour++)
                            @php
                                $time = sprintf('%02d:00', $hour % 24);
                            @endphp

                            <option value="{{ $time }}" @selected(old('end_time', isset($availabilitySlot) ? \Carbon\Carbon::parse($availabilitySlot->end_time)->format('H:i') : '') == $time)>

                                {{ date('h:i A', strtotime($time)) }}

                            </option>
                        @endfor

                    </select>

                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3">

                    <a href="{{ route('doctor.availability-slots.index') }}"
                        class="rounded-lg border border-gray-300 px-5 py-2 text-gray-700 hover:bg-gray-100">

                        إلغاء

                    </a>

                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-medium text-white hover:bg-blue-700">

                        حفظ الموعد

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-layouts.dashboard>
