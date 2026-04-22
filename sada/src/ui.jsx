/* global React */
// Sada — Shared Icons & small UI primitives

const Icon = ({ name, size = 18, stroke = 1.75, className = '', style = {} }) => {
  const paths = {
    home: <path d="M3 10l9-7 9 7v10a2 2 0 0 1-2 2h-4v-7h-6v7H5a2 2 0 0 1-2-2V10z" />,
    sparkle: <><path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.5 5.5l2.8 2.8M15.7 15.7l2.8 2.8M5.5 18.5l2.8-2.8M15.7 8.3l2.8-2.8" /></>,
    calendar: <><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M8 3v4M16 3v4M3 10h18" /></>,
    megaphone: <><path d="M3 11v2a2 2 0 0 0 2 2h2l6 4V5L7 9H5a2 2 0 0 0-2 2z" /><path d="M17 8a5 5 0 0 1 0 8" /></>,
    moon: <path d="M21 12.5A9 9 0 1 1 11.5 3a7 7 0 0 0 9.5 9.5z" />,
    chart: <><path d="M3 3v18h18" /><path d="M7 14l4-4 3 3 5-6" /></>,
    settings: <><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v.1a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z" /></>,
    search: <><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></>,
    bell: <><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></>,
    plus: <><path d="M12 5v14M5 12h14" /></>,
    chevronDown: <path d="M6 9l6 6 6-6" />,
    chevronLeft: <path d="M15 6l-6 6 6 6" />,
    chevronRight: <path d="M9 6l6 6-6 6" />,
    arrowLeft: <><path d="M19 12H5M12 19l-7-7 7-7" /></>,
    arrowRight: <><path d="M5 12h14M12 5l7 7-7 7" /></>,
    up: <><path d="M7 14l5-5 5 5" /></>,
    down: <><path d="M7 10l5 5 5-5" /></>,
    check: <path d="M20 6L9 17l-5-5" />,
    x: <><path d="M18 6L6 18M6 6l12 12" /></>,
    sun: <><circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" /></>,
    menu: <><path d="M4 6h16M4 12h16M4 18h16" /></>,
    image: <><rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="9" r="1.5" /><path d="M21 15l-5-5L5 21" /></>,
    video: <><path d="M23 7l-7 5 7 5V7z" /><rect x="1" y="5" width="15" height="14" rx="2" /></>,
    instagram: <><rect x="3" y="3" width="18" height="18" rx="5" /><circle cx="12" cy="12" r="4" /><circle cx="17.5" cy="6.5" r="0.8" fill="currentColor" stroke="none" /></>,
    facebook: <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />,
    edit: <><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></>,
    copy: <><rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" /></>,
    trash: <><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /></>,
    download: <><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" /></>,
    upload: <><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" /></>,
    heart: <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 1 0-7.8 7.8l1.1 1.1L12 21.3l7.8-7.8 1.1-1.1a5.5 5.5 0 0 0 0-7.8z" />,
    message: <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />,
    send: <><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" /></>,
    clock: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></>,
    users: <><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8" /></>,
    building: <><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M9 3v18M3 9h18" /></>,
    zap: <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />,
    target: <><circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="5" /><circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" /></>,
    eye: <><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></>,
    filter: <path d="M22 3H2l8 9.5V19l4 2v-8.5L22 3z" />,
    globe: <><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" /></>,
    crown: <path d="M2 4l5 8 5-7 5 7 5-8v15H2z" />,
    palette: <><circle cx="13.5" cy="6.5" r="1" fill="currentColor" /><circle cx="17.5" cy="10.5" r="1" fill="currentColor" /><circle cx="8.5" cy="7.5" r="1" fill="currentColor" /><circle cx="6.5" cy="12.5" r="1" fill="currentColor" /><path d="M12 22a10 10 0 1 1 10-10c0 2-1 3-3 3h-2a2 2 0 0 0-2 2c0 1 1 2 2 2a2 2 0 0 1 2 2 3 3 0 0 1-3 3z" /></>,
    star: <path d="M12 2l3.1 6.3 7 1-5 4.9 1.2 6.9L12 17.8l-6.3 3.3 1.2-6.9L1.9 9.3l7-1L12 2z" />,
    flash: <path d="M12 2v10l4-4M12 22v-10l-4 4" />,
    info: <><circle cx="12" cy="12" r="9" /><path d="M12 16v-4M12 8h0" /></>,
    credit: <><rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" /></>,
    rocket: <><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2a2.82 2.82 0 0 0-3-3z" /><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z" /><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0" /></>,
    layers: <><path d="M12 2l10 6-10 6L2 8l10-6z" /><path d="M2 17l10 5 10-5M2 12l10 5 10-5" /></>,
    share: <><circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" /><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" /></>,
    save: <><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" /><path d="M17 21v-8H7v8M7 3v5h8" /></>,
    more: <><circle cx="12" cy="12" r="1.5" fill="currentColor" /><circle cx="19" cy="12" r="1.5" fill="currentColor" /><circle cx="5" cy="12" r="1.5" fill="currentColor" /></>,
    tiktok: <path d="M9 3v12a3 3 0 1 1-3-3" />,
    snapchat: <path d="M12 2c3 0 5 2 5 6v2c1 1 2 2 4 2-1 2-2 3-4 3 0 2-2 3-5 3s-5-1-5-3c-2 0-3-1-4-3 2 0 3-1 4-2V8c0-4 2-6 5-6z" />,
    sliders: <><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6" /></>,
    bookmark: <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />,
    user: <><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></>,
    phone: <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.35 1.84.59 2.8.72A2 2 0 0 1 22 16.92z" />,
    minarette: <></>,
  };

  return (
    <svg
      width={size} height={size} viewBox="0 0 24 24"
      fill="none" stroke="currentColor"
      strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round"
      className={className}
      style={style}
    >
      {paths[name] || null}
    </svg>
  );
};

// Toggle switch
const Toggle = ({ on, onChange }) => (
  <button
    className="toggle"
    data-on={on}
    onClick={() => onChange && onChange(!on)}
    aria-pressed={on}
    type="button"
  />
);

// Flag for dialect
const Flag = ({ code, size = 16 }) => {
  const flags = {
    sa: '🇸🇦', ae: '🇦🇪', kw: '🇰🇼', qa: '🇶🇦', bh: '🇧🇭', om: '🇴🇲', ar: '🏳️'
  };
  return <span style={{ fontSize: size, lineHeight: 1 }}>{flags[code] || '🏳️'}</span>;
};

// Placeholder image
const ImgPlaceholder = ({ label = 'صورة', w = '100%', h = 120, style = {} }) => (
  <div className="placeholder-img" style={{ width: w, height: h, borderRadius: 10, ...style }}>
    {label}
  </div>
);

// Avatar
const Avatar = ({ name, color, size = 32 }) => {
  const initials = (name || '؟').split(' ').map(s => s[0]).slice(0, 2).join('');
  const bg = color || `linear-gradient(135deg, var(--sada-400), var(--sada-600))`;
  return (
    <div style={{
      width: size, height: size, borderRadius: '50%', background: bg,
      color: '#fff', fontWeight: 700, display: 'grid', placeItems: 'center',
      fontSize: size * 0.4, flexShrink: 0,
    }}>{initials}</div>
  );
};

// Tiny spark line
const Sparkline = ({ values = [], color = 'var(--accent)', height = 32 }) => {
  const max = Math.max(...values);
  const min = Math.min(...values);
  const range = max - min || 1;
  const pts = values.map((v, i) => {
    const x = (i / (values.length - 1)) * 100;
    const y = 100 - ((v - min) / range) * 100;
    return `${x},${y}`;
  }).join(' ');
  return (
    <svg viewBox="0 0 100 100" preserveAspectRatio="none" style={{ width: '100%', height, display: 'block' }}>
      <polyline points={pts} fill="none" stroke={color} strokeWidth="2.5" vectorEffect="non-scaling-stroke" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
};

// Arabic geometric decorative SVG motif — used sparingly
const Arabesque = ({ color = 'currentColor', size = 200, opacity = 0.15 }) => (
  <svg width={size} height={size} viewBox="0 0 200 200" style={{ opacity }}>
    <defs>
      <pattern id="geom" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
        <path d="M20 0 L40 20 L20 40 L0 20 Z" fill="none" stroke={color} strokeWidth="0.7" />
        <circle cx="20" cy="20" r="6" fill="none" stroke={color} strokeWidth="0.5" />
      </pattern>
    </defs>
    <rect width="200" height="200" fill="url(#geom)" />
  </svg>
);

// ===== Alert (inline banner) =====
const Alert = ({ variant = 'info', title, children, onClose, icon = true }) => {
  const iconName = { info: 'info', success: 'check', warning: 'info', error: 'x', brand: 'sparkle' }[variant];
  return (
    <div className={`alert alert-${variant}`} role="alert">
      {icon && <div className="alert-icon"><Icon name={iconName} size={16} /></div>}
      <div className="alert-body">
        {title && <div className="alert-title">{title}</div>}
        {children && <div className="alert-desc">{children}</div>}
      </div>
      {onClose && (
        <button className="alert-close" onClick={onClose} aria-label="إغلاق">
          <Icon name="x" size={14} />
        </button>
      )}
    </div>
  );
};

// ===== Toast system =====
const ToastContext = React.createContext(null);

const ToastProvider = ({ children }) => {
  const [toasts, setToasts] = React.useState([]);

  const push = React.useCallback((t) => {
    const id = Math.random().toString(36).slice(2);
    const toast = { id, duration: 4000, variant: 'info', ...t };
    setToasts((ts) => [...ts, toast]);
    if (toast.duration > 0) {
      setTimeout(() => setToasts((ts) => ts.filter(x => x.id !== id)), toast.duration);
    }
    return id;
  }, []);

  const dismiss = React.useCallback((id) => {
    setToasts((ts) => ts.filter(t => t.id !== id));
  }, []);

  const api = React.useMemo(() => ({
    show: push,
    success: (title, desc) => push({ variant: 'success', title, desc }),
    error:   (title, desc) => push({ variant: 'error',   title, desc }),
    info:    (title, desc) => push({ variant: 'info',    title, desc }),
    warning: (title, desc) => push({ variant: 'warning', title, desc }),
    dismiss,
  }), [push, dismiss]);

  return (
    <ToastContext.Provider value={api}>
      {children}
      <div className="toast-viewport" aria-live="polite">
        {toasts.map(t => (
          <div key={t.id} className={`toast toast-${t.variant}`}>
            <div className="toast-icon">
              <Icon name={{ info:'info', success:'check', warning:'info', error:'x' }[t.variant]} size={16} />
            </div>
            <div className="toast-body">
              {t.title && <div className="toast-title">{t.title}</div>}
              {t.desc && <div className="toast-desc">{t.desc}</div>}
            </div>
            <button className="toast-close" onClick={() => dismiss(t.id)} aria-label="إغلاق">
              <Icon name="x" size={13} />
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
};

const useToast = () => {
  const ctx = React.useContext(ToastContext);
  if (!ctx) return { show: () => {}, success: () => {}, error: () => {}, info: () => {}, warning: () => {}, dismiss: () => {} };
  return ctx;
};

// ===== Modal (generic) =====
const Modal = ({ open, onClose, title, children, size = 'md', footer }) => {
  React.useEffect(() => {
    if (!open) return;
    const onKey = (e) => { if (e.key === 'Escape') onClose && onClose(); };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [open, onClose]);

  if (!open) return null;
  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div
        className={`modal modal-${size}`}
        onClick={(e) => e.stopPropagation()}
        role="dialog"
        aria-modal="true"
      >
        {title && (
          <div className="modal-head">
            <h3>{title}</h3>
            <button className="btn btn-icon btn-icon-sm btn-ghost" onClick={onClose} aria-label="إغلاق">
              <Icon name="x" size={14} />
            </button>
          </div>
        )}
        <div className="modal-body">{children}</div>
        {footer && <div className="modal-foot">{footer}</div>}
      </div>
    </div>
  );
};

// ===== Confirm Modal =====
const ConfirmModal = ({
  open, onClose, onConfirm,
  title = 'تأكيد الإجراء',
  message = 'هل أنت متأكد من المتابعة؟',
  confirmText = 'تأكيد',
  cancelText = 'إلغاء',
  variant = 'default', // default | danger
  icon,
}) => {
  const iconName = icon || (variant === 'danger' ? 'trash' : 'info');
  return (
    <Modal
      open={open}
      onClose={onClose}
      size="sm"
      title={null}
      footer={
        <>
          <button className="btn btn-secondary" onClick={onClose}>{cancelText}</button>
          <button
            className={variant === 'danger' ? 'btn btn-danger' : 'btn btn-primary'}
            onClick={() => { onConfirm && onConfirm(); }}
          >
            {confirmText}
          </button>
        </>
      }
    >
      <div className="confirm-body">
        <div className={`confirm-icon confirm-icon-${variant}`}>
          <Icon name={iconName} size={22} />
        </div>
        <div>
          <div className="confirm-title">{title}</div>
          <div className="confirm-message">{message}</div>
        </div>
      </div>
    </Modal>
  );
};

Object.assign(window, {
  Icon, Toggle, Flag, ImgPlaceholder, Avatar, Sparkline, Arabesque,
  Alert, ToastProvider, useToast, Modal, ConfirmModal,
});
