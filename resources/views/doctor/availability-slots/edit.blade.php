<x-layouts.dashboard title="تعديل الموعد" role="doctor">

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-2xl font-bold text-gray-900">
                    تعديل الموعد
                </h1>

                <p class="mt-1 text-gray-500">
                    يمكنك تعديل اليوم ووقت بداية الموعد فقط، وسيتم احتساب وقت النهاية تلقائيًا.
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

        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">

            <p class="text-sm text-yellow-700">

                مدة الموعد ثابتة (ساعة واحدة)، لذلك سيتم احتساب وقت النهاية تلقائيًا.

            </p>

        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <form action="{{ route('doctor.availability-slots.update',$availabilitySlot) }}"
                  method="POST"
                  class="space-y-6 p-6">

                @csrf
                @method('PUT')

                {{-- Day --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">

                        اليوم

                    </label>

                    <input
                        type="date"
                        name="day"
                        min="{{ now()->format('Y-m-d') }}"
                        value="{{ old('day', $availabilitySlot->day->format('Y-m-d')) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2">

                </div>

                {{-- Start Time --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">

                        وقت البداية

                    </label>

                    <select
                        name="start_time"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2">

                        @for($hour = 0; $hour < 24; $hour++)

                            @php
                                $time = sprintf('%02d:00', $hour);
                            @endphp

                            <option
                                value="{{ $time }}"
                                @selected(old('start_time', \Carbon\Carbon::parse($availabilitySlot->start_time)->format('H:i')) == $time)>

                                {{ date('h:i A', strtotime($time)) }}

                            </option>

                        @endfor

                    </select>

                </div>

                {{-- Current End Time --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-gray-700">

                        وقت النهاية

                    </label>

                    <input
                        type="text"
                        readonly
                        value="{{ \Carbon\Carbon::parse($availabilitySlot->end_time)->format('h:i A') }}"
                        class="w-full rounded-lg border bg-gray-100 px-4 py-2 text-gray-600">

                </div>

                <div class="flex justify-end gap-3">

                    <a href="{{ route('doctor.availability-slots.index') }}"
                       class="rounded-lg border border-gray-300 px-5 py-2">

                        إلغاء

                    </a>

                    <button
                        class="rounded-lg bg-yellow-500 px-5 py-2 text-white hover:bg-yellow-600">

                        حفظ التعديلات

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-layouts.dashboard>