/* global React, ReactDOM, Icon, Toggle, Sidebar, Topbar, ToastProvider, NotificationsDropdown,
   DashboardScreen, GenerateScreen, CalendarScreen,
   SeasonalScreen, CampaignsScreen, CreateCampaignScreen, AnalyticsScreen,
   BillingScreen, LandingScreen, OnboardingScreen, ComponentsScreen, HistoryScreen */

const { useState, useEffect } = React;

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "theme": "light",
  "font": "tajawal",
  "dialect": "sa",
  "variation": "balanced"
}/*EDITMODE-END*/;

const FONT_OPTIONS = {
  tajawal:   { label: 'Tajawal',   stack: "'Tajawal', system-ui, sans-serif" },
  ibmarabic: { label: 'IBM Plex Arabic', stack: "'IBM Plex Sans Arabic', system-ui, sans-serif" },
  rubik:     { label: 'Rubik',     stack: "'Rubik', system-ui, sans-serif" },
  cairo:     { label: 'Cairo',     stack: "'Cairo', system-ui, sans-serif" },
  readex:    { label: 'Readex Pro', stack: "'Readex Pro', system-ui, sans-serif" },
};

const ROUTES = [
  { id: 'landing',   title: 'صفحة التسويق',       crumbs: ['خارج التطبيق'] },
  { id: 'onboarding',title: 'الإعداد الأولي',     crumbs: ['البداية'] },
  { id: 'home',      title: 'الرئيسية',            crumbs: ['لوحة التحكم'] },
  { id: 'generate',  title: 'توليد محتوى',         crumbs: ['المحتوى', 'جديد'] },
  { id: 'calendar',  title: 'التقويم',             crumbs: ['المحتوى'] },
  { id: 'history',   title: 'سجل المحتوى',         crumbs: ['المحتوى', 'الأرشيف'] },
  { id: 'seasonal',  title: 'المواسم',             crumbs: ['المحتوى'] },
  { id: 'campaigns', title: 'الحملات الإعلانية',   crumbs: ['Meta Ads'] },
  { id: 'analytics', title: 'التحليلات',           crumbs: ['الأداء'] },
  { id: 'billing',   title: 'الإعدادات والفوترة',  crumbs: ['الحساب'] },
  { id: 'settings',  title: 'الإعدادات',           crumbs: ['الحساب'] },
  { id: 'components',title: 'مكتبة المكونات',      crumbs: ['المطوّرون', 'مرجع التصميم'] },
];

const App = () => {
  const saved = (() => {
    try { return JSON.parse(localStorage.getItem('sada-tweaks') || 'null'); } catch { return null; }
  })();
  const initial = { ...TWEAK_DEFAULTS, ...(saved || {}) };

  const [theme, setTheme] = useState(initial.theme);
  const [font, setFont] = useState(initial.font);
  const [dialect, setDialect] = useState(initial.dialect);
  const [variation, setVariation] = useState(initial.variation);
  const [route, setRoute] = useState(localStorage.getItem('sada-route') || 'home');
  const [tweaksOpen, setTweaksOpen] = useState(false);
  const [mobileNav, setMobileNav] = useState(false);

  // Persist route
  useEffect(() => { localStorage.setItem('sada-route', route); }, [route]);

  // Apply theme + font to <html>
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
    document.body.style.setProperty('--font-arabic', FONT_OPTIONS[font].stack);
    document.body.style.fontFamily = FONT_OPTIONS[font].stack;
  }, [theme, font]);

  // Persist tweaks locally
  useEffect(() => {
    localStorage.setItem('sada-tweaks', JSON.stringify({ theme, font, dialect, variation }));
  }, [theme, font, dialect, variation]);

  // Edit mode host protocol
  useEffect(() => {
    const onMsg = (e) => {
      if (!e.data || typeof e.data !== 'object') return;
      if (e.data.type === '__activate_edit_mode') setTweaksOpen(true);
      if (e.data.type === '__deactivate_edit_mode') setTweaksOpen(false);
    };
    window.addEventListener('message', onMsg);
    window.parent.postMessage({ type: '__edit_mode_available' }, '*');
    return () => window.removeEventListener('message', onMsg);
  }, []);

  const persistEdit = (edits) => {
    window.parent.postMessage({ type: '__edit_mode_set_keys', edits }, '*');
  };

  const meta = ROUTES.find(r => r.id === route) || ROUTES[2];

  // Landing + Onboarding are full-bleed
  if (route === 'landing') {
    return (
      <>
        <LandingScreen onEnterApp={() => setRoute('onboarding')} />
        <TweaksFab open={tweaksOpen} onToggle={() => setTweaksOpen(v => !v)} />
        {tweaksOpen && (
          <TweaksPanel
            route={route} setRoute={setRoute}
            theme={theme} setTheme={(v) => { setTheme(v); persistEdit({ theme: v }); }}
            font={font} setFont={(v) => { setFont(v); persistEdit({ font: v }); }}
            variation={variation} setVariation={(v) => { setVariation(v); persistEdit({ variation: v }); }}
            onClose={() => setTweaksOpen(false)}
          />
        )}
      </>
    );
  }

  if (route === 'onboarding') {
    return (
      <>
        <OnboardingScreen onFinish={() => setRoute('home')} />
        <TweaksFab open={tweaksOpen} onToggle={() => setTweaksOpen(v => !v)} />
        {tweaksOpen && (
          <TweaksPanel
            route={route} setRoute={setRoute}
            theme={theme} setTheme={(v) => { setTheme(v); persistEdit({ theme: v }); }}
            font={font} setFont={(v) => { setFont(v); persistEdit({ font: v }); }}
            variation={variation} setVariation={(v) => { setVariation(v); persistEdit({ variation: v }); }}
            onClose={() => setTweaksOpen(false)}
          />
        )}
      </>
    );
  }

  return (
    <div className="app" data-screen-label={`sada-${route}`}>
      <div className="app-main">
        <Topbar
          title={meta.title}
          crumbs={meta.crumbs}
          theme={theme}
          onToggleTheme={() => {
            const t = theme === 'dark' ? 'light' : 'dark';
            setTheme(t); persistEdit({ theme: t });
          }}
          onOpenMobileNav={() => setMobileNav(true)}
        />
        <div className="content-area">
          {route === 'home'      && <DashboardScreen variation={variation} />}
          {route === 'generate'  && <GenerateScreen dialect={dialect} setDialect={(d) => { setDialect(d); persistEdit({ dialect: d }); }} />}
          {route === 'calendar'  && <CalendarScreen />}
          {route === 'history'   && <HistoryScreen />}
          {route === 'seasonal'  && <SeasonalScreen />}
          {route === 'campaigns' && <CampaignsScreen onCreate={() => setRoute('create-campaign')} />}
          {route === 'create-campaign' && <CreateCampaignScreen onBack={() => setRoute('campaigns')} />}
          {route === 'analytics' && <AnalyticsScreen />}
          {route === 'billing'   && <BillingScreen />}
          {route === 'settings'  && <BillingScreen />}
          {route === 'components'&& <ComponentsScreen />}
        </div>
      </div>
      <Sidebar
        activeRoute={route}
        onNavigate={setRoute}
        mobileOpen={mobileNav}
        onCloseMobile={() => setMobileNav(false)}
      />
      <TweaksFab open={tweaksOpen} onToggle={() => setTweaksOpen(v => !v)} />
      {tweaksOpen && (
        <TweaksPanel
          route={route} setRoute={setRoute}
          theme={theme} setTheme={(v) => { setTheme(v); persistEdit({ theme: v }); }}
          font={font} setFont={(v) => { setFont(v); persistEdit({ font: v }); }}
          variation={variation} setVariation={(v) => { setVariation(v); persistEdit({ variation: v }); }}
          onClose={() => setTweaksOpen(false)}
        />
      )}
    </div>
  );
};

const TweaksFab = ({ open, onToggle }) => (
  !open && (
    <button
      onClick={onToggle}
      style={{
        position: 'fixed', bottom: 20, left: 20, zIndex: 100,
        width: 48, height: 48, borderRadius: '50%',
        background: 'var(--accent)', color: '#fff',
        boxShadow: 'var(--shadow-lg)',
        display: 'grid', placeItems: 'center',
        cursor: 'pointer',
        border: 'none',
      }}
      title="Tweaks"
    >
      <Icon name="palette" size={20} />
    </button>
  )
);

const TweaksPanel = ({
  route, setRoute, theme, setTheme, font, setFont,
  variation, setVariation, onClose,
}) => (
  <div className="tweaks-panel">
    <div className="tweaks-head">
      <h3>Tweaks · تخصيص</h3>
      <button className="btn btn-icon btn-icon-sm btn-ghost" onClick={onClose}>
        <Icon name="x" size={14} />
      </button>
    </div>
    <div className="tweaks-body">
      <div className="tweaks-row">
        <div className="tweaks-label">الشاشة</div>
        <select className="select" style={{ height: 34, fontSize: 13 }} value={route} onChange={e => setRoute(e.target.value)}>
          <option value="landing">صفحة التسويق</option>
          <option value="onboarding">الإعداد الأولي</option>
          <option value="home">الرئيسية</option>
          <option value="generate">توليد محتوى</option>
          <option value="calendar">التقويم</option>
          <option value="history">سجل المحتوى</option>
          <option value="seasonal">المواسم</option>
          <option value="campaigns">الحملات</option>
          <option value="analytics">التحليلات</option>
          <option value="billing">الفوترة والإعدادات</option>
          <option value="components">مكتبة المكونات</option>
        </select>
      </div>

      <div className="tweaks-row-inline">
        <div className="tweaks-label">الوضع (Light / Dark)</div>
        <div style={{ display: 'flex', gap: 4, padding: 3, background: 'var(--bg-muted)', borderRadius: 8 }}>
          <button
            onClick={() => setTheme('light')}
            style={{
              padding: '4px 10px', borderRadius: 5, fontSize: 12, fontWeight: 600,
              background: theme === 'light' ? 'var(--bg-surface)' : 'transparent',
              color: theme === 'light' ? 'var(--text-primary)' : 'var(--text-muted)',
              boxShadow: theme === 'light' ? 'var(--shadow-sm)' : 'none',
            }}
          >☀ Light</button>
          <button
            onClick={() => setTheme('dark')}
            style={{
              padding: '4px 10px', borderRadius: 5, fontSize: 12, fontWeight: 600,
              background: theme === 'dark' ? 'var(--bg-surface)' : 'transparent',
              color: theme === 'dark' ? 'var(--text-primary)' : 'var(--text-muted)',
              boxShadow: theme === 'dark' ? 'var(--shadow-sm)' : 'none',
            }}
          >☾ Dark</button>
        </div>
      </div>

      <div className="tweaks-row">
        <div className="tweaks-label">الخط العربي</div>
        <select className="select" style={{ height: 34, fontSize: 13 }} value={font} onChange={e => setFont(e.target.value)}>
          {Object.entries(FONT_OPTIONS).map(([k, v]) => (
            <option key={k} value={k}>{v.label}</option>
          ))}
        </select>
      </div>

      <div className="tweaks-row">
        <div className="tweaks-label">نسخة التصميم (Variation)</div>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 4 }}>
          {[
            { id: 'balanced', label: 'متوازنة' },
            { id: 'minimal', label: 'بسيطة' },
            { id: 'bold', label: 'جريئة' },
          ].map(v => (
            <button
              key={v.id}
              onClick={() => setVariation(v.id)}
              style={{
                padding: '6px', fontSize: 12, fontWeight: 600,
                background: variation === v.id ? 'var(--accent-soft)' : 'var(--bg-muted)',
                color: variation === v.id ? 'var(--accent-text)' : 'var(--text-muted)',
                border: variation === v.id ? '1px solid var(--accent)' : '1px solid transparent',
                borderRadius: 6,
              }}
            >{v.label}</button>
          ))}
        </div>
      </div>

      <div style={{
        padding: 10, background: 'var(--bg-muted)', borderRadius: 8,
        fontSize: 11, color: 'var(--text-muted)', lineHeight: 1.6,
      }}>
        💡 اللهجة تُبدّل من داخل شاشة «توليد محتوى» — كل لهجة تولّد نصاً مختلفاً.
      </div>
    </div>
  </div>
);

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <ToastProvider>
    <App />
  </ToastProvider>
);
