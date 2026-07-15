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

        {{-- Messages Area --}}
        <div class="flex-1 overflow-y-auto px-5 py-6 space-y-4 bg-gray-50" data-chat-messages data-conversation-id="{{ $conversation->id }}">
            @forelse ($conversation->messages as $message)
                @php
                    $isDoctor = $message->sender_type === \App\Models\User::class;
                    
                    // جلب الرابط الصحيح مباشرة من باكيج Spatie Media Library
                    $fileUrl = $message->getFirstMediaUrl('attachment'); 
                    $hasAttachment = !empty($fileUrl);
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    @endif

                    <div class="max-w-[70%] rounded-2xl px-4 py-2.5 text-sm {{ $isDoctor ? 'bg-blue-600 text-white rounded-bl-sm' : 'bg-white text-gray-800 border border-gray-200 rounded-br-sm' }}">

                        @if ($message->type === 'text')
                            <p class="leading-relaxed">{{ $message->body }}</p>
                        @elseif ($message->type === 'image' && $hasAttachment)
                            <img src="{{ $fileUrl }}" alt="صورة مرسلة" class="rounded-lg max-w-full" onerror="console.error('فشل تحميل الصورة من الرابط:', this.src)">
                        @elseif ($message->type === 'voice' && $hasAttachment)
                            <audio controls src="{{ $fileUrl }}" class="max-w-full" onerror="console.error('فشل تشغيل الصوت من الرابط:', this.src)"></audio>
                        @elseif ($hasAttachment)
                            <a href="{{ $fileUrl }}" target="_blank"
                                class="flex items-center gap-1 underline {{ $isDoctor ? 'text-white' : 'text-blue-600' }}">
                                📎 ملف مرفق
                            </a>
                        @else
                            <p class="leading-relaxed">{{ $message->body }}</p>
                        @endif

                        <span class="block mt-1 text-[11px] {{ $isDoctor ? 'text-blue-100' : 'text-gray-400' }}">
                            {{ $message->created_at->format('h:i A') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="flex h-full items-center justify-center text-gray-400 text-sm" data-no-messages>
                    ابدأ المحادثة بإرسال أول رسالة
                </div>
            @endforelse
        </div>

        {{-- Input Actions --}}
        @if ($conversation->status === 'active')
            <form method="POST" action="{{ route('doctor.conversations.send', $conversation->id) }}"
                enctype="multipart/form-data" class="flex items-end gap-3 border-t border-gray-100 px-5 py-4">
                @csrf

                {{-- زرار إرفاق ملف/صورة --}}
                <label class="shrink-0 cursor-pointer text-gray-400 hover:text-blue-600 p-2" title="إرفاق ملف">
                    <input type="file" name="attachment" accept="image/*,application/pdf,.doc,.docx" class="hidden"
                        onchange="this.form.querySelector('textarea').required = false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </label>

                {{-- زرار تسجيل صوتي --}}
                <button type="button" data-voice-record class="shrink-0 text-gray-400 hover:text-blue-600 p-2"
                    title="تسجيل صوتي">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 11a7 7 0 01-14 0M12 18v3m-4 0h8M12 15a3 3 0 003-3V6a3 3 0 10-6 0v6a3 3 0 003 3z" />
                    </svg>
                </button>

                <textarea name="body" rows="1" placeholder="اكتب رسالتك..."
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('[data-chat-messages]');
            if (!container) return;

            const conversationId = container.dataset.conversationId;

            if (typeof Echo !== 'undefined') {
                Echo.private(`conversation.${conversationId}`)
                    .listen('MessageSent', (event) => {
                        console.log("استقبال رسالة بالزمن الفعلي:", event);
                        
                        const noMsgPlaceholder = document.querySelector('[data-no-messages]');
                        if (noMsgPlaceholder) noMsgPlaceholder.remove();

                        const bubble = buildMessageBubble(event.message);
                        container.insertAdjacentHTML('beforeend', bubble);
                        container.scrollTop = container.scrollHeight;
                    });
            }

            function buildMessageBubble(message) {
                const isDoctor = message.sender_type && message.sender_type.includes('User'); 
                const side = isDoctor ? 'justify-start' : 'justify-end';
                const bg = isDoctor ?
                    'bg-blue-600 text-white rounded-bl-sm' :
                    'bg-white text-gray-800 border border-gray-200 rounded-br-sm';

                let content = '';
                
                // للحصول على الرابط في جافا سكريبت، يفضل أن يرسل الـ Backend رابطاً جاهزاً بالكامل في حقل attachment_url
                const fileUrl = message.attachment_url || message.body;
                const hasAttachment = fileUrl && fileUrl.trim() !== '' && (fileUrl.startsWith('http') || fileUrl.includes('/storage'));

                if (message.type === 'text') {
                    content = `<p class="leading-relaxed">${escapeHtml(message.body)}</p>`;
                } else if (message.type === 'image' && hasAttachment) {
                    content = `<img src="${fileUrl}" alt="صورة مرسلة" class="rounded-lg max-w-full" onerror="console.error('فشل تحميل الصورة في JS:', this.src)">`;
                } else if (message.type === 'voice' && hasAttachment) {
                    content = `<audio controls src="${fileUrl}" class="max-w-full" onerror="console.error('فشل تشغيل الصوت في JS:', this.src)"></audio>`;
                } else if (hasAttachment) {
                    const linkColor = isDoctor ? 'text-white' : 'text-blue-600';
                    content = `
                        <a href="${fileUrl}" target="_blank" class="flex items-center gap-1 underline ${linkColor}">
                            📎 ملف مرفق
                        </a>`;
                } else {
                    content = `<p class="leading-relaxed">${escapeHtml(message.body)}</p>`;
                }

                return `
                <div class="flex ${side}">
                    <div class="max-w-[70%] rounded-2xl px-4 py-2.5 text-sm ${bg}">
                        ${content}
                        <span class="block mt-1 text-[11px] opacity-70">الآن</span>
                    </div>
                </div>`;
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
@endpush