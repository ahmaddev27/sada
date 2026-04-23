/* global React, Icon */
// Sada — Seasonal Hub

const SeasonalScreen = () => {
  const upcoming = {
    name: 'اليوم الوطني السعودي',
    subtitle: 'ذكرى توحيد المملكة · ٢٣ سبتمبر',
    countdown: { days: 14, hours: 6, minutes: 42 },
    templates: 12,
  };

  const seasons = [
    { name: 'رمضان المبارك', date: '٧ مارس — ٥ أبريل', templates: 24, countdown: 'بدأ', color: 'var(--sada-500)', icon: 'moon', featured: true },
    { name: 'عيد الفطر', date: '٦ أبريل ٢٠٢٦', templates: 18, countdown: '٥ أيام', color: 'var(--sand-500)', icon: 'star' },
    { name: 'عيد الأضحى', date: '١٧ يونيو ٢٠٢٦', templates: 14, countdown: '٦٠ يوماً', color: 'var(--sand-600)', icon: 'moon' },
    { name: 'اليوم الوطني السعودي', date: '٢٣ سبتمبر ٢٠٢٦', templates: 21, countdown: '١٤ يوماً', color: 'var(--success)', icon: 'crown' },
    { name: 'يوم التأسيس', date: '٢٢ فبراير ٢٠٢٧', templates: 16, countdown: '٣٠٥ يوماً', color: 'var(--sada-600)', icon: 'crown' },
    { name: 'يوم الاتحاد الإماراتي', date: '٢ ديسمبر ٢٠٢٦', templates: 19, countdown: '٨٤ يوماً', color: 'var(--info)', icon: 'crown' },
  ];

  return (
    <div className="stack-lg">
      {/* Hero */}
      <div style={{
        position: 'relative',
        borderRadius: 20,
        padding: '36px 40px',
        background: 'linear-gradient(120deg, var(--sada-700), var(--sada-500) 55%, var(--sada-400))',
        color: '#fff',
        overflow: 'hidden',
        minHeight: 280,
      }}>
        {/* Decorative geometric bg */}
        <svg style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', opacity: 0.12 }}>
          <defs>
            <pattern id="islamic" width="60" height="60" patternUnits="userSpaceOnUse">
              <path d="M30 0 L60 30 L30 60 L0 30 Z M30 15 L45 30 L30 45 L15 30 Z" fill="none" stroke="#fff" strokeWidth="0.8" />
              <circle cx="30" cy="30" r="4" fill="none" stroke="#fff" strokeWidth="0.6" />
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#islamic)" />
        </svg>

        {/* Calendar pair */}
        <div style={{
          position: 'absolute', top: 24, left: 32, zIndex: 1,
          background: 'rgba(255,255,255,0.12)',
          padding: '10px 16px', borderRadius: 12,
          backdropFilter: 'blur(8px)',
          fontSize: 12, display: 'flex', gap: 20,
        }}>
          <div>
            <div style={{ opacity: 0.8, fontSize: 10 }}>هجري</div>
            <div style={{ fontWeight: 700, fontSize: 14 }}>٢٩ رمضان ١٤٤٧</div>
          </div>
          <div style={{ width: 1, background: 'rgba(255,255,255,0.3)' }} />
          <div>
            <div style={{ opacity: 0.8, fontSize: 10 }}>ميلادي</div>
            <div style={{ fontWeight: 700, fontSize: 14 }}>٢٠ أبريل ٢٠٢٦</div>
          </div>
        </div>

        <div style={{ position: 'relative', maxWidth: 620 }}>
          <div style={{ fontSize: 12, fontWeight: 600, opacity: 0.85, letterSpacing: '0.05em', marginBottom: 8 }}>
            المناسبة القادمة
          </div>
          <h2 style={{ margin: 0, fontSize: 36, fontWeight: 800, letterSpacing: '-0.02em', lineHeight: 1.15 }}>
            {upcoming.name}
          </h2>
          <div style={{ fontSize: 14, opacity: 0.9, marginTop: 6 }}>{upcoming.subtitle}</div>

          {/* Countdown */}
          <div style={{ display: 'flex', gap: 16, marginTop: 24 }}>
            {[
              { n: upcoming.countdown.days, l: 'يوم' },
              { n: upcoming.countdown.hours, l: 'ساعة' },
              { n: upcoming.countdown.minutes, l: 'دقيقة' },
            ].map((c, i) => (
              <div key={i} style={{
                background: 'rgba(255,255,255,0.15)',
                padding: '14px 20px',
                borderRadius: 12,
                textAlign: 'center',
                minWidth: 84,
                backdropFilter: 'blur(8px)',
              }}>
                <div style={{ fontSize: 32, fontWeight: 800, fontVariantNumeric: 'tabular-nums', lineHeight: 1 }}>
                  {String(c.n).padStart(2, '٠')}
                </div>
                <div style={{ fontSize: 11, opacity: 0.85, marginTop: 4 }}>{c.l}</div>
              </div>
            ))}
          </div>

          <div style={{ display: 'flex', gap: 10, marginTop: 28 }}>
            <button className="btn btn-lg" style={{ background: '#fff', color: 'var(--sada-700)' }}>
              <Icon name="sparkle" size={16} /> ولّد حملة جاهزة
            </button>
            <button className="btn btn-lg btn-ghost" style={{ color: '#fff', border: '1px solid rgba(255,255,255,0.3)' }}>
              تصفح {upcoming.templates} قالباً
            </button>
          </div>
        </div>
      </div>

      {/* Grid */}
      <div>
        <div className="section-head">
          <div>
            <h2>مناسبات خليجية قادمة</h2>
            <div className="sub">كل موسم مرفق بقوالب جاهزة، هاشتاجات، وأفكار بصرية</div>
          </div>
          <button className="btn btn-sm btn-ghost">عرض التقويم السنوي <Icon name="arrowLeft" size={14} /></button>
        </div>

        <div className="grid-3">
          {seasons.map((s, i) => (
            <SeasonCard key={i} season={s} />
          ))}
        </div>
      </div>
    </div>
  );
};

const SeasonCard = ({ season }) => (
  <div className="card card-hoverable" style={{ overflow: 'hidden' }}>
    <div style={{
      height: 100,
      background: `linear-gradient(135deg, ${season.color}, color-mix(in oklab, ${season.color} 70%, var(--ink-900)))`,
      position: 'relative',
      overflow: 'hidden',
    }}>
      <svg style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', opacity: 0.25 }}>
        <pattern id={`p-${season.name}`} width="24" height="24" patternUnits="userSpaceOnUse">
          <circle cx="12" cy="12" r="1.2" fill="#fff" />
          <path d="M12 3 L21 12 L12 21 L3 12 Z" fill="none" stroke="#fff" strokeWidth="0.4" />
        </pattern>
        <rect width="100%" height="100%" fill={`url(#p-${season.name})`} />
      </svg>
      <div style={{
        position: 'absolute', top: 12, left: 12,
        color: '#fff',
      }}>
        <Icon name={season.icon} size={24} />
      </div>
      {season.featured && (
        <span style={{
          position: 'absolute', top: 12, right: 12,
          fontSize: 10, fontWeight: 700,
          background: 'rgba(255,255,255,0.25)', color: '#fff',
          padding: '3px 8px', borderRadius: 99,
          backdropFilter: 'blur(8px)',
        }}>مميّز</span>
      )}
    </div>
    <div style={{ padding: 16 }}>
      <div style={{ fontSize: 16, fontWeight: 700, marginBottom: 4 }}>{season.name}</div>
      <div style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 12 }}>{season.date}</div>
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        padding: '10px 0 0', borderTop: '1px solid var(--border-subtle)',
      }}>
        <div style={{ fontSize: 12 }}>
          <span style={{ color: 'var(--text-muted)' }}>يبدأ بعد </span>
          <span style={{ fontWeight: 700, color: 'var(--accent)' }}>{season.countdown}</span>
        </div>
        <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>
          {season.templates} قالب
        </div>
      </div>
      <button className="btn btn-sm btn-secondary" style={{ width: '100%', marginTop: 12 }}>
        استكشف <Icon name="arrowLeft" size={13} />
      </button>
    </div>
  </div>
);

Object.assign(window, { SeasonalScreen });
