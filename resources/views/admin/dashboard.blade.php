<x-layouts.dashboard title="لوحة التحكم" role="admin">
    <div class="breadcrumb">الرئيسية / لوحة التحكم</div>
    <div class="page-head"><div><h1 class="page-title">مرحباً، محمد 👋</h1><p class="page-description">إليك نظرة سريعة على أداء المنصة اليوم.</p></div><button class="primary-button" type="button">+ إضافة حجز جديد</button></div>
    <section class="stats" aria-label="الإحصائيات">
        @foreach ([['إجمالي الحجوزات','1,248','+12.5%','calendar'],['حجوزات اليوم','46','+8.2%','clock'],['الأطباء النشطون','128','+4.1%','doctor'],['إجمالي المرضى','3,864','+15.3%','users']] as [$label,$value,$change,$icon])
            <article class="stat"><div class="stat-top"><div><span class="stat-label">{{ $label }}</span><div class="stat-value">{{ $value }}</div><span class="stat-change">↑ {{ $change }} عن الشهر السابق</span></div><span class="stat-icon"><x-ui-icon :name="$icon" /></span></div></article>
        @endforeach
    </section>
    <section class="dashboard-grid">
        <article class="panel"><div class="panel-head"><h2 class="panel-title">الحجوزات الأسبوعية</h2><span class="field-hint">هذا الأسبوع</span></div><div class="chart" aria-label="رسم تجريبي للحجوزات">@foreach ([45,68,53,82,65,92,72] as $height)<div class="bar bar-height-{{ $height }}"><span>{{ ['س','ح','ن','ث','ر','خ','ج'][$loop->index] }}</span></div>@endforeach</div></article>
        <article class="panel"><div class="panel-head"><h2 class="panel-title">مواعيد اليوم</h2><a class="auth-link" href="#">عرض الكل</a></div>@foreach ([['09:00','سارة محمود','د. أحمد منصور'],['10:30','عمر خالد','د. منى عادل'],['12:00','نور إبراهيم','د. سامح يوسف'],['02:30','حسن علي','د. ريم طارق']] as [$time,$patient,$doctor])<div class="schedule-item"><span class="schedule-time">{{ $time }}</span><span class="schedule-copy"><strong>{{ $patient }}</strong><small>{{ $doctor }}</small></span><span class="status">مؤكد</span></div>@endforeach</article>
    </section>
</x-layouts.dashboard>
