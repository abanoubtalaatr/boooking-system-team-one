<x-layouts.dashboard title="الحجوزات">

<div class="booking-page">

    <div class="booking-header">

        <div>
            <h1>الحجوزات</h1>
            <p>إدارة جميع الحجوزات داخل المنصة</p>
        </div>

        <a href="#" class="btn-primary">
            <x-ui-icon name="calendar"/>
            إضافة حجز
        </a>

    </div>


    <form method="GET" class="booking-filters">

        <div class="search-box">

            <x-ui-icon name="search"/>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="ابحث برقم الحجز أو اسم الطبيب أو المريض">

        </div>

        <select name="status">

            <option value="">كل الحالات</option>

            @foreach($statuses as $status)

                <option
                    value="{{ $status->value }}"
                    @selected(request('status')==$status->value)>

                    {{ ucfirst($status->value) }}

                </option>

            @endforeach

        </select>

        <button class="btn-primary">
            بحث
        </button>

    </form>



    <div class="stats-grid">

        <div class="stat-card">

            <div class="stat-icon">
                <x-ui-icon name="calendar"/>
            </div>

            <div>

                <small>إجمالي الحجوزات</small>

                <h2>{{ $stats['total'] }}</h2>

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-icon">
                <x-ui-icon name="clock"/>
            </div>

            <div>

                <small>قيد الانتظار</small>

                <h2>{{ $stats['pending'] }}</h2>

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-icon">
                <x-ui-icon name="doctor"/>
            </div>

            <div>

                <small>مؤكدة</small>

                <h2>{{ $stats['confirmed'] }}</h2>

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-icon">
                <x-ui-icon name="users"/>
            </div>

            <div>

                <small>ملغية</small>

                <h2>{{ $stats['cancelled'] }}</h2>

            </div>

        </div>

    </div>




    <div class="table-card">

        <table class="booking-table">

            <thead>

            <tr>

                <th>#</th>

                <th>رقم الحجز</th>

                <th>المريض</th>

                <th>الطبيب</th>

                <th>التاريخ</th>

                <th>الحالة</th>

                <th></th>

            </tr>

            </thead>

            <tbody>

            @forelse($bookings as $booking)

                <tr>

                    <td>{{ $booking->id }}</td>

                    <td>{{ $booking->booking_number }}</td>

                    <td>{{ $booking->patient->name }}</td>

                    <td>{{ $booking->doctor->name }}</td>

                    <td>{{ $booking->booking_date }}</td>

                    <td>

                        <span class="status status-{{ $booking->status->value }}">

                            {{ ucfirst($booking->status->value) }}

                        </span>

                    </td>

                    <td>

                        <a
                            class="details-btn"
                            href="{{ route('admin.bookings.show',$booking) }}">

                            عرض

                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7">

                        لا توجد حجوزات

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    {{ $bookings->links() }}

</div>

</x-layouts.dashboard>
