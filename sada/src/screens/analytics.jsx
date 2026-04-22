/* global React, Icon, Sparkline */
// Sada — Analytics

const AnalyticsScreen = () => {
  const kpis = [
    { label: 'الوصول', value: '١٢٥,٤٠٠', delta: '+18.2%', up: true },
    { label: 'الانطباعات', value: '٣٢٤,٨٠٠', delta: '+22.4%', up: true },
    { label: 'معدل التفاعل', value: '٤.٨٪', delta: '+0.6%', up: true },
    { label: 'النقرات', value: '٨,٢٤٠', delta: '+12.1%', up: true },
    { label: 'المتابعون الجدد', value: '٥٦٢', delta: '+8.3%', up: true },
    { label: 'ROAS', value: '٤.٠x', delta: '+0.4x', up: true },
  ];

  const insights = [
    { emoji: '🎯', title: 'منشورات بأسئلة مباشرة تحقق تفاعلاً أعلى بـ ٦٢٪', desc: 'آخر ٣٠ يوم — مقارنة بالمنشورات العادية' },
    { emoji: '📅', title: 'أفضل يوم للنشر: الثلاثاء ٨:٣٠م', desc: 'متوسط تفاعل ٦.٢٪ في هذه النافذة' },
    { emoji: '⚠️', title: 'الوصول انخفض ١٥٪ في آخر ٣ أيام', desc: 'قد يكون بسبب تغيير في خوارزمية انستجرام' },
  ];

  return (
    <div className="stack-lg">
      {/* Top bar */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
        <div className="segmented">
          {['نظرة عامة', 'حسب المنصة', 'حسب المنشور', 'الجمهور', 'التقارير'].map((t, i) => (
            <button key={i} data-active={i === 0}>{t}</button>
          ))}
        </div>
        <div style={{ display: 'flex', gap: 8 }}>
          <div className="segmented">
            {['٧ أيام', '٣٠ يوم', '٩٠ يوم', 'مخصص'].map((t, i) => (
              <button key={i} data-active={i === 1}>{t}</button>
            ))}
          </div>
          <button className="btn btn-sm btn-secondary"><Icon name="download" size={14} /> تصدير تقرير PDF</button>
        </div>
      </div>

      {/* KPIs */}
      <div className="grid-3" style={{ gridTemplateColumns: 'repeat(6, 1fr)' }}>
        {kpis.map((k, i) => (
          <div key={i} className="kpi-card" style={{ padding: 16 }}>
            <div className="kpi-label">{k.label}</div>
            <div className="kpi-value" style={{ fontSize: 22 }}>{k.value}</div>
            <div className={`kpi-delta ${k.up ? 'delta-up' : 'delta-down'}`}>
              <Icon name={k.up ? 'up' : 'down'} size={12} />
              <span>{k.delta}</span>
            </div>
          </div>
        ))}
      </div>

      {/* Engagement chart */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>التفاعل عبر الوقت</h3>
            <div className="sub">آخر ٣٠ يوم — تفاعل + وصول</div>
          </div>
          <div style={{ display: 'flex', gap: 12, fontSize: 12 }}>
            <span style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
              <span className="dot dot-brand" /> التفاعل
            </span>
            <span style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
              <span className="dot" style={{ background: 'var(--sand-500)' }} /> الوصول
            </span>
          </div>
        </div>
        <div className="card-body" style={{ padding: 24 }}>
          <EngagementChart />
        </div>
      </div>

      {/* Heatmap + top posts */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.3fr 1fr', gap: 16 }} className="two-col">
        <div className="card">
          <div className="card-head">
            <div>
              <h3>أفضل أوقات النشر</h3>
              <div className="sub">بناءً على تفاعل آخر ٦٠ يوم</div>
            </div>
          </div>
          <div className="card-body" style={{ padding: 20 }}>
            <Heatmap />
          </div>
        </div>

        <div className="card">
          <div className="card-head"><h3>أفضل المنشورات</h3></div>
          <div style={{ padding: 0 }}>
            {[
              { title: 'إفطار اليوم: وجبات من متجرنا', eng: '٨٫٢٪', reach: '٢٤٬٠٠٠' },
              { title: 'قصة ليلة القدر — دعاء خاص', eng: '٧٫١٪', reach: '١٨٬٥٠٠' },
              { title: 'خصم ٣٠٪ — عرض محدود', eng: '٦٫٤٪', reach: '١٥٬٢٠٠' },
              { title: 'خلف الكواليس في متجرنا', eng: '٥٫٩٪', reach: '١٢٬٤٠٠' },
            ].map((p, i) => (
              <div key={i} style={{
                padding: '12px 20px',
                borderBottom: i < 3 ? '1px solid var(--border-subtle)' : 'none',
                display: 'flex', gap: 12, alignItems: 'center',
              }}>
                <div style={{
                  width: 28, height: 28, borderRadius: 6,
                  background: 'var(--accent-soft)', color: 'var(--accent-text)',
                  display: 'grid', placeItems: 'center',
                  fontWeight: 800, fontSize: 13,
                }}>{i + 1}</div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 13, fontWeight: 600, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{p.title}</div>
                  <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>
                    تفاعل {p.eng} · وصول {p.reach}
                  </div>
                </div>
                <Icon name="chevronLeft" size={14} style={{ color: 'var(--text-muted)' }} />
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* AI insights */}
      <div className="card" style={{
        background: 'linear-gradient(120deg, var(--bg-surface), var(--accent-soft) 120%)',
        borderColor: 'var(--border-brand)',
      }}>
        <div className="card-head">
          <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
            <div style={{
              width: 32, height: 32,
              background: 'linear-gradient(135deg, var(--sada-500), var(--sand-500))',
              borderRadius: 9, display: 'grid', placeItems: 'center', color: '#fff',
            }}><Icon name="sparkle" size={16} /></div>
            <div>
              <h3>رؤى ذكية من Sada AI</h3>
              <div className="sub">تحليلات تلقائية مبنية على بياناتك</div>
            </div>
          </div>
        </div>
        <div className="card-body grid-3">
          {insights.map((ins, i) => (
            <div key={i} style={{
              padding: 18,
              background: 'var(--bg-surface)',
              border: '1px solid var(--border-subtle)',
              borderRadius: 12,
            }}>
              <div style={{ fontSize: 24, marginBottom: 10 }}>{ins.emoji}</div>
              <div style={{ fontSize: 14, fontWeight: 700, lineHeight: 1.5, marginBottom: 6 }}>{ins.title}</div>
              <div style={{ fontSize: 12, color: 'var(--text-muted)', lineHeight: 1.6 }}>{ins.desc}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

const EngagementChart = () => {
  const days = 30;
  const eng = [3.2, 3.8, 3.5, 4.1, 3.9, 4.3, 4.5, 4.2, 4.8, 5.1, 4.7, 5.0, 5.3, 4.9, 5.5, 5.2, 5.8, 5.4, 6.0, 5.7, 6.2, 5.9, 6.4, 6.1, 6.6, 6.3, 6.8, 6.5, 7.0, 6.7];
  const reach = [18, 22, 20, 24, 23, 26, 28, 25, 30, 33, 29, 32, 35, 31, 37, 34, 40, 36, 42, 39, 45, 41, 48, 43, 50, 46, 53, 48, 55, 50];

  const maxEng = Math.max(...eng);
  const maxReach = Math.max(...reach);
  const w = 100, h = 40;

  const engPts = eng.map((v, i) => `${(i / (days - 1)) * w},${h - (v / maxEng) * (h - 4)}`).join(' ');
  const reachPts = reach.map((v, i) => `${(i / (days - 1)) * w},${h - (v / maxReach) * (h - 4)}`).join(' ');

  return (
    <div>
      <svg viewBox={`0 0 ${w} ${h}`} preserveAspectRatio="none" style={{ width: '100%', height: 260, display: 'block' }}>
        <defs>
          <linearGradient id="brandGrad" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stopColor="var(--sada-500)" stopOpacity="0.3" />
            <stop offset="100%" stopColor="var(--sada-500)" stopOpacity="0" />
          </linearGradient>
        </defs>
        {/* Grid */}
        {[0.25, 0.5, 0.75].map((t, i) => (
          <line key={i} x1="0" x2={w} y1={h * t} y2={h * t}
            stroke="var(--border-subtle)" strokeWidth="0.2" vectorEffect="non-scaling-stroke" />
        ))}
        {/* Reach */}
        <polyline points={reachPts} fill="none" stroke="var(--sand-500)"
          strokeWidth="1.5" vectorEffect="non-scaling-stroke" strokeLinejoin="round" strokeLinecap="round"
          strokeDasharray="2 2" />
        {/* Engagement fill */}
        <polygon points={`0,${h} ${engPts} ${w},${h}`} fill="url(#brandGrad)" />
        {/* Engagement line */}
        <polyline points={engPts} fill="none" stroke="var(--sada-500)"
          strokeWidth="2" vectorEffect="non-scaling-stroke" strokeLinejoin="round" strokeLinecap="round" />
      </svg>
      <div style={{
        display: 'flex', justifyContent: 'space-between',
        marginTop: 8, fontSize: 11, color: 'var(--text-muted)',
        direction: 'rtl',
      }}>
        <span>٢٠ أبريل</span>
        <span>١٠ أبريل</span>
        <span>٣١ مارس</span>
        <span>٢٠ مارس</span>
      </div>
    </div>
  );
};

const Heatmap = () => {
  const days = ['أحد', 'اثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت'];
  const hours = [6, 9, 12, 15, 18, 21, 24];

  // Seed pseudo-random intensity; peak evenings
  const data = days.map((_, d) => hours.map((h, i) => {
    const eveningBoost = (h >= 18 && h <= 21) ? 1.5 : 1;
    const weekdayBoost = (d === 2 || d === 4) ? 1.3 : 1;
    return Math.min(1, (0.2 + Math.random() * 0.6) * eveningBoost * weekdayBoost);
  }));

  return (
    <div>
      <div style={{
        display: 'grid',
        gridTemplateColumns: `60px repeat(${hours.length}, 1fr)`,
        gap: 4,
      }}>
        <div />
        {hours.map((h, i) => (
          <div key={i} style={{ fontSize: 10, color: 'var(--text-muted)', textAlign: 'center', fontWeight: 600 }}>
            {h}:٠٠
          </div>
        ))}
        {days.map((day, d) => (
          <React.Fragment key={d}>
            <div style={{ fontSize: 11, color: 'var(--text-muted)', fontWeight: 600, alignSelf: 'center' }}>{day}</div>
            {data[d].map((v, i) => (
              <div key={i} style={{
                aspectRatio: '1/1',
                borderRadius: 4,
                background: `color-mix(in oklab, var(--sada-500) ${Math.round(v * 85)}%, var(--bg-muted))`,
                border: '1px solid var(--border-subtle)',
                minHeight: 24,
              }} />
            ))}
          </React.Fragment>
        ))}
      </div>
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        marginTop: 16, fontSize: 11, color: 'var(--text-muted)',
      }}>
        <span>أقل تفاعلاً</span>
        <div style={{ display: 'flex', gap: 3 }}>
          {[0.1, 0.3, 0.5, 0.7, 0.9].map((v, i) => (
            <div key={i} style={{
              width: 20, height: 12, borderRadius: 2,
              background: `color-mix(in oklab, var(--sada-500) ${Math.round(v * 85)}%, var(--bg-muted))`,
            }} />
          ))}
        </div>
        <span>أعلى تفاعلاً</span>
      </div>
    </div>
  );
};

Object.assign(window, { AnalyticsScreen });
