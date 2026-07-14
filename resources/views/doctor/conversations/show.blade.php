<x-layouts.dashboard title="محادثة مع {{ $conversation->patient->name ?? 'مريض' }}" role="doctor">
    <div class="flex flex-col h-[calc(100vh-180px)] bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <a href="{{ route('doctor.conversations') }}"
               class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-100 text-gray-500"
               aria-label="رجوع للاستشارات">
                <x-ui-icon name="chevron" />
            </a>

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-semibold text-sm">
                {{ mb_substr($conversation->patient->name ?? 'م', 0, 1) }}
            </span>

            <div>
                <p class="font-medium text-gray-900">
                    {{ $conversation->patient->name ?? 'مريض محذوف' }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ $conversation->status === 'active' ? 'محادثة نشطة' : 'محادثة مغلقة' }}
                </p>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto px-5 py-6 space-y-4 bg-gray-50" data-chat-messages>
    @forelse ($conversation->messages as $message)
        @php
            $isDoctor = $message->sender_type === \App\Models\User::class;
        @endphp

        <div class="flex {{ $isDoctor ? 'justify-start' : 'justify-end' }} group">

            @if ($isDoctor)
                <form method="POST"
                      action="{{ route('doctor.conversations.messages.destroy', [$conversation->id, $message->id]) }}"
                      onsubmit="return confirm('متأكد إنك عايز تمسح الرسالة دي؟')"
                      class="opacity-0 group-hover:opacity-100 transition-opacity self-center ml-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500" aria-label="حذف الرسالة">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </form>
            @endif

            <div class="max-w-[70%] rounded-2xl px-4 py-2.5 text-sm
                {{ $isDoctor
                    ? 'bg-blue-600 text-white rounded-bl-sm'
                    : 'bg-white text-gray-800 border border-gray-200 rounded-br-sm' }}">

                @if ($message->type === 'text')
                    <p class="leading-relaxed">{{ $message->body }}</p>
                @elseif ($message->type === 'image')
                    <img src="{{ $message->body }}" alt="صورة مرسلة" class="rounded-lg max-w-full">
                @elseif ($message->type === 'voice')
                    <audio controls src="{{ $message->body }}" class="max-w-full"></audio>
                @else
                    <a href="{{ $message->body }}" target="_blank"
                       class="flex items-center gap-1 underline {{ $isDoctor ? 'text-white' : 'text-blue-600' }}">
                        📎 ملف مرفق
                    </a>
                @endif

                <span class="block mt-1 text-[11px] {{ $isDoctor ? 'text-blue-100' : 'text-gray-400' }}">
                    {{ $message->created_at->format('h:i A') }}
                </span>
            </div>
        </div>
    @empty
        <div class="flex h-full items-center justify-center text-gray-400 text-sm">
            ابدأ المحادثة بإرسال أول رسالة
        </div>
    @endforelse
</div>

        {{-- Input --}}
        @if ($conversation->status === 'active')
            <form method="POST"
                  action="{{ route('doctor.conversations.send', $conversation->id) }}"
                  class="flex items-end gap-3 border-t border-gray-100 px-5 py-4">
                @csrf
                <textarea name="body" rows="1" required placeholder="اكتب رسالتك..."
                    class="flex-1 resize-none rounded-lg border border-gray-200 px-4 py-2.5 text-sm text-gray-800
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                <button type="submit"
                    class="shrink-0 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    إرسال
                </button>
            </form>
        @else
            <p class="px-5 py-4 text-center text-sm text-gray-400 border-t border-gray-100">
                تم إغلاق هذه المحادثة
            </p>
        @endif
    </div>
</x-layouts.dashboard>