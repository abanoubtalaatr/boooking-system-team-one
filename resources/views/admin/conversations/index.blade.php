<x-layouts.dashboard title="Doctor Conversations">

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold">
                {{ $doctor->name }}
            </h1>

            <p class="text-gray-500">
                Patients Conversations
            </p>
        </div>

        <div class="bg-white rounded-xl shadow border overflow-hidden">

            <table class="min-w-full">

                <thead class="bg-gray-100">

                    <tr>

                        <th class="px-6 py-4 text-left">
                            Patient
                        </th>

                        <th class="px-6 py-4 text-left">
                            Last Message
                        </th>

                        <th class="px-6 py-4 text-left">
                            Last Activity
                        </th>

                        <th class="px-6 py-4 text-center">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                @forelse($conversations as $conversation)

                    <tr class="border-t hover:bg-gray-50">

                        <td class="px-6 py-4">

                            <div>

                                <div class="font-semibold">

                                    {{ $conversation->patient->name }}

                                </div>

                                <div class="text-sm text-gray-500">

                                    {{ $conversation->patient->email }}

                                </div>

                            </div>

                        </td>

                        <td class="px-6 py-4">

                            {{ Str::limit($conversation->latestMessage?->body,40) }}

                        </td>

                        <td class="px-6 py-4">

                            {{ optional($conversation->last_message_at)->diffForHumans() }}

                        </td>

                        <td class="px-6 py-4 text-center">

                            <a
                                href="{{ route('admin.conversations.show',$conversation) }}"
                                class="rounded-lg bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">

                                Open Chat

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="4" class="text-center py-10">

                            No conversations found.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        {{ $conversations->links() }}

    </div>

</x-layouts.dashboard>