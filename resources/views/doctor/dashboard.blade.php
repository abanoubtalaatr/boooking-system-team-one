<x-layouts.dashboard title="لوحة الطبيب" role="doctor">
    <div class="breadcrumb">الرئيسية / لوحة الطبيب</div>
    <div class="page-head"><div><h1 class="page-title">صباح الخير، د. أحمد 👋</h1><p class="page-description">لديك 8 مواعيد مؤكدة خلال اليوم.</p></div><button class="primary-button" type="button">عرض جدول اليوم</button></div>
    <section class="stats" aria-label="الإحصائيات">
        @foreach ([['مواعيد اليوم','8','موعدان مكتملان','calendar'],['المرضى هذا الشهر','94','+11.2% عن السابق','users'],['ساعات العمل','32','هذا الأسبوع','clock'],['متوسط التقييم','4.9','من 286 تقييماً','star']] as [$label,$value,$change,$icon])
            <article class="stat"><div class="stat-top"><div><span class="stat-label">{{ $label }}</span><div class="stat-value">{{ $value }}</div><span class="stat-change">{{ $change }}</span></div><span class="stat-icon"><x-ui-icon :name="$icon" /></span></div></article>
        @endforeach
    </section>
    <section class="dashboard-grid">
        <article class="panel"><div class="panel-head"><h2 class="panel-title">نشاط الحجوزات</h2><span class="field-hint">آخر 7 أيام</span></div><div class="chart" aria-label="رسم تجريبي للنشاط">@foreach ([38,57,49,74,62,86,70] as $height)<div class="bar bar-height-{{ $height }}"><span>{{ ['س','ح','ن','ث','ر','خ','ج'][$loop->index] }}</span></div>@endforeach</div></article>
        <article class="panel"><div class="panel-head"><h2 class="panel-title">المواعيد القادمة</h2><a class="auth-link" href="#">الجدول الكامل</a></div>@foreach ([['09:00','سارة محمود','كشف جديد'],['10:30','محمود أحمد','متابعة'],['12:00','ليلى حسن','كشف جديد'],['02:30','خالد سعيد','متابعة']] as [$time,$patient,$type])<div class="schedule-item"><span class="schedule-time">{{ $time }}</span><span class="schedule-copy"><strong>{{ $patient }}</strong><small>{{ $type }}</small></span><span class="status">مؤكد</span></div>@endforeach</article>
    </section>
</x-layouts.dashboard>
