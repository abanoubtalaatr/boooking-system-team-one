<x-layouts.dashboard title="إضافة تخصص">

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

                    إضافة تخصص جديد

                </h1>

                <p>

                    قم بإدخال بيانات التخصص ثم اضغط حفظ.

                </p>

            </div>

            <a
                href="{{ route('admin.specializations.index') }}"
                class="secondary-button">

                رجوع

            </a>

        </div>

        <div class="form-card">

            <form
                action="{{ route('admin.specializations.store') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                @include(
                    'admin.specializations.form'
                )

            </form>

        </div>

    </section>

</x-layouts.dashboard>
