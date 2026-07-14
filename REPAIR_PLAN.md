# خطة إصلاح مشروع Booking System

## الهدف

إغلاق جميع ملاحظات مراجعة Pull Request رقم 12، ثم معالجة المشكلات البنيوية المعروفة في المشروع، مع حماية مسارات الأموال وإضافة اختبارات تمنع رجوع الأخطاء.

## ملخص الحالة الحالية

- الفرع المستهدف: `payment`
- مرجع المراجعة: PR `#12`، commit `189ea1e`
- الإطار: Laravel `13.19.0`، PHP `8.3`، Pest `4.7.5`
- قاعدة البيانات: MySQL
- خط الأساس الأخير: المجموعة الكاملة ناجحة؛ 89 اختبارًا، 88 ناجحًا، اختبار واحد متجاوز، 529 assertion
- أعلى المخاطر: تكرار Refund، معالجة Webhook ناجح بمعاملة مختلفة، وتحصيل Cash بعد إلغاء الحجز
- ملف المتابعة الرئيسي: `REPAIR_PLAN.md`
- حالة التنفيذ: أُغلقت ملاحظات PR #12 القابلة للإصلاح محليًا؛ بقي تكامل reconciliation مع واجهة Paymob موثقة، وحالات الحساب غير موجودة حاليًا في مخطط `users`

## مسارات العمل

- الدفع: `app/Services/Payments/`
- الحجز: `app/Actions/Booking/` و`app/Services/BookingService.php`
- المصادقة والمسارات: `app/Http/Controllers/` و`routes/`
- النماذج والمخطط: `app/Models/` و`database/migrations/`
- الاختبارات: `tests/Feature/` و`tests/Unit/`
- دليل التحقق: نتائج أوامر Pest وPint و`git diff --check`

## قواعد التنفيذ

- [ ] تنفيذ أقل تغيير متماسك لكل مشكلة وعدم دمج إصلاحات غير مترابطة في خطوة واحدة.
- [ ] كتابة اختبار regression يفشل قبل كل إصلاح ثم ينجح بعده.
- [ ] عدم إعادة محاولة أي عملية مالية mutating تلقائيًا ما لم يضمن المزود idempotency موثقة.
- [ ] عدم اعتبار timeout أو 5xx نجاحًا أو فشلًا نهائيًا؛ تصنيف النتيجة كحالة غير مؤكدة ثم إجراء reconciliation.
- [ ] استخدام transactions و`lockForUpdate()` عند تغيير حالة الحجز أو الدفع أو المحفظة.
- [ ] عدم تعديل migrations المنفذة؛ إنشاء migrations جديدة بأوامر Artisan.
- [ ] تشغيل `vendor/bin/pint --dirty --format agent` بعد أي تعديل PHP.
- [ ] تحديث هذا الملف ووضع `[x]` فقط بعد وجود دليل اختبار ناجح.

## المرحلة 0: تثبيت خط الأساس

- [x] تشغيل اختبارات الدفع الحالية وتسجيل النتيجة:
  - `php artisan test --compact tests/Feature/BookingPaymentApiTest.php`
  - `php artisan test --compact tests/Feature/PaymentSecurityApiTest.php`
  - `php artisan test --compact tests/Feature/PaymobWebhookTest.php`
  - `php artisan test --compact tests/Feature/PaymobGatewayHttpTest.php`
- [x] تشغيل اختبارات المصادقة الحالية:
  - `php artisan test --compact tests/Feature/WebAuthenticationTest.php`
  - `php artisan test --compact tests/Feature/PatientAuth/PatientAuthTest.php`
- [x] حفظ قائمة المسارات المحمية وغير المحمية من `php artisan route:list --except-vendor -v`.
- [x] التأكد من أن working tree لا يحتوي تغييرات غير مرتبطة قبل بدء الإصلاح.
- [x] بوابة المراجعة: لا تبدأ المرحلة 1 قبل توثيق أي اختبار فاشل موجود مسبقًا وفصله عن أخطاء الإصلاح.

## المرحلة 1: إغلاق الـBlockers المالية في PR #12

### 1.1 منع تكرار Refund عبر HTTP retry

- [x] فصل إعداد HTTP المشترك بحيث يمكن تعطيل retry للعمليات المالية mutating.
- [x] منع `retry()` التلقائي على endpoint الخاص بـvoid/refund.
- [x] إبقاء `connectTimeout` و`timeout` صريحين في عميل Refund.
- [x] تعديل نتيجة بوابة Refund لتميّز بين:
  - نجاح مؤكد.
  - رفض مؤكد لم ينفذ العملية.
  - نتيجة غير معروفة بسبب timeout أو 5xx أو انقطاع الاتصال.
- [x] عدم تحويل النتيجة غير المعروفة إلى `Failed` قابل لإعادة الإرسال مباشرة.
- [x] إضافة اختبار يثبت أن استجابة 500 لا ترسل طلب Refund ثانيًا في نفس الاستدعاء.
- [x] إضافة assertion على عدد طلبات HTTP المرسلة إلى endpoint الخاص بالـRefund.

### 1.2 Reconciliation آمن للاستردادات

- [x] التحقق من وثائق Paymob الحالية؛ لم يظهر endpoint عام موثق لمصالحة Refund العادي، لذلك لا يُفترض endpoint أو تُعاد العملية الغامضة تلقائيًا.
- [x] إضافة حالة `pending_verification` للاسترداد في Enum؛ عمود الحالة نصي ولا يحتاج migration.
- [ ] تعديل `RetryPendingRefunds` بحيث يعيد فحص حالة المزود أولًا بدل إعادة Refund بصورة عمياء.
- [x] منع الـJob من إعادة إرسال Refund ذي الحالة `pending_verification`؛ إعادة الإرسال الآمن بعد reconciliation ما زالت معلقة.
- [ ] تسجيل provider transaction/refund identifiers اللازمة للمصالحة.
- [ ] إضافة اختبارات للسيناريوهات: timeout بعد تنفيذ المزود، رفض مؤكد، نجاح مؤكد، وإعادة تشغيل Job أكثر من مرة.

### 1.3 معالجة Webhook ناجح بمعرّف transaction مختلف

- [x] منع استبدال `provider_transaction_id` المستقر لمعاملة ناجحة بمعرّف جديد.
- [x] إبقاء Webhook المكرر لنفس transaction ID idempotent بلا Wallet credit أو Notification إضافية.
- [x] تحديد سياسة المعاملة الناجحة الثانية: تسجيل تحذير reconciliation، وعدم إلغاء الحجز الأصلي أو عكس رصيده.
- [ ] إضافة سجل دائم للمعاملة المكررة إذا لزم، مع unique constraint على provider transaction ID.
- [ ] رد مبلغ المعاملة الإضافية وحدها أو إرسالها إلى reconciliation، دون تغيير الحجز المؤكد إلى `RefundPending`.
- [x] إضافة اختبار: نجاح transaction A ثم نجاح transaction B لنفس payment reference.
- [x] التحقق في الاختبار أن الحجز يبقى `Confirmed`، والموعد `Booked`، والرصيد يُضاف مرة واحدة، مع تسجيل transaction B للتحقيق.

### 1.4 منع تحصيل Cash بعد الإلغاء

- [x] جعل `WalletService::markCashCollected()` يشترط أن يكون الحجز `Confirmed` والدفع `CashDue` معًا داخل نفس transaction.
- [x] عند إلغاء حجز Cash، تحويل حالة الدفع إلى `Voided` وتحديث `booking.payment_status` أيضًا.
- [x] التأكد من عدم إنشاء Wallet transaction أو إرسال نجاح بعد الإلغاء.
- [x] إضافة اختبار: Cash checkout ثم cancel ثم محاولة cash-collected ترجع `409` ولا تغير المحفظة.
- [x] إضافة اختبار idempotency لتحصيل Cash الصحيح بعد النجاح مرة واحدة.

- [ ] بوابة مراجعة المرحلة 1: تشغيل جميع اختبارات الدفع، والتأكد من عدم وجود طلب مالي mutating يعاد تلقائيًا، ثم مراجعة حالات Booking/Payment/Refund/Wallet كآلة حالات واحدة.

## المرحلة 2: إصلاح الملاحظات المتوسطة في PR #12

### 2.1 إنشاء Payment Intention بصورة آمنة

- [x] إزالة retry التلقائي على 5xx/connection outcome عند إنشاء Intention ما لم تؤكد Paymob idempotency لـ`special_reference`.
- [x] الاحتفاظ بحالة `PendingVerification` عند غموض النتيجة وعدم إنشاء Intention جديدة تلقائيًا.
- [ ] إضافة reconciliation أو استعلام عن Intention السابقة قبل السماح بمحاولة جديدة.
- [x] إضافة اختبارات تؤكد عدم إرسال create-intention مرتين عند connection error أو 500.

### 2.2 تقوية ربط Webhook بالدفع

- [x] رفض Webhook إذا كانت كل المراجع المدعومة فارغة.
- [x] عدم البحث بواسطة `provider_order_id` فارغ.
- [x] استخدام lookup يرفض النتائج المبهمة بدل افتراض uniqueness غير موثقة من Paymob.
- [x] التحقق داخل `validateTransaction()` من تطابق order ID المخزن عند استخدام fallback.
- [ ] إضافة اختبارات: reference فارغ وorder ID فارغ، order ID مجهول، وorder ID يخص Payment آخر.

### 2.3 حماية favorites وsearch history وreviews

- [x] وضع favorites خلف `auth:patient` وsearch history خلف `auth:sanctum` وفق مفتاحه الخارجي الحالي إلى `users`.
- [x] إزالة المستخدم الثابت رقم `1` من `FavoriteService` واستخدام المريض المصادق عليه.
- [ ] حسم ملكية favorites وsearch histories في المخطط: تحويل `user_id` إلى `patient_id` عبر migrations جديدة، أو توثيق سبب إبقائه polymorphic إن تقرر ذلك.
- [ ] كتابة Policies وForm Request authorization للقراءة والتعديل والحذف.
- [x] إبقاء `reviews.index` و`reviews.show` عامين، وحماية عمليات الكتابة فقط.
- [x] حماية إنشاء وتعديل وحذف Review بـ`auth:patient` وربط `patient_id` بالمستخدم المصادق عليه بدل قبوله من body.
- [x] السماح للمريض بتعديل وحذف تقييمه فقط، ومنع انتحال مريض آخر.
- [x] إضافة اختبارات 401 و403 واختبارات ownership للمسارات المتأثرة.

### 2.4 تقوية Web login

- [x] قصر المصادقة على أدوار Dashboard المدعومة: `admin` و`doctor`.
- [ ] منع الحساب الموقوف أو غير المكتمل من الوصول للوحة غير المسموح بها.
- [x] التعامل مع role غير معروف برسالة رفض آمنة بدل `ValueError` أو تحويله ضمنيًا إلى طبيب.
- [ ] إضافة اختبارات للحساب الموقوف وpending profile بعد إضافة حالات الحساب إلى مخطط `users`؛ اختبار الدور غير المدعوم مكتمل.

- [ ] بوابة مراجعة المرحلة 2: فحص `route:list -v` والتأكد من حماية كل mutation، ثم تشغيل اختبارات المصادقة والأمان الجديدة.

## المرحلة 3: توحيد Models والمخطط وقواعد المجال

### 3.1 إصلاح Users وDoctor profiles

- [ ] مقارنة كل حقول `User::$fillable` وcasts مع مخطط `users` الفعلي.
- [ ] إنشاء migration جديدة للحقول المعتمدة فعليًا مثل `status` و`created_by`، أو حذف الاعتماد عليها إن لم تكن جزءًا من المنتج.
- [ ] إضافة index/constraints للأدوار والحالات بحسب الاستخدام.
- [ ] توحيد `consultation_price` و`price` على اسم واحد في Actions وRequests وModels وResources.
- [ ] إضافة اختبار لإنشاء طبيب بواسطة Admin وإكمال الملف ثم approve/suspend.

### 3.2 إزالة ازدواج التخصصات والعلاقات

- [ ] حسم استخدام `Specialization` مقابل `Specialty` بدل وجود Modelين على جدول `specializations` بعلاقات مختلفة.
- [ ] توحيد `specialization_id` مع pivot `doctor_specialty` حسب قرار المجال.
- [ ] إصلاح `$fillable` والعلاقات التي تشير إلى أعمدة غير موجودة مثل `admin_id` و`specialty_id`.
- [ ] إضافة اختبارات علاقات الطبيب والتخصص والمستشفى.

### 3.3 تدقيق الفلاتر والملكية

- [ ] إصلاح فلتر `gender` الذي يحول القيمة النصية إلى boolean.
- [ ] التحقق من فلتر `rating_from` والـaggregate المستخدم معه.
- [ ] مراجعة علاقات Reviews/Favorites للتأكد أن doctor ID يشير إلى `users.id` بصورة متسقة.
- [ ] إضافة اختبارات لكل فلتر مدعوم في API الأطباء.

- [ ] بوابة مراجعة المرحلة 3: تشغيل migrations على قاعدة اختبار نظيفة، ثم `migrate:rollback` للمigrations الجديدة، وتشغيل اختبارات Models وإدارة الأطباء.

## المرحلة 4: توحيد المحادثات والوسائط والبث

- [ ] اختيار تنفيذ واحد فقط للمحادثات بين Controllers المباشرة وطبقة Chat Actions/Services.
- [ ] توحيد أسماء methods مثل `handle`/`execute` و`start`/`startOrGet` وإزالة الكود غير الموصول بعد إثبات عدم استخدامه.
- [ ] توحيد حقول الرسائل على `body` و`sender_id` و`sender_type` المطابقة للمخطط.
- [ ] توحيد Media Library collection على اسم واحد مثل `attachment` في Model وAction وController وResource.
- [ ] إضافة MIME/size validation حسب نوع text/image/voice/file.
- [ ] تحديث Conversation وMessage policies لدعم كل من `Patient` و`User` بصورة صحيحة.
- [ ] تعريف قنوات Broadcasting خاصة بالمحادثات والتحقق من participant authorization.
- [ ] منع تهيئة Laravel Echo/Pusher عندما تكون مفاتيح البث غير مهيأة، أو استكمال إعداد Reverb الصحيح.
- [ ] إضافة اختبارات بدء محادثة، ownership، إرسال نص ومرفق، حذف رسالة، read/seen، وbroadcast authorization.
- [ ] بوابة مراجعة المرحلة 4: اختبارات Chat ناجحة، اسم collection واحد فقط في `rg`، ولا توجد أخطاء Pusher حديثة في browser logs.

## المرحلة 5: استكمال المسارات والتشغيل

- [ ] ربط CRUD الخاص بمواعيد الطبيب بمسارات `auth:sanctum` و`role:doctor` وتفعيل Policies.
- [ ] مراجعة المسارات المكررة أو المؤقتة مثل `api_auth_additions.php` وتعليقات الكود القديمة.
- [ ] إنشاء `public/storage` link في بيئة التشغيل أو توثيقه داخل deployment/setup command المناسب.
- [ ] مراجعة timezone المطلوبة للمشروع وضبطها عبر configuration بدل الاعتماد غير الواضح على بيئة الجهاز.
- [ ] التحقق من إعداد queue worker وscheduler اللازمين لـExpireBookingHolds وRetryPendingRefunds.
- [ ] التحقق من إعداد Reverb/Echo في بيئة التطوير والإنتاج دون كشف أسرار `.env`.
- [ ] بوابة مراجعة المرحلة 5: route smoke test، scheduler/queue dry verification، وفحص browser/backend logs الحديثة.

## المرحلة 6: التحقق النهائي قبل الدمج

- [ ] تشغيل الاختبارات المتأثرة بعد كل مرحلة بأضيق ملف أو filter.
- [ ] تشغيل المجموعة كاملة: `php artisan test --compact`.
- [ ] تشغيل التنسيق: `vendor/bin/pint --dirty --format agent`.
- [ ] تشغيل `git diff --check` ومراجعة diff يدويًا مقابل كل بند في هذه الخطة.
- [ ] تشغيل `composer audit` و`npm audit` وتسجيل النتائج دون تحديث dependencies إلا بموافقة منفصلة.
- [ ] التأكد من عدم وجود secrets أو قيم `.env` في diff.
- [ ] إعادة مراجعة PR #12 والتأكد من إغلاق كل thread بدليل اختبار محدد.
- [ ] بوابة الدمج النهائية: صفر اختبارات فاشلة، صفر Blockers مالية، وكل migrations الجديدة قابلة للتطبيق على قاعدة نظيفة وقاعدة موجودة.

## ترتيب التنفيذ المقترح

1. منع cash collection بعد cancel.
2. فصل Refund عن HTTP retry وتصنيف النتائج غير المؤكدة.
3. إصلاح Webhook ذي transaction ID المختلف.
4. بناء reconciliation للاستردادات والـintentions.
5. حماية المسارات وملكية البيانات.
6. تقوية Web login.
7. توحيد المخطط وModels.
8. توحيد Chat/Media/Broadcasting.
9. استكمال التشغيل والمسارات ثم التحقق النهائي.

## سجل التقدم

- 2026-07-14: إنشاء خطة الإصلاح بناءً على مراجعة PR #12 وفحص المشروع عبر Laravel Boost.
- 2026-07-14: اكتمال المرحلة 0؛ نجحت اختبارات الدفع والمصادقة الأساسية، وحُفظت قائمة 73 route مع middleware.
- 2026-07-14: إصلاح تحصيل Cash بعد الإلغاء في `CancelBookingAction` و`WalletService`، وإضافة اختباري regression وidempotency. دليل التحقق: 25 اختبارًا ناجحًا و218 assertion في مجموعات الدفع المتأثرة، وPint ناجح.
- 2026-07-14: فصل طلب Refund عن HTTP retry، وإضافة `RefundStatus::PendingVerification` واختبار يمنع إعادة الإرسال المباشر أو المجدول عند غموض النتيجة. دليل التحقق: 34 اختبار دفع ناجحًا و272 assertion، وPint ناجح.
- 2026-07-14: اجتازت الدفعة الأولى المجموعة الكاملة: 81 اختبارًا، 80 ناجحًا، اختبار واحد متجاوز، 477 assertion؛ و`git diff --check` ناجح.
- 2026-07-14: إصلاح Webhook لمعاملة ثانية، تعطيل retry لإنشاء intention، تقوية lookup، حماية favorites/search history/reviews، وتقوية Web login. دليل التحقق: 89 اختبارًا، 88 ناجحًا، اختبار واحد متجاوز، 529 assertion؛ وPint و`git diff --check` ناجحان.
- الخطوة التالية خارج ملاحظات PR القابلة للإغلاق محليًا: ربط reconciliation بواجهة Paymob موثقة، ثم تنفيذ المراحل البنيوية 3–5.
