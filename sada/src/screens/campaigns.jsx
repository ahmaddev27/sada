/* global React, Icon, Flag */
// Sada — Campaigns (Paid Ads)

const CampaignsScreen = ({ onCreate }) => {
  const campaigns = [
    { name: 'حملة العودة للمدارس', platform: 'instagram', status: 'نشطة', statusType: 'success', budget: '٥٬٠٠٠ ر.س', spend: '٢٬٣٤٠ ر.س', roas: '٣.٨x', ctr: '٢.٤٪', cpc: '١.٨٠', days: '٣/٧' },
    { name: 'إطلاق مجموعة رمضان', platform: 'facebook', status: 'نشطة', statusType: 'success', budget: '٨٬٠٠٠ ر.س', spend: '٦٬٧٢٠ ر.س', roas: '٤.٢x', ctr: '٣.١٪', cpc: '٢.١٠', days: '١٢/١٤' },
    { name: 'عرض اليوم الوطني', platform: 'instagram', status: 'قيد المراجعة', statusType: 'warning', budget: '١٢٬٠٠٠ ر.س', spend: '٠ ر.س', roas: '—', ctr: '—', cpc: '—', days: '٠/١٠' },
    { name: 'اعادة استهداف الزوار', platform: 'facebook', status: 'متوقفة', statusType: 'neutral', budget: '٣٬٠٠٠ ر.س', spend: '٢٬٨٥٠ ر.س', roas: '٢.١x', ctr: '١.٢٪', cpc: '٢.٦٥', days: '٣٠/٣٠' },
    { name: 'حملة الصيف', platform: 'instagram', status: 'مسودة', statusType: 'neutral', budget: '٦٬٠٠٠ ر.س', spend: '—', roas: '—', ctr: '—', cpc: '—', days: '—' },
  ];

  const summary = [
    { label: 'حملات نشطة', value: '٢', icon: 'zap' },
    { label: 'الإنفاق هذا الشهر', value: '٩٬٠٦٠ ر.س', icon: 'credit' },
    { label: 'متوسط ROAS', value: '٤.٠x', icon: 'target', delta: '+0.3x' },
    { label: 'إجمالي النقرات', value: '٤٬٨٢٠', icon: 'eye' },
  ];

  return (
    <div className="stack-lg">
      {/* Summary */}
      <div className="grid-4">
        {summary.map((s, i) => (
          <div key={i} className="card" style={{ padding: 18, display: 'flex', gap: 14, alignItems: 'center' }}>
            <div style={{
              width: 40, height: 40, borderRadius: 10,
              background: 'var(--accent-soft)', color: 'var(--accent)',
              display: 'grid', placeItems: 'center',
            }}>
              <Icon name={s.icon} size={20} />
            </div>
            <div>
              <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{s.label}</div>
              <div style={{ fontSize: 20, fontWeight: 700, fontVariantNumeric: 'tabular-nums' }}>{s.value}</div>
              {s.delta && <div className="delta-up" style={{ fontSize: 11, fontWeight: 600 }}>{s.delta}</div>}
            </div>
          </div>
        ))}
      </div>

      {/* Table */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>الحملات الإعلانية</h3>
            <div className="sub">{campaigns.length} حملة · Meta (Instagram + Facebook)</div>
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <div style={{
              display: 'flex', alignItems: 'center', gap: 6,
              padding: '0 10px', height: 34,
              background: 'var(--bg-muted)', borderRadius: 8,
              fontSize: 12,
            }}>
              <Icon name="search" size={14} style={{ color: 'var(--text-muted)' }} />
              <input placeholder="بحث..." style={{ background: 'transparent', border: 'none', outline: 'none', width: 140 }} />
            </div>
            <button className="btn btn-sm btn-secondary"><Icon name="filter" size={14} /> فلترة</button>
            <button className="btn btn-sm btn-primary" onClick={onCreate}><Icon name="plus" size={14} /> حملة جديدة</button>
          </div>
        </div>

        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
            <thead>
              <tr style={{ background: 'var(--bg-surface-2)' }}>
                {['الحملة', 'المنصة', 'الحالة', 'الميزانية', 'الإنفاق', 'ROAS', 'CTR', 'CPC', 'المدة', ''].map((h, i) => (
                  <th key={i} style={{
                    padding: '10px 16px',
                    fontSize: 11, fontWeight: 600, color: 'var(--text-muted)',
                    textAlign: 'right',
                    borderBottom: '1px solid var(--border-subtle)',
                    whiteSpace: 'nowrap',
                  }}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {campaigns.map((c, i) => (
                <tr key={i} style={{
                  borderBottom: i < campaigns.length - 1 ? '1px solid var(--border-subtle)' : 'none',
                  transition: 'background var(--dur-fast)',
                }}
                onMouseEnter={e => e.currentTarget.style.background = 'var(--bg-muted)'}
                onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                >
                  <td style={{ padding: '14px 16px', fontWeight: 600 }}>{c.name}</td>
                  <td style={{ padding: '14px 16px' }}>
                    <span style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
                      <Icon name={c.platform} size={14} />
                      {c.platform === 'instagram' ? 'انستجرام' : 'فيسبوك'}
                    </span>
                  </td>
                  <td style={{ padding: '14px 16px' }}>
                    <span className={`badge badge-${c.statusType}`}>
                      <span className={`dot dot-${c.statusType === 'neutral' ? 'info' : c.statusType}`} />
                      {c.status}
                    </span>
                  </td>
                  <td style={{ padding: '14px 16px', fontVariantNumeric: 'tabular-nums' }}>{c.budget}</td>
                  <td style={{ padding: '14px 16px', fontVariantNumeric: 'tabular-nums' }}>{c.spend}</td>
                  <td style={{ padding: '14px 16px', fontVariantNumeric: 'tabular-nums', fontWeight: 600, color: c.roas !== '—' ? 'var(--success)' : 'inherit' }}>{c.roas}</td>
                  <td style={{ padding: '14px 16px', fontVariantNumeric: 'tabular-nums' }}>{c.ctr}</td>
                  <td style={{ padding: '14px 16px', fontVariantNumeric: 'tabular-nums' }}>{c.cpc}</td>
                  <td style={{ padding: '14px 16px', fontVariantNumeric: 'tabular-nums', color: 'var(--text-muted)' }}>{c.days}</td>
                  <td style={{ padding: '14px 16px', textAlign: 'left' }}>
                    <button className="btn btn-icon btn-icon-sm btn-ghost"><Icon name="chevronLeft" size={14} /></button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Objective picker preview */}
      <div className="card">
        <div className="card-head">
          <div>
            <h3>ابدأ حملة جديدة</h3>
            <div className="sub">اختر الهدف الذي يناسب أولويتك</div>
          </div>
        </div>
        <div className="card-body grid-4" style={{ padding: 20 }}>
          {[
            { id: 'awareness', label: 'الوعي بالعلامة', desc: 'وصول أكبر للمحتوى', icon: 'eye', color: 'var(--info)' },
            { id: 'traffic', label: 'زيارات الموقع', desc: 'نقرات إلى متجرك', icon: 'arrowLeft', color: 'var(--sand-500)' },
            { id: 'conversions', label: 'مبيعات', desc: 'شراء فعلي + ROAS', icon: 'target', color: 'var(--sada-500)' },
            { id: 'engagement', label: 'التفاعل', desc: 'تعليقات + حفظ', icon: 'heart', color: 'var(--error)' },
          ].map(o => (
            <button key={o.id} className="card card-hoverable" onClick={onCreate} style={{ padding: 18, textAlign: 'right', cursor: 'pointer', background: 'var(--bg-surface-2)' }}>
              <div style={{
                width: 40, height: 40, borderRadius: 10,
                background: `color-mix(in oklab, ${o.color} 15%, transparent)`,
                color: o.color,
                display: 'grid', placeItems: 'center',
                marginBottom: 12,
              }}>
                <Icon name={o.icon} size={20} />
              </div>
              <div style={{ fontSize: 14, fontWeight: 700, marginBottom: 2 }}>{o.label}</div>
              <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{o.desc}</div>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
};

Object.assign(window, { CampaignsScreen });
