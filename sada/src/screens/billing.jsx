/* global React, Icon, Avatar, ImgPlaceholder, Toggle, useToast, ConfirmModal, CustomSelect, Checkbox, LogoUploader */
// Sada — Billing, Settings, Brand Identity, Integrations

const { useState: useState_b } = React;

// ====== Brand Identity Tab ======
const BrandIdentityTab = () => {
  const toast = useToast();
  const [logo, setLogo] = useState_b(null);
  const [industry, setIndustry] = useState_b('fashion');
  const [priceLevel, setPriceLevel] = useState_b('mid-high');
  const [tones, setTones] = useState_b(['ودي', 'خليجي', 'واثق']);
  const [keywords, setKeywords] = useState_b(['جودة عالية', 'توصيل سريع', 'ضمان استرجاع', 'عروض أسبوعية']);
  const [avoid, setAvoid] = useState_b(['لغة رسمية جداً', 'وعود مبالغ فيها']);
  const [newKw, setNewKw] = useState_b('');
  const [newAvoid, setNewAvoid] = useState_b('');

  const removeItem = (list, setter, v) => setter(list.filter(x => x !== v));
  const addItem = (list, setter, v, clear) => {
    if (!v.trim()) return;
    setter([...list, v.trim()]);
    clear('');
  };

  return (
    <div className="stack-lg">
      <div className="alert alert-brand">
        <div className="alert-icon"><Icon name="sparkle" size={16}/></div>
        <div className="alert-body">
          <div className="alert-title">هوية المتجر = صوت موحّد لكل المنشورات</div>
          <div className="alert-desc">كل ما تضعه هنا يُستخدم عند توليد أي محتوى — نبرة، كلمات مفتاحية، وممنوعات.</div>
        </div>
      </div>

      {/* Logo + Colors */}
      <div className="card">
        <div className="card-head"><h3>الشعار والألوان</h3></div>
        <div className="card-body">
          <div className="brand-grid-2">
            <div>
              <div className="input-label" style={{ marginBottom: 10 }}>الشعار (PNG / JPG / SVG)</div>
              <LogoUploader value={logo} onChange={setLogo} outputSize={512} shape="rounded" />
            </div>

            <div>
              <div className="input-label" style={{ marginBottom: 10 }}>ألوان العلامة</div>
              <div className="stack-sm">
                {[
                  { name: 'اللون الأساسي', val: '#0F6F5C' },
                  { name: 'اللون الثانوي', val: '#C8965F' },
                  { name: 'لون الإبراز', val: '#2A9080' },
                ].map((c, i) => (
                  <div key={i} className="brand-color-row">
                    <div className="brand-color-chip" style={{ background: c.val }} />
                    <input className="input" defaultValue={c.val} style={{ fontFamily: 'var(--font-mono)', fontSize: 12, flex: 1 }} />
                    <div style={{ fontSize: 12, color: 'var(--text-muted)', minWidth: 90 }}>{c.name}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Brand info */}
      <div className="card">
        <div className="card-head"><h3>معلومات العلامة</h3></div>
        <div className="card-body">
          <div className="brand-grid-2">
            <div className="input-group">
              <label className="input-label">اسم العلامة التجارية</label>
              <input className="input" defaultValue="متجر أنيق" />
            </div>
            <div className="input-group">
              <label className="input-label">القطاع</label>
              <CustomSelect
                full
                searchable
                value={industry}
                onChange={setIndustry}
                options={[
                  { value: 'fashion', label: 'أزياء ومجوهرات' },
                  { value: 'food', label: 'مطاعم وأطعمة' },
                  { value: 'beauty', label: 'تجميل وعناية' },
                  { value: 'tech', label: 'تقنية وإلكترونيات' },
                  { value: 'home', label: 'منزل وأثاث' },
                  { value: 'health', label: 'صحة وعافية' },
                  { value: 'services', label: 'خدمات' },
                  { value: 'education', label: 'تعليم وتدريب' },
                ]}
              />
            </div>
            <div className="input-group" style={{ gridColumn: '1 / -1' }}>
              <label className="input-label">وصف المتجر وقصته</label>
              <textarea
                className="textarea"
                rows={4}
                defaultValue="متجر أنيق — علامة سعودية تقدم مجوهرات ذهبية معاصرة بتصاميم خليجية أصيلة. منذ 2019، نخدم عملاءنا في الرياض وجدة والدمام بجودة عالية وضمان استرجاع لمدة 14 يوماً."
              />
              <div className="input-hint">يُستخدم كسياق لكل منشور وكل حملة.</div>
            </div>
            <div className="input-group">
              <label className="input-label">الجمهور المستهدف</label>
              <input className="input" defaultValue="نساء سعوديات، 25-45 سنة، الطبقة المتوسطة والعليا" />
            </div>
            <div className="input-group">
              <label className="input-label">مستوى الأسعار</label>
              <CustomSelect
                full
                value={priceLevel}
                onChange={setPriceLevel}
                options={[
                  { value: 'budget',   label: 'اقتصادي',        hint: 'أقل من السوق' },
                  { value: 'mid',      label: 'متوسط',          hint: 'منافس' },
                  { value: 'mid-high', label: 'متوسط إلى مرتفع', hint: 'جودة + سعر' },
                  { value: 'luxury',   label: 'فاخر (Luxury)',   hint: 'تجربة راقية' },
                ]}
              />
            </div>
          </div>
        </div>
      </div>

      {/* Tone of voice */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>نبرة الصوت</h3>
            <div className="sub">كيف يجب أن يُشعر الجمهور بمنشوراتك؟</div>
          </div>
        </div>
        <div className="card-body">
          <div className="input-label" style={{ marginBottom: 10 }}>اختر الصفات التي تصف نبرتك (3-5)</div>
          <div className="row" style={{ flexWrap: 'wrap' }}>
            {['ودي', 'رسمي', 'خليجي', 'شبابي', 'فاخر', 'مرح', 'مباشر', 'عاطفي', 'عملي', 'واثق', 'دافئ', 'عصري'].map(t => (
              <button
                key={t}
                className="chip"
                data-selected={tones.includes(t)}
                onClick={() => setTones(tones.includes(t) ? tones.filter(x => x !== t) : [...tones, t])}
              >
                {tones.includes(t) && <Icon name="check" size={12} />}
                {t}
              </button>
            ))}
          </div>

        </div>
      </div>

      {/* Keywords + Avoid */}
      <div className="brand-grid-2">
        <div className="card">
          <div className="card-head">
            <div>
              <h3>كلمات وعبارات مفضّلة</h3>
              <div className="sub">ستظهر بشكل متكرر في منشوراتك</div>
            </div>
          </div>
          <div className="card-body">
            <div className="tag-list">
              {keywords.map(k => (
                <div key={k} className="tag tag-accent">
                  {k}
                  <button onClick={() => removeItem(keywords, setKeywords, k)}><Icon name="x" size={11}/></button>
                </div>
              ))}
            </div>
            <div className="row-sm" style={{ marginTop: 14 }}>
              <input
                className="input"
                placeholder="أضف كلمة جديدة..."
                value={newKw}
                onChange={e => setNewKw(e.target.value)}
                onKeyDown={e => e.key === 'Enter' && addItem(keywords, setKeywords, newKw, setNewKw)}
              />
              <button className="btn btn-primary" onClick={() => addItem(keywords, setKeywords, newKw, setNewKw)}>إضافة</button>
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-head">
            <div>
              <h3>كلمات ممنوعة</h3>
              <div className="sub">لن تُستخدم أبداً في أي منشور</div>
            </div>
          </div>
          <div className="card-body">
            <div className="tag-list">
              {avoid.map(k => (
                <div key={k} className="tag tag-danger">
                  {k}
                  <button onClick={() => removeItem(avoid, setAvoid, k)}><Icon name="x" size={11}/></button>
                </div>
              ))}
            </div>
            <div className="row-sm" style={{ marginTop: 14 }}>
              <input
                className="input"
                placeholder="أضف ما لا تريده..."
                value={newAvoid}
                onChange={e => setNewAvoid(e.target.value)}
                onKeyDown={e => e.key === 'Enter' && addItem(avoid, setAvoid, newAvoid, setNewAvoid)}
              />
              <button className="btn btn-danger" onClick={() => addItem(avoid, setAvoid, newAvoid, setNewAvoid)}>إضافة</button>
            </div>
          </div>
        </div>
      </div>

      {/* Sample output */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>معاينة بهويتك</h3>
            <div className="sub">هكذا سيبدو منشور مولّد بإعداداتك الحالية</div>
          </div>
          <button className="btn btn-sm btn-ghost" onClick={() => toast.info('جاري التجديد...')}>
            <Icon name="sparkle" size={13}/> جدّد
          </button>
        </div>
        <div className="card-body">
          <div className="brand-preview">
            <div className="brand-preview-post">
              <div className="row" style={{ alignItems: 'center', marginBottom: 10 }}>
                <div style={{
                  width: 36, height: 36, borderRadius: '50%',
                  background: 'linear-gradient(135deg, var(--sada-500), var(--sada-700))',
                  color: '#fff', display: 'grid', placeItems: 'center',
                  fontWeight: 700,
                }}>أ</div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontWeight: 700, fontSize: 13 }}>متجر أنيق</div>
                  <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>منذ دقائق</div>
                </div>
                <Icon name="instagram" size={16} />
              </div>
              <ImgPlaceholder label="صورة منتج — سوار ذهبي" h={220} />
              <div style={{ fontSize: 14, lineHeight: 1.8, marginTop: 12 }}>
                الأناقة مو كلام، هي تفصيل دقيق في كل قطعة 💛<br/>
                سوار «خليج» بذهب عيار ١٨ — صنعة محلية، تصميم يعكس ذوقك الخليجي.<br/><br/>
                <span style={{ color: 'var(--accent)', fontWeight: 600 }}>
                  #متجر_أنيق #مجوهرات_سعودية #خليج_ذهبي #هدية_راقية
                </span>
              </div>
            </div>
            <div className="brand-preview-meta">
              <div className="brand-preview-stat">
                <Icon name="check" size={14}/>
                <span>نبرة: ودي · خليجي · واثق</span>
              </div>
              <div className="brand-preview-stat">
                <Icon name="check" size={14}/>
                <span>كلمات مفتاحية: ٢ مستخدمة</span>
              </div>
              <div className="brand-preview-stat">
                <Icon name="check" size={14}/>
                <span>ممنوعات: لا مخالفات</span>
              </div>
              <div className="brand-preview-stat">
                <Icon name="check" size={14}/>
                <span>لهجة: سعودي خليجي</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="row" style={{ justifyContent: 'flex-end' }}>
        <button className="btn btn-secondary">إلغاء التغييرات</button>
        <button className="btn btn-primary" onClick={() => toast.success('تم الحفظ', 'تم تحديث هوية العلامة')}>
          <Icon name="check" size={14}/> حفظ هوية العلامة
        </button>
      </div>
    </div>
  );
};

// ====== Integrations Tab ======
const IntegrationsTab = () => {
  const toast = useToast();
  const [connections, setConnections] = useState_b({
    instagram: { connected: true,  account: '@aneeq.sa', followers: '12.4K' },
    facebook:  { connected: true,  account: 'Aneeq Boutique', followers: '8.1K' },
    tiktok:    { connected: false },
    snapchat:  { connected: false },
    x:         { connected: false },
    salla:     { connected: true,  account: 'aneeq.salla.sa' },
    zid:       { connected: false },
    shopify:   { connected: false },
    whatsapp:  { connected: true,  account: '+966 50 123 4567' },
  });
  const [disconnectKey, setDisconnectKey] = useState_b(null);

  const platforms = [
    { id: 'instagram', name: 'Instagram', icon: 'instagram', color: '#E1306C', desc: 'نشر منشورات، قصص، وريلز' },
    { id: 'facebook',  name: 'Facebook',  icon: 'facebook',  color: '#1877F2', desc: 'نشر على صفحتك وإدارة الحملات' },
    { id: 'tiktok',    name: 'TikTok',    icon: 'video',     color: '#000',    desc: 'نشر فيديوهات قصيرة وترندات' },
    { id: 'snapchat',  name: 'Snapchat',  icon: 'flash',     color: '#FFFC00', darkIcon: true, desc: 'قصص، Spotlight، وإعلانات' },
    { id: 'x',         name: 'X (Twitter)', icon: 'send',    color: '#000',    desc: 'تغريدات مجدولة وترندات' },
  ];

  const stores = [
    { id: 'salla',   name: 'سلة',    color: '#B9D651', desc: 'استيراد المنتجات والعروض تلقائياً', logo: 'سلة' },
    { id: 'zid',     name: 'زد',     color: '#5E2C8C', desc: 'مزامنة المخزون والأسعار', logo: 'زد' },
    { id: 'shopify', name: 'Shopify', color: '#95BF47', desc: 'استيراد الكتالوج من متجرك', logo: 'S' },
  ];

  const messaging = [
    { id: 'whatsapp', name: 'WhatsApp Business', icon: 'message', color: '#25D366', desc: 'إرسال حملات ورسائل جماعية' },
  ];

  const connect = (id) => {
    setConnections({ ...connections, [id]: { connected: true, account: `@demo_${id}`, followers: '—' } });
    toast.success('تم الربط', 'تم الربط بنجاح — يمكنك النشر الآن');
  };
  const doDisconnect = () => {
    if (!disconnectKey) return;
    setConnections({ ...connections, [disconnectKey]: { connected: false } });
    toast.info('تم الإلغاء', 'تم إلغاء الربط');
    setDisconnectKey(null);
  };

  const PlatformCard = ({ p }) => {
    const c = connections[p.id] || {};
    return (
      <div className={`integ-card ${c.connected ? 'is-connected' : ''}`}>
        <div className="integ-icon" style={{ background: p.color, color: p.darkIcon ? '#000' : '#fff' }}>
          {p.logo ? <span style={{ fontWeight: 800, fontSize: 14 }}>{p.logo}</span> : <Icon name={p.icon} size={20}/>}
        </div>
        <div className="integ-body">
          <div className="integ-name">
            {p.name}
            {c.connected && <span className="badge badge-success" style={{ fontSize: 10, padding: '2px 7px' }}><span className="dot dot-success"/> متصل</span>}
          </div>
          <div className="integ-desc">{p.desc}</div>
          {c.connected && c.account && (
            <div className="integ-account">
              <Icon name="check" size={12}/>
              <span>{c.account}</span>
              {c.followers && <span style={{ color: 'var(--text-muted)' }}>· {c.followers} متابع</span>}
            </div>
          )}
        </div>
        <div className="integ-action">
          {c.connected ? (
            <button className="btn btn-sm btn-secondary" onClick={() => setDisconnectKey(p.id)}>إلغاء الربط</button>
          ) : (
            <button className="btn btn-sm btn-primary" onClick={() => connect(p.id)}>ربط</button>
          )}
        </div>
      </div>
    );
  };

  return (
    <div className="stack-lg">
      <div className="alert alert-info">
        <div className="alert-icon"><Icon name="info" size={16}/></div>
        <div className="alert-body">
          <div className="alert-title">كل ربط يفتح إمكانيات جديدة</div>
          <div className="alert-desc">اربط منصاتك لتنشر مباشرة، تجدول حملات، وتستورد منتجات من متجرك تلقائياً.</div>
        </div>
      </div>

      {/* Social */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>منصات التواصل الاجتماعي</h3>
            <div className="sub">انشر مباشرة أو جدوِل منشوراتك</div>
          </div>
          <div className="row-sm">
            <span className="badge badge-neutral">{platforms.filter(p => connections[p.id]?.connected).length} من {platforms.length} متصل</span>
          </div>
        </div>
        <div className="card-body" style={{ padding: 0 }}>
          {platforms.map(p => <PlatformCard key={p.id} p={p} />)}
        </div>
      </div>

      {/* E-commerce */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>المتاجر الإلكترونية</h3>
            <div className="sub">استورد المنتجات والعروض تلقائياً</div>
          </div>
        </div>
        <div className="card-body" style={{ padding: 0 }}>
          {stores.map(p => <PlatformCard key={p.id} p={p} />)}
        </div>
      </div>

      {/* Messaging */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>قنوات التواصل المباشر</h3>
            <div className="sub">أرسل حملات وعروض لقوائم عملائك</div>
          </div>
        </div>
        <div className="card-body" style={{ padding: 0 }}>
          {messaging.map(p => <PlatformCard key={p.id} p={p} />)}
        </div>
      </div>

      {/* Meta Business Suite deep integration */}
      <div className="card" style={{ background: 'var(--bg-surface-2)' }}>
        <div className="card-head">
          <div>
            <h3>Meta Business Suite</h3>
            <div className="sub">ميزات متقدمة للنشر والحملات الممولة</div>
          </div>
          <span className="badge badge-brand"><Icon name="zap" size={10}/> متقدم</span>
        </div>
        <div className="card-body stack-sm">
          {[
            { label: 'نشر تلقائي عند الجدولة', on: true },
            { label: 'مزامنة ردود الرسائل مع الذكاء الاصطناعي', on: false },
            { label: 'استيراد تحليلات الحملات الممولة', on: true },
            { label: 'تحسين الميزانية تلقائياً (CBO)', on: false },
            { label: 'A/B Testing للمنشورات', on: true },
          ].map((f, i) => (
            <div key={i} className="setting-row">
              <div style={{ fontSize: 14, fontWeight: 500 }}>{f.label}</div>
              <Toggle on={f.on} onChange={() => {}} />
            </div>
          ))}
        </div>
      </div>

      <ConfirmModal
        open={!!disconnectKey}
        onClose={() => setDisconnectKey(null)}
        onConfirm={doDisconnect}
        title="إلغاء الربط؟"
        message="سيتم إيقاف النشر التلقائي والمزامنة لهذه المنصة. يمكنك إعادة الربط لاحقاً."
        confirmText="إلغاء الربط"
        variant="danger"
      />
    </div>
  );
};

// ====== Publishing Tab ======
const PublishingTab = () => {
  const toast = useToast();
  return (
    <div className="stack-lg">
      <div className="alert alert-brand">
        <div className="alert-icon"><Icon name="zap" size={16}/></div>
        <div className="alert-body">
          <div className="alert-title">إعدادات النشر والجدولة</div>
          <div className="alert-desc">تحكّم في مواعيد النشر، والقنوات الافتراضية، وسلوك الجدولة.</div>
        </div>
      </div>

      <div className="card">
        <div className="card-head"><h3>المنصات الافتراضية للنشر</h3></div>
        <div className="card-body stack-sm">
          {[
            { id: 'instagram', name: 'Instagram', on: true, icon: 'instagram' },
            { id: 'facebook', name: 'Facebook', on: true, icon: 'facebook' },
            { id: 'tiktok', name: 'TikTok', on: false, icon: 'video' },
            { id: 'snapchat', name: 'Snapchat', on: false, icon: 'flash' },
            { id: 'x', name: 'X (Twitter)', on: false, icon: 'send' },
          ].map(p => (
            <div key={p.id} className="setting-row">
              <div className="row-sm" style={{ alignItems: 'center' }}>
                <Icon name={p.icon} size={16}/>
                <div style={{ fontWeight: 600, fontSize: 14 }}>{p.name}</div>
              </div>
              <Toggle on={p.on} onChange={() => {}} />
            </div>
          ))}
        </div>
      </div>

      <div className="brand-grid-2">
        <div className="card">
          <div className="card-head"><h3>أفضل أوقات النشر</h3></div>
          <div className="card-body">
            <div className="input-label" style={{ marginBottom: 8 }}>حسب تحليلات جمهورك:</div>
            <div className="stack-sm">
              {[
                { day: 'الأحد', times: ['١١:٠٠ ص', '٨:٣٠ م'] },
                { day: 'الإثنين', times: ['٩:٠٠ ص', '٧:٠٠ م'] },
                { day: 'الثلاثاء', times: ['١٢:٠٠ م', '٩:٠٠ م'] },
                { day: 'الأربعاء', times: ['١٠:٣٠ ص', '٨:٠٠ م'] },
                { day: 'الخميس', times: ['١:٠٠ م', '١٠:٠٠ م'] },
                { day: 'الجمعة', times: ['بعد الجمعة', '٩:٠٠ م'] },
                { day: 'السبت', times: ['١١:٣٠ ص', '٨:٠٠ م'] },
              ].map((d, i) => (
                <div key={i} className="publish-time-row">
                  <div style={{ fontWeight: 600, fontSize: 13, minWidth: 70 }}>{d.day}</div>
                  <div className="row-sm" style={{ flexWrap: 'wrap' }}>
                    {d.times.map(t => (
                      <span key={t} className="chip" style={{ padding: '4px 10px', fontSize: 12 }}>{t}</span>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="card">
          <div className="card-head"><h3>سلوك الجدولة</h3></div>
          <div className="card-body stack-sm">
            {[
              { label: 'نشر تلقائي عند الموعد المحدد', on: true, desc: 'بدون مراجعة يدوية' },
              { label: 'إرسال تنبيه قبل ١٥ دقيقة', on: true, desc: 'على البريد والجوال' },
              { label: 'إعادة نشر المنشورات الناجحة', on: false, desc: 'بعد ٣٠ يوماً' },
              { label: 'موافقة المالك قبل النشر', on: false, desc: 'مراجعة إلزامية' },
            ].map((s, i) => (
              <div key={i} className="setting-row setting-row-block">
                <div>
                  <div style={{ fontSize: 14, fontWeight: 600 }}>{s.label}</div>
                  <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{s.desc}</div>
                </div>
                <Toggle on={s.on} onChange={() => {}} />
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="row" style={{ justifyContent: 'flex-end' }}>
        <button className="btn btn-primary" onClick={() => toast.success('تم الحفظ')}>
          <Icon name="check" size={14}/> حفظ إعدادات النشر
        </button>
      </div>
    </div>
  );
};

// ====== Main Billing+Settings Screen ======
const BillingScreen = ({ initialTab = 'billing' }) => {
  const [tab, setTab] = useState_b(initialTab);

  const tabs = [
    { id: 'profile',      label: 'الملف الشخصي' },
    { id: 'brand',        label: 'هوية العلامة', highlight: true },
    { id: 'integrations', label: 'التكاملات والربط', highlight: true },
    { id: 'publishing',   label: 'النشر والجدولة', highlight: true },
    { id: 'team',         label: 'الفريق' },
    { id: 'billing',      label: 'الفوترة' },
    { id: 'notifications',label: 'الإشعارات' },
    { id: 'api',          label: 'API' },
  ];

  const invoices = [
    { date: '١ أبريل ٢٠٢٦', num: 'SADA-2026-0412', amount: '٢٩٩ ر.س', status: 'مدفوعة' },
    { date: '١ مارس ٢٠٢٦', num: 'SADA-2026-0389', amount: '٢٩٩ ر.س', status: 'مدفوعة' },
    { date: '١ فبراير ٢٠٢٦', num: 'SADA-2026-0356', amount: '٢٩٩ ر.س', status: 'مدفوعة' },
    { date: '١ يناير ٢٠٢٦', num: 'SADA-2026-0312', amount: '٢٩٩ ر.س', status: 'مدفوعة' },
  ];

  return (
    <div className="stack-lg">
      <div className="settings-tabs-scroll">
        <div className="settings-tabs">
          {tabs.map(t => (
            <button
              key={t.id}
              onClick={() => setTab(t.id)}
              data-active={tab === t.id}
              className="settings-tab-btn"
            >
              {t.label}
              {t.highlight && tab !== t.id && <span className="settings-tab-dot" />}
            </button>
          ))}
        </div>
      </div>

      {tab === 'brand'        && <BrandIdentityTab />}
      {tab === 'integrations' && <IntegrationsTab />}
      {tab === 'publishing'   && <PublishingTab />}

      {tab === 'billing' && (
        <>
          {/* Balance hero */}
          <div className="billing-hero-grid">
            <div className="card billing-balance-card">
              <svg style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', opacity: 0.1 }}>
                <pattern id="bill-p" width="40" height="40" patternUnits="userSpaceOnUse">
                  <path d="M20 0 L40 20 L20 40 L0 20 Z" fill="none" stroke="#fff" strokeWidth="0.6" />
                </pattern>
                <rect width="100%" height="100%" fill="url(#bill-p)" />
              </svg>
              <div style={{ position: 'relative' }}>
                <div style={{ fontSize: 12, opacity: 0.85, letterSpacing: '0.05em', marginBottom: 6 }}>الرصيد الحالي</div>
                <div className="billing-balance-value">
                  <span className="num">١٬٢٤٠</span>
                  <span className="unit">/ ٢٬٠٠٠ توكن</span>
                </div>
                <div style={{
                  height: 6, background: 'rgba(255,255,255,0.2)',
                  borderRadius: 99, marginTop: 16, overflow: 'hidden',
                }}>
                  <div style={{ width: '62%', height: '100%', background: '#fff', borderRadius: 99 }} />
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 10, fontSize: 12, opacity: 0.8 }}>
                  <span>٦٢٪ مستخدم هذا الشهر</span>
                  <span>تتجدد خلال ١٢ يوماً</span>
                </div>
                <div className="row" style={{ marginTop: 24, flexWrap: 'wrap' }}>
                  <button className="btn btn-lg" style={{ background: '#fff', color: 'var(--sada-700)' }}>
                    <Icon name="plus" size={16} /> شحن توكنز
                  </button>
                  <button className="btn btn-lg btn-ghost" style={{ color: '#fff', border: '1px solid rgba(255,255,255,0.3)' }}>
                    ترقية الباقة
                  </button>
                </div>
              </div>
            </div>

            <div className="card">
              <div className="card-head"><h3>طرق الدفع</h3></div>
              <div className="card-body stack-sm">
                {[
                  { type: 'mada', label: 'مدى', last4: '٤٥٦٧', default: true },
                  { type: 'visa', label: 'Visa', last4: '١٢٣٤', default: false },
                  { type: 'applepay', label: 'Apple Pay', last4: '', default: false },
                ].map((pm, i) => (
                  <div key={i} className="payment-method-row" data-default={pm.default}>
                    <div style={{
                      width: 40, height: 28, borderRadius: 5,
                      background: pm.type === 'mada' ? '#0F766E' : pm.type === 'visa' ? '#1A1F71' : '#000',
                      color: '#fff',
                      fontSize: 9, fontWeight: 800,
                      display: 'grid', placeItems: 'center',
                      letterSpacing: '0.02em',
                    }}>
                      {pm.type === 'mada' ? 'مدى' : pm.type === 'visa' ? 'VISA' : ''}
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontSize: 13, fontWeight: 600 }}>{pm.label} {pm.last4 && `•••• ${pm.last4}`}</div>
                      {pm.default && <div style={{ fontSize: 11, color: 'var(--accent-text)', fontWeight: 600 }}>افتراضي</div>}
                    </div>
                    <button className="btn btn-icon btn-icon-sm btn-ghost"><Icon name="settings" size={14} /></button>
                  </div>
                ))}
                <button className="btn btn-sm btn-secondary" style={{ width: '100%', marginTop: 4 }}>
                  <Icon name="plus" size={14} /> إضافة طريقة دفع
                </button>
              </div>
            </div>
          </div>

          {/* Plans */}
          <div className="grid-3">
            {[
              { name: 'Starter', price: '٩٩ ر.س', desc: '١٬٠٠٠ توكن/شهر · ١ workspace', featured: false },
              { name: 'Growth', price: '٢٩٩ ر.س', desc: '٢٬٠٠٠ توكن/شهر · ٣ workspaces', featured: true, current: true },
              { name: 'Agency', price: '٧٩٩ ر.س', desc: '١٠٬٠٠٠ توكن/شهر · unlimited', featured: false },
            ].map((p, i) => (
              <div key={i} className="card" style={{
                padding: 20,
                borderColor: p.current ? 'var(--accent)' : 'var(--border-subtle)',
                background: p.featured ? 'var(--bg-surface-2)' : 'var(--bg-surface)',
                position: 'relative',
              }}>
                {p.current && (
                  <span style={{
                    position: 'absolute', top: 12, left: 12,
                    fontSize: 10, fontWeight: 700,
                    padding: '3px 8px', borderRadius: 99,
                    background: 'var(--accent)', color: '#fff',
                  }}>باقتك الحالية</span>
                )}
                <div style={{ fontSize: 14, fontWeight: 700, marginBottom: 6 }}>{p.name}</div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: 4, marginBottom: 8 }}>
                  <span style={{ fontSize: 26, fontWeight: 800 }}>{p.price}</span>
                  <span style={{ fontSize: 12, color: 'var(--text-muted)' }}>/شهر</span>
                </div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 14 }}>{p.desc}</div>
                <button className={`btn btn-sm ${p.current ? 'btn-secondary' : 'btn-primary'}`} style={{ width: '100%' }}>
                  {p.current ? 'الباقة الحالية' : 'ترقية'}
                </button>
              </div>
            ))}
          </div>

          {/* Invoices */}
          <div className="card">
            <div className="card-head">
              <div>
                <h3>الفواتير</h3>
                <div className="sub">شامل ضريبة القيمة المضافة ١٥٪</div>
              </div>
              <button className="btn btn-sm btn-ghost"><Icon name="download" size={14} /> تصدير الكل</button>
            </div>
            <div className="table-scroll">
              <table className="cmp-table">
                <thead>
                  <tr>
                    {['التاريخ', 'رقم الفاتورة', 'المبلغ', 'الحالة', ''].map((h, i) => (
                      <th key={i}>{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {invoices.map((inv, i) => (
                    <tr key={i}>
                      <td>{inv.date}</td>
                      <td className="mono" style={{ fontSize: 12 }}>{inv.num}</td>
                      <td style={{ fontWeight: 600 }} className="mono">{inv.amount}</td>
                      <td><span className="badge badge-success">{inv.status}</span></td>
                      <td><button className="btn btn-sm btn-ghost"><Icon name="download" size={14} /> PDF</button></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}

      {!['billing', 'brand', 'integrations', 'publishing'].includes(tab) && (
        <div className="card">
          <div className="card-body stack" style={{ padding: 40, textAlign: 'center', alignItems: 'center' }}>
            <div style={{
              width: 48, height: 48, borderRadius: 12,
              background: 'var(--accent-soft)', color: 'var(--accent)',
              display: 'grid', placeItems: 'center',
            }}>
              <Icon name="settings" size={24} />
            </div>
            <div style={{ fontSize: 16, fontWeight: 700 }}>
              {tabs.find(t => t.id === tab)?.label}
            </div>
            <div style={{ fontSize: 13, color: 'var(--text-muted)', maxWidth: 400 }}>
              هذا القسم جاهز للاستخدام — الإعدادات والحقول المخصصة لـ«{tabs.find(t => t.id === tab)?.label}» متوفرة بنفس نمط تبويب الفوترة.
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

Object.assign(window, { BillingScreen });
