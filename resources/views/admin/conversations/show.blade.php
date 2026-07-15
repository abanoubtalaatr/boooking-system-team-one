<x-layouts.dashboard title="Conversation">

<div class="max-w-5xl mx-auto">

    <div class="bg-white rounded-xl shadow">

        <div class="border-b p-5">

            <h2 class="font-bold text-xl">

                {{ $conversation->doctor->name }}

            </h2>

            <p class="text-gray-500">

                Patient :
                {{ $conversation->patient->name }}

            </p>

        </div>

        <div class="p-6 space-y-4">

            @foreach($messages as $message)

                @php

                    $doctorMessage =
                        $message->sender_type === App\Models\User::class;

                @endphp

                <div class="flex {{ $doctorMessage ? 'justify-end' : 'justify-start' }}">

                    <div class="max-w-lg rounded-xl px-4 py-3

                        {{ $doctorMessage
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-900' }}">

                        <p>

                            {{ $message->body }}

                        </p>

                        <div class="text-xs mt-2 opacity-70">

                            {{ $message->created_at->format('d M Y h:i A') }}

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        <div class="border-t p-5">

            {{ $messages->links() }}

        </div>

    </div>

</div>

</x-layouts.dashboard>