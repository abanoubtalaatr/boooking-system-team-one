<x-layouts.dashboard title="التخصصات">

    {{-- @push('styles')
        @vite('resources/css/admin/specializations.css')
    @endpush --}}

    <section class="specializations-page">

        <div class="page-header">

            <div>

                <span class="page-label">
                    Dashboard
                </span>

                <h1>

                    التخصصات الطبية

                </h1>

                <p>

                    إدارة جميع التخصصات الموجودة داخل المنصة.

                </p>

            </div>

            <a
                href="{{ route('admin.specializations.create') }}"
                class="primary-button">

                إضافة تخصص

            </a>

        </div>

        <div class="summary-grid">

            <div class="summary-card">

                <span>

                    إجمالي التخصصات

                </span>

                <h2>

                    {{ $totalSpecializations }}

                </h2>

            </div>

        </div>


        <div class="toolbar">

            <form
                action="{{ route('admin.specializations.index') }}"
                method="GET"
                class="search-form">

                <input

                    type="search"

                    name="search"

                    value="{{ request('search') }}"

                    placeholder="ابحث عن تخصص">

                <button>

                    <x-ui-icon name="search"/>

                </button>

            </form>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>#</th>

                        <th>الصورة</th>

                        <th>الاسم</th>

                        <th>عدد الأطباء</th>

                        <th>تاريخ الإنشاء</th>

                        <th width="180">

                            الإجراءات

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($specializations as $specialization)

                        <tr>

                            <td>

                                {{ $specialization->id }}

                            </td>

                            <td>

                                <img src="{{Storage::url($specialization->image)}}"
                                    class="specialization-image"
                                    alt="{{ $specialization->name }}">
                            </td>

                            <td>
                                {{ $specialization->name }}
                            </td>

                            <td>
                                <span class="doctor-badge">
                                    {{ $specialization->doctors_count }}
                                </span>
                            </td>

                            <td>
                                {{ $specialization->created_at->format('d M Y') }}
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.specializations.edit',$specialization) }}" class="edit-btn">
                                        تعديل
                                    </a>
                                    <form method="POST" action="{{ route('admin.specializations.destroy',$specialization) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            onclick="return confirm('هل تريد حذف هذا التخصص؟')"
                                            class="delete-btn">
                                            حذف
                                        </button>
                                    </form>
                                </div>

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <h3>
                                        لا توجد تخصصات
                                    </h3>
                                    <p>
                                        قم بإضافة أول تخصص.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $specializations->links() }}
        </div>
    </section>

</x-layouts.dashboard>
