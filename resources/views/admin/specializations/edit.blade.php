<x-layouts.dashboard title="تعديل تخصص">

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

                    تعديل تخصص

                </h1>

                <p>

                    قم بتعديل البيانات ثم احفظ.

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

                action="{{ route('admin.specializations.update',$specialization) }}"

                method="POST"

                enctype="multipart/form-data">

                @csrf

                @method('PUT')

                @include(

                    'admin.specializations.form'

                )

            </form>

        </div>

    </section>

</x-layouts.dashboard>
