/* global React, Icon, Avatar, ImgPlaceholder, useToast, Modal, ConfirmModal, Checkbox, CustomSelect */
// Sada — History / سجل المحتوى

const { useState: uS_h, useMemo: uM_h } = React;

const HISTORY_DATA = [
  { id: 1, type: 'منشور', title: 'عرض نهاية الأسبوع — خصم ٢٠٪', platform: 'instagram', status: 'published', date: '٢٠٢٦-٠٤-١٨', time: '٢٠:٣٠', dialect: 'سعودي', tokens: 45, impressions: 24810, engagement: '8.4%', campaign: 'عروض الربيع' },
  { id: 2, type: 'قصة',  title: 'تشويق رمضاني — عطر الليل', platform: 'instagram', status: 'scheduled', date: '٢٠٢٦-٠٤-١٩', time: '٠٨:٠٠', dialect: 'فصحى',   tokens: 28, impressions: '—', engagement: '—', campaign: 'رمضان ٢٠٢٦' },
  { id: 3, type: 'ريلز', title: 'كيف تختارين السوار المناسب', platform: 'tiktok',    status: 'published', date: '٢٠٢٦-٠٤-١٧', time: '١٩:٤٥', dialect: 'إماراتي', tokens: 72, impressions: 54200, engagement: '12.1%', campaign: 'توعوي' },
  { id: 4, type: 'منشور', title: 'منتج جديد — عطر شرقي',      platform: 'facebook',  status: 'draft',     date: '٢٠٢٦-٠٤-١٦', time: '١٥:٢٠', dialect: 'سعودي',   tokens: 38, impressions: '—', engagement: '—', campaign: 'إطلاقات' },
  { id: 5, type: 'إعلان', title: 'حملة الجمعة البيضاء',        platform: 'facebook',  status: 'published', date: '٢٠٢٦-٠٤-١٥', time: '١٢:٠٠', dialect: 'فصحى',   tokens: 95, impressions: 128400, engagement: '6.7%', campaign: 'الجمعة البيضاء' },
  { id: 6, type: 'منشور', title: 'قهوة الصباح الخليجية',       platform: 'instagram', status: 'failed',    date: '٢٠٢٦-٠٤-١٥', time: '٠٨:٣٠', dialect: 'كويتي',   tokens: 41, impressions: '—', engagement: '—', campaign: '—' },
  { id: 7, type: 'قصة',  title: 'ورشة العمل — اختيار الهدايا', platform: 'snapchat',  status: 'published', date: '٢٠٢٦-٠٤-١٤', time: '١٠:١٥', dialect: 'سعودي',   tokens: 22, impressions: 8400,   engagement: '—', campaign: 'توعوي' },
  { id: 8, type: 'ريلز', title: 'خلف الكواليس — جلسة التصوير', platform: 'instagram', status: 'published', date: '٢٠٢٦-٠٤-١٣', time: '٢١:٠٠', dialect: 'سعودي',   tokens: 68, impressions: 32100,  engagement: '9.8%', campaign: '—' },
  { id: 9, type: 'منشور', title: 'عرض اليوم الوطني قادم',      platform: 'x',         status: 'scheduled', date: '٢٠٢٦-٠٤-٢٢', time: '١٨:٠٠', dialect: 'فصحى',   tokens: 18, impressions: '—', engagement: '—', campaign: 'اليوم الوطني' },
  { id: 10, type: 'منشور', title: 'اقتباس — الأناقة حضور',     platform: 'facebook',  status: 'published', date: '٢٠٢٦-٠٤-١٢', time: '١٦:٤٥', dialect: 'فصحى',   tokens: 15, impressions: 14200,  engagement: '5.3%', campaign: '—' },
  { id: 11, type: 'ريلز', title: 'عطر واحد، قصتان',            platform: 'tiktok',    status: 'draft',     date: '٢٠٢٦-٠٤-١١', time: '—',     dialect: 'إماراتي', tokens: 52, impressions: '—', engagement: '—', campaign: '—' },
  { id: 12, type: 'إعلان', title: 'وصلت شحنة عيد الفطر',       platform: 'instagram', status: 'published', date: '٢٠٢٦-٠٤-١٠', time: '١٩:٣٠', dialect: 'سعودي',   tokens: 82, impressions: 96500,  engagement: '7.2%', campaign: 'عيد الفطر' },
  { id: 13, type: 'منشور', title: 'طقم المجوهرات الجديد',       platform: 'instagram', status: 'published', date: '٢٠٢٦-٠٤-٠٩', time: '١٣:١٠', dialect: 'سعودي',   tokens: 44, impressions: 18700,  engagement: '6.9%', campaign: '—' },
  { id: 14, type: 'قصة',  title: 'شكر للعميلات',                platform: 'snapchat',  status: 'published', date: '٢٠٢٦-٠٤-٠٨', time: '٢٢:٠٠', dialect: 'كويتي',   tokens: 12, impressions: 5200,   engagement: '—', campaign: '—' },
  { id: 15, type: 'منشور', title: 'دليل العناية بالذهب',        platform: 'facebook',  status: 'failed',    date: '٢٠٢٦-٠٤-٠٧', time: '١١:٠٠', dialect: 'فصحى',   tokens: 55, impressions: '—', engagement: '—', campaign: 'توعوي' },
];

const STATUS_META = {
  published: { label: 'منشور',   badge: 'badge-success', dot: 'dot-success' },
  scheduled: { label: 'مجدول',   badge: 'badge-info',    dot: 'dot-info' },
  draft:     { label: 'مسودة',   badge: 'badge-neutral', dot: '' },
  failed:    { label: 'فشل',     badge: 'badge-error',   dot: '' },
};

const HistoryScreen = () => {
  const toast = useToast();
  const [status, setStatus] = uS_h('all');
  const [platform, setPlatform] = uS_h('all');
  const [type, setType] = uS_h('all');
  const [campaign, setCampaign] = uS_h('all');
  const [range, setRange] = uS_h('30d');
  const [search, setSearch] = uS_h('');
  const [selected, setSelected] = uS_h(new Set());
  const [previewId, setPreviewId] = uS_h(null);
  const [confirmDel, setConfirmDel] = uS_h(null);
  const [view, setView] = uS_h('table');

  const campaigns = uM_h(() => {
    const set = new Set();
    HISTORY_DATA.forEach(r => r.campaign && r.campaign !== '—' && set.add(r.campaign));
    return Array.from(set);
  }, []);

  const filtered = uM_h(() => {
    return HISTORY_DATA.filter(r => {
      if (status !== 'all' && r.status !== status) return false;
      if (platform !== 'all' && r.platform !== platform) return false;
      if (type !== 'all' && r.type !== type) return false;
      if (campaign !== 'all' && r.campaign !== campaign) return false;
      if (search && !r.title.includes(search)) return false;
      return true;
    });
  }, [status, platform, type, campaign, search]);

  const stats = uM_h(() => ({
    total: filtered.length,
    published: filtered.filter(r => r.status === 'published').length,
    scheduled: filtered.filter(r => r.status === 'scheduled').length,
    drafts: filtered.filter(r => r.status === 'draft').length,
    impressions: filtered.reduce((s, r) => s + (typeof r.impressions === 'number' ? r.impressions : 0), 0),
    tokens: filtered.reduce((s, r) => s + r.tokens, 0),
  }), [filtered]);

  const toggleSel = (id) => {
    const next = new Set(selected);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    setSelected(next);
  };
  const toggleAll = () => {
    if (selected.size === filtered.length) setSelected(new Set());
    else setSelected(new Set(filtered.map(r => r.id)));
  };

  const preview = HISTORY_DATA.find(r => r.id === previewId);

  const resetFilters = () => {
    setStatus('all'); setPlatform('all'); setType('all'); setCampaign('all'); setRange('30d'); setSearch('');
  };

  const activeFilterCount = [status, platform, type, campaign].filter(v => v !== 'all').length + (search ? 1 : 0);

  return (
    <div className="stack-lg">
      {/* Header */}
      <div className="history-hero">
        <div>
          <h2 style={{ margin: 0, fontSize: 22, fontWeight: 800, letterSpacing: '-0.01em' }}>سجل المحتوى</h2>
          <p style={{ margin: '6px 0 0', color: 'var(--text-muted)', fontSize: 14 }}>
            كل ما أُنشئ، جُدوِل، أو نُشر — مع الفلاتر والبحث.
          </p>
        </div>
        <div className="row-sm">
          <button className="btn btn-secondary btn-sm" onClick={() => toast.info('جاري التصدير...', 'CSV')}>
            <Icon name="download" size={14}/> تصدير CSV
          </button>
          <button className="btn btn-primary btn-sm">
            <Icon name="sparkle" size={14}/> توليد محتوى
          </button>
        </div>
      </div>

      {/* KPI strip */}
      <div className="history-kpis">
        <div className="history-kpi">
          <div className="history-kpi-label">إجمالي</div>
          <div className="history-kpi-value">{stats.total}</div>
        </div>
        <div className="history-kpi">
          <div className="history-kpi-label"><span className="dot dot-success"/> منشور</div>
          <div className="history-kpi-value">{stats.published}</div>
        </div>
        <div className="history-kpi">
          <div className="history-kpi-label"><span className="dot dot-info"/> مجدول</div>
          <div className="history-kpi-value">{stats.scheduled}</div>
        </div>
        <div className="history-kpi">
          <div className="history-kpi-label">مسودات</div>
          <div className="history-kpi-value">{stats.drafts}</div>
        </div>
        <div className="history-kpi">
          <div className="history-kpi-label">انطباعات</div>
          <div className="history-kpi-value">{stats.impressions.toLocaleString('ar-SA')}</div>
        </div>
        <div className="history-kpi">
          <div className="history-kpi-label">توكنز مستخدمة</div>
          <div className="history-kpi-value">{stats.tokens}</div>
        </div>
      </div>

      {/* Filters */}
      <div className="card">
        <div className="history-filters">
          <div className="history-search">
            <Icon name="search" size={15} style={{ color: 'var(--text-muted)' }} />
            <input
              placeholder="ابحث في العناوين..."
              value={search}
              onChange={e => setSearch(e.target.value)}
            />
            {search && (
              <button onClick={() => setSearch('')}><Icon name="x" size={13}/></button>
            )}
          </div>

          <CustomSelect
            value={status} onChange={setStatus}
            options={[
              { value: 'all',       label: 'كل الحالات' },
              { value: 'published', label: 'منشور' },
              { value: 'scheduled', label: 'مجدول' },
              { value: 'draft',     label: 'مسودة' },
              { value: 'failed',    label: 'فشل' },
            ]}
          />
          <CustomSelect
            value={platform} onChange={setPlatform}
            options={[
              { value: 'all',       label: 'كل المنصات' },
              { value: 'instagram', label: 'Instagram', icon: <Icon name="instagram" size={12}/> },
              { value: 'facebook',  label: 'Facebook',  icon: <Icon name="facebook" size={12}/> },
              { value: 'tiktok',    label: 'TikTok',    icon: <Icon name="video" size={12}/> },
              { value: 'snapchat',  label: 'Snapchat',  icon: <Icon name="flash" size={12}/> },
              { value: 'x',         label: 'X (Twitter)', icon: <Icon name="send" size={12}/> },
            ]}
          />
          <CustomSelect
            value={type} onChange={setType}
            options={[
              { value: 'all',   label: 'كل الأنواع' },
              { value: 'منشور', label: 'منشور' },
              { value: 'قصة',   label: 'قصة' },
              { value: 'ريلز',  label: 'ريلز' },
              { value: 'إعلان',  label: 'إعلان' },
            ]}
          />
          <CustomSelect
            searchable
            value={campaign} onChange={setCampaign}
            options={[{ value: 'all', label: 'كل الحملات' }, ...campaigns.map(c => ({ value: c, label: c }))]}
          />
          <CustomSelect
            value={range} onChange={setRange}
            options={[
              { value: '7d',  label: 'آخر ٧ أيام' },
              { value: '30d', label: 'آخر ٣٠ يوم' },
              { value: '90d', label: 'آخر ٩٠ يوم' },
              { value: 'all', label: 'كل الوقت' },
            ]}
          />

          {activeFilterCount > 0 && (
            <button className="btn btn-sm btn-ghost" onClick={resetFilters}>
              <Icon name="x" size={13}/> مسح ({activeFilterCount})
            </button>
          )}

          <div style={{ flex: 1 }} />

          <div className="segmented" style={{ height: 38 }}>
            <button data-active={view === 'table'} onClick={() => setView('table')}>
              <Icon name="menu" size={14}/>
            </button>
            <button data-active={view === 'grid'} onClick={() => setView('grid')}>
              <Icon name="image" size={14}/>
            </button>
          </div>
        </div>

        {/* Bulk actions bar */}
        {selected.size > 0 && (
          <div className="history-bulk-bar">
            <div style={{ fontSize: 13, fontWeight: 600 }}>
              محدد {selected.size} من {filtered.length}
            </div>
            <div className="row-sm" style={{ marginRight: 'auto' }}>
              <button className="btn btn-sm btn-ghost"><Icon name="download" size={13}/> تصدير</button>
              <button className="btn btn-sm btn-ghost"><Icon name="copy" size={13}/> نسخ</button>
              <button className="btn btn-sm btn-danger" onClick={() => setConfirmDel('bulk')}>
                <Icon name="trash" size={13}/> حذف
              </button>
              <button className="btn btn-icon btn-icon-sm btn-ghost" onClick={() => setSelected(new Set())}>
                <Icon name="x" size={14}/>
              </button>
            </div>
          </div>
        )}

        {/* Table view */}
        {view === 'table' && (
          <div className="table-scroll">
            <table className="history-table">
              <thead>
                <tr>
                  <th style={{ width: 36 }}>
                    <Checkbox
                      size="sm"
                      checked={selected.size > 0 && selected.size === filtered.length}
                      indeterminate={selected.size > 0 && selected.size < filtered.length}
                      onChange={toggleAll}
                    />
                  </th>
                  <th>العنوان</th>
                  <th>النوع</th>
                  <th>المنصة</th>
                  <th>اللهجة</th>
                  <th>الحملة</th>
                  <th>التاريخ</th>
                  <th>الحالة</th>
                  <th>الانطباعات</th>
                  <th>التفاعل</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {filtered.length === 0 && (
                  <tr><td colSpan={11} style={{ padding: 40, textAlign: 'center', color: 'var(--text-muted)' }}>
                    لا توجد نتائج — جرّب تعديل الفلاتر
                  </td></tr>
                )}
                {filtered.map(r => {
                  const s = STATUS_META[r.status];
                  return (
                    <tr key={r.id} data-selected={selected.has(r.id)}>
                      <td>
                        <Checkbox
                          size="sm"
                          checked={selected.has(r.id)}
                          onChange={() => toggleSel(r.id)}
                        />
                      </td>
                      <td>
                        <button className="history-title-btn" onClick={() => setPreviewId(r.id)}>
                          {r.title}
                        </button>
                      </td>
                      <td><span className="badge badge-neutral" style={{ fontSize: 11 }}>{r.type}</span></td>
                      <td>
                        <div className="row-sm" style={{ alignItems: 'center' }}>
                          <Icon name={r.platform} size={14}/>
                          <span style={{ fontSize: 12, textTransform: 'capitalize' }}>{r.platform}</span>
                        </div>
                      </td>
                      <td style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.dialect}</td>
                      <td style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.campaign}</td>
                      <td>
                        <div style={{ fontSize: 12, color: 'var(--text-secondary)' }} className="mono">{r.date}</div>
                        <div style={{ fontSize: 11, color: 'var(--text-muted)' }} className="mono">{r.time}</div>
                      </td>
                      <td>
                        <span className={`badge ${s.badge}`}>
                          {s.dot && <span className={`dot ${s.dot}`}/>} {s.label}
                        </span>
                      </td>
                      <td className="mono">{typeof r.impressions === 'number' ? r.impressions.toLocaleString('ar-SA') : r.impressions}</td>
                      <td className="mono">{r.engagement}</td>
                      <td>
                        <div className="row-sm">
                          <button className="btn btn-icon btn-icon-sm btn-ghost" onClick={() => setPreviewId(r.id)} title="معاينة">
                            <Icon name="eye" size={14}/>
                          </button>
                          <button className="btn btn-icon btn-icon-sm btn-ghost" title="إعادة استخدام">
                            <Icon name="copy" size={14}/>
                          </button>
                          <button className="btn btn-icon btn-icon-sm btn-ghost" onClick={() => setConfirmDel(r.id)} title="حذف">
                            <Icon name="trash" size={14}/>
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Grid view */}
        {view === 'grid' && (
          <div className="history-grid">
            {filtered.map(r => {
              const s = STATUS_META[r.status];
              return (
                <div key={r.id} className="history-card" onClick={() => setPreviewId(r.id)}>
                  <ImgPlaceholder label={r.type} h={140} />
                  <div style={{ padding: 14 }}>
                    <div className="row-sm" style={{ justifyContent: 'space-between', marginBottom: 8 }}>
                      <span className={`badge ${s.badge}`}>{s.label}</span>
                      <Icon name={r.platform} size={14} />
                    </div>
                    <div style={{ fontWeight: 700, fontSize: 13, marginBottom: 4, lineHeight: 1.4 }}>{r.title}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)' }} className="mono">{r.date} · {r.time}</div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* Preview modal */}
      <Modal
        open={!!preview}
        onClose={() => setPreviewId(null)}
        title={preview?.title}
        size="md"
        footer={
          <>
            <button className="btn btn-secondary" onClick={() => setPreviewId(null)}>إغلاق</button>
            <button className="btn btn-primary"><Icon name="copy" size={14}/> إعادة استخدام كنموذج</button>
          </>
        }
      >
        {preview && (
          <div className="stack">
            <div className="row" style={{ flexWrap: 'wrap', gap: 8 }}>
              <span className={`badge ${STATUS_META[preview.status].badge}`}>{STATUS_META[preview.status].label}</span>
              <span className="badge badge-neutral">{preview.type}</span>
              <span className="badge badge-brand"><Icon name={preview.platform} size={10}/> {preview.platform}</span>
              <span className="badge badge-sand">{preview.dialect}</span>
            </div>
            <ImgPlaceholder label="معاينة الوسيط" h={240} />
            <div style={{ fontSize: 13, display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
              <div><div style={{ color: 'var(--text-muted)', fontSize: 11, marginBottom: 2 }}>التاريخ</div><div className="mono">{preview.date} · {preview.time}</div></div>
              <div><div style={{ color: 'var(--text-muted)', fontSize: 11, marginBottom: 2 }}>الحملة</div><div>{preview.campaign}</div></div>
              <div><div style={{ color: 'var(--text-muted)', fontSize: 11, marginBottom: 2 }}>الانطباعات</div><div className="mono">{typeof preview.impressions === 'number' ? preview.impressions.toLocaleString('ar-SA') : preview.impressions}</div></div>
              <div><div style={{ color: 'var(--text-muted)', fontSize: 11, marginBottom: 2 }}>التفاعل</div><div className="mono">{preview.engagement}</div></div>
              <div><div style={{ color: 'var(--text-muted)', fontSize: 11, marginBottom: 2 }}>التوكنز</div><div className="mono">{preview.tokens}</div></div>
            </div>
          </div>
        )}
      </Modal>

      <ConfirmModal
        open={!!confirmDel}
        onClose={() => setConfirmDel(null)}
        onConfirm={() => {
          toast.success('تم الحذف', confirmDel === 'bulk' ? `${selected.size} عنصر` : 'تم حذف العنصر');
          setConfirmDel(null);
          if (confirmDel === 'bulk') setSelected(new Set());
        }}
        title={confirmDel === 'bulk' ? 'حذف العناصر المحددة؟' : 'حذف هذا العنصر؟'}
        message="سيتم نقل العناصر إلى سلة المحذوفات. يمكن استرجاعها خلال ٣٠ يوماً."
        confirmText="نعم، احذف"
        variant="danger"
      />
    </div>
  );
};

Object.assign(window, { HistoryScreen });
