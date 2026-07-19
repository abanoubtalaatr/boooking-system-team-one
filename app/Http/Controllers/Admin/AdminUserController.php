<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteAdminUserRequest;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminPermissionsRequest;
use App\Http\Requests\Admin\UpdateAdminStatusRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Services\AdminUserService;
use App\Support\AdminPermissionCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $admins) {}

    public function index(Request $request): View
    {
        $admins = User::query()
            ->role(['admin', 'super-admin'])
            ->with(['roles', 'permissions'])
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['permissionGroups' => AdminPermissionCatalog::groups()]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $admin = $this->admins->create($request->validated(), $request->user(), $this->context($request));

        return redirect()->route('admin.users.edit', $admin)->with('success', 'تم إنشاء حساب الأدمن بنجاح.');
    }

    public function edit(User $admin): View
    {
        abort_unless($admin->isAdmin(), 404);

        return view('admin.users.edit', [
            'admin' => $admin->load(['roles', 'permissions']),
            'permissionGroups' => AdminPermissionCatalog::groups(),
            'selectedPermissions' => $admin->getDirectPermissions()->pluck('name')->all(),
            'isProtectedSuperAdmin' => $admin->id === 1 && $admin->email === 'camila.herman@example.net',
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $admin): RedirectResponse
    {
        $this->admins->update($admin, $request->validated(), $request->user(), $this->context($request));

        return back()->with('success', 'تم تحديث بيانات الأدمن.');
    }

    public function updateStatus(UpdateAdminStatusRequest $request, User $admin): RedirectResponse
    {
        $this->admins->updateStatus($admin, UserStatus::from($request->validated('status')), $request->user(), $this->context($request));

        return back()->with('success', 'تم تحديث حالة حساب الأدمن.');
    }

    public function updatePermissions(UpdateAdminPermissionsRequest $request, User $admin): RedirectResponse
    {
        $this->admins->syncPermissions($admin, $request->validated('permissions'), $request->user(), $this->context($request));

        return back()->with('success', 'تم تحديث صلاحيات الأدمن.');
    }

    public function destroy(DeleteAdminUserRequest $request, User $admin): RedirectResponse
    {
        $this->admins->delete($admin, $request->user(), $this->context($request));

        return redirect()->route('admin.users.index')->with('success', 'تم حذف حساب الأدمن بنجاح.');
    }

    private function context(Request $request): array
    {
        return ['ip_address' => $request->ip(), 'user_agent' => $request->userAgent()];
    }
}
