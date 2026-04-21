/* global React, Icon */
// Sada — Notifications Dropdown

const { useState, useRef, useEffect } = React;

const NOTIFICATIONS = [
  {
    id: 1, type: 'campaign', unread: true,
    icon: 'zap', color: 'var(--success)',
    title: 'حملة «إطلاق مجموعة رمضان» تجاوزت ROAS ٤x',
    desc: 'أداء قوي — فكّر في زيادة الميزانية اليومية.',
    time: 'قبل ٥ دقائق',
  },
  {
    id: 2, type: 'content', unread: true,
    icon: 'sparkle', color: 'var(--accent)',
    title: 'تم توليد ١٢ منشور جديد',
    desc: 'محتوى أسبوع اليوم الوطني جاهز للمراجعة.',
    time: 'قبل ٢٠ دقيقة',
  },
  {
    id: 3, type: 'billing', unread: true,
    icon: 'credit', color: 'var(--warning)',
    title: 'رصيدك من التوكنز منخفض',
    desc: 'متبقي ٢٤٠ توكن فقط — اشحن قبل نفاد الرصيد.',
    time: 'قبل ساعة',
  },
  {
    id: 4, type: 'content', unread: false,
    icon: 'check', color: 'var(--success)',
    title: 'نُشر منشور «عروض نهاية الأسبوع»',
    desc: 'على انستجرام · وصل ٤٬٢٠٠ شخص حتى الآن.',
    time: 'قبل ٣ ساعات',
  },
  {
    id: 5, type: 'campaign', unread: false,
    icon: 'info', color: 'var(--info)',
    title: 'حملة «عرض اليوم الوطني» قيد المراجعة من Meta',
    desc: 'عادة تستغرق المراجعة ٦-٢٤ ساعة.',
    time: 'أمس',
  },
  {
    id: 6, type: 'system', unread: false,
    icon: 'users', color: 'var(--sand-500)',
    title: 'منى الشهري قبلت دعوة الانضمام للفريق',
    desc: 'دور: محرر محتوى.',
    time: 'منذ يومين',
  },
  {
    id: 7, type: 'billing', unread: false,
    icon: 'check', color: 'var(--success)',
    title: 'تم تجديد اشتراك Growth بنجاح',
    desc: 'خُصم ٣٩٩ ر.س من البطاقة المنتهية ٤٢٤٢.',
    time: 'منذ ٣ أيام',
  },
];

const NotificationsDropdown = () => {
  const [open, setOpen] = useState(false);
  const [activeTab, setActiveTab] = useState('all');
  const [notifications, setNotifications] = useState(NOTIFICATIONS);
  const ref = useRef(null);

  useEffect(() => {
    if (!open) return;
    const onClick = (e) => {
      if (ref.current && !ref.current.contains(e.target)) setOpen(false);
    };
    const onKey = (e) => { if (e.key === 'Escape') setOpen(false); };
    document.addEventListener('mousedown', onClick);
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('mousedown', onClick);
      document.removeEventListener('keydown', onKey);
    };
  }, [open]);

  const unreadCount = notifications.filter(n => n.unread).length;

  const tabs = [
    { id: 'all',      label: 'الكل',     count: notifications.length },
    { id: 'campaign', label: 'الحملات',  count: notifications.filter(n => n.type === 'campaign').length },
    { id: 'content',  label: 'المحتوى',  count: notifications.filter(n => n.type === 'content').length },
    { id: 'billing',  label: 'الفوترة', count: notifications.filter(n => n.type === 'billing').length },
  ];

  const filtered = activeTab === 'all' ? notifications : notifications.filter(n => n.type === activeTab);

  const markAllRead = () => {
    setNotifications(ns => ns.map(n => ({ ...n, unread: false })));
  };
  const markOneRead = (id) => {
    setNotifications(ns => ns.map(n => n.id === id ? { ...n, unread: false } : n));
  };

  return (
    <div className="notif-wrap" ref={ref}>
      <button
        className="btn btn-icon btn-ghost"
        onClick={() => setOpen(v => !v)}
        title="الإشعارات"
        style={{ position: 'relative' }}
        aria-expanded={open}
      >
        <Icon name="bell" />
        {unreadCount > 0 && (
          <span style={{
            position: 'absolute', top: 4, left: 4,
            minWidth: 16, height: 16, padding: '0 4px',
            borderRadius: 8,
            background: 'var(--error)', color: 'white',
            fontSize: 10, fontWeight: 700,
            display: 'grid', placeItems: 'center',
            border: '2px solid var(--bg-surface)',
            fontVariantNumeric: 'tabular-nums',
          }}>{unreadCount}</span>
        )}
      </button>

      {open && (
        <div className="notif-dropdown">
          <div className="notif-head">
            <h3>الإشعارات</h3>
            {unreadCount > 0 && (
              <button className="notif-mark-read" onClick={markAllRead}>
                تحديد الكل كمقروء
              </button>
            )}
          </div>

          <div className="notif-tabs">
            {tabs.map(t => (
              <button
                key={t.id}
                className="notif-tab"
                data-active={activeTab === t.id}
                onClick={() => setActiveTab(t.id)}
              >
                {t.label}
                {t.count > 0 && <span className="notif-tab-count">{t.count}</span>}
              </button>
            ))}
          </div>

          <div className="notif-list">
            {filtered.length === 0 ? (
              <div className="notif-empty">
                <div className="notif-empty-icon">
                  <Icon name="bell" size={20} />
                </div>
                <div style={{ fontSize: 13, fontWeight: 600, marginBottom: 2 }}>لا توجد إشعارات</div>
                <div style={{ fontSize: 12 }}>سنخبرك عند حدوث شيء مهم.</div>
              </div>
            ) : (
              filtered.map(n => (
                <div
                  key={n.id}
                  className="notif-item"
                  data-unread={n.unread}
                  onClick={() => markOneRead(n.id)}
                >
                  <div className="notif-icon" style={{
                    background: `color-mix(in oklab, ${n.color} 14%, transparent)`,
                    color: n.color,
                  }}>
                    <Icon name={n.icon} size={16} />
                  </div>
                  <div className="notif-body">
                    <div className="notif-title">{n.title}</div>
                    <div className="notif-desc">{n.desc}</div>
                    <div className="notif-time">{n.time}</div>
                  </div>
                </div>
              ))
            )}
          </div>

          <div className="notif-footer">
            <a>عرض كل الإشعارات</a>
          </div>
        </div>
      )}
    </div>
  );
};

Object.assign(window, { NotificationsDropdown });
