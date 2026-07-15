<x-layouts.dashboard title="لوحة التحكم" role="admin">
   <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        #
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        Doctor
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        Specialization
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        Hospital
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                        Status
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                        Action
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($doctors as $doctor)

                    <tr class="hover:bg-gray-50">

                        <td class="px-6 py-4">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700">
                                    {{ strtoupper(substr($doctor->name,0,1)) }}
                                </div>

                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ $doctor->name }}
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        {{ $doctor->email }}
                                    </p>
                                </div>

                            </div>
                        </td>

                        <td class="px-6 py-4">
                            {{ $doctor->doctorProfile?->specialization?->name ?? '-' }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $doctor->doctorProfile?->hospital?->name ?? '-' }}
                        </td>

                        <td class="px-6 py-4 text-center">

                            @if($doctor->doctorProfile?->is_active)
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                    Active
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                    Inactive
                                </span>
                            @endif

                        </td>

                        <td class="px-6 py-4 text-center">

                            

                            <a
    href="{{ route('admin.doctors.conversations', $doctor) }}"
    class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
>
    View Conversations
</a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No doctors found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="border-t px-6 py-4">
        {{ $doctors->links() }}
    </div>

</div>
</x-layouts.dashboard>
