/* global React, Icon, Flag */
// Sada — Content Calendar

const { useState: useState_c } = React;

const CalendarScreen = () => {
  const [view, setView] = useState_c('month');
  const [filter, setFilter] = useState_c('all');

  const posts = {
    3: [{ platform: 'instagram', title: 'نصائح رمضانية', color: 'var(--sada-500)' }],
    5: [
      { platform: 'instagram', title: 'إفطار اليوم', color: 'var(--sada-500)' },
      { platform: 'facebook', title: 'عرض إفطار', color: 'var(--info)' },
    ],
    8: [{ platform: 'instagram', title: 'قصة خلف الكواليس', color: 'var(--sand-500)' }],
    12: [{ platform: 'facebook', title: 'تذكير بالعرض', color: 'var(--info)' }],
    15: [
      { platform: 'instagram', title: 'ليلة القدر', color: 'var(--sada-500)' },
      { platform: 'instagram', title: 'دعاء', color: 'var(--sada-500)' },
    ],
    20: [
      { platform: 'instagram', title: 'إطلاق مجموعة رمضان', color: 'var(--sada-500)' },
      { platform: 'instagram', title: 'قصة: خلف الكواليس', color: 'var(--sand-500)' },
    ],
    21: [{ platform: 'facebook', title: 'عرض ٢٤ ساعة', color: 'var(--info)' }],
    23: [{ platform: 'instagram', title: 'العد التنازلي', color: 'var(--sada-500)' }],
    25: [{ platform: 'instagram', title: 'تهنئة العيد', color: 'var(--sand-500)' }],
    28: [{ platform: 'facebook', title: 'ما بعد العيد', color: 'var(--info)' }],
  };

  // Start Sunday (RTL: Sunday on the right)
  const daysOfWeek = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
  const daysInMonth = 30;
  const firstDayOffset = 3; // Wednesday start for April 2026 (example)

  const cells = [];
  for (let i = 0; i < firstDayOffset; i++) cells.push(null);
  for (let d = 1; d <= daysInMonth; d++) cells.push(d);
  while (cells.length % 7 !== 0) cells.push(null);

  const seasonalDays = new Set([23, 24, 25]); // e.g. Eid

  return (
    <div className="stack">
      <div style={{ display: 'grid', gridTemplateColumns: '240px 1fr', gap: 20 }} className="cal-grid">
        {/* Sidebar filters (on left — visually, in RTL this appears on the left) */}
        <div className="stack">
          <div className="card">
            <div className="card-head"><h3>فلاتر</h3></div>
            <div className="card-body stack-sm">
              <div className="stack-sm">
                <div style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-muted)' }}>الحالة</div>
                {[
                  { id: 'all', label: 'الكل', count: 18 },
                  { id: 'scheduled', label: 'مجدولة', count: 12, dot: 'var(--info)' },
                  { id: 'draft', label: 'مسودة', count: 4, dot: 'var(--warning)' },
                  { id: 'published', label: 'منشورة', count: 2, dot: 'var(--success)' },
                ].map(f => (
                  <div
                    key={f.id}
                    onClick={() => setFilter(f.id)}
                    style={{
                      padding: '8px 10px',
                      borderRadius: 8,
                      cursor: 'pointer',
                      display: 'flex', alignItems: 'center', gap: 8,
                      background: filter === f.id ? 'var(--accent-soft)' : 'transparent',
                      color: filter === f.id ? 'var(--accent-text)' : 'var(--text-secondary)',
                      fontSize: 13,
                      fontWeight: filter === f.id ? 600 : 500,
                    }}
                  >
                    {f.dot ? <span className="dot" style={{ background: f.dot }} /> : <span style={{ width: 7 }} />}
                    <span>{f.label}</span>
                    <span style={{ marginRight: 'auto', fontSize: 11, color: 'var(--text-muted)' }}>{f.count}</span>
                  </div>
                ))}
              </div>
              <hr className="divider" />
              <div className="stack-sm">
                <div style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-muted)' }}>المنصة</div>
                <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, cursor: 'pointer' }}>
                  <input type="checkbox" defaultChecked /> <Icon name="instagram" size={14} /> انستجرام
                </label>
                <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, cursor: 'pointer' }}>
                  <input type="checkbox" defaultChecked /> <Icon name="facebook" size={14} /> فيسبوك
                </label>
              </div>
            </div>
          </div>

          <div className="card" style={{ background: 'var(--bg-seasonal-soft)', borderColor: 'var(--sand-200)' }}>
            <div className="card-body stack-sm">
              <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                <Icon name="moon" size={14} style={{ color: 'var(--sand-700)' }} />
                <div style={{ fontSize: 12, fontWeight: 700, color: 'var(--sand-700)' }}>مواسم هذا الشهر</div>
              </div>
              <div style={{ fontSize: 13 }}>آخر ٣ أيام من رمضان</div>
              <div style={{ fontSize: 13 }}>عيد الفطر · ٢٣-٢٥</div>
            </div>
          </div>
        </div>

        {/* Calendar */}
        <div className="card">
          <div className="card-head" style={{ gap: 12 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <button className="btn btn-icon btn-icon-sm btn-ghost"><Icon name="chevronRight" size={16} /></button>
              <h3 style={{ fontSize: 18 }}>أبريل ٢٠٢٦</h3>
              <button className="btn btn-icon btn-icon-sm btn-ghost"><Icon name="chevronLeft" size={16} /></button>
              <button className="btn btn-sm btn-ghost" style={{ marginRight: 12 }}>اليوم</button>
            </div>
            <div style={{ display: 'flex', gap: 8 }}>
              <div className="segmented">
                {['month', 'week', 'list'].map(v => (
                  <button key={v} data-active={view === v} onClick={() => setView(v)}>
                    {v === 'month' ? 'شهري' : v === 'week' ? 'أسبوعي' : 'قائمة'}
                  </button>
                ))}
              </div>
              <button className="btn btn-sm btn-primary"><Icon name="plus" size={14} /> منشور جديد</button>
            </div>
          </div>

          <div style={{ padding: 0 }}>
            {/* Day headers */}
            <div style={{
              display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)',
              borderBottom: '1px solid var(--border-subtle)',
            }}>
              {daysOfWeek.map((d, i) => (
                <div key={i} style={{
                  padding: '10px 12px',
                  fontSize: 12, fontWeight: 600, color: 'var(--text-muted)',
                  textAlign: 'center',
                  borderLeft: i < 6 ? '1px solid var(--border-subtle)' : 'none',
                }}>{d}</div>
              ))}
            </div>

            {/* Cells */}
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(7, 1fr)',
            }}>
              {cells.map((day, idx) => {
                const row = Math.floor(idx / 7);
                const col = idx % 7;
                const isLastRow = row === Math.floor((cells.length - 1) / 7);
                const dayPosts = day ? (posts[day] || []) : [];
                const isSeasonal = day && seasonalDays.has(day);
                const isToday = day === 20;
                return (
                  <div key={idx} style={{
                    minHeight: 110,
                    padding: 8,
                    borderLeft: col < 6 ? '1px solid var(--border-subtle)' : 'none',
                    borderBottom: !isLastRow ? '1px solid var(--border-subtle)' : 'none',
                    background: !day ? 'var(--bg-muted)' : isSeasonal ? 'var(--bg-seasonal-soft)' : 'var(--bg-surface)',
                    position: 'relative',
                    cursor: day ? 'pointer' : 'default',
                    transition: 'background var(--dur-fast)',
                  }}>
                    {day && (
                      <>
                        <div style={{
                          display: 'flex',
                          justifyContent: 'space-between',
                          alignItems: 'center',
                          marginBottom: 6,
                        }}>
                          <div style={{
                            fontSize: 13,
                            fontWeight: isToday ? 700 : 500,
                            color: isToday ? '#fff' : 'var(--text-primary)',
                            background: isToday ? 'var(--accent)' : 'transparent',
                            width: 24, height: 24, borderRadius: '50%',
                            display: 'grid', placeItems: 'center',
                          }}>{day}</div>
                          {isSeasonal && <Icon name="moon" size={12} style={{ color: 'var(--sand-700)' }} />}
                        </div>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
                          {dayPosts.slice(0, 2).map((p, i) => (
                            <div key={i} style={{
                              fontSize: 11,
                              padding: '3px 6px',
                              borderRadius: 4,
                              background: `color-mix(in oklab, ${p.color} 18%, transparent)`,
                              color: p.color,
                              fontWeight: 600,
                              overflow: 'hidden',
                              textOverflow: 'ellipsis',
                              whiteSpace: 'nowrap',
                              display: 'flex',
                              alignItems: 'center',
                              gap: 4,
                            }}>
                              <span className="dot" style={{ background: p.color, width: 5, height: 5 }} />
                              {p.title}
                            </div>
                          ))}
                          {dayPosts.length > 2 && (
                            <div style={{ fontSize: 10, color: 'var(--text-muted)', fontWeight: 600, padding: '0 6px' }}>
                              +{dayPosts.length - 2} أخرى
                            </div>
                          )}
                        </div>
                      </>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

Object.assign(window, { CalendarScreen });
