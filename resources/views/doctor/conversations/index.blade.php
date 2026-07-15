<x-layouts.dashboard title="الاستشارات" role="doctor">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">الاستشارات</h1>
        <p class="mt-1 text-sm text-gray-500">المحادثات النشطة مع المرضى</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        @forelse ($conversations as $conversation)
            @php
                $lastMessage = $conversation->messages->first();
                $unread = $conversation->messages
                    ->where('sender_type', 'App\\Models\\Patient')
                    ->whereNull('read_at')
                    ->count();
            @endphp

            <a href="{{ route('doctor.conversations.show', $conversation) }}"
               class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-semibold text-sm">
                    {{ mb_substr($conversation->patient->name ?? 'م', 0, 1) }}
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-medium text-gray-900 truncate">
                            {{ $conversation->patient->name ?? 'مريض محذوف' }}
                        </span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ $conversation->last_message_at?->diffForHumans() ?? '—' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 truncate">
                        {{ Str::limit($lastMessage->body ?? 'لا توجد رسائل بعد', 60) }}
                    </p>
                </div>

                @if ($unread > 0)
                    <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-blue-600 text-white text-xs font-semibold px-1.5">
                        {{ $unread }}
                    </span>
                @endif
            </a>
        @empty
            <div class="px-5 py-16 text-center text-gray-400 text-sm">
                لا توجد استشارات نشطة حاليًا
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $conversations->links() }}
    </div>
</x-layouts.dashboard>
