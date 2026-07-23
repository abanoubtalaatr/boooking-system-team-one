@props(['groups', 'selected' => [], 'disabled' => false])

<div class="grid gap-4" data-permission-groups>
    @foreach ($groups as $groupKey => $group)
        @php($groupPermissions = array_keys($group['permissions']))
        <details class="rounded-xl border border-slate-200 bg-white" open>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 font-bold text-slate-800">
                <span>{{ $group['label'] }}</span>
                <label class="flex items-center gap-2 text-sm font-medium text-blue-700" onclick="event.stopPropagation()">
                    <input type="checkbox" data-permission-group="{{ $groupKey }}" @disabled($disabled)>
                    تحديد المجموعة
                </label>
            </summary>
            <div class="grid gap-3 border-t border-slate-100 p-5 md:grid-cols-2">
                @foreach ($group['permissions'] as $permission => $label)
                    <label class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                        <input name="permissions[]" value="{{ $permission }}" type="checkbox" data-permission-item="{{ $groupKey }}"
                            @checked(in_array($permission, old('permissions', $selected), true)) @disabled($disabled)>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </details>
    @endforeach
</div>
