<x-layouts.dashboard title="تفاصيل الحجز">

<div class="booking-details">

    <div class="page-header">

        <div>

            <h1>تفاصيل الحجز</h1>

            <p>{{ $booking->booking_number }}</p>

        </div>

        <a href="{{ route('admin.bookings') }}" class="back-btn">

            رجوع

        </a>

    </div>


    <div class="details-grid">

        <div class="details-card">

            <h3>بيانات الحجز</h3>

            <table>

                <tr>
                    <td>رقم الحجز</td>
                    <td>{{ $booking->booking_number }}</td>
                </tr>

                <tr>
                    <td>الحالة</td>
                    <td>

                        <span class="status status-{{ $booking->status->value }}">
                            {{ ucfirst($booking->status->value) }}
                        </span>

                    </td>
                </tr>

                <tr>
                    <td>نوع الكشف</td>
                    <td>{{ $booking->consultation_type->value }}</td>
                </tr>

                <tr>
                    <td>السعر</td>
                    <td>{{ number_format($booking->price,2) }} جنيه</td>
                </tr>

                <tr>
                    <td>حالة الدفع</td>
                    <td>{{ ucfirst($booking->payment_status->value) }}</td>
                </tr>

                <tr>
                    <td>التاريخ</td>
                    <td>{{ $booking->booking_date }}</td>
                </tr>

                <tr>
                    <td>الوقت</td>
                    <td>{{ $booking->booking_time }}</td>
                </tr>

            </table>

        </div>



        <div class="details-card">

            <h3>بيانات المريض</h3>

            <table>

                <tr>

                    <td>الاسم</td>

                    <td>{{ $booking->patient->name }}</td>

                </tr>

                <tr>

                    <td>الهاتف</td>

                    <td>{{ $booking->patient->phone }}</td>

                </tr>

                <tr>

                    <td>البريد</td>

                    <td>{{ $booking->patient->email }}</td>

                </tr>

            </table>

        </div>



        <div class="details-card">

            <h3>بيانات الطبيب</h3>

            <table>

                <tr>

                    <td>الاسم</td>

                    <td>{{ $booking->doctor->name }}</td>

                </tr>

                <tr>

                    <td>التخصص</td>

                    <td>{{ $booking->doctor->doctorProfile->specialization->name }}</td>

                </tr>

                <tr>

                    <td>سعر الكشف</td>

                    <td>{{ $booking->doctor->doctorProfile->consultation_fee }}</td>

                </tr>

            </table>

        </div>



        <div class="details-card">

            <h3>الموعد</h3>

            <table>

                <tr>

                    <td>اليوم</td>

                    <td>{{ $booking->slot->day }}</td>

                </tr>

                <tr>

                    <td>من</td>

                    <td>{{ $booking->slot->start_time }}</td>

                </tr>

                <tr>

                    <td>إلى</td>

                    <td>{{ $booking->slot->end_time }}</td>

                </tr>

            </table>

        </div>

    </div>

</div>

</x-layouts.dashboard>
