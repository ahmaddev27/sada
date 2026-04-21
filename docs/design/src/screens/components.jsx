/* global React, Icon, Toggle, Flag, Avatar, Sparkline, ImgPlaceholder,
   Alert, Modal, ConfirmModal, useToast */
// صفحة مكتبة المكونات (Components Library) — مرجع بصري لكل عناصر التصميم.

const { useState: uS_c } = React;

const Section = ({ id, title, subtitle, children }) => (
  <section id={id} className="cmp-section">
    <div className="cmp-section-head">
      <h2>{title}</h2>
      {subtitle && <p>{subtitle}</p>}
    </div>
    <div className="cmp-section-body">{children}</div>
  </section>
);

const Demo = ({ label, code, children, flex = false }) => (
  <div className="demo-card">
    <div className="demo-head">
      <div className="demo-label">{label}</div>
      {code && <div className="demo-code mono">{code}</div>}
    </div>
    <div className={`demo-canvas ${flex ? 'demo-canvas-flex' : ''}`}>{children}</div>
  </div>
);

const ComponentsScreen = () => {
  const toast = useToast();
  const [togOn, setTogOn] = uS_c(true);
  const [chipSel, setChipSel] = uS_c('instagram');
  const [seg, setSeg] = uS_c('week');
  const [showModal, setShowModal] = uS_c(false);
  const [showConfirm, setShowConfirm] = uS_c(false);
  const [showDangerConfirm, setShowDangerConfirm] = uS_c(false);
  const [alertOpen, setAlertOpen] = uS_c(true);

  const sections = [
    { id: 'colors', label: 'الألوان' },
    { id: 'typography', label: 'الخطوط' },
    { id: 'buttons', label: 'الأزرار' },
    { id: 'inputs', label: 'المدخلات' },
    { id: 'badges', label: 'الشارات والشِّبس' },
    { id: 'cards', label: 'البطاقات' },
    { id: 'feedback', label: 'التنبيهات والإشعارات' },
    { id: 'modals', label: 'النوافذ المنبثقة' },
    { id: 'data', label: 'عرض البيانات' },
    { id: 'nav', label: 'التنقل' },
    { id: 'media', label: 'الوسائط' },
  ];

  const copyJsx = (txt) => {
    navigator.clipboard?.writeText(txt);
    toast.success('تم النسخ', 'الكود جاهز في الحافظة');
  };

  return (
    <div className="cmp-wrap">
      {/* Intro */}
      <div className="cmp-intro">
        <div>
          <div className="badge badge-brand" style={{ marginBottom: 12 }}>
            <Icon name="palette" size={12} /> مكتبة المكونات
          </div>
          <h1 className="cmp-h1">مرجع التصميم — Sada Design System</h1>
          <p className="cmp-lead">
            كل المكونات المستخدمة في المنصة في مكان واحد — استخدمها كمرجع للتطوير،
            أو انسخها مباشرةً في أي صفحة جديدة.
          </p>
        </div>
        <div className="cmp-intro-actions">
          <button className="btn btn-secondary" onClick={() => toast.info('قريباً', 'سيتم إضافة تصدير Figma')}>
            <Icon name="download" size={14} /> تصدير Figma
          </button>
          <button className="btn btn-primary" onClick={() => copyJsx('<Button />')}>
            <Icon name="copy" size={14} /> نسخ الـ tokens
          </button>
        </div>
      </div>

      <div className="cmp-layout">
        {/* TOC */}
        <aside className="cmp-toc">
          <div className="cmp-toc-label">المحتويات</div>
          {sections.map(s => (
            <a key={s.id} href={`#${s.id}`} className="cmp-toc-link">{s.label}</a>
          ))}
        </aside>

        <div className="cmp-main">
          {/* === Colors === */}
          <Section id="colors" title="الألوان" subtitle="نظام ألوان صدى: الأخضر الزمردي الأساسي + ألوان الرمال الدافئة.">
            <div className="cmp-subhead">العلامة — Sada Green</div>
            <div className="swatch-row">
              {[50,100,200,300,400,500,600,700,800,900].map(n => (
                <div key={n} className="swatch">
                  <div className="swatch-chip" style={{ background: `var(--sada-${n})` }} />
                  <div className="swatch-name">sada-{n}</div>
                </div>
              ))}
            </div>

            <div className="cmp-subhead">الرمال — Sand</div>
            <div className="swatch-row">
              {[50,100,200,300,400,500,600,700].map(n => (
                <div key={n} className="swatch">
                  <div className="swatch-chip" style={{ background: `var(--sand-${n})` }} />
                  <div className="swatch-name">sand-{n}</div>
                </div>
              ))}
            </div>

            <div className="cmp-subhead">الحالات الدلالية</div>
            <div className="swatch-row">
              {[
                ['success', 'نجاح'], ['warning', 'تحذير'],
                ['error', 'خطأ'], ['info', 'معلومة'],
              ].map(([k, ar]) => (
                <div key={k} className="swatch">
                  <div className="swatch-chip" style={{ background: `var(--${k})` }} />
                  <div className="swatch-name">{ar}</div>
                </div>
              ))}
            </div>
          </Section>

          {/* === Typography === */}
          <Section id="typography" title="الخطوط والنصوص" subtitle="الخط الأساسي Tajawal — يدعم العربية بكل أوزانها.">
            <div className="type-stack">
              <div className="type-row">
                <div className="type-sample" style={{ fontSize: 40, fontWeight: 800, lineHeight: 1.15 }}>
                  العنوان الرئيسي — H1
                </div>
                <div className="type-meta mono">40px · 800 · line 1.15</div>
              </div>
              <div className="type-row">
                <div className="type-sample" style={{ fontSize: 28, fontWeight: 700 }}>
                  عنوان فرعي — H2
                </div>
                <div className="type-meta mono">28px · 700</div>
              </div>
              <div className="type-row">
                <div className="type-sample" style={{ fontSize: 20, fontWeight: 700 }}>
                  عنوان قسم — H3
                </div>
                <div className="type-meta mono">20px · 700</div>
              </div>
              <div className="type-row">
                <div className="type-sample" style={{ fontSize: 16, fontWeight: 600 }}>
                  عنوان بطاقة — H4
                </div>
                <div className="type-meta mono">16px · 600</div>
              </div>
              <div className="type-row">
                <div className="type-sample" style={{ fontSize: 14 }}>
                  نص الفقرة الأساسية — يستخدم في معظم المحتوى ويراعي قراءة مريحة.
                </div>
                <div className="type-meta mono">14px · 400</div>
              </div>
              <div className="type-row">
                <div className="type-sample" style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                  نص ثانوي / caption — للتفاصيل والمساعدة.
                </div>
                <div className="type-meta mono">12px · muted</div>
              </div>
              <div className="type-row">
                <div className="type-sample mono" style={{ fontSize: 13 }}>
                  1,240 SAR · +12.4%
                </div>
                <div className="type-meta mono">mono · tabular</div>
              </div>
            </div>
          </Section>

          {/* === Buttons === */}
          <Section id="buttons" title="الأزرار" subtitle="خمس صيغ أساسية + ثلاثة أحجام + حالات.">
            <div className="demo-grid">
              <Demo label="Primary" code="<button class='btn btn-primary'>">
                <button className="btn btn-primary"><Icon name="sparkle" size={14}/> توليد منشور</button>
              </Demo>
              <Demo label="Secondary" code="btn-secondary">
                <button className="btn btn-secondary">إلغاء</button>
              </Demo>
              <Demo label="Ghost" code="btn-ghost">
                <button className="btn btn-ghost">عرض المزيد</button>
              </Demo>
              <Demo label="Accent Soft" code="btn-accent-soft">
                <button className="btn btn-accent-soft"><Icon name="plus" size={14}/> إضافة</button>
              </Demo>
              <Demo label="Danger" code="btn-danger">
                <button className="btn btn-danger"><Icon name="trash" size={14}/> حذف</button>
              </Demo>
              <Demo label="Disabled" code="disabled">
                <button className="btn btn-primary" disabled>غير متاح</button>
              </Demo>
            </div>

            <div className="cmp-subhead">الأحجام</div>
            <div className="row" style={{ flexWrap: 'wrap', alignItems: 'center' }}>
              <button className="btn btn-primary btn-sm">صغير (sm)</button>
              <button className="btn btn-primary">عادي</button>
              <button className="btn btn-primary btn-lg">كبير (lg)</button>
              <button className="btn btn-primary btn-xl">ضخم (xl)</button>
            </div>

            <div className="cmp-subhead">زر أيقونة</div>
            <div className="row" style={{ alignItems: 'center' }}>
              <button className="btn btn-icon btn-ghost"><Icon name="bell"/></button>
              <button className="btn btn-icon btn-secondary"><Icon name="settings"/></button>
              <button className="btn btn-icon btn-primary"><Icon name="plus"/></button>
              <button className="btn btn-icon btn-icon-sm btn-ghost"><Icon name="x" size={14}/></button>
            </div>
          </Section>

          {/* === Inputs === */}
          <Section id="inputs" title="حقول الإدخال" subtitle="كلها تدعم RTL وحالات التركيز والخطأ.">
            <div className="grid-2">
              <div className="input-group">
                <label className="input-label">اسم المتجر</label>
                <input className="input" placeholder="مثال: متجر أنيق" defaultValue="متجر أنيق" />
                <div className="input-hint">سيظهر في كل المنشورات المولّدة.</div>
              </div>
              <div className="input-group">
                <label className="input-label">البريد الإلكتروني</label>
                <input className="input" placeholder="you@brand.sa" />
              </div>
              <div className="input-group">
                <label className="input-label">القائمة المنسدلة</label>
                <select className="select" defaultValue="sa">
                  <option value="sa">🇸🇦 السعودية</option>
                  <option value="ae">🇦🇪 الإمارات</option>
                  <option value="kw">🇰🇼 الكويت</option>
                </select>
              </div>
              <div className="input-group">
                <label className="input-label">حقل نصي طويل</label>
                <textarea className="textarea" placeholder="اكتب وصف الحملة هنا..."></textarea>
              </div>
            </div>

            <div className="cmp-subhead">Toggle / Switch</div>
            <div className="row" style={{ alignItems: 'center', gap: 24 }}>
              <label style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer' }}>
                <Toggle on={togOn} onChange={setTogOn} />
                <span style={{ fontSize: 14, fontWeight: 500 }}>نشر تلقائي</span>
              </label>
              <label style={{ display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer' }}>
                <Toggle on={false} onChange={() => {}} />
                <span style={{ fontSize: 14, color: 'var(--text-muted)' }}>إيقاف</span>
              </label>
            </div>
          </Section>

          {/* === Badges & Chips === */}
          <Section id="badges" title="الشارات والشِّبس (Badges & Chips)">
            <div className="cmp-subhead">الشارات — للحالة</div>
            <div className="row" style={{ flexWrap: 'wrap' }}>
              <span className="badge badge-success"><span className="dot dot-success"/> مُنشور</span>
              <span className="badge badge-warning">قيد المراجعة</span>
              <span className="badge badge-error">فشل النشر</span>
              <span className="badge badge-info">مسودة</span>
              <span className="badge badge-brand"><Icon name="sparkle" size={10}/> جديد</span>
              <span className="badge badge-sand">موسم رمضان</span>
              <span className="badge badge-neutral">أرشيف</span>
            </div>

            <div className="cmp-subhead">الشِّبس — قابلة للاختيار</div>
            <div className="row" style={{ flexWrap: 'wrap' }}>
              {['instagram','facebook','tiktok','snapchat','x'].map(c => (
                <div
                  key={c}
                  className="chip"
                  data-selected={chipSel === c}
                  onClick={() => setChipSel(c)}
                >
                  {c === 'instagram' && <Icon name="instagram" size={14}/>}
                  {c === 'facebook' && <Icon name="facebook" size={14}/>}
                  <span style={{ textTransform: 'capitalize' }}>{c}</span>
                </div>
              ))}
            </div>

            <div className="cmp-subhead">Segmented Control</div>
            <div className="segmented">
              {['day','week','month'].map(v => (
                <button key={v} data-active={seg === v} onClick={() => setSeg(v)}>
                  {v === 'day' ? 'يومي' : v === 'week' ? 'أسبوعي' : 'شهري'}
                </button>
              ))}
            </div>
          </Section>

          {/* === Cards === */}
          <Section id="cards" title="البطاقات">
            <div className="grid-2">
              <div className="card">
                <div className="card-head">
                  <div>
                    <h3>بطاقة أساسية</h3>
                    <div className="sub">مع رأس وجسم</div>
                  </div>
                  <button className="btn btn-ghost btn-sm">عرض الكل</button>
                </div>
                <div className="card-body">
                  محتوى البطاقة. يستخدم لعرض معلومة مجمّعة أو قائمة.
                </div>
              </div>

              <div className="kpi-card">
                <div className="kpi-label"><Icon name="eye" size={13}/> الانطباعات</div>
                <div className="kpi-value">48,230</div>
                <div className="kpi-delta delta-up"><Icon name="up" size={12}/> +12.4%</div>
                <div className="kpi-spark">
                  <Sparkline values={[20,22,19,24,28,26,31,30,34]} />
                </div>
              </div>

              <div className="card card-hoverable">
                <div className="card-body">
                  <div className="row" style={{ alignItems: 'center', marginBottom: 10 }}>
                    <Avatar name="متجر أنيق" size={36} />
                    <div style={{ flex: 1 }}>
                      <div style={{ fontWeight: 700, fontSize: 14 }}>متجر أنيق</div>
                      <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>منذ ساعتين</div>
                    </div>
                    <span className="badge badge-success"><span className="dot dot-success"/> نشط</span>
                  </div>
                  <ImgPlaceholder label="صورة المنشور" h={140} />
                </div>
              </div>

              <div className="card" style={{ background: 'var(--bg-seasonal-soft)', borderColor: 'var(--sand-200)' }}>
                <div className="card-body">
                  <div className="badge badge-sand" style={{ marginBottom: 8 }}>🌙 موسمي</div>
                  <h3 style={{ margin: 0, marginBottom: 6 }}>رمضان يقترب</h3>
                  <p style={{ margin: 0, color: 'var(--text-muted)', fontSize: 13 }}>
                    ابدأ بإعداد حملتك قبل بداية الشهر الكريم بأسبوعين.
                  </p>
                </div>
              </div>
            </div>
          </Section>

          {/* === Feedback === */}
          <Section id="feedback" title="التنبيهات والإشعارات" subtitle="Alerts ثابتة + Toasts تظهر وتختفي.">
            <div className="cmp-subhead">Alerts — تنبيهات مضمّنة</div>
            <div className="stack">
              {alertOpen && (
                <Alert variant="info" title="معلومة" onClose={() => setAlertOpen(false)}>
                  ربط حساب إنستغرام سيفعّل النشر التلقائي لكل منشوراتك القادمة.
                </Alert>
              )}
              <Alert variant="success" title="تم بنجاح">
                نُشر منشور «عرض نهاية الأسبوع» على إنستغرام وفيسبوك.
              </Alert>
              <Alert variant="warning" title="تنبيه رصيد">
                تبقّى 12% من توكنز هذا الشهر — قم بالشحن قبل نفاد الرصيد.
              </Alert>
              <Alert variant="error" title="فشل النشر">
                انتهت صلاحية ربط Meta. يرجى إعادة المصادقة من الإعدادات.
              </Alert>
              <Alert variant="brand" title="ميزة جديدة">
                أصبح بإمكانك الآن توليد فيديو قصير من صورة ثابتة بنقرة واحدة.
              </Alert>
            </div>

            <div className="cmp-subhead">Toasts — إشعارات عابرة</div>
            <div className="row" style={{ flexWrap: 'wrap' }}>
              <button className="btn btn-secondary" onClick={() => toast.success('تم الحفظ', 'حفظنا التغييرات على إعداداتك.')}>
                <Icon name="check" size={14}/> Toast نجاح
              </button>
              <button className="btn btn-secondary" onClick={() => toast.error('خطأ في النشر', 'تأكد من اتصال الإنترنت وأعد المحاولة.')}>
                <Icon name="x" size={14}/> Toast خطأ
              </button>
              <button className="btn btn-secondary" onClick={() => toast.warning('تحذير', 'سيتم نشر المحتوى خلال 30 ثانية.')}>
                <Icon name="info" size={14}/> Toast تحذير
              </button>
              <button className="btn btn-secondary" onClick={() => toast.info('معلومة', 'لديك 3 مسودات بانتظار المراجعة.')}>
                <Icon name="info" size={14}/> Toast معلومة
              </button>
            </div>
          </Section>

          {/* === Modals === */}
          <Section id="modals" title="النوافذ المنبثقة (Modals & Confirms)">
            <div className="row" style={{ flexWrap: 'wrap' }}>
              <button className="btn btn-primary" onClick={() => setShowModal(true)}>
                <Icon name="edit" size={14}/> فتح Modal عام
              </button>
              <button className="btn btn-secondary" onClick={() => setShowConfirm(true)}>
                تأكيد عادي
              </button>
              <button className="btn btn-danger" onClick={() => setShowDangerConfirm(true)}>
                <Icon name="trash" size={14}/> تأكيد حذف (خطر)
              </button>
            </div>

            <Modal
              open={showModal}
              onClose={() => setShowModal(false)}
              title="إنشاء حملة جديدة"
              size="md"
              footer={
                <>
                  <button className="btn btn-secondary" onClick={() => setShowModal(false)}>إلغاء</button>
                  <button
                    className="btn btn-primary"
                    onClick={() => { setShowModal(false); toast.success('تم', 'تم إنشاء الحملة بنجاح'); }}
                  >إنشاء</button>
                </>
              }
            >
              <div className="stack">
                <div className="input-group">
                  <label className="input-label">اسم الحملة</label>
                  <input className="input" placeholder="مثال: حملة الجمعة البيضاء" />
                </div>
                <div className="input-group">
                  <label className="input-label">الميزانية اليومية (ر.س)</label>
                  <input className="input" defaultValue="150" type="number" />
                </div>
                <div className="input-group">
                  <label className="input-label">الوصف</label>
                  <textarea className="textarea" placeholder="وصف مختصر عن هدف الحملة..."></textarea>
                </div>
              </div>
            </Modal>

            <ConfirmModal
              open={showConfirm}
              onClose={() => setShowConfirm(false)}
              onConfirm={() => { setShowConfirm(false); toast.info('تم التأكيد'); }}
              title="نشر المنشور الآن؟"
              message="سيتم نشر المنشور فوراً على جميع المنصات المختارة. لا يمكن التراجع بعد النشر."
              confirmText="نشر الآن"
              cancelText="إلغاء"
            />

            <ConfirmModal
              open={showDangerConfirm}
              onClose={() => setShowDangerConfirm(false)}
              onConfirm={() => { setShowDangerConfirm(false); toast.error('تم الحذف'); }}
              title="حذف الحملة نهائياً؟"
              message="سيتم حذف الحملة وجميع منشوراتها المرتبطة. هذا الإجراء لا يمكن التراجع عنه."
              confirmText="نعم، احذف"
              variant="danger"
            />
          </Section>

          {/* === Data display === */}
          <Section id="data" title="عرض البيانات">
            <div className="cmp-subhead">جدول</div>
            <div className="card">
              <table className="cmp-table">
                <thead>
                  <tr>
                    <th>المنشور</th>
                    <th>المنصة</th>
                    <th>الانطباعات</th>
                    <th>التفاعل</th>
                    <th>الحالة</th>
                  </tr>
                </thead>
                <tbody>
                  {[
                    ['عرض الجمعة البيضاء', 'instagram', '24,810', '8.4%', 'success', 'مُنشور'],
                    ['تشويق رمضاني', 'facebook', '12,340', '6.1%', 'warning', 'مراجعة'],
                    ['منتج جديد — عطر', 'instagram', '—', '—', 'info', 'مسودة'],
                  ].map((r, i) => (
                    <tr key={i}>
                      <td style={{ fontWeight: 600 }}>{r[0]}</td>
                      <td><Icon name={r[1]} size={14}/></td>
                      <td className="mono">{r[2]}</td>
                      <td className="mono">{r[3]}</td>
                      <td><span className={`badge badge-${r[4]}`}>{r[5]}</span></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="cmp-subhead">Avatars</div>
            <div className="row" style={{ alignItems: 'center' }}>
              <Avatar name="أحمد العتيبي" size={28} />
              <Avatar name="سارة المنصوري" size={36} />
              <Avatar name="فيصل الخالدي" size={44} />
              <div style={{ display: 'flex' }}>
                {['أح','سا','في','مح'].map((n, i) => (
                  <div key={i} style={{
                    width: 32, height: 32, borderRadius: '50%',
                    background: `linear-gradient(135deg, var(--sada-${400 + i*100}), var(--sada-${500 + i*100}))`,
                    color: '#fff', display: 'grid', placeItems: 'center',
                    fontSize: 11, fontWeight: 700,
                    border: '2px solid var(--bg-surface)',
                    marginLeft: i === 0 ? 0 : -10,
                  }}>{n}</div>
                ))}
              </div>
            </div>

            <div className="cmp-subhead">Progress</div>
            <div className="stack-sm">
              <div className="token-meter" style={{ maxWidth: 360 }}>
                <div className="token-meter-head">
                  <span className="label">التوكنز المستخدمة</span>
                  <span className="val">1,240 / 2,000</span>
                </div>
                <div className="token-bar">
                  <div className="token-bar-fill" style={{ width: '62%' }} />
                </div>
              </div>
            </div>

            <div className="cmp-subhead">Sparkline</div>
            <div className="card" style={{ padding: 16, maxWidth: 300 }}>
              <Sparkline values={[12, 18, 14, 22, 30, 28, 36, 34, 42]} />
            </div>
          </Section>

          {/* === Nav === */}
          <Section id="nav" title="عناصر التنقل">
            <div className="cmp-subhead">Breadcrumbs</div>
            <div className="crumbs" style={{ fontSize: 13 }}>
              <span>المحتوى</span>
              <span style={{ opacity: 0.5 }}>•</span>
              <span>الحملات</span>
              <span style={{ opacity: 0.5 }}>•</span>
              <span style={{ color: 'var(--text-primary)', fontWeight: 600 }}>الجمعة البيضاء</span>
            </div>

            <div className="cmp-subhead">Tabs</div>
            <div className="cmp-tabs">
              <button data-active="true">نظرة عامة</button>
              <button>المنشورات</button>
              <button>التحليلات</button>
              <button>الإعدادات</button>
            </div>

            <div className="cmp-subhead">Pagination</div>
            <div className="row" style={{ alignItems: 'center', gap: 4 }}>
              <button className="btn btn-icon btn-icon-sm btn-secondary"><Icon name="chevronRight" size={14}/></button>
              <button className="btn btn-icon btn-icon-sm btn-primary">1</button>
              <button className="btn btn-icon btn-icon-sm btn-ghost">2</button>
              <button className="btn btn-icon btn-icon-sm btn-ghost">3</button>
              <span style={{ color: 'var(--text-muted)', padding: '0 6px' }}>…</span>
              <button className="btn btn-icon btn-icon-sm btn-ghost">12</button>
              <button className="btn btn-icon btn-icon-sm btn-secondary"><Icon name="chevronLeft" size={14}/></button>
            </div>
          </Section>

          {/* === Media === */}
          <Section id="media" title="الوسائط والعناصر الزخرفية">
            <div className="grid-3">
              <ImgPlaceholder label="صورة منتج 1:1" h={160} />
              <ImgPlaceholder label="صورة قصة 9:16" h={160} />
              <ImgPlaceholder label="صورة غلاف 16:9" h={160} />
            </div>

            <div className="cmp-subhead">Skeleton</div>
            <div className="stack-sm" style={{ maxWidth: 360 }}>
              <div className="skeleton" style={{ height: 18, width: '60%' }} />
              <div className="skeleton" style={{ height: 14, width: '90%' }} />
              <div className="skeleton" style={{ height: 14, width: '80%' }} />
              <div className="skeleton" style={{ height: 80, width: '100%', marginTop: 6 }} />
            </div>

            <div className="cmp-subhead">العلم (Flag)</div>
            <div className="row" style={{ alignItems: 'center', fontSize: 18 }}>
              <Flag code="sa" size={24} /> <Flag code="ae" size={24} />
              <Flag code="kw" size={24} /> <Flag code="qa" size={24} />
              <Flag code="bh" size={24} /> <Flag code="om" size={24} />
            </div>
          </Section>
        </div>
      </div>
    </div>
  );
};

Object.assign(window, { ComponentsScreen });
