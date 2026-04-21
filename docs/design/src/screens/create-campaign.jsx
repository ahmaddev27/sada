/* global React, Icon, Flag, CustomSelect */
// Sada — Create Campaign (multi-step wizard)

const { useState } = React;

const CreateCampaignScreen = ({ onBack, initialObjective }) => {
  const [step, setStep] = useState(0);
  const [data, setData] = useState({
    objective: initialObjective || 'conversions',
    name: '',
    platforms: ['instagram'],
    audienceType: 'saved',
    audienceName: 'جمهور متجر أنيق — نسائي ٢٠-٣٥',
    genders: ['female'],
    ageMin: 20,
    ageMax: 35,
    cities: ['الرياض', 'جدة', 'الدمام'],
    interests: ['موضة', 'تسوق', 'جمال'],
    budgetType: 'daily',
    budget: 200,
    duration: 7,
    startDate: 'اليوم',
    creativeType: 'carousel',
    headline: 'مجموعة الصيف ٢٠٢٦ — وصلت الآن',
    primaryText: 'اكتشفي أحدث تصاميم الموسم. شحن مجاني داخل المملكة لفترة محدودة.',
    cta: 'تسوّقي الآن',
    landingUrl: 'https://aniq.sa/summer',
  });

  const steps = [
    { id: 'objective', label: 'الهدف', icon: 'target' },
    { id: 'details',   label: 'التفاصيل', icon: 'edit' },
    { id: 'audience',  label: 'الجمهور', icon: 'users' },
    { id: 'budget',    label: 'الميزانية', icon: 'credit' },
    { id: 'creative',  label: 'الإبداع', icon: 'image' },
    { id: 'review',    label: 'المراجعة', icon: 'check' },
  ];

  const upd = (k, v) => setData(d => ({ ...d, [k]: v }));
  const next = () => setStep(s => Math.min(s + 1, steps.length - 1));
  const prev = () => setStep(s => Math.max(s - 1, 0));

  return (
    <div className="cc-wrap">
      {/* Header */}
      <div className="cc-header">
        <button className="btn btn-ghost btn-sm" onClick={onBack}>
          <Icon name="chevronRight" size={16} /> العودة للحملات
        </button>
        <div style={{ flex: 1 }} />
        <button className="btn btn-ghost btn-sm"><Icon name="save" size={14} /> حفظ كمسودة</button>
      </div>

      <div className="cc-title-row">
        <h1 style={{ fontSize: 22, margin: 0 }}>إنشاء حملة إعلانية</h1>
        <div className="sub" style={{ marginTop: 4 }}>اتبع الخطوات لإطلاق حملتك على Meta</div>
      </div>

      {/* Stepper */}
      <div className="cc-stepper">
        {steps.map((s, i) => (
          <React.Fragment key={s.id}>
            <button
              className="cc-step"
              data-state={i < step ? 'done' : i === step ? 'active' : 'upcoming'}
              onClick={() => i < step && setStep(i)}
            >
              <div className="cc-step-dot">
                {i < step ? <Icon name="check" size={14} /> : <Icon name={s.icon} size={14} />}
              </div>
              <span className="cc-step-label">{s.label}</span>
            </button>
            {i < steps.length - 1 && <div className="cc-step-line" data-done={i < step} />}
          </React.Fragment>
        ))}
      </div>

      {/* Content */}
      <div className="cc-body">
        <div className="cc-main">
          {step === 0 && <StepObjective data={data} upd={upd} />}
          {step === 1 && <StepDetails data={data} upd={upd} />}
          {step === 2 && <StepAudience data={data} upd={upd} />}
          {step === 3 && <StepBudget data={data} upd={upd} />}
          {step === 4 && <StepCreative data={data} upd={upd} />}
          {step === 5 && <StepReview data={data} />}
        </div>
        <div className="cc-aside">
          <CampaignPreview data={data} />
        </div>
      </div>

      {/* Footer */}
      <div className="cc-footer">
        <button className="btn btn-secondary" onClick={prev} disabled={step === 0}>
          <Icon name="chevronRight" size={14} /> السابق
        </button>
        <div style={{ flex: 1, textAlign: 'center', fontSize: 12, color: 'var(--text-muted)' }}>
          الخطوة {step + 1} من {steps.length}
        </div>
        {step < steps.length - 1 ? (
          <button className="btn btn-primary" onClick={next}>
            التالي <Icon name="chevronLeft" size={14} />
          </button>
        ) : (
          <button className="btn btn-primary">
            <Icon name="rocket" size={14} /> إطلاق الحملة
          </button>
        )}
      </div>
    </div>
  );
};

/* ------ STEP 1: OBJECTIVE ------ */
const StepObjective = ({ data, upd }) => {
  const objectives = [
    { id: 'awareness',   label: 'الوعي بالعلامة', desc: 'وصول أكبر لجمهور جديد',      icon: 'eye',    color: 'var(--info)',     best: 'مناسب للبراندات الجديدة' },
    { id: 'traffic',     label: 'زيارات الموقع',   desc: 'نقرات إلى متجرك الإلكتروني', icon: 'arrowLeft', color: 'var(--sand-500)', best: 'زيادة حركة الموقع' },
    { id: 'conversions', label: 'مبيعات',           desc: 'شراء فعلي + تحسين ROAS',     icon: 'target', color: 'var(--sada-500)', best: 'الأكثر استخداماً · موصى به' },
    { id: 'engagement',  label: 'التفاعل',          desc: 'تعليقات، حفظ، مشاركة',        icon: 'heart',  color: 'var(--error)',     best: 'بناء مجتمع' },
    { id: 'leads',       label: 'جمع عملاء محتملين', desc: 'نماذج + أرقام تواصل',         icon: 'user',   color: 'var(--warning)',   best: 'للخدمات والاستشارات' },
    { id: 'app',         label: 'تثبيت التطبيق',    desc: 'تحميل تطبيقك من المتاجر',     icon: 'phone',  color: 'var(--text-primary)', best: 'للتطبيقات' },
  ];

  return (
    <div className="stack-lg">
      <div>
        <h2 style={{ fontSize: 18, margin: '0 0 4px' }}>اختر هدف الحملة</h2>
        <div className="sub">نستخدم الهدف لتحسين التوصيل والـ ROAS تلقائياً.</div>
      </div>
      <div className="cc-obj-grid">
        {objectives.map(o => (
          <button
            key={o.id}
            className="cc-obj-card"
            data-active={data.objective === o.id}
            onClick={() => upd('objective', o.id)}
          >
            <div className="cc-obj-icon" style={{ background: `color-mix(in oklab, ${o.color} 14%, transparent)`, color: o.color }}>
              <Icon name={o.icon} size={22} />
            </div>
            <div style={{ fontSize: 15, fontWeight: 700, marginBottom: 2 }}>{o.label}</div>
            <div style={{ fontSize: 12.5, color: 'var(--text-muted)', marginBottom: 10 }}>{o.desc}</div>
            <div className="cc-obj-best">{o.best}</div>
          </button>
        ))}
      </div>
    </div>
  );
};

/* ------ STEP 2: DETAILS ------ */
const StepDetails = ({ data, upd }) => {
  const platforms = [
    { id: 'instagram', label: 'انستجرام', icon: 'instagram', color: '#E1306C' },
    { id: 'facebook',  label: 'فيسبوك',   icon: 'facebook',  color: '#1877F2' },
    { id: 'tiktok',    label: 'تيك توك',  icon: 'tiktok',    color: '#000000' },
    { id: 'snapchat',  label: 'سناب شات', icon: 'snapchat',  color: '#FFFC00' },
  ];
  const toggle = (id) => {
    const cur = data.platforms.includes(id)
      ? data.platforms.filter(p => p !== id)
      : [...data.platforms, id];
    upd('platforms', cur);
  };

  return (
    <div className="stack-lg">
      <div>
        <h2 style={{ fontSize: 18, margin: '0 0 4px' }}>تفاصيل الحملة</h2>
        <div className="sub">سمِّ حملتك واختر منصات النشر.</div>
      </div>

      <div className="field">
        <label>اسم الحملة</label>
        <input
          className="input"
          placeholder="مثال: إطلاق مجموعة الصيف ٢٠٢٦"
          value={data.name}
          onChange={e => upd('name', e.target.value)}
        />
        <div className="field-hint">يظهر في لوحة التحكم فقط — لن يراه الجمهور.</div>
      </div>

      <div className="field">
        <label>منصات النشر</label>
        <div className="cc-platforms">
          {platforms.map(p => (
            <button
              key={p.id}
              className="cc-platform"
              data-active={data.platforms.includes(p.id)}
              onClick={() => toggle(p.id)}
            >
              <div className="cc-platform-icon" style={{ background: `color-mix(in oklab, ${p.color} 12%, transparent)`, color: p.color }}>
                <Icon name={p.icon} size={20} />
              </div>
              <span>{p.label}</span>
              {data.platforms.includes(p.id) && (
                <div className="cc-platform-check">
                  <Icon name="check" size={12} />
                </div>
              )}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
};

/* ------ STEP 3: AUDIENCE ------ */
const StepAudience = ({ data, upd }) => {
  const [cityInput, setCityInput] = useState('');
  const [interestInput, setInterestInput] = useState('');
  const saved = [
    { id: 'women-sa', name: 'جمهور متجر أنيق — نسائي ٢٠-٣٥', size: '١.٢م' },
    { id: 'lookalike', name: 'Lookalike — مشابهين لعملائنا',   size: '٨٥٠ك' },
    { id: 'retargeting', name: 'زوار الموقع — ٣٠ يوم',         size: '١٢ك' },
  ];

  const toggleCity = (c) => {
    const cur = data.cities.includes(c) ? data.cities.filter(x => x !== c) : [...data.cities, c];
    upd('cities', cur);
  };
  const rmInterest = (t) => upd('interests', data.interests.filter(x => x !== t));
  const addInterest = () => {
    if (interestInput.trim()) {
      upd('interests', [...data.interests, interestInput.trim()]);
      setInterestInput('');
    }
  };
  const addCity = () => {
    if (cityInput.trim() && !data.cities.includes(cityInput.trim())) {
      upd('cities', [...data.cities, cityInput.trim()]);
      setCityInput('');
    }
  };

  const reach = Math.max(180000, 2000000 - (data.genders.length === 1 ? 700000 : 0) - (data.cities.length < 3 ? 400000 : 0) - ((data.ageMax - data.ageMin) < 20 ? 300000 : 0));
  const reachFmt = reach.toLocaleString('ar-SA');

  return (
    <div className="stack-lg">
      <div>
        <h2 style={{ fontSize: 18, margin: '0 0 4px' }}>الجمهور المستهدف</h2>
        <div className="sub">اختر جمهوراً محفوظاً أو ابنِ جمهوراً مخصصاً.</div>
      </div>

      {/* Audience type */}
      <div className="cc-aud-tabs">
        {[
          { id: 'saved', label: 'جمهور محفوظ', icon: 'bookmark' },
          { id: 'custom', label: 'جمهور مخصص', icon: 'sliders' },
          { id: 'lookalike', label: 'Lookalike', icon: 'users' },
        ].map(t => (
          <button
            key={t.id}
            className="cc-aud-tab"
            data-active={data.audienceType === t.id}
            onClick={() => upd('audienceType', t.id)}
          >
            <Icon name={t.icon} size={14} /> {t.label}
          </button>
        ))}
      </div>

      {data.audienceType === 'saved' && (
        <div className="cc-saved-list">
          {saved.map(s => (
            <button
              key={s.id}
              className="cc-saved-card"
              data-active={data.audienceName === s.name}
              onClick={() => upd('audienceName', s.name)}
            >
              <div className="cc-saved-radio" data-checked={data.audienceName === s.name}>
                <div />
              </div>
              <div style={{ flex: 1, textAlign: 'right' }}>
                <div style={{ fontWeight: 600, fontSize: 14 }}>{s.name}</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>حجم تقديري: {s.size} شخص</div>
              </div>
              <Icon name="users" size={16} style={{ color: 'var(--text-muted)' }} />
            </button>
          ))}
        </div>
      )}

      {data.audienceType === 'custom' && (
        <div className="stack-lg">
          {/* Gender */}
          <div className="field">
            <label>الجنس</label>
            <div className="cc-gender-row">
              {[
                { id: 'female', label: 'نساء' },
                { id: 'male',   label: 'رجال' },
              ].map(g => (
                <button
                  key={g.id}
                  className="cc-gender-btn"
                  data-active={data.genders.includes(g.id)}
                  onClick={() => {
                    const cur = data.genders.includes(g.id) ? data.genders.filter(x => x !== g.id) : [...data.genders, g.id];
                    upd('genders', cur.length ? cur : [g.id]);
                  }}
                >
                  {g.label}
                </button>
              ))}
            </div>
          </div>

          {/* Age */}
          <div className="field">
            <label>العمر: {data.ageMin} - {data.ageMax}</label>
            <div className="cc-age-row">
              <input type="range" min="13" max="65" value={data.ageMin} onChange={e => upd('ageMin', Math.min(+e.target.value, data.ageMax - 1))} className="cc-slider" />
              <input type="range" min="13" max="65" value={data.ageMax} onChange={e => upd('ageMax', Math.max(+e.target.value, data.ageMin + 1))} className="cc-slider" />
            </div>
          </div>

          {/* Cities */}
          <div className="field">
            <label>المدن</label>
            <div className="tag-list">
              {data.cities.map(c => (
                <span key={c} className="tag tag-removable">
                  {c}
                  <button onClick={() => toggleCity(c)}><Icon name="x" size={10} /></button>
                </span>
              ))}
              <div className="tag-add-wrap">
                <input
                  className="tag-add-input"
                  placeholder="+ أضف مدينة..."
                  value={cityInput}
                  onChange={e => setCityInput(e.target.value)}
                  onKeyDown={e => e.key === 'Enter' && (e.preventDefault(), addCity())}
                />
              </div>
            </div>
            <div className="cc-city-suggest">
              {['الرياض', 'جدة', 'الدمام', 'مكة', 'المدينة', 'الطائف', 'أبها', 'تبوك'].filter(c => !data.cities.includes(c)).slice(0, 5).map(c => (
                <button key={c} className="cc-city-chip" onClick={() => toggleCity(c)}>+ {c}</button>
              ))}
            </div>
          </div>

          {/* Interests */}
          <div className="field">
            <label>الاهتمامات</label>
            <div className="tag-list">
              {data.interests.map(t => (
                <span key={t} className="tag tag-removable">
                  {t}
                  <button onClick={() => rmInterest(t)}><Icon name="x" size={10} /></button>
                </span>
              ))}
              <div className="tag-add-wrap">
                <input
                  className="tag-add-input"
                  placeholder="+ أضف اهتمام..."
                  value={interestInput}
                  onChange={e => setInterestInput(e.target.value)}
                  onKeyDown={e => e.key === 'Enter' && (e.preventDefault(), addInterest())}
                />
              </div>
            </div>
          </div>
        </div>
      )}

      {data.audienceType === 'lookalike' && (
        <div className="cc-lookalike-info">
          <Icon name="users" size={32} style={{ color: 'var(--accent)' }} />
          <div style={{ fontSize: 14, fontWeight: 600, marginTop: 8 }}>بناء جمهور Lookalike</div>
          <div className="sub" style={{ fontSize: 12.5, marginTop: 4 }}>نستخدم قاعدة عملائك الحاليين لبناء جمهور مشابه على Meta. سيظهر خلال ٢٤ ساعة.</div>
          <button className="btn btn-secondary btn-sm" style={{ marginTop: 12 }}>
            <Icon name="upload" size={14} /> رفع قاعدة العملاء
          </button>
        </div>
      )}

      {/* Reach meter */}
      <div className="cc-reach-card">
        <div className="cc-reach-head">
          <div>
            <div className="sub" style={{ fontSize: 11 }}>الوصول التقديري اليومي</div>
            <div style={{ fontSize: 22, fontWeight: 700, fontVariantNumeric: 'tabular-nums', marginTop: 2 }}>{reachFmt}</div>
          </div>
          <div className="cc-reach-badge" data-size={reach > 1000000 ? 'wide' : reach > 400000 ? 'good' : 'narrow'}>
            {reach > 1000000 ? 'واسع جداً' : reach > 400000 ? 'جيد' : 'ضيق'}
          </div>
        </div>
        <div className="cc-reach-bar">
          <div className="cc-reach-fill" style={{ width: `${Math.min(95, (reach / 2000000) * 100)}%` }} />
          <div className="cc-reach-zones">
            <span>ضيق</span><span>جيد</span><span>واسع</span>
          </div>
        </div>
      </div>
    </div>
  );
};

/* ------ STEP 4: BUDGET ------ */
const StepBudget = ({ data, upd }) => {
  const estImpressions = Math.round(data.budget * data.duration * (data.budgetType === 'daily' ? 1 : 1/data.duration) * 280);
  const estClicks = Math.round(estImpressions * 0.024);
  const estConversions = Math.round(estClicks * 0.038);

  return (
    <div className="stack-lg">
      <div>
        <h2 style={{ fontSize: 18, margin: '0 0 4px' }}>الميزانية والجدولة</h2>
        <div className="sub">حدد كم ستنفق ولأي مدة.</div>
      </div>

      <div className="field">
        <label>نوع الميزانية</label>
        <div className="segmented">
          {[
            { id: 'daily', label: 'يومية' },
            { id: 'lifetime', label: 'إجمالية' },
          ].map(t => (
            <button key={t.id} data-active={data.budgetType === t.id} onClick={() => upd('budgetType', t.id)}>
              {t.label}
            </button>
          ))}
        </div>
        <div className="field-hint">
          {data.budgetType === 'daily' ? 'الحد الأقصى اليومي — يتوقف الإنفاق بعد الوصول.' : 'الميزانية توزّع تلقائياً على مدة الحملة.'}
        </div>
      </div>

      <div className="field">
        <label>المبلغ ({data.budgetType === 'daily' ? 'ر.س / يوم' : 'ر.س إجمالي'})</label>
        <div className="cc-budget-input">
          <span className="cc-budget-prefix">ر.س</span>
          <input
            type="number"
            className="input"
            value={data.budget}
            onChange={e => upd('budget', +e.target.value)}
            min="20"
            style={{ paddingInlineStart: 52, fontSize: 18, fontWeight: 700 }}
          />
        </div>
        <div className="cc-budget-presets">
          {[100, 200, 500, 1000, 2000].map(v => (
            <button key={v} className="cc-preset-chip" data-active={data.budget === v} onClick={() => upd('budget', v)}>
              {v.toLocaleString('ar-SA')}
            </button>
          ))}
        </div>
      </div>

      <div className="field">
        <label>المدة</label>
        <div className="cc-duration-row">
          {[3, 7, 14, 30].map(d => (
            <button key={d} className="cc-duration-chip" data-active={data.duration === d} onClick={() => upd('duration', d)}>
              {d} يوم
            </button>
          ))}
          <button className="cc-duration-chip" data-active={false}>مخصص</button>
        </div>
      </div>

      <div className="field">
        <label>تاريخ البدء</label>
        <div className="segmented">
          <button data-active={data.startDate === 'اليوم'} onClick={() => upd('startDate', 'اليوم')}>يبدأ اليوم</button>
          <button data-active={data.startDate === 'مجدول'} onClick={() => upd('startDate', 'مجدول')}>مجدول</button>
        </div>
      </div>

      {/* Estimates */}
      <div className="cc-estimate-card">
        <div className="cc-estimate-title">
          <Icon name="sparkle" size={14} />
          <span>النتائج التقديرية</span>
        </div>
        <div className="cc-estimate-grid">
          <div>
            <div className="sub" style={{ fontSize: 11 }}>الانطباعات</div>
            <div className="cc-estimate-val">{estImpressions.toLocaleString('ar-SA')}</div>
          </div>
          <div>
            <div className="sub" style={{ fontSize: 11 }}>النقرات</div>
            <div className="cc-estimate-val">{estClicks.toLocaleString('ar-SA')}</div>
          </div>
          <div>
            <div className="sub" style={{ fontSize: 11 }}>التحويلات</div>
            <div className="cc-estimate-val" style={{ color: 'var(--success)' }}>{estConversions.toLocaleString('ar-SA')}</div>
          </div>
          <div>
            <div className="sub" style={{ fontSize: 11 }}>الإجمالي</div>
            <div className="cc-estimate-val">{(data.budgetType === 'daily' ? data.budget * data.duration : data.budget).toLocaleString('ar-SA')} ر.س</div>
          </div>
        </div>
      </div>
    </div>
  );
};

/* ------ STEP 5: CREATIVE ------ */
const StepCreative = ({ data, upd }) => {
  const ctaOptions = ['تسوّقي الآن', 'اطلب الآن', 'اعرف المزيد', 'اتصل بنا', 'احجز موعد', 'سجّل الآن'];

  return (
    <div className="stack-lg">
      <div>
        <h2 style={{ fontSize: 18, margin: '0 0 4px' }}>الإبداع الإعلاني</h2>
        <div className="sub">اختر التنسيق واكتب النص — سنولّد لك اقتراحات إذا لزم.</div>
      </div>

      <div className="field">
        <label>تنسيق الإعلان</label>
        <div className="cc-format-row">
          {[
            { id: 'single',   label: 'صورة واحدة', icon: 'image' },
            { id: 'carousel', label: 'كاروسيل',    icon: 'layers' },
            { id: 'video',    label: 'فيديو',      icon: 'video' },
            { id: 'reels',    label: 'ريلز/ستوري',  icon: 'phone' },
          ].map(f => (
            <button key={f.id} className="cc-format-card" data-active={data.creativeType === f.id} onClick={() => upd('creativeType', f.id)}>
              <Icon name={f.icon} size={22} />
              <span>{f.label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Media placeholder */}
      <div className="field">
        <label>الوسائط</label>
        <div className="cc-media-drop">
          <div className="cc-media-icon"><Icon name="upload" size={22} /></div>
          <div style={{ fontSize: 14, fontWeight: 600 }}>اسحب الصور/الفيديو هنا</div>
          <div className="sub" style={{ fontSize: 12, marginTop: 2 }}>أو اختر من مكتبة المحتوى · JPG, PNG, MP4 — حتى ٣٠ م.ب</div>
          <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
            <button className="btn btn-secondary btn-sm"><Icon name="image" size={14} /> من المكتبة</button>
            <button className="btn btn-primary btn-sm"><Icon name="sparkle" size={14} /> توليد بالـ AI</button>
          </div>
        </div>
      </div>

      <div className="field">
        <label>العنوان الرئيسي (Headline)</label>
        <input
          className="input"
          value={data.headline}
          onChange={e => upd('headline', e.target.value)}
          maxLength="40"
        />
        <div className="field-hint">{data.headline.length}/٤٠ حرف · موصى به: أقل من ٤٠ حرف</div>
      </div>

      <div className="field">
        <label>النص الأساسي</label>
        <textarea
          className="textarea"
          rows="3"
          value={data.primaryText}
          onChange={e => upd('primaryText', e.target.value)}
          maxLength="125"
        />
        <div className="field-hint">{data.primaryText.length}/١٢٥ حرف</div>
      </div>

      <div className="field">
        <label>زر الحث (CTA)</label>
        <div className="cc-cta-row">
          {ctaOptions.map(c => (
            <button key={c} className="cc-cta-chip" data-active={data.cta === c} onClick={() => upd('cta', c)}>{c}</button>
          ))}
        </div>
      </div>

      <div className="field">
        <label>رابط الوجهة</label>
        <input className="input" value={data.landingUrl} onChange={e => upd('landingUrl', e.target.value)} dir="ltr" />
      </div>
    </div>
  );
};

/* ------ STEP 6: REVIEW ------ */
const StepReview = ({ data }) => {
  const total = data.budgetType === 'daily' ? data.budget * data.duration : data.budget;
  const objectiveLabels = {
    awareness: 'الوعي بالعلامة', traffic: 'زيارات الموقع', conversions: 'مبيعات',
    engagement: 'التفاعل', leads: 'جمع عملاء محتملين', app: 'تثبيت التطبيق',
  };

  const Row = ({ label, value }) => (
    <div className="cc-review-row">
      <span className="cc-review-label">{label}</span>
      <span className="cc-review-value">{value}</span>
    </div>
  );

  return (
    <div className="stack-lg">
      <div>
        <h2 style={{ fontSize: 18, margin: '0 0 4px' }}>المراجعة النهائية</h2>
        <div className="sub">راجع التفاصيل قبل الإطلاق. سترسل للمراجعة تلقائياً من Meta (قد تستغرق حتى ٢٤ ساعة).</div>
      </div>

      <div className="cc-review-section">
        <div className="cc-review-head">
          <Icon name="target" size={14} /> <span>الحملة</span>
        </div>
        <Row label="الاسم" value={data.name || '—'} />
        <Row label="الهدف" value={objectiveLabels[data.objective]} />
        <Row label="المنصات" value={data.platforms.join('، ')} />
      </div>

      <div className="cc-review-section">
        <div className="cc-review-head">
          <Icon name="users" size={14} /> <span>الجمهور</span>
        </div>
        <Row label="النوع" value={data.audienceType === 'saved' ? 'محفوظ' : data.audienceType === 'custom' ? 'مخصص' : 'Lookalike'} />
        {data.audienceType === 'saved' && <Row label="الجمهور" value={data.audienceName} />}
        {data.audienceType === 'custom' && (
          <>
            <Row label="الجنس" value={data.genders.map(g => g === 'female' ? 'نساء' : 'رجال').join('، ')} />
            <Row label="العمر" value={`${data.ageMin} - ${data.ageMax}`} />
            <Row label="المدن" value={data.cities.join('، ')} />
            <Row label="الاهتمامات" value={data.interests.join('، ')} />
          </>
        )}
      </div>

      <div className="cc-review-section">
        <div className="cc-review-head">
          <Icon name="credit" size={14} /> <span>الميزانية</span>
        </div>
        <Row label="النوع" value={data.budgetType === 'daily' ? 'يومية' : 'إجمالية'} />
        <Row label="المبلغ" value={`${data.budget.toLocaleString('ar-SA')} ر.س ${data.budgetType === 'daily' ? '/ يوم' : ''}`} />
        <Row label="المدة" value={`${data.duration} يوم`} />
        <Row label="الإجمالي المتوقع" value={<strong>{total.toLocaleString('ar-SA')} ر.س</strong>} />
      </div>

      <div className="cc-review-section">
        <div className="cc-review-head">
          <Icon name="image" size={14} /> <span>الإبداع</span>
        </div>
        <Row label="التنسيق" value={data.creativeType} />
        <Row label="العنوان" value={data.headline} />
        <Row label="CTA" value={data.cta} />
      </div>

      <div className="cc-confirm-notice">
        <Icon name="info" size={16} />
        <div>
          <strong>قبل الإطلاق:</strong> سنخصم {total.toLocaleString('ar-SA')} ر.س من رصيد حسابك الإعلاني. يمكنك الإيقاف في أي وقت.
        </div>
      </div>
    </div>
  );
};

/* ------ PREVIEW SIDEBAR ------ */
const CampaignPreview = ({ data }) => {
  return (
    <div className="cc-preview">
      <div className="cc-preview-head">
        <Icon name="phone" size={14} />
        <span>معاينة الإعلان · انستجرام</span>
      </div>
      <div className="cc-phone-mock">
        <div className="cc-phone-top">
          <div className="cc-phone-avatar">أن</div>
          <div style={{ flex: 1 }}>
            <div style={{ fontSize: 12, fontWeight: 700 }}>aniq_store</div>
            <div style={{ fontSize: 10, color: 'var(--text-muted)' }}>ممول · Sponsored</div>
          </div>
          <Icon name="more" size={16} />
        </div>
        <div className="cc-phone-media">
          <div className="cc-phone-media-inner">
            <Icon name="image" size={36} style={{ opacity: 0.5 }} />
            <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 6 }}>صورة الإعلان</div>
          </div>
        </div>
        <div className="cc-phone-actions">
          <Icon name="heart" size={18} />
          <Icon name="message" size={18} />
          <Icon name="share" size={18} />
          <div style={{ flex: 1 }} />
          <Icon name="bookmark" size={18} />
        </div>
        <div className="cc-phone-body">
          <div className="cc-phone-headline">{data.headline || 'عنوان الإعلان...'}</div>
          <div className="cc-phone-text">{data.primaryText || 'النص الأساسي سيظهر هنا...'}</div>
          <button className="cc-phone-cta">
            {data.cta} <Icon name="chevronLeft" size={12} />
          </button>
        </div>
      </div>

      <div className="cc-preview-meta">
        <div><strong>{data.platforms.length}</strong> منصات</div>
        <div><strong>{data.duration}</strong> يوم</div>
        <div><strong>{(data.budgetType === 'daily' ? data.budget * data.duration : data.budget).toLocaleString('ar-SA')}</strong> ر.س</div>
      </div>
    </div>
  );
};

Object.assign(window, { CreateCampaignScreen });
