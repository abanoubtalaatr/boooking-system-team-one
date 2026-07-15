<x-layouts.dashboard title="بيانات المستشفى" role="admin">

    <div class="space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

            <h1 class="text-2xl font-bold text-gray-900">

                {{ $hospital->name }}

            </h1>

            <div class="mt-6 grid md:grid-cols-2 gap-6">

                <div>

                    <h3 class="font-semibold text-gray-700">
                        العنوان
                    </h3>

                    <p class="mt-2 text-gray-600">

                        {{ $hospital->address }}

                    </p>

                </div>

                <div>

                    <h3 class="font-semibold text-gray-700">
                        عدد الأطباء
                    </h3>

                    <p class="mt-2">

                        {{ $hospital->doctorProfiles->count() }}

                    </p>

                </div>

                <div>

                    <h3 class="font-semibold text-gray-700">
                        Latitude
                    </h3>

                    <p class="mt-2">

                        {{ $hospital->latitude ?? '-' }}

                    </p>

                </div>

                <div>

                    <h3 class="font-semibold text-gray-700">
                        Longitude
                    </h3>

                    <p class="mt-2">

                        {{ $hospital->longitude ?? '-' }}

                    </p>

                </div>

            </div>

        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">

            <div class="border-b px-6 py-4">

                <h2 class="text-xl font-bold">
                    الأطباء بالمستشفى
                </h2>

            </div>

            <table class="min-w-full">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-4 text-right">
                            الطبيب
                        </th>

                        <th class="px-6 py-4 text-right">
                            التخصص
                        </th>

                    </tr>

                </thead>

                <tbody>

                @forelse($hospital->doctorProfiles as $profile)

                    <tr class="border-t">

                        <td class="px-6 py-4">

                            {{ $profile->user->name }}

                        </td>

                        <td class="px-6 py-4">

                            {{ $profile->specialization?->name ?? '-' }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="2" class="text-center py-10 text-gray-500">

                            لا يوجد أطباء فى هذه المستشفى.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</x-layouts.dashboard>