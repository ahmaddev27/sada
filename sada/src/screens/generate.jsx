/* global React, Icon, Toggle, Flag, Avatar, ImgPlaceholder, CustomSelect */
// Sada — Generate Content Screen

const { useState: useState_g, useMemo: useMemo_g } = React;

// Pre-baked samples per dialect × prompt
const DIALECT_SAMPLES = {
  'fos': [
    {
      title: 'الخيار ١ · موجز وعصري',
      body: 'بمناسبة اليوم الوطني، نقدّم لكم خصماً يصل إلى ٣٠٪ على كل المنتجات. احتفلوا معنا بوطنٍ صنعنا فيه الذكرى، ودعوا اختياراتكم تنبض بالفخر.\n\nاطلب الآن — التوصيل خلال ٤٨ ساعة.',
      tags: ['#اليوم_الوطني_السعودي', '#خصومات', '#متجر_أنيق', '#نحلم_ونحقق'],
    },
    {
      title: 'الخيار ٢ · قصصي مُؤثّر',
      body: 'كل منتج في متجرنا يحمل قصة. وقصتنا بدأت بحلمٍ صغير في وطنٍ كبير.\n\nفي اليوم الوطني، نحتفل بكل من آمن بنا — ونقدّم لكم ٣٠٪ خصماً كتقديرٍ صادق.',
      tags: ['#اليوم_الوطني', '#هي_لنا_دار', '#متجر_أنيق'],
    },
    {
      title: 'الخيار ٣ · CTA مباشر',
      body: 'خصم ٣٠٪ — ٤٨ ساعة فقط.\nبمناسبة اليوم الوطني السعودي، استمتع بعروضٍ غير مسبوقة على جميع المنتجات.\n\nالرابط في البايو ↓',
      tags: ['#عروض_اليوم_الوطني', '#السعودية', '#خصومات_حصرية'],
    },
  ],
  'sa': [
    {
      title: 'الخيار ١ · حماسي',
      body: 'بمناسبة اليوم الوطني السعودي، عطيناكم ٣٠٪ خصم على كل المنتجات! 🇸🇦\nفرصة ما تتعوّض — اختاروا اللي يعجبكم والتوصيل على حسابنا.\n\nاضغطوا الرابط في البايو ولا تفوّتون!',
      tags: ['#اليوم_الوطني_السعودي', '#متجر_أنيق', '#ترا_الفرصة_ذهبية'],
    },
    {
      title: 'الخيار ٢ · دافئ وعاطفي',
      body: 'يوم وطني سعيد عليكم كلكم 🤍\nاحنا في متجر أنيق فخورين إنا جزء من هالوطن، وعشانكم جهّزنا خصم ٣٠٪ على كل شي.\nاللي تشوفه يعجبك، احجزه قبل لا يخلص!',
      tags: ['#اليوم_الوطني', '#هي_لنا_دار', '#سعودي_وافتخر'],
    },
    {
      title: 'الخيار ٣ · قصير ومركّز',
      body: 'خصم ٣٠٪ لمناسبة اليوم الوطني. ٤٨ ساعة بس!\nلا تطنّش — الرابط بالبايو 👆',
      tags: ['#خصومات', '#اليوم_الوطني_السعودي'],
    },
  ],
  'ae': [
    {
      title: 'الخيار ١ · خليجي إماراتي',
      body: 'بمناسبة اليوم الوطني، عطيناكم ٣٠٪ خصم على كل شي في المتجر.\nيا هلا وسهلا فيكم، والعرض لمدة ٤٨ ساعة بس.\n\nالرابط في البايو — ياسكم تفوّتون الفرصة.',
      tags: ['#اليوم_الوطني', '#الامارات', '#عروض_حصرية'],
    },
    {
      title: 'الخيار ٢ · رسمي ودي',
      body: 'يحلى الفخر معاكم — اليوم الوطني يستاهل احتفال خاص.\nخصم ٣٠٪ على جميع المنتجات، ولكم منا أجمل التحيات.',
      tags: ['#الامارات', '#عيد_الاتحاد', '#متجر_أنيق'],
    },
    {
      title: 'الخيار ٣ · CTA مباشر',
      body: 'عرض اليوم الوطني — ٣٠٪ خصم، ٤٨ ساعة فقط.\nاطلب الحين والتوصيل على حسابنا.',
      tags: ['#اليوم_الوطني', '#توصيل_مجاني'],
    },
  ],
  'kw': [
    {
      title: 'الخيار ١ · كويتي دافئ',
      body: 'يا هلا بالشعب الطيب 🤍\nبمناسبة اليوم الوطني، عطيناكم ٣٠٪ خصم على كل شي.\nعرضنا بس لـ٤٨ ساعة — لا تفوّتونه، الرابط بالبايو!',
      tags: ['#اليوم_الوطني_الكويتي', '#كويت', '#عروض'],
    },
    {
      title: 'الخيار ٢ · مختصر',
      body: 'خصم ٣٠٪ على كل المنتجات — ٤٨ ساعة.\nاليوم الوطني يجمعنا 🇰🇼',
      tags: ['#الكويت', '#خصومات'],
    },
    {
      title: 'الخيار ٣ · سردي',
      body: 'احنا في متجر أنيق نحبّكم، ونحب هالبلد اللي احتوانا.\nعشانكم، اخترنا لكم خصم ٣٠٪ احتفالاً باليوم الوطني.',
      tags: ['#الكويت_حبيبتي', '#عروض_الكويت'],
    },
  ],
  'qa': [
    {
      title: 'الخيار ١ · قطري',
      body: 'بمناسبة اليوم الوطني، نهديكم خصم ٣٠٪ على كل المنتجات.\nالعرض ساري لمدة ٤٨ ساعة — احجزوا قبل ما ينتهي.',
      tags: ['#اليوم_الوطني_القطري', '#قطر'],
    },
    { title: 'الخيار ٢', body: 'يومٍ يجمع الكل — عيد وطني مبارك. خصم ٣٠٪ لكم.', tags: ['#قطر', '#خصومات'] },
    { title: 'الخيار ٣', body: 'عرض محدود — ٣٠٪ على كل شيء. ٤٨ ساعة فقط.', tags: ['#عروض'] },
  ],
  'bh': [
    { title: 'الخيار ١', body: 'بمناسبة اليوم الوطني البحريني، عطيناكم ٣٠٪ خصم. العرض لـ٤٨ ساعة.', tags: ['#البحرين', '#اليوم_الوطني'] },
    { title: 'الخيار ٢', body: 'يا هلا بالبحرين — ٣٠٪ خصم احتفالاً بيومنا الوطني.', tags: ['#البحرين_حبيبتي'] },
    { title: 'الخيار ٣', body: 'خصم ٣٠٪ — ٤٨ ساعة بس. لا تطنّشون!', tags: ['#عروض'] },
  ],
  'om': [
    { title: 'الخيار ١', body: 'اليوم الوطني العُماني — ٣٠٪ خصم على كل المنتجات لمدة ٤٨ ساعة.', tags: ['#عُمان', '#اليوم_الوطني_العماني'] },
    { title: 'الخيار ٢', body: 'من متجر أنيق إلى كل عُماني — كل عام وعُمان بخير. خصم ٣٠٪ لكم.', tags: ['#عُمان_أرض_الخير'] },
    { title: 'الخيار ٣', body: 'عرض اليوم الوطني — ٣٠٪ خصم، لا تفوّته.', tags: ['#عروض_عُمان'] },
  ],
};

const DIALECTS = [
  { id: 'fos', label: 'الفصحى', flag: 'ar' },
  { id: 'sa',  label: 'السعودية', flag: 'sa' },
  { id: 'ae',  label: 'الإماراتية', flag: 'ae' },
  { id: 'kw',  label: 'الكويتية', flag: 'kw' },
  { id: 'qa',  label: 'القطرية', flag: 'qa' },
  { id: 'bh',  label: 'البحرينية', flag: 'bh' },
  { id: 'om',  label: 'العُمانية', flag: 'om' },
];

const GenerateScreen = ({ dialect, setDialect }) => {
  const [contentType, setContentType] = useState_g('post');
  const [platform, setPlatform] = useState_g('instagram');
  const [useBrand, setUseBrand] = useState_g(true);
  const [emojis, setEmojis] = useState_g(true);
  const [advancedOpen, setAdvancedOpen] = useState_g(false);
  const [prompt, setPrompt] = useState_g('خصم ٣٠٪ على كل المنتجات بمناسبة اليوم الوطني السعودي');
  const [selected, setSelected] = useState_g(0);
  const [tokens] = useState_g(42);

  const results = useMemo_g(() => DIALECT_SAMPLES[dialect] || DIALECT_SAMPLES.fos, [dialect]);

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20, alignItems: 'start' }} className="gen-grid">
      {/* Right panel — Input */}
      <div className="card" style={{ position: 'sticky', top: 80 }}>
        <div className="card-head">
          <div>
            <h3>لوحة التوليد</h3>
            <div className="sub">اضبط المعايير ثم ولّد ٣ خيارات</div>
          </div>
          <span className="badge badge-brand"><Icon name="zap" size={12} /> {tokens} توكن</span>
        </div>

        <div className="card-body stack-lg">
          {/* Content type */}
          <div className="input-group">
            <label className="input-label">نوع المحتوى</label>
            <div className="segmented" style={{ width: '100%', display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)' }}>
              {[
                { id: 'post', label: 'منشور', icon: 'image' },
                { id: 'reel', label: 'ريل', icon: 'video' },
                { id: 'story', label: 'قصة', icon: 'star' },
                { id: 'ad', label: 'إعلان', icon: 'megaphone' },
              ].map(t => (
                <button key={t.id} data-active={contentType === t.id} onClick={() => setContentType(t.id)}>
                  <Icon name={t.icon} size={14} style={{ marginLeft: 4, verticalAlign: '-3px' }} />
                  {t.label}
                </button>
              ))}
            </div>
          </div>

          {/* Platform */}
          <div className="input-group">
            <label className="input-label">المنصة</label>
            <div style={{ display: 'flex', gap: 8 }}>
              {[
                { id: 'instagram', label: 'انستجرام' },
                { id: 'facebook', label: 'فيسبوك' },
              ].map(p => (
                <button
                  key={p.id}
                  className="chip"
                  data-selected={platform === p.id}
                  onClick={() => setPlatform(p.id)}
                  style={{ flex: 1, justifyContent: 'center', height: 40, fontSize: 14 }}
                >
                  <Icon name={p.id} size={16} />
                  {p.label}
                </button>
              ))}
            </div>
          </div>

          {/* Dialect */}
          <div className="input-group">
            <label className="input-label">اللهجة</label>
            <div style={{
              display: 'flex', gap: 6, flexWrap: 'wrap',
              padding: 4, background: 'var(--bg-muted)', borderRadius: 12,
            }}>
              {DIALECTS.map(d => (
                <button
                  key={d.id}
                  className="chip"
                  data-selected={dialect === d.id}
                  onClick={() => setDialect(d.id)}
                  style={{
                    background: dialect === d.id ? 'var(--bg-surface)' : 'transparent',
                    border: dialect === d.id ? '1px solid var(--accent)' : '1px solid transparent',
                  }}
                >
                  <Flag code={d.flag} />
                  {d.label}
                </button>
              ))}
            </div>
          </div>

          {/* Toggles */}
          <div className="stack-sm">
            <div className="tweaks-row-inline" style={{ padding: '6px 0' }}>
              <div>
                <div style={{ fontSize: 13, fontWeight: 600 }}>استخدام هوية العلامة</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>يطبّق نبرة «متجر أنيق» والكلمات المحظورة</div>
              </div>
              <Toggle on={useBrand} onChange={setUseBrand} />
            </div>
            <div className="tweaks-row-inline" style={{ padding: '6px 0' }}>
              <div>
                <div style={{ fontSize: 13, fontWeight: 600 }}>إيموجيات في المحتوى</div>
                <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>بشكل مناسب للسياق الخليجي</div>
              </div>
              <Toggle on={emojis} onChange={setEmojis} />
            </div>
          </div>

          {/* Prompt */}
          <div className="input-group">
            <label className="input-label">الفكرة</label>
            <textarea
              className="textarea"
              value={prompt}
              onChange={e => setPrompt(e.target.value)}
              placeholder="اكتب وصفاً قصيراً للفكرة..."
              style={{ minHeight: 96 }}
            />
            <div className="input-hint" style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>كن محدداً للحصول على أفضل النتائج</span>
              <span>{prompt.length} حرف</span>
            </div>
          </div>

          {/* Advanced */}
          <div>
            <button
              onClick={() => setAdvancedOpen(v => !v)}
              className="btn btn-ghost btn-sm"
              style={{ padding: 0, color: 'var(--text-muted)' }}
            >
              <Icon name={advancedOpen ? 'chevronDown' : 'chevronLeft'} size={14} />
              إعدادات متقدمة
            </button>
            {advancedOpen && (
              <div style={{
                marginTop: 12,
                padding: 14,
                background: 'var(--bg-muted)',
                borderRadius: 10,
                display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12,
              }}>
                <div className="input-group">
                  <label className="input-label" style={{ fontSize: 12 }}>طول النص</label>
                  <CustomSelect
                    full size="sm"
                    value="med"
                    onChange={() => {}}
                    options={[
                      { value: 'short', label: 'قصير', hint: '50-100 حرف' },
                      { value: 'med',   label: 'متوسط', hint: '150-250 حرف' },
                      { value: 'long',  label: 'طويل',  hint: '300+ حرف' },
                    ]}
                  />
                </div>
                <div className="input-group">
                  <label className="input-label" style={{ fontSize: 12 }}>CTA</label>
                  <input className="input" style={{ height: 34, fontSize: 13 }} placeholder="مثلاً: اطلب الآن" />
                </div>
              </div>
            )}
          </div>

          <button className="btn btn-primary btn-lg" style={{ width: '100%' }}>
            <Icon name="sparkle" size={16} />
            ولّد المحتوى (٣ خيارات)
          </button>
        </div>
      </div>

      {/* Left panel — Output */}
      <div className="stack">
        <div style={{
          display: 'flex', justifyContent: 'space-between', alignItems: 'center',
          padding: '0 4px',
        }}>
          <div style={{ fontSize: 14, fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8 }}>
            <Icon name="sparkle" size={14} style={{ color: 'var(--accent)' }} />
            ٣ خيارات مولّدة · {DIALECTS.find(d => d.id === dialect)?.label}
          </div>
          <button className="btn btn-sm btn-ghost">
            <Icon name="sparkle" size={14} />
            ولّد المزيد
          </button>
        </div>

        {results.map((r, i) => (
          <GeneratedCard
            key={i}
            result={r}
            selected={selected === i}
            onSelect={() => setSelected(i)}
            platform={platform}
          />
        ))}

        {/* Preview */}
        <div className="card" style={{ overflow: 'hidden' }}>
          <div className="card-head">
            <div>
              <h3>معاينة مباشرة</h3>
              <div className="sub">كيف سيبدو المنشور على {platform === 'instagram' ? 'انستجرام' : 'فيسبوك'}</div>
            </div>
          </div>
          <div className="card-body" style={{ background: 'var(--bg-muted)', padding: 24 }}>
            <InstagramPreview content={results[selected]} />
          </div>
        </div>

        {/* Action bar */}
        <div className="card" style={{ padding: 14, display: 'flex', gap: 10, alignItems: 'center', justifyContent: 'space-between' }}>
          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>
            الخيار المختار: <strong style={{ color: 'var(--text-primary)' }}>{results[selected]?.title}</strong>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <button className="btn btn-sm btn-ghost">حفظ كمسودة</button>
            <button className="btn btn-sm btn-secondary"><Icon name="calendar" size={14} /> جدولة</button>
            <button className="btn btn-sm btn-primary"><Icon name="send" size={14} /> نشر فوري</button>
          </div>
        </div>
      </div>
    </div>
  );
};

const GeneratedCard = ({ result, selected, onSelect, platform }) => (
  <div
    onClick={onSelect}
    className="card card-hoverable"
    style={{
      cursor: 'pointer',
      borderColor: selected ? 'var(--accent)' : 'var(--border-subtle)',
      boxShadow: selected ? 'var(--shadow-focus)' : 'none',
    }}
  >
    <div style={{
      padding: '12px 16px',
      background: selected ? 'var(--accent-soft)' : 'var(--bg-muted)',
      borderBottom: '1px solid var(--border-subtle)',
      display: 'flex', justifyContent: 'space-between', alignItems: 'center',
      color: selected ? 'var(--accent-text)' : 'var(--text-secondary)',
      fontSize: 13, fontWeight: 700,
    }}>
      <span>{result.title}</span>
      {selected && <Icon name="check" size={14} />}
    </div>
    <div style={{ padding: 16 }}>
      <p style={{
        margin: 0, fontSize: 14, lineHeight: 1.7,
        color: 'var(--text-primary)',
        whiteSpace: 'pre-line',
      }}>{result.body}</p>
      <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginTop: 12 }}>
        {result.tags.map((t, i) => (
          <span key={i} style={{
            fontSize: 12, color: 'var(--accent-text)',
            padding: '3px 8px',
            background: 'var(--accent-soft)',
            borderRadius: 6, fontWeight: 500,
          }}>{t}</span>
        ))}
      </div>
      <div style={{ display: 'flex', gap: 4, marginTop: 14, justifyContent: 'flex-end', paddingTop: 10, borderTop: '1px solid var(--border-subtle)' }}>
        <button className="btn btn-sm btn-ghost"><Icon name="copy" size={14} /> نسخ</button>
        <button className="btn btn-sm btn-ghost"><Icon name="edit" size={14} /> تعديل</button>
      </div>
    </div>
  </div>
);

const InstagramPreview = ({ content }) => (
  <div style={{
    maxWidth: 420, margin: '0 auto',
    background: 'var(--bg-surface)',
    borderRadius: 12,
    overflow: 'hidden',
    border: '1px solid var(--border-default)',
    direction: 'rtl',
  }}>
    {/* IG header */}
    <div style={{ padding: 12, display: 'flex', alignItems: 'center', gap: 10 }}>
      <div style={{
        width: 36, height: 36, borderRadius: '50%',
        background: 'linear-gradient(135deg, var(--sada-500), var(--sand-500))',
        display: 'grid', placeItems: 'center', color: '#fff', fontWeight: 700, fontSize: 14,
      }}>أن</div>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 13, fontWeight: 700 }}>متجر_أنيق</div>
        <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>الرياض، السعودية</div>
      </div>
    </div>
    {/* Image */}
    <div style={{
      aspectRatio: '1/1',
      background: 'linear-gradient(135deg, var(--sand-100), var(--sand-200))',
      display: 'grid', placeItems: 'center',
      position: 'relative',
    }}>
      <div style={{ textAlign: 'center', color: 'var(--sand-700)' }}>
        <div style={{ fontSize: 48, fontWeight: 800 }}>٣٠٪</div>
        <div style={{ fontSize: 14, fontWeight: 600, marginTop: 4 }}>خصم اليوم الوطني</div>
      </div>
    </div>
    {/* Actions */}
    <div style={{ padding: '10px 12px', display: 'flex', gap: 14, color: 'var(--text-primary)' }}>
      <Icon name="heart" size={20} />
      <Icon name="message" size={20} />
      <Icon name="send" size={20} />
    </div>
    {/* Caption */}
    <div style={{ padding: '0 12px 14px', fontSize: 13, lineHeight: 1.7 }}>
      <span style={{ fontWeight: 700, marginLeft: 6 }}>متجر_أنيق</span>
      <span style={{ whiteSpace: 'pre-line' }}>{content?.body}</span>
      <div style={{ marginTop: 6, color: 'var(--info)' }}>{content?.tags.join(' ')}</div>
    </div>
  </div>
);

Object.assign(window, { GenerateScreen });
