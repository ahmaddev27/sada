/* global React, Icon, Toggle, Avatar */
// Sada — App Shell: Sidebar, Topbar

const { useState } = React;

const Sidebar = ({ activeRoute, onNavigate, onCloseMobile, mobileOpen }) => {
  const [wsOpen, setWsOpen] = useState(false);

  // Lock body scroll when sidebar open on mobile
  React.useEffect(() => {
    if (mobileOpen) {
      const prev = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      return () => { document.body.style.overflow = prev; };
    }
  }, [mobileOpen]);

  // Close on ESC
  React.useEffect(() => {
    if (!mobileOpen) return;
    const onKey = (e) => { if (e.key === 'Escape') onCloseMobile && onCloseMobile(); };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [mobileOpen, onCloseMobile]);

  const nav = [
    { id: 'home',     label: 'الرئيسية',         icon: 'home' },
    { id: 'generate', label: 'توليد محتوى',      icon: 'sparkle', badge: 'جديد' },
    { id: 'calendar', label: 'التقويم',          icon: 'calendar' },
    { id: 'history',  label: 'سجل المحتوى',      icon: 'clock' },
    { id: 'campaigns',label: 'الحملات الإعلانية',icon: 'megaphone' },
    { id: 'seasonal', label: 'المواسم',          icon: 'moon' },
    { id: 'analytics',label: 'التحليلات',        icon: 'chart' },
  ];
  const bottom = [
    { id: 'billing',    label: 'الفوترة',         icon: 'credit' },
    { id: 'settings',   label: 'الإعدادات',       icon: 'settings' },
    { id: 'components', label: 'مكتبة المكونات',  icon: 'palette' },
  ];

  return (
    <>
      <aside className="sidebar" data-open={mobileOpen}>
        <div className="sidebar-top">
          <div className="sidebar-logo">
            <div className="mark">ص</div>
            <span>صدى</span>
          </div>
          <button className="workspace-picker" onClick={() => setWsOpen(v => !v)}>
            <div className="avatar">أن</div>
            <div className="ws-info">
              <div className="ws-name">متجر أنيق</div>
              <div className="ws-plan">Growth · السعودية</div>
            </div>
            <Icon name="chevronDown" size={14} className="chev" />
          </button>
        </div>

        <nav className="sidebar-nav">
          <div className="nav-section">العمل</div>
          {nav.map(item => (
            <div
              key={item.id}
              className="nav-item"
              data-active={activeRoute === item.id}
              onClick={() => { onNavigate(item.id); onCloseMobile && onCloseMobile(); }}
            >
              <Icon name={item.icon} className="icon" />
              <span>{item.label}</span>
              {item.badge && <span className="nav-badge">{item.badge}</span>}
            </div>
          ))}

          <div className="nav-section" style={{ marginTop: 16 }}>الحساب</div>
          {bottom.map(item => (
            <div
              key={item.id}
              className="nav-item"
              data-active={activeRoute === item.id}
              onClick={() => { onNavigate(item.id); onCloseMobile && onCloseMobile(); }}
            >
              <Icon name={item.icon} className="icon" />
              <span>{item.label}</span>
            </div>
          ))}
        </nav>

        <div className="sidebar-bottom">
          <div className="token-meter">
            <div className="token-meter-head">
              <span className="label">التوكنز</span>
              <span className="val">1,240 / 2,000</span>
            </div>
            <div className="token-bar"><div className="token-bar-fill" style={{ width: '62%' }} /></div>
            <button className="token-refill">شحن المزيد</button>
          </div>

          <div className="user-chip">
            <div className="avatar">أح</div>
            <div className="user-info">
              <div className="user-name">أحمد العتيبي</div>
              <div className="user-role">مالك · Admin</div>
            </div>
            <Icon name="chevronDown" size={14} />
          </div>
        </div>
      </aside>
      <div className="sidebar-overlay" data-open={mobileOpen} onClick={onCloseMobile} />
    </>
  );
};

const Topbar = ({ title, crumbs = [], onOpenMobileNav, onOpenTweaks, theme, onToggleTheme }) => {
  return (
    <header className="topbar">
      <div style={{ display: 'flex', alignItems: 'center', gap: 12, minWidth: 0, flex: 1 }}>
        <button
          className="btn btn-icon btn-ghost"
          onClick={onOpenMobileNav}
          data-mobile-only="true"
          aria-label="فتح القائمة"
        >
          <Icon name="menu" />
        </button>
        <div className="topbar-title" style={{ minWidth: 0, flex: 1 }}>
          {crumbs.length > 0 && (
            <div className="crumbs">
              {crumbs.map((c, i) => (
                <React.Fragment key={i}>
                  <span>{c}</span>
                  {i < crumbs.length - 1 && <span style={{ opacity: 0.5 }}>•</span>}
                </React.Fragment>
              ))}
            </div>
          )}
          <h1 style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{title}</h1>
        </div>
      </div>

      <div className="topbar-actions">
        <div className="topbar-search" style={{
          display: 'flex', alignItems: 'center', gap: 8,
          padding: '0 12px', height: 38,
          background: 'var(--bg-muted)',
          borderRadius: 10,
          minWidth: 260,
        }}>
          <Icon name="search" size={16} style={{ color: 'var(--text-muted)' }} />
          <input
            placeholder="ابحث في كل شيء... (⌘K)"
            style={{
              flex: 1, background: 'transparent', border: 'none', outline: 'none',
              fontSize: 13, color: 'var(--text-primary)',
            }}
          />
          <kbd style={{
            fontSize: 10, padding: '2px 6px', background: 'var(--bg-surface)',
            borderRadius: 4, color: 'var(--text-muted)', fontFamily: 'var(--font-mono)',
          }}>⌘K</kbd>
        </div>
        <button className="btn btn-icon btn-ghost" onClick={onToggleTheme} title="تبديل الوضع">
          <Icon name={theme === 'dark' ? 'sun' : 'moon'} />
        </button>
        <NotificationsDropdown />
      </div>
    </header>
  );
};

Object.assign(window, { Sidebar, Topbar });
