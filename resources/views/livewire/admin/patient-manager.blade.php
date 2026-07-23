<div>
    <div class="mb-5 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm md:flex-row md:items-center">
        <label class="relative min-w-0 flex-1" role="search">
            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400"><x-ui-icon name="search" /></span>
            <input
                class="w-full rounded-lg border border-slate-200 py-3 pr-12 pl-4 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="ابحث بالاسم أو رقم الهاتف أو البريد الإلكتروني"
                aria-label="بحث عن مريض"
            >
        </label>
        @can('patients.create')
            <button class="primary-button" type="button" wire:click="create">إضافة مريض</button>
        @endcan
    </div>

    @if ($successMessage !== '')
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700" role="status">{{ $successMessage }}</div>
    @endif

    <x-admin.paginator
        :per-page-options="$perPageOptions"
        :current-per-page="$perPage"
        per-page-label="عدد المرضى في الصفحة"
        use-wire
        class="mb-3.5"
    />

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm" wire:loading.class="opacity-60" wire:target="search,setPerPage,gotoPage,previousPage,nextPage,save,delete">
        <table class="w-full text-right text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-4">المريض</th>
                    <th class="p-4">الهاتف</th>
                    <th class="p-4">البريد الإلكتروني</th>
                    <th class="p-4">الحجوزات</th>
                    <th class="p-4">التحقق</th>
                    <th class="p-4">تاريخ التسجيل</th>
                    <th class="p-4">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($patients as $patient)
                    <tr wire:key="patient-{{ $patient->id }}">
                        <td class="p-4 font-semibold text-slate-900">{{ $patient->name }}</td>
                        <td class="p-4" dir="ltr">{{ $patient->phone }}</td>
                        <td class="p-4">{{ $patient->email }}</td>
                        <td class="p-4">{{ $patient->patient_bookings_count }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-3 py-1 {{ $patient->isVerified() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $patient->isVerified() ? 'موثق' : 'غير موثق' }}
                            </span>
                        </td>
                        <td class="p-4">{{ $patient->created_at?->format('Y-m-d') }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <a class="inline-flex size-9 items-center justify-center rounded-full bg-cyan-50 text-cyan-700 transition hover:bg-cyan-600 hover:text-white" href="{{ route('admin.patients.show', $patient) }}" aria-label="عرض بروفايل {{ $patient->name }}" title="عرض بروفايل المريض">
                                    <x-ui-icon name="eye" class="size-4" />
                                </a>
                                    @can('patients.update')
                                        <button class="inline-flex size-9 items-center justify-center rounded-full bg-blue-50 text-blue-600 transition hover:bg-blue-100" type="button" wire:click="edit({{ $patient->id }})" aria-label="تعديل {{ $patient->name }}" title="تعديل المريض">
                                            <x-ui-icon name="edit" class="size-4" />
                                        </button>
                                    @endcan
                                    @can('patients.delete')
                                        <button class="inline-flex size-9 items-center justify-center rounded-full bg-red-50 text-red-600 transition hover:bg-red-600 hover:text-white" type="button" wire:click="delete({{ $patient->id }})" wire:confirm="هل أنت متأكد من حذف حساب المريض؟" aria-label="حذف {{ $patient->name }}" title="حذف المريض">
                                            <x-ui-icon name="trash" class="size-4" />
                                        </button>
                                    @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-10 text-center text-slate-500" colspan="7">لا توجد بيانات مرضى مطابقة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5"><x-admin.paginator :paginator="$patients" item-label="مريض" use-wire /></div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/45 p-4" wire:transition>
            <section class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="patient-form-title">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900" id="patient-form-title">{{ $form->patient ? 'تعديل المريض' : 'إضافة مريض' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">أدخل بيانات حساب المريض كاملة.</p>
                    </div>
                    <button class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100" type="button" wire:click="closeForm" aria-label="إغلاق">✕</button>
                </div>

                <form class="grid gap-5" wire:submit="save">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2">الاسم الكامل
                            <input class="rounded-lg border border-slate-200 px-4 py-3" wire:model.blur="form.name" required>
                            @error('form.name')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2">رقم الهاتف
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="tel" wire:model.blur="form.phone" dir="ltr" required>
                            @error('form.phone')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2">البريد الإلكتروني
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="email" wire:model.blur="form.email" dir="ltr" required>
                            @error('form.email')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2">تاريخ الميلاد
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="date" wire:model.blur="form.birthdate">
                            @error('form.birthdate')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2">كلمة المرور {{ $form->patient ? '(اتركها فارغة دون تغيير)' : '' }}
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="password" wire:model="form.password" @required(! $form->patient)>
                            @error('form.password')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2">تأكيد كلمة المرور
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="password" wire:model="form.password_confirmation" @required(! $form->patient)>
                        </label>
                        <label class="grid gap-2">خط العرض
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="number" step="0.0000001" wire:model.blur="form.latitude" dir="ltr">
                            @error('form.latitude')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="grid gap-2">خط الطول
                            <input class="rounded-lg border border-slate-200 px-4 py-3" type="number" step="0.0000001" wire:model.blur="form.longitude" dir="ltr">
                            @error('form.longitude')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <label class="grid gap-2">مسار صورة الملف الشخصي
                        <input class="rounded-lg border border-slate-200 px-4 py-3" wire:model.blur="form.profile_photo" placeholder="مثال: patients/avatar.jpg">
                        @error('form.profile_photo')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                        <input class="size-5 rounded border-slate-300" type="checkbox" wire:model="form.verified">
                        <span><strong class="block">الحساب موثق</strong><small class="text-slate-500">يسمح باعتبار رقم الهاتف والبريد محققين.</small></span>
                    </label>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <button class="secondary-button" type="button" wire:click="closeForm">إلغاء</button>
                        <button class="primary-button" type="submit" wire:loading.attr="disabled" wire:target="save">{{ $form->patient ? 'حفظ التعديلات' : 'إنشاء المريض' }}</button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>
