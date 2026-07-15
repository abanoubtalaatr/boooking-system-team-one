<x-layouts.dashboard title="المحادثة" role="admin">

<div class="max-w-5xl mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="border-b border-gray-200 p-6 flex items-center justify-between">

            <div>

                <h2 class="text-2xl font-bold text-gray-900">
                    تفاصيل المحادثة
                </h2>

                <div class="mt-2 space-y-1 text-sm text-gray-600">

                    <p>
                        <span class="font-semibold">👨‍⚕️ الطبيب:</span>
                        {{ $conversation->doctor->name }}
                    </p>

                    <p>
                        <span class="font-semibold">🧑‍🤝‍🧑 المريض:</span>
                        {{ $conversation->patient->name }}
                    </p>

                </div>

            </div>

            <a
                href="{{ route('admin.doctors.conversations', $conversation->doctor) }}"
                class="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50">

                رجوع للمحادثات

            </a>

        </div>

        {{-- Messages --}}
        <div class="p-6 space-y-5 bg-gray-50 min-h-[500px]">

            @forelse($messages as $message)

                @php
                    $doctorMessage = $message->sender_type === App\Models\User::class;
                @endphp

                <div class="flex {{ $doctorMessage ? 'justify-end' : 'justify-start' }}">

                    <div class="max-w-lg rounded-2xl px-5 py-3 shadow-sm

                        {{ $doctorMessage
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white border border-gray-200 text-gray-900' }}">

                        <div class="mb-2 text-xs font-semibold opacity-80">

                            {{ $doctorMessage ? '👨‍⚕️ الطبيب' : '🧑‍🤝‍🧑 المريض' }}

                        </div>

                        <p class="leading-7 break-words">

                            {{ $message->body }}

                        </p>

                        <div class="mt-3 text-xs opacity-70">

                            {{ $message->created_at->format('d/m/Y - h:i A') }}

                        </div>

                    </div>

                </div>

            @empty

                <div class="text-center py-12 text-gray-500">

                    لا توجد رسائل في هذه المحادثة.

                </div>

            @endforelse

        </div>

        {{-- Pagination --}}
        <div class="border-t border-gray-200 p-5">

            {{ $messages->links() }}

        </div>

    </div>

</div>

</x-layouts.dashboard>