/* global React, Icon, Flag */
// Sada — Landing Page + Onboarding

const { useState: useState_l } = React;

const LandingScreen = ({ onEnterApp }) => {
  return (
    <div style={{ background: 'var(--bg-page)', minHeight: '100vh' }}>
      {/* Nav */}
      <nav style={{
        position: 'sticky', top: 0, zIndex: 40,
        padding: '16px 40px',
        background: 'color-mix(in oklab, var(--bg-page) 85%, transparent)',
        backdropFilter: 'blur(10px)',
        borderBottom: '1px solid var(--border-subtle)',
        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 40 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontWeight: 800, fontSize: 22 }}>
            <div style={{
              width: 32, height: 32,
              background: 'var(--sada-500)',
              borderRadius: 9, color: '#fff',
              display: 'grid', placeItems: 'center', fontWeight: 800,
            }}>ص</div>
            <span>صدى</span>
          </div>
          <div style={{ display: 'flex', gap: 24 }} className="landing-nav">
            {['المميزات', 'التسعير', 'المواسم', 'المدونة'].map(l => (
              <a key={l} style={{ fontSize: 14, color: 'var(--text-secondary)', fontWeight: 500 }}>{l}</a>
            ))}
          </div>
        </div>
        <div style={{ display: 'flex', gap: 8 }}>
          <button className="btn btn-sm btn-ghost" onClick={onEnterApp}>تسجيل دخول</button>
          <button className="btn btn-sm btn-primary" onClick={onEnterApp}>ابدأ مجاناً <Icon name="arrowLeft" size={14} /></button>
        </div>
      </nav>

      {/* Hero */}
      <section style={{
        padding: '80px 40px 40px',
        maxWidth: 1280, margin: '0 auto',
        display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 40, alignItems: 'center',
      }} className="hero-grid">
        <div>
          <span style={{
            display: 'inline-flex', alignItems: 'center', gap: 6,
            padding: '5px 12px',
            background: 'var(--accent-soft)', color: 'var(--accent-text)',
            borderRadius: 99, fontSize: 12, fontWeight: 600,
            marginBottom: 20,
          }}>
            <Icon name="sparkle" size={12} /> جديد — لهجات خليجية بدقة
          </span>
          <h1 style={{
            margin: 0, fontSize: 52, fontWeight: 800,
            letterSpacing: '-0.02em', lineHeight: 1.1,
            color: 'var(--text-primary)',
          }}>
            محتوى تسويقي خليجي
            <span style={{
              display: 'block',
              background: 'linear-gradient(90deg, var(--sada-500), var(--sand-500))',
              WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent',
            }}>يتكلم لغتك.</span>
          </h1>
          <p style={{
            margin: '20px 0 32px', fontSize: 17, lineHeight: 1.7,
            color: 'var(--text-secondary)', maxWidth: 520,
          }}>
            منصة ذكاء اصطناعي كاملة لتوليد المحتوى، جدولة النشر، وإطلاق الحملات —
            بلهجتك، وبمواسمك، ومن مكان واحد.
          </p>
          <div style={{ display: 'flex', gap: 12 }}>
            <button className="btn btn-xl btn-primary" onClick={onEnterApp}>
              جرّب مجاناً <Icon name="arrowLeft" size={16} />
            </button>
            <button className="btn btn-xl btn-secondary">
              <Icon name="video" size={16} /> شاهد عرضاً مباشراً
            </button>
          </div>
          <div style={{
            marginTop: 28, display: 'flex', gap: 24, fontSize: 13, color: 'var(--text-muted)',
            flexWrap: 'wrap',
          }}>
            <span style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
              <Icon name="check" size={14} style={{ color: 'var(--success)' }} /> ٣٠ يوم مجاناً
            </span>
            <span style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
              <Icon name="check" size={14} style={{ color: 'var(--success)' }} /> بدون بطاقة ائتمان
            </span>
            <span style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
              <Icon name="check" size={14} style={{ color: 'var(--success)' }} /> دعم عربي ٢٤/٧
            </span>
          </div>
        </div>
        <div style={{ position: 'relative' }}>
          <LandingMockup />
        </div>
      </section>

      {/* Social proof */}
      <section style={{
        padding: '40px 40px 60px',
        maxWidth: 1280, margin: '0 auto',
        textAlign: 'center',
      }}>
        <div style={{ fontSize: 13, color: 'var(--text-muted)', marginBottom: 20, fontWeight: 500 }}>
          موثوق من أكثر من ١٥٠ متجراً وعلامة خليجية
        </div>
        <div style={{
          display: 'flex', gap: 48, justifyContent: 'center', alignItems: 'center',
          flexWrap: 'wrap', opacity: 0.5,
        }}>
          {['متجر أنيق', 'قهوة الرواق', 'طلبات فاست', 'لاونج ٤٢', 'بيت الورد', 'نكهات'].map(b => (
            <span key={b} style={{ fontSize: 18, fontWeight: 700, fontFamily: 'var(--font-arabic-display)' }}>{b}</span>
          ))}
        </div>
      </section>

      {/* Features */}
      <section style={{
        padding: '60px 40px',
        maxWidth: 1280, margin: '0 auto',
      }}>
        <div style={{ textAlign: 'center', marginBottom: 48 }}>
          <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--accent)', marginBottom: 8, letterSpacing: '0.05em' }}>
            المميزات
          </div>
          <h2 style={{ margin: 0, fontSize: 40, fontWeight: 800, letterSpacing: '-0.02em' }}>
            كل ما تحتاجه لتسويقك الرقمي
          </h2>
        </div>
        <div className="grid-3">
          {[
            { icon: 'sparkle', title: 'توليد محتوى بلهجتك', desc: 'فصحى، سعودي، إماراتي، كويتي، قطري، بحريني، عُماني — كلها مع فهم عميق للسياق.', color: 'var(--sada-500)' },
            { icon: 'moon', title: 'حملات موسمية بضغطة', desc: 'رمضان، العيدين، اليوم الوطني، التأسيس — قوالب وأفكار جاهزة ومحترمة.', color: 'var(--sand-500)' },
            { icon: 'calendar', title: 'جدولة ذكية على Meta', desc: 'انشر على انستجرام وفيسبوك من جدول واحد، مع اقتراح أفضل أوقات النشر.', color: 'var(--info)' },
            { icon: 'megaphone', title: 'إعلانات ممولة', desc: 'أطلق حملات مدفوعة على Meta من نفس المكان — بميزانية صغيرة أو كبيرة.', color: 'var(--warning)' },
            { icon: 'chart', title: 'تحليلات وتقارير', desc: 'رؤى ذكية بالعربية + تقارير قابلة للتخصيص بشعارك (مثالية للوكالات).', color: 'var(--success)' },
            { icon: 'building', title: 'Workspaces للوكالات', desc: 'أدر عملاءك في مساحات منفصلة، بفواتير وأذونات مستقلة.', color: 'var(--sada-600)' },
          ].map((f, i) => (
            <div key={i} className="card card-hoverable" style={{ padding: 24 }}>
              <div style={{
                width: 44, height: 44, borderRadius: 11,
                background: `color-mix(in oklab, ${f.color} 14%, transparent)`,
                color: f.color,
                display: 'grid', placeItems: 'center',
                marginBottom: 16,
              }}>
                <Icon name={f.icon} size={22} />
              </div>
              <h3 style={{ margin: 0, fontSize: 16, fontWeight: 700, marginBottom: 6 }}>{f.title}</h3>
              <p style={{ margin: 0, fontSize: 13, color: 'var(--text-muted)', lineHeight: 1.7 }}>{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Pricing */}
      <section style={{ padding: '60px 40px', maxWidth: 1180, margin: '0 auto' }}>
        <div style={{ textAlign: 'center', marginBottom: 40 }}>
          <h2 style={{ margin: 0, fontSize: 40, fontWeight: 800, letterSpacing: '-0.02em' }}>
            أسعار شفافة، بلا مفاجآت
          </h2>
          <p style={{ fontSize: 15, color: 'var(--text-muted)', marginTop: 8 }}>
            ابدأ مجاناً، وارقّ عندما تحتاج. دفع بالريال/الدرهم عبر Moyasar & Tap.
          </p>
        </div>
        <div className="grid-3">
          {[
            { name: 'Starter', price: '٩٩', tokens: '١٬٠٠٠', features: ['مستخدم واحد', 'مساحة عمل واحدة', 'انستجرام + فيسبوك', 'توليد محتوى بـ٣ لهجات', 'دعم عبر البريد'] },
            { name: 'Growth', price: '٢٩٩', tokens: '٢٬٠٠٠', featured: true, features: ['حتى ٣ مستخدمين', '٣ مساحات عمل', 'كل المنصات والميزات', 'كل اللهجات الخليجية', 'حملات إعلانية Meta', 'دعم فوري'] },
            { name: 'Agency', price: '٧٩٩', tokens: '١٠٬٠٠٠', features: ['مستخدمون غير محدودين', 'مساحات غير محدودة', 'White-label تقارير', 'API وصلاحيات متقدمة', 'مدير حساب مخصص'] },
          ].map(p => (
            <div key={p.name} className="card" style={{
              padding: 28,
              borderColor: p.featured ? 'var(--accent)' : 'var(--border-subtle)',
              borderWidth: p.featured ? 2 : 1,
              position: 'relative',
              background: p.featured ? 'var(--bg-surface)' : 'var(--bg-surface)',
              transform: p.featured ? 'scale(1.03)' : 'scale(1)',
            }}>
              {p.featured && (
                <div style={{
                  position: 'absolute', top: -14, right: 24,
                  padding: '4px 14px', fontSize: 11, fontWeight: 700,
                  background: 'var(--accent)', color: '#fff',
                  borderRadius: 99,
                }}>الأكثر شعبية</div>
              )}
              <div style={{ fontSize: 18, fontWeight: 800, marginBottom: 10 }}>{p.name}</div>
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, marginBottom: 6 }}>
                <span style={{ fontSize: 44, fontWeight: 800, letterSpacing: '-0.02em' }}>{p.price}</span>
                <span style={{ fontSize: 14, color: 'var(--text-muted)' }}>ر.س / شهر</span>
              </div>
              <div style={{ fontSize: 13, color: 'var(--text-muted)', marginBottom: 24 }}>
                {p.tokens} توكن/شهر
              </div>
              <button className={`btn btn-lg ${p.featured ? 'btn-primary' : 'btn-secondary'}`} style={{ width: '100%', marginBottom: 24 }}>
                ابدأ بـ{p.name}
              </button>
              <div className="stack-sm">
                {p.features.map((f, i) => (
                  <div key={i} style={{ display: 'flex', gap: 8, fontSize: 13, alignItems: 'flex-start' }}>
                    <Icon name="check" size={16} style={{ color: 'var(--accent)', flexShrink: 0, marginTop: 2 }} />
                    <span>{f}</span>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Testimonial */}
      <section style={{
        padding: '60px 40px',
        maxWidth: 900, margin: '0 auto',
        textAlign: 'center',
      }}>
        <div style={{
          fontSize: 28, fontWeight: 700, lineHeight: 1.5, letterSpacing: '-0.01em',
          color: 'var(--text-primary)',
        }}>
          «لأول مرة، أداة تسويقية تفهم لهجتنا الخليجية وتحترم مواسمنا.
          صدى وفّرت لنا ساعات كل أسبوع.»
        </div>
        <div style={{ display: 'flex', gap: 14, justifyContent: 'center', alignItems: 'center', marginTop: 28 }}>
          <div style={{
            width: 48, height: 48, borderRadius: '50%',
            background: 'linear-gradient(135deg, var(--sand-400), var(--sand-600))',
            color: '#fff', fontWeight: 700, fontSize: 18,
            display: 'grid', placeItems: 'center',
          }}>نس</div>
          <div style={{ textAlign: 'right' }}>
            <div style={{ fontSize: 14, fontWeight: 700 }}>نورة السبيعي</div>
            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>مديرة التسويق، متجر أنيق</div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer style={{
        marginTop: 40,
        padding: '40px 40px',
        borderTop: '1px solid var(--border-subtle)',
        background: 'var(--bg-surface)',
      }}>
        <div style={{
          maxWidth: 1280, margin: '0 auto',
          display: 'flex', justifyContent: 'space-between',
          flexWrap: 'wrap', gap: 20,
          fontSize: 13, color: 'var(--text-muted)',
        }}>
          <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
            <div style={{
              width: 24, height: 24, background: 'var(--sada-500)',
              borderRadius: 6, color: '#fff',
              display: 'grid', placeItems: 'center', fontWeight: 800, fontSize: 13,
            }}>ص</div>
            <span style={{ fontWeight: 700, color: 'var(--text-primary)' }}>صدى</span>
            <span>© ٢٠٢٦ — جميع الحقوق محفوظة</span>
          </div>
          <div style={{ display: 'flex', gap: 20 }}>
            <a>الخصوصية</a>
            <a>الشروط</a>
            <a>الدعم</a>
            <a>تواصل</a>
          </div>
        </div>
      </footer>
    </div>
  );
};

// Mockup shown in hero
const LandingMockup = () => (
  <div style={{
    background: 'var(--bg-surface)',
    border: '1px solid var(--border-default)',
    borderRadius: 20,
    padding: 20,
    boxShadow: 'var(--shadow-xl)',
    position: 'relative',
    overflow: 'hidden',
  }}>
    {/* Window chrome */}
    <div style={{ display: 'flex', gap: 6, marginBottom: 16 }}>
      <div style={{ width: 10, height: 10, borderRadius: '50%', background: '#FF5F57' }} />
      <div style={{ width: 10, height: 10, borderRadius: '50%', background: '#FEBC2E' }} />
      <div style={{ width: 10, height: 10, borderRadius: '50%', background: '#28C840' }} />
    </div>

    {/* Inner mock */}
    <div style={{
      background: 'var(--bg-surface-2)',
      borderRadius: 12,
      padding: 16,
      display: 'grid', gridTemplateColumns: '1fr 1.4fr', gap: 14,
    }}>
      {/* Left card */}
      <div style={{
        background: 'var(--bg-surface)',
        border: '1px solid var(--border-subtle)',
        borderRadius: 10, padding: 14,
      }}>
        <div style={{ fontSize: 10, color: 'var(--text-muted)', marginBottom: 4 }}>اللهجة</div>
        <div style={{ display: 'flex', gap: 4, flexWrap: 'wrap', marginBottom: 10 }}>
          {['🇸🇦 سعودي', '🇦🇪 إماراتي', 'فصحى'].map((l, i) => (
            <span key={i} style={{
              fontSize: 10, padding: '3px 7px',
              background: i === 0 ? 'var(--accent-soft)' : 'var(--bg-muted)',
              color: i === 0 ? 'var(--accent-text)' : 'var(--text-muted)',
              borderRadius: 99, fontWeight: 600,
            }}>{l}</span>
          ))}
        </div>
        <div style={{
          fontSize: 10, color: 'var(--text-muted)',
          padding: 10, background: 'var(--bg-muted)',
          borderRadius: 6, lineHeight: 1.6,
        }}>
          خصم ٣٠٪ بمناسبة اليوم الوطني...
        </div>
        <div style={{
          marginTop: 10, padding: '6px 10px',
          background: 'var(--accent)', color: '#fff',
          borderRadius: 6, textAlign: 'center',
          fontSize: 10, fontWeight: 700,
        }}>✨ ولّد</div>
      </div>

      {/* Right preview */}
      <div style={{
        background: '#fff',
        border: '1px solid var(--border-subtle)',
        borderRadius: 10, overflow: 'hidden',
      }}>
        <div style={{
          aspectRatio: '1/1',
          background: 'linear-gradient(135deg, var(--sand-100), var(--sand-300))',
          display: 'grid', placeItems: 'center',
        }}>
          <div style={{ textAlign: 'center', color: 'var(--sand-700)' }}>
            <div style={{ fontSize: 34, fontWeight: 800 }}>٣٠٪</div>
            <div style={{ fontSize: 10, fontWeight: 600 }}>خصم</div>
          </div>
        </div>
        <div style={{ padding: 10, fontSize: 10, lineHeight: 1.5, color: '#111' }}>
          <strong>متجر_أنيق</strong> عطيناكم ٣٠٪ خصم على كل شي 🇸🇦
        </div>
      </div>
    </div>

    {/* Floating metric */}
    <div style={{
      position: 'absolute', bottom: 16, left: 16,
      background: 'var(--bg-surface)',
      border: '1px solid var(--border-default)',
      borderRadius: 10, padding: '10px 14px',
      boxShadow: 'var(--shadow-md)',
      display: 'flex', gap: 10, alignItems: 'center',
    }}>
      <div style={{
        width: 28, height: 28, borderRadius: 7,
        background: 'var(--success-bg)', color: 'var(--success)',
        display: 'grid', placeItems: 'center',
      }}>
        <Icon name="up" size={14} />
      </div>
      <div>
        <div style={{ fontSize: 10, color: 'var(--text-muted)' }}>التفاعل هذا الأسبوع</div>
        <div style={{ fontSize: 13, fontWeight: 800 }}>+٤٥٪</div>
      </div>
    </div>
  </div>
);

// --- Onboarding ---
const OnboardingScreen = ({ onFinish }) => {
  const [step, setStep] = useState_l(1);
  const total = 4;

  return (
    <div style={{
      minHeight: '100vh', background: 'var(--bg-page)',
      display: 'flex', flexDirection: 'column',
    }}>
      <header style={{
        padding: '20px 40px',
        borderBottom: '1px solid var(--border-subtle)',
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        background: 'var(--bg-surface)',
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontWeight: 800, fontSize: 18 }}>
          <div style={{
            width: 28, height: 28, background: 'var(--sada-500)',
            borderRadius: 7, color: '#fff',
            display: 'grid', placeItems: 'center', fontWeight: 800, fontSize: 14,
          }}>ص</div>
          صدى
        </div>
        <button className="btn btn-sm btn-ghost" onClick={onFinish}>تخطّي</button>
      </header>

      <div style={{
        flex: 1,
        maxWidth: 640, margin: '0 auto',
        padding: '40px 20px', width: '100%',
      }}>
        {/* Progress RTL */}
        <div style={{ display: 'flex', gap: 8, marginBottom: 32 }}>
          {[1, 2, 3, 4].map(n => (
            <div key={n} style={{
              flex: 1, height: 4, borderRadius: 99,
              background: n <= step ? 'var(--accent)' : 'var(--border-subtle)',
              transition: 'background var(--dur-base)',
            }} />
          ))}
        </div>

        <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-muted)', marginBottom: 6 }}>
          الخطوة {step} من {total}
        </div>

        {step === 1 && <StepOne />}
        {step === 2 && <StepTwo />}
        {step === 3 && <StepThree />}
        {step === 4 && <StepFour />}

        <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 40 }}>
          <button
            className="btn btn-secondary"
            onClick={() => setStep(Math.max(1, step - 1))}
            style={{ visibility: step === 1 ? 'hidden' : 'visible' }}
          >
            <Icon name="chevronRight" size={14} /> السابق
          </button>
          <button
            className="btn btn-primary"
            onClick={() => step < total ? setStep(step + 1) : onFinish()}
          >
            {step === total ? 'ابدأ استخدام صدى' : 'التالي'}
            <Icon name="chevronLeft" size={14} />
          </button>
        </div>
      </div>
    </div>
  );
};

const StepOne = () => (
  <div>
    <h2 style={{ margin: 0, fontSize: 28, fontWeight: 800, letterSpacing: '-0.01em' }}>
      لنبدأ بإعداد مساحة عملك
    </h2>
    <p style={{ fontSize: 14, color: 'var(--text-muted)', marginTop: 6, marginBottom: 32 }}>
      هذه المعلومات تساعد صدى على فهم طبيعة عملك
    </p>
    <div className="stack-lg">
      <div className="input-group">
        <label className="input-label">اسم المساحة</label>
        <input className="input" defaultValue="متجر أنيق" placeholder="مثلاً: متجر أنيق" />
      </div>
      <div className="input-group">
        <label className="input-label">نوع النشاط</label>
        <select className="select">
          <option>تجارة إلكترونية</option>
          <option>مطعم / مقهى</option>
          <option>خدمات</option>
          <option>عقارات</option>
          <option>تعليم</option>
        </select>
      </div>
      <div className="input-group">
        <label className="input-label">الدول المستهدفة</label>
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          {[
            { code: 'sa', label: 'السعودية', sel: true },
            { code: 'ae', label: 'الإمارات', sel: true },
            { code: 'kw', label: 'الكويت', sel: false },
            { code: 'qa', label: 'قطر', sel: false },
            { code: 'bh', label: 'البحرين', sel: false },
            { code: 'om', label: 'عُمان', sel: false },
          ].map(c => (
            <span key={c.code} className="chip" data-selected={c.sel}>
              <Flag code={c.code} /> {c.label}
            </span>
          ))}
        </div>
      </div>
      <div className="input-group">
        <label className="input-label">اللهجة الافتراضية</label>
        <select className="select" defaultValue="sa">
          <option value="fos">الفصحى</option>
          <option value="sa">🇸🇦 السعودية</option>
          <option value="ae">🇦🇪 الإماراتية</option>
        </select>
      </div>
    </div>
  </div>
);

const StepTwo = () => (
  <div>
    <h2 style={{ margin: 0, fontSize: 28, fontWeight: 800 }}>هوية علامتك التجارية</h2>
    <p style={{ fontSize: 14, color: 'var(--text-muted)', marginTop: 6, marginBottom: 32 }}>
      كلما عرفت صدى علامتك أكثر، كلما كان المحتوى أقرب لصوتك
    </p>
    <div className="stack-lg">
      <div className="input-group">
        <label className="input-label">شعار العلامة</label>
        <div style={{
          border: '2px dashed var(--border-default)',
          borderRadius: 12, padding: 28, textAlign: 'center',
          background: 'var(--bg-surface-2)',
          cursor: 'pointer',
        }}>
          <Icon name="upload" size={22} style={{ color: 'var(--text-muted)', marginBottom: 8 }} />
          <div style={{ fontSize: 13, fontWeight: 600 }}>اسحب الملف هنا، أو اضغط لاختياره</div>
          <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>PNG, SVG — أقصى ٢MB</div>
        </div>
      </div>

      <div className="input-group">
        <label className="input-label">ألوان العلامة</label>
        <div style={{ display: 'flex', gap: 10 }}>
          {['#0F6F5C', '#C8965F', '#0E1512'].map((c, i) => (
            <div key={i} style={{ display: 'flex', gap: 8, alignItems: 'center', padding: '8px 12px', background: 'var(--bg-muted)', borderRadius: 8 }}>
              <div style={{ width: 20, height: 20, borderRadius: 5, background: c, border: '1px solid var(--border-default)' }} />
              <span style={{ fontFamily: 'var(--font-mono)', fontSize: 12 }}>{c}</span>
            </div>
          ))}
          <button className="btn btn-sm btn-secondary">+ إضافة</button>
        </div>
      </div>

      <div className="input-group">
        <label className="input-label">نبرة الصوت</label>
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          {[
            { label: 'ودّية', sel: true },
            { label: 'عصرية', sel: true },
            { label: 'رسمية', sel: false },
            { label: 'فاخرة', sel: false },
            { label: 'مرحة', sel: false },
          ].map(t => (
            <span key={t.label} className="chip" data-selected={t.sel}>{t.label}</span>
          ))}
        </div>
      </div>
    </div>
  </div>
);

const StepThree = () => (
  <div>
    <h2 style={{ margin: 0, fontSize: 28, fontWeight: 800 }}>اربط حسابات Meta</h2>
    <p style={{ fontSize: 14, color: 'var(--text-muted)', marginTop: 6, marginBottom: 32 }}>
      اربط حساباتك على انستجرام وفيسبوك لتتمكن من الجدولة والنشر مباشرة
    </p>
    <div className="stack">
      <button className="card card-hoverable" style={{
        padding: 20, display: 'flex', alignItems: 'center', gap: 16,
        cursor: 'pointer', textAlign: 'right',
      }}>
        <div style={{
          width: 48, height: 48, borderRadius: 12,
          background: 'linear-gradient(135deg, #833AB4, #E1306C, #FD1D1D)',
          color: '#fff', display: 'grid', placeItems: 'center',
        }}>
          <Icon name="instagram" size={22} />
        </div>
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 15, fontWeight: 700 }}>انستجرام</div>
          <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>اربط حساب الأعمال على انستجرام</div>
        </div>
        <span className="btn btn-sm btn-primary">اربط</span>
      </button>

      <button className="card card-hoverable" style={{
        padding: 20, display: 'flex', alignItems: 'center', gap: 16,
        cursor: 'pointer', textAlign: 'right',
        borderColor: 'var(--accent)',
        background: 'var(--accent-soft)',
      }}>
        <div style={{
          width: 48, height: 48, borderRadius: 12,
          background: '#1877F2',
          color: '#fff', display: 'grid', placeItems: 'center',
        }}>
          <Icon name="facebook" size={22} />
        </div>
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 15, fontWeight: 700 }}>فيسبوك · متجر أنيق</div>
          <div style={{ fontSize: 12, color: 'var(--success)', fontWeight: 600 }}>
            <Icon name="check" size={12} style={{ verticalAlign: '-2px' }} /> تم الربط بنجاح
          </div>
        </div>
        <button className="btn btn-sm btn-ghost">إدارة</button>
      </button>
    </div>
  </div>
);

const StepFour = () => (
  <div style={{ textAlign: 'center', padding: '40px 0' }}>
    <div style={{
      width: 80, height: 80, margin: '0 auto 24px',
      background: 'linear-gradient(135deg, var(--sada-500), var(--sand-500))',
      borderRadius: '50%', color: '#fff',
      display: 'grid', placeItems: 'center',
      animation: 'pop 0.5s var(--ease-out)',
    }}>
      <Icon name="check" size={40} stroke={3} />
    </div>
    <style>{`@keyframes pop { from { transform: scale(0); } to { transform: scale(1); } }`}</style>
    <h2 style={{ margin: 0, fontSize: 32, fontWeight: 800 }}>جاهز! 🎉</h2>
    <p style={{ fontSize: 15, color: 'var(--text-muted)', marginTop: 12, marginBottom: 32, maxWidth: 440, margin: '12px auto 32px' }}>
      مساحة عملك جاهزة. يمكنك الآن البدء بتوليد محتوى، جدولة منشورات، أو تصفح القوالب الموسمية.
    </p>
    <div style={{ display: 'flex', gap: 12, justifyContent: 'center' }}>
      <button className="btn btn-lg btn-primary"><Icon name="sparkle" size={16} /> ولّد أول منشور</button>
      <button className="btn btn-lg btn-secondary">تصفح القوالب</button>
    </div>
  </div>
);

Object.assign(window, { LandingScreen, OnboardingScreen });
