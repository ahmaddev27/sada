/* global React, Icon, Modal */
// Sada — Extended UI: CustomSelect (Select2-style), Checkbox, LogoUploader

const { useState: uS_x, useEffect: uE_x, useRef: uR_x, useCallback: uC_x } = React;

// =====================================================================
// CustomSelect — Select2-style dropdown
// Props:
//   options:  [{ value, label, hint?, icon?, disabled? }]
//   value:    current value
//   onChange: (v) => void
//   placeholder, searchable, size ('sm'|'md'), disabled, full, portal
// =====================================================================
const CustomSelect = ({
  options = [],
  value,
  onChange,
  placeholder = 'اختر...',
  searchable = false,
  size = 'md',
  disabled = false,
  full = false,
  icon,
  renderOption,
}) => {
  const [open, setOpen] = uS_x(false);
  const [query, setQuery] = uS_x('');
  const [highlight, setHighlight] = uS_x(0);
  const rootRef = uR_x(null);
  const listRef = uR_x(null);
  const searchRef = uR_x(null);

  const current = options.find(o => o.value === value);

  const filtered = searchable && query
    ? options.filter(o => o.label.includes(query) || String(o.value).toLowerCase().includes(query.toLowerCase()))
    : options;

  // close on outside click / escape
  uE_x(() => {
    if (!open) return;
    const onDoc = (e) => {
      if (rootRef.current && !rootRef.current.contains(e.target)) setOpen(false);
    };
    const onKey = (e) => {
      if (e.key === 'Escape') { setOpen(false); return; }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        setHighlight(h => Math.min(h + 1, filtered.length - 1));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setHighlight(h => Math.max(h - 1, 0));
      } else if (e.key === 'Enter') {
        e.preventDefault();
        const opt = filtered[highlight];
        if (opt && !opt.disabled) {
          onChange && onChange(opt.value);
          setOpen(false);
          setQuery('');
        }
      }
    };
    document.addEventListener('mousedown', onDoc);
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('mousedown', onDoc);
      document.removeEventListener('keydown', onKey);
    };
  }, [open, filtered, highlight, onChange]);

  // focus search when open
  uE_x(() => {
    if (open && searchable) {
      setTimeout(() => searchRef.current?.focus(), 30);
    }
    if (open) {
      const idx = Math.max(0, options.findIndex(o => o.value === value));
      setHighlight(idx);
    }
  }, [open]);

  // scroll highlighted into view
  uE_x(() => {
    if (!open || !listRef.current) return;
    const el = listRef.current.querySelector(`[data-idx="${highlight}"]`);
    if (el) el.scrollIntoView({ block: 'nearest' });
  }, [highlight, open]);

  return (
    <div
      ref={rootRef}
      className="cs-root"
      data-size={size}
      data-open={open}
      data-disabled={disabled}
      style={full ? { width: '100%' } : {}}
    >
      <button
        type="button"
        className="cs-trigger"
        onClick={() => !disabled && setOpen(v => !v)}
        disabled={disabled}
      >
        {icon && <span className="cs-trigger-icon">{icon}</span>}
        {current?.icon && <span className="cs-trigger-icon">{current.icon}</span>}
        <span className={`cs-value ${!current ? 'is-placeholder' : ''}`}>
          {current ? current.label : placeholder}
        </span>
        <Icon name="chevronDown" size={14} className="cs-chev" />
      </button>

      {open && (
        <div className="cs-panel" role="listbox">
          {searchable && (
            <div className="cs-search">
              <Icon name="search" size={13} />
              <input
                ref={searchRef}
                value={query}
                onChange={e => { setQuery(e.target.value); setHighlight(0); }}
                placeholder="ابحث..."
              />
              {query && (
                <button className="cs-search-clear" onClick={() => setQuery('')}>
                  <Icon name="x" size={12} />
                </button>
              )}
            </div>
          )}
          <div className="cs-options" ref={listRef}>
            {filtered.length === 0 && (
              <div className="cs-empty">لا توجد نتائج</div>
            )}
            {filtered.map((opt, i) => {
              const selected = opt.value === value;
              return (
                <button
                  key={opt.value}
                  type="button"
                  className="cs-option"
                  data-idx={i}
                  data-highlighted={highlight === i}
                  data-selected={selected}
                  data-disabled={opt.disabled}
                  onMouseEnter={() => setHighlight(i)}
                  onClick={() => {
                    if (opt.disabled) return;
                    onChange && onChange(opt.value);
                    setOpen(false);
                    setQuery('');
                  }}
                >
                  {opt.icon && <span className="cs-option-icon">{opt.icon}</span>}
                  <span className="cs-option-body">
                    <span className="cs-option-label">
                      {renderOption ? renderOption(opt) : opt.label}
                    </span>
                    {opt.hint && <span className="cs-option-hint">{opt.hint}</span>}
                  </span>
                  {selected && <Icon name="check" size={14} className="cs-option-check" />}
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

// =====================================================================
// Checkbox — styled checkbox
// =====================================================================
const Checkbox = ({ checked, onChange, label, hint, indeterminate, disabled, size = 'md' }) => {
  const inputRef = uR_x(null);
  uE_x(() => {
    if (inputRef.current) inputRef.current.indeterminate = !!indeterminate;
  }, [indeterminate]);

  const box = (
    <span className="cb-box" data-checked={!!checked} data-indeterminate={!!indeterminate} data-disabled={!!disabled}>
      {checked && !indeterminate && (
        <svg viewBox="0 0 16 16" width="11" height="11">
          <path d="M3 8.5l3 3 7-7" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" fill="none" />
        </svg>
      )}
      {indeterminate && (
        <svg viewBox="0 0 16 16" width="11" height="11">
          <path d="M3.5 8h9" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" fill="none" />
        </svg>
      )}
    </span>
  );

  if (!label && !hint) {
    return (
      <label className="cb-wrap" data-size={size}>
        <input
          ref={inputRef}
          type="checkbox"
          checked={!!checked}
          onChange={e => onChange && onChange(e.target.checked)}
          disabled={disabled}
          className="cb-native"
        />
        {box}
      </label>
    );
  }

  return (
    <label className="cb-wrap cb-wrap-labeled" data-size={size} data-disabled={!!disabled}>
      <input
        ref={inputRef}
        type="checkbox"
        checked={!!checked}
        onChange={e => onChange && onChange(e.target.checked)}
        disabled={disabled}
        className="cb-native"
      />
      {box}
      <span className="cb-text">
        <span className="cb-label">{label}</span>
        {hint && <span className="cb-hint">{hint}</span>}
      </span>
    </label>
  );
};

// =====================================================================
// LogoUploader — drag/drop, crop, resize
// Props:
//   value: dataUrl | null
//   onChange: (dataUrl | null) => void
//   outputSize: output square size in px (default 512)
//   shape: 'square' | 'rounded' | 'circle'
// =====================================================================
const LogoUploader = ({ value, onChange, outputSize = 512, shape = 'rounded' }) => {
  const [cropOpen, setCropOpen] = uS_x(false);
  const [rawImage, setRawImage] = uS_x(null); // HTMLImageElement
  const [rawFileName, setRawFileName] = uS_x('');
  const [zoom, setZoom] = uS_x(1);
  const [offsetX, setOffsetX] = uS_x(0);
  const [offsetY, setOffsetY] = uS_x(0);
  const [dragging, setDragging] = uS_x(false);
  const canvasRef = uR_x(null);
  const containerRef = uR_x(null);
  const fileInputRef = uR_x(null);
  const dragStartRef = uR_x({ x: 0, y: 0, ox: 0, oy: 0 });

  const CROP_VIEW = 280; // px on screen (display size of crop canvas)

  const pickFile = () => fileInputRef.current?.click();

  const handleFile = (file) => {
    if (!file) return;
    if (!file.type.startsWith('image/')) return;
    const fr = new FileReader();
    fr.onload = () => {
      const img = new Image();
      img.onload = () => {
        setRawImage(img);
        setRawFileName(file.name);
        // initial: cover fit — set zoom so the shorter dim fills the crop area
        const shorter = Math.min(img.width, img.height);
        const z = CROP_VIEW / shorter;
        setZoom(z);
        setOffsetX(0);
        setOffsetY(0);
        setCropOpen(true);
      };
      img.src = fr.result;
    };
    fr.readAsDataURL(file);
  };

  const onDrop = (e) => {
    e.preventDefault();
    setDragging(false);
    const file = e.dataTransfer.files?.[0];
    handleFile(file);
  };

  // redraw crop canvas
  uE_x(() => {
    if (!cropOpen || !rawImage || !canvasRef.current) return;
    const c = canvasRef.current;
    const ctx = c.getContext('2d');
    c.width = CROP_VIEW;
    c.height = CROP_VIEW;
    ctx.clearRect(0, 0, CROP_VIEW, CROP_VIEW);

    // checker bg
    ctx.fillStyle = '#f0f0f0';
    ctx.fillRect(0, 0, CROP_VIEW, CROP_VIEW);
    ctx.fillStyle = '#fafafa';
    const s = 10;
    for (let y = 0; y < CROP_VIEW; y += s) {
      for (let x = 0; x < CROP_VIEW; x += s) {
        if (((x / s) + (y / s)) % 2 === 0) ctx.fillRect(x, y, s, s);
      }
    }

    const w = rawImage.width * zoom;
    const h = rawImage.height * zoom;
    const cx = (CROP_VIEW - w) / 2 + offsetX;
    const cy = (CROP_VIEW - h) / 2 + offsetY;
    ctx.drawImage(rawImage, cx, cy, w, h);
  }, [cropOpen, rawImage, zoom, offsetX, offsetY]);

  const onPointerDown = (e) => {
    e.preventDefault();
    const pt = e.touches ? e.touches[0] : e;
    dragStartRef.current = { x: pt.clientX, y: pt.clientY, ox: offsetX, oy: offsetY };
    const onMove = (ev) => {
      const m = ev.touches ? ev.touches[0] : ev;
      const dx = m.clientX - dragStartRef.current.x;
      const dy = m.clientY - dragStartRef.current.y;
      setOffsetX(dragStartRef.current.ox + dx);
      setOffsetY(dragStartRef.current.oy + dy);
    };
    const onUp = () => {
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', onUp);
      document.removeEventListener('touchmove', onMove);
      document.removeEventListener('touchend', onUp);
    };
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onUp);
    document.addEventListener('touchmove', onMove, { passive: false });
    document.addEventListener('touchend', onUp);
  };

  const applyCrop = () => {
    if (!rawImage) return;
    // output canvas
    const out = document.createElement('canvas');
    out.width = outputSize;
    out.height = outputSize;
    const octx = out.getContext('2d');
    const scale = outputSize / CROP_VIEW;
    const w = rawImage.width * zoom * scale;
    const h = rawImage.height * zoom * scale;
    const cx = (outputSize - w) / 2 + offsetX * scale;
    const cy = (outputSize - h) / 2 + offsetY * scale;
    octx.drawImage(rawImage, cx, cy, w, h);
    const url = out.toDataURL('image/png', 0.95);
    onChange && onChange(url);
    setCropOpen(false);
    setRawImage(null);
  };

  const removeLogo = () => onChange && onChange(null);

  const fileSizeText = () => {
    if (!value) return '';
    const bytes = Math.round((value.length * 3) / 4);
    const kb = bytes / 1024;
    return kb < 1024 ? `${kb.toFixed(0)}KB` : `${(kb/1024).toFixed(1)}MB`;
  };

  const shapeRadius = shape === 'circle' ? '50%' : shape === 'rounded' ? 18 : 4;

  return (
    <>
      <div className="logo-uploader">
        {!value ? (
          <div
            className="logo-dropzone"
            data-dragging={dragging}
            onDragOver={e => { e.preventDefault(); setDragging(true); }}
            onDragLeave={() => setDragging(false)}
            onDrop={onDrop}
            onClick={pickFile}
          >
            <div className="logo-dropzone-icon">
              <Icon name="upload" size={22} />
            </div>
            <div className="logo-dropzone-title">اسحب شعارك هنا أو انقر لاختياره</div>
            <div className="logo-dropzone-hint">PNG · JPG · SVG · حتى 5MB · يُضبط تلقائياً إلى {outputSize}×{outputSize}</div>
          </div>
        ) : (
          <div className="logo-filled">
            <div
              className="logo-filled-preview"
              style={{ borderRadius: shapeRadius, background: `url(${value}) center/cover, var(--bg-muted)` }}
            />
            <div className="logo-filled-body">
              <div className="logo-filled-name">{rawFileName || 'logo.png'}</div>
              <div className="logo-filled-meta">{outputSize}×{outputSize} · {fileSizeText()}</div>
              <div className="row-sm" style={{ marginTop: 10 }}>
                <button type="button" className="btn btn-sm btn-secondary" onClick={pickFile}>
                  <Icon name="upload" size={13}/> تغيير
                </button>
                <button type="button" className="btn btn-sm btn-ghost" onClick={() => setCropOpen(true)} disabled={!rawImage}>
                  <Icon name="edit" size={13}/> قصّ
                </button>
                <button type="button" className="btn btn-sm btn-ghost btn-danger-ghost" onClick={removeLogo}>
                  <Icon name="trash" size={13}/>
                </button>
              </div>
            </div>
          </div>
        )}
        <input
          ref={fileInputRef}
          type="file"
          accept="image/png,image/jpeg,image/svg+xml,image/webp"
          style={{ display: 'none' }}
          onChange={e => handleFile(e.target.files?.[0])}
        />
      </div>

      <Modal
        open={cropOpen}
        onClose={() => setCropOpen(false)}
        title="قصّ الشعار"
        size="sm"
        footer={
          <>
            <button className="btn btn-secondary" onClick={() => setCropOpen(false)}>إلغاء</button>
            <button className="btn btn-primary" onClick={applyCrop}>
              <Icon name="check" size={14}/> تطبيق
            </button>
          </>
        }
      >
        <div className="crop-ui">
          <div
            ref={containerRef}
            className="crop-stage"
            onMouseDown={onPointerDown}
            onTouchStart={onPointerDown}
            data-shape={shape}
          >
            <canvas ref={canvasRef} />
            <div className="crop-mask" data-shape={shape} />
          </div>
          <div className="crop-controls">
            <div className="crop-control-row">
              <Icon name="image" size={14}/>
              <input
                type="range"
                min="0.2" max="3" step="0.02"
                value={zoom}
                onChange={e => setZoom(parseFloat(e.target.value))}
                className="brand-range"
              />
              <span className="mono" style={{ fontSize: 11, color: 'var(--text-muted)', minWidth: 36, textAlign: 'end' }}>
                {Math.round(zoom * 100)}%
              </span>
            </div>
            <div className="crop-hint">اسحب الصورة لتحريكها · استخدم الشريط للتكبير</div>
          </div>
        </div>
      </Modal>
    </>
  );
};

Object.assign(window, { CustomSelect, Checkbox, LogoUploader });
