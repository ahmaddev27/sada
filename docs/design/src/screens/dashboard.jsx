/* global React, Icon, Sparkline, ImgPlaceholder, Avatar, Flag */
// Sada — Dashboard Home

const DashboardScreen = ({ variation = 'A' }) => {
  const userName = 'أحمد';
  const kpis = [
    { label: 'منشورات هذا الأسبوع', value: '١٢', delta: '+20%', up: true, spark: [3, 5, 4, 7, 6, 8, 12] },
    { label: 'الوصول (Reach)',      value: '45,230', delta: '+12.4%', up: true, spark: [28, 32, 30, 38, 41, 43, 45] },
    { label: 'معدل التفاعل',         value: '٤.٨٪', delta: '+0.6%', up: true, spark: [3.2, 3.8, 3.6, 4.1, 4.3, 4.5, 4.8] },
    { label: 'التوكنز المستهلكة',    value: '1,240', delta: '62% من الباقة', up: null, spark: [200, 420, 560, 780, 900, 1100, 1240] },
  ];

  const scheduled = [
    { day: 'اليوم', date: '٢٠ أبريل', time: '٨:٣٠ م', title: 'إطلاق مجموعة رمضان الجديدة', platform: 'instagram', status: 'جاهز', color: 'var(--sada-500)' },
    { day: 'اليوم', date: '٢٠ أبريل', time: '١١:٠٠ م', title: 'قصة: خلف الكواليس من متجرنا', platform: 'instagram', status: 'جاهز', color: 'var(--sand-500)' },
    { day: 'غداً',  date: '٢١ أبريل', time: '٦:٠٠ م', title: 'عرض ٣٠٪ خصم لمدة ٢٤ ساعة', platform: 'facebook', status: 'مسودة', color: 'var(--info)' },
  ];

  return (
    <div className="stack-lg">
      {/* Welcome + AI insight row */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.6fr 1fr', gap: 16 }} className="welcome-row">
        <div style={{
          background: 'linear-gradient(135deg, var(--sada-600), var(--sada-500) 60%, var(--sada-400))',
          borderRadius: 'var(--radius-lg)',
          padding: '24px 28px',
          color: '#fff',
          position: 'relative',
          overflow: 'hidden',
          minHeight: 140,
        }}>
          <div style={{
            position: 'absolute', top: -40, left: -40,
            width: 240, height: 240,
            background: 'radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%)',
            pointerEvents: 'none',
          }} />
          <GeometricCorner />
          <div style={{ fontSize: 13, opacity: 0.85, marginBottom: 6 }}>صباح الخير،</div>
          <h2 style={{ margin: 0, fontSize: 26, fontWeight: 700, letterSpacing: '-0.01em' }}>
            {userName} — لديك ٣ منشورات مجدولة اليوم
          </h2>
          <p style={{ margin: '8px 0 16px', opacity: 0.88, fontSize: 14, maxWidth: 540 }}>
            الوصول الكلي هذا الأسبوع ارتفع ١٢٪. استمر على هذه الوتيرة.
          </p>
          <div style={{ display: 'flex', gap: 8 }}>
            <button className="btn btn-sm" style={{ background: '#fff', color: 'var(--sada-700)' }}>
              <Icon name="sparkle" size={14} /> ولّد منشوراً جديداً
            </button>
            <button className="btn btn-sm btn-ghost" style={{ color: '#fff', border: '1px solid rgba(255,255,255,0.3)' }}>
              عرض الجدول
            </button>
          </div>
        </div>

        <AiInsightCard />
      </div>

      {/* KPI row */}
      <div className="grid-4">
        {kpis.map((k, i) => (
          <div className="kpi-card" key={i}>
            <div className="kpi-label">{k.label}</div>
            <div className="kpi-value">{k.value}</div>
            <div className={`kpi-delta ${k.up ? 'delta-up' : k.up === false ? 'delta-down' : ''}`}>
              {k.up !== null && <Icon name={k.up ? 'up' : 'down'} size={14} />}
              <span>{k.delta}</span>
            </div>
            <div className="kpi-spark">
              <Sparkline values={k.spark} color={i === 3 ? 'var(--sand-500)' : 'var(--sada-500)'} />
            </div>
          </div>
        ))}
      </div>

      {/* Seasonal banner */}
      <SeasonalBanner />

      {/* Two-column */}
      <div style={{ display: 'grid', gridTemplateColumns: '1.5fr 1fr', gap: 16 }} className="two-col">
        {/* Upcoming posts timeline */}
        <div className="card">
          <div className="card-head">
            <div>
              <h3>منشورات قادمة</h3>
              <div className="sub">الأيام السبعة القادمة</div>
            </div>
            <button className="btn btn-sm btn-ghost">عرض الكل <Icon name="arrowLeft" size={14} /></button>
          </div>
          <div className="card-body" style={{ padding: 0 }}>
            {scheduled.map((s, i) => (
              <div key={i} style={{
                padding: '14px 20px',
                borderBottom: i < scheduled.length - 1 ? '1px solid var(--border-subtle)' : 'none',
                display: 'flex',
                alignItems: 'center',
                gap: 14,
                transition: 'background var(--dur-fast)',
              }}
              onMouseEnter={e => e.currentTarget.style.background = 'var(--bg-muted)'}
              onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
              >
                <div style={{ width: 64, textAlign: 'center', flexShrink: 0 }}>
                  <div style={{ fontSize: 11, color: 'var(--text-muted)', fontWeight: 600 }}>{s.day}</div>
                  <div style={{ fontSize: 13, fontWeight: 700 }}>{s.date.split(' ')[0]}</div>
                  <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{s.date.split(' ')[1]}</div>
                </div>
                <div style={{ width: 2, height: 40, background: s.color, borderRadius: 2, flexShrink: 0 }} />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 14, fontWeight: 600, marginBottom: 2 }}>{s.title}</div>
                  <div style={{ fontSize: 12, color: 'var(--text-muted)', display: 'flex', gap: 10 }}>
                    <span style={{ display: 'flex', gap: 4, alignItems: 'center' }}>
                      <Icon name={s.platform} size={12} /> {s.platform === 'instagram' ? 'انستجرام' : 'فيسبوك'}
                    </span>
                    <span>•</span>
                    <span style={{ display: 'flex', gap: 4, alignItems: 'center' }}>
                      <Icon name="clock" size={12} /> {s.time}
                    </span>
                  </div>
                </div>
                <span className={`badge ${s.status === 'جاهز' ? 'badge-success' : 'badge-warning'}`}>{s.status}</span>
                <button className="btn btn-icon btn-icon-sm btn-ghost"><Icon name="chevronLeft" size={14} /></button>
              </div>
            ))}
          </div>
        </div>

        {/* Quick actions + team */}
        <div className="stack">
          <div className="card">
            <div className="card-head"><h3>إجراءات سريعة</h3></div>
            <div className="card-body" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
              {[
                { icon: 'sparkle', label: 'توليد منشور', color: 'var(--sada-500)' },
                { icon: 'calendar', label: 'جدولة', color: 'var(--info)' },
                { icon: 'megaphone', label: 'حملة مدفوعة', color: 'var(--sand-500)' },
                { icon: 'chart', label: 'تقرير', color: 'var(--ink-500)' },
              ].map((q, i) => (
                <button key={i} className="card-hoverable" style={{
                  padding: '14px 12px',
                  background: 'var(--bg-muted)',
                  border: '1px solid var(--border-subtle)',
                  borderRadius: 12,
                  display: 'flex', flexDirection: 'column',
                  alignItems: 'flex-start', gap: 8, cursor: 'pointer',
                  transition: 'all var(--dur-fast)',
                }}>
                  <div style={{
                    width: 32, height: 32,
                    background: 'var(--bg-surface)',
                    border: '1px solid var(--border-subtle)',
                    borderRadius: 8,
                    display: 'grid', placeItems: 'center',
                    color: q.color,
                  }}>
                    <Icon name={q.icon} size={16} />
                  </div>
                  <div style={{ fontSize: 13, fontWeight: 600 }}>{q.label}</div>
                </button>
              ))}
            </div>
          </div>

          <div className="card">
            <div className="card-head">
              <h3>الفريق</h3>
              <button className="btn btn-sm btn-ghost">دعوة</button>
            </div>
            <div className="card-body stack-sm">
              {[
                { name: 'أحمد العتيبي', role: 'مالك', status: 'متصل' },
                { name: 'نورة السبيعي', role: 'محرر محتوى', status: 'متصل' },
                { name: 'فيصل الدوسري', role: 'مصمم', status: 'غير متصل' },
              ].map((m, i) => (
                <div key={i} style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
                  <div style={{ position: 'relative' }}>
                    <Avatar name={m.name} size={32} />
                    <span style={{
                      position: 'absolute', bottom: 0, left: 0,
                      width: 9, height: 9, borderRadius: '50%',
                      background: m.status === 'متصل' ? 'var(--success)' : 'var(--ink-300)',
                      border: '2px solid var(--bg-surface)',
                    }} />
                  </div>
                  <div style={{ flex: 1 }}>
                    <div style={{ fontSize: 13, fontWeight: 600 }}>{m.name}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{m.role}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const AiInsightCard = () => (
  <div className="card" style={{
    background: 'linear-gradient(180deg, var(--bg-surface), var(--bg-surface-2))',
    position: 'relative',
    overflow: 'hidden',
  }}>
    <div className="card-body stack">
      <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
        <div style={{
          width: 28, height: 28,
          background: 'linear-gradient(135deg, var(--sada-500), var(--sand-500))',
          borderRadius: 8, display: 'grid', placeItems: 'center',
          color: '#fff', fontSize: 14,
        }}>
          <Icon name="sparkle" size={14} />
        </div>
        <div style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-muted)' }}>رؤية من Sada AI</div>
      </div>
      <h3 style={{ margin: 0, fontSize: 16, fontWeight: 700, lineHeight: 1.5 }}>
        منشوراتك المسائية (٨–١٠م) تحقق تفاعلاً أعلى بـ <span style={{ color: 'var(--sada-500)' }}>٤٥٪</span> من الصباحية.
      </h3>
      <div style={{ fontSize: 13, color: 'var(--text-muted)', lineHeight: 1.7 }}>
        جرّب جدولة المزيد في هذه النافذة الزمنية لتعظيم الوصول.
      </div>
      <div style={{ display: 'flex', gap: 8 }}>
        <button className="btn btn-sm btn-accent-soft">طبّق الاقتراح</button>
        <button className="btn btn-sm btn-ghost">لاحقاً</button>
      </div>
    </div>
  </div>
);

const SeasonalBanner = () => (
  <div className="seasonal-banner" style={{
    borderRadius: 16,
    padding: '20px 24px',
    display: 'flex',
    alignItems: 'center',
    gap: 20,
    position: 'relative',
    overflow: 'hidden',
  }}>
    <div style={{
      position: 'absolute', left: 0, top: 0, bottom: 0, width: 180,
      opacity: 0.15, pointerEvents: 'none',
    }}>
      <PalmMotif />
    </div>
    <div style={{
      width: 64, height: 64,
      background: 'var(--sand-500)',
      borderRadius: 14,
      display: 'grid', placeItems: 'center',
      color: '#fff',
      flexShrink: 0,
      boxShadow: '0 8px 20px rgba(200,150,95,0.35)',
    }}>
      <Icon name="moon" size={28} />
    </div>
    <div style={{ flex: 1, minWidth: 0 }}>
      <div className="seasonal-banner-eyebrow" style={{ fontSize: 12, fontWeight: 700, marginBottom: 4 }}>
        موسم قادم · اليوم الوطني السعودي
      </div>
      <h3 style={{ margin: 0, fontSize: 18, fontWeight: 700, color: 'var(--text-primary)' }}>
        خلال <span className="seasonal-banner-accent">١٤ يوماً</span> — هل نولّد لك حملة جاهزة؟
      </h3>
      <div style={{ fontSize: 13, color: 'var(--text-secondary)', marginTop: 4 }}>
        ٧ قوالب منشورات، تصاميم جاهزة، وهاشتاجات مختارة — كل شيء بلهجتك.
      </div>
    </div>
    <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
      <button className="btn btn-ghost btn-sm seasonal-banner-later">لاحقاً</button>
      <button className="btn btn-sm" style={{ background: 'var(--sand-500)', color: '#fff' }}>
        ولّد الحملة <Icon name="arrowLeft" size={14} />
      </button>
    </div>
  </div>
);

const GeometricCorner = () => (
  <svg viewBox="0 0 120 120" style={{
    position: 'absolute', top: 0, left: 0, width: 120, height: 120,
    opacity: 0.18, pointerEvents: 'none',
  }}>
    <g fill="none" stroke="#fff" strokeWidth="1">
      <polygon points="60,10 95,30 95,70 60,90 25,70 25,30" />
      <polygon points="60,25 82,37 82,63 60,75 38,63 38,37" />
      <circle cx="60" cy="50" r="10" />
      <path d="M30 50 L 45 50 M75 50 L 90 50 M60 20 L 60 30 M60 70 L 60 80" />
    </g>
  </svg>
);

const PalmMotif = () => (
  <svg viewBox="0 0 200 120" style={{ width: '100%', height: '100%' }}>
    <g fill="none" stroke="var(--sand-700)" strokeWidth="1.2" strokeLinecap="round">
      {[...Array(9)].map((_, i) => {
        const angle = -85 + i * 12;
        const rad = (angle * Math.PI) / 180;
        const x2 = 40 + Math.cos(rad) * 60;
        const y2 = 110 - Math.sin(rad) * 70;
        return <path key={i} d={`M40 110 Q ${40 + Math.cos(rad) * 30} ${110 - Math.sin(rad) * 50} ${x2} ${y2}`} />;
      })}
      <path d="M40 110 L 40 60" strokeWidth="2" />
    </g>
  </svg>
);

Object.assign(window, { DashboardScreen });
