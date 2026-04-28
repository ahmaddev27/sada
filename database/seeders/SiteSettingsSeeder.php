<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── General ────────────────────────────────────────────────────
            ['key' => 'site_name',        'value' => 'صدى',                              'type' => 'string', 'group' => 'general',  'label_ar' => 'اسم الموقع',               'is_public' => true,  'sort_order' => 1],
            ['key' => 'site_slogan',      'value' => 'تسويق خليجي بذكاء اصطناعي أصيل', 'type' => 'string', 'group' => 'general',  'label_ar' => 'الشعار (Slogan)',           'is_public' => true,  'sort_order' => 2],
            ['key' => 'site_description', 'value' => 'منصة صدى للتسويق الرقمي بالذكاء الاصطناعي — توليد محتوى تسويقي بلهجات خليجية متعددة وإدارة الحملات الإعلانية.', 'type' => 'text', 'group' => 'general', 'label_ar' => 'وصف الموقع', 'is_public' => true, 'sort_order' => 3],
            ['key' => 'maintenance_mode', 'value' => '0',                                'type' => 'bool',   'group' => 'general',  'label_ar' => 'وضع الصيانة',              'is_public' => false, 'sort_order' => 4],
            ['key' => 'registration_open','value' => '1',                                'type' => 'bool',   'group' => 'general',  'label_ar' => 'التسجيل مفتوح',            'is_public' => true,  'sort_order' => 5],

            // ── Branding ───────────────────────────────────────────────────
            ['key' => 'logo_path',        'value' => null, 'type' => 'image',  'group' => 'branding', 'label_ar' => 'الشعار الرئيسي',      'is_public' => true,  'sort_order' => 1],
            ['key' => 'logo_dark_path',   'value' => null, 'type' => 'image',  'group' => 'branding', 'label_ar' => 'شعار الوضع الداكن',   'is_public' => true,  'sort_order' => 2],
            ['key' => 'favicon_path',     'value' => null, 'type' => 'image',  'group' => 'branding', 'label_ar' => 'الأيقونة (Favicon)',   'is_public' => true,  'sort_order' => 3],
            ['key' => 'og_image_path',    'value' => null, 'type' => 'image',  'group' => 'branding', 'label_ar' => 'صورة المشاركة (OG)',   'is_public' => true,  'sort_order' => 4],
            ['key' => 'primary_color',    'value' => '#0F6F5C', 'type' => 'string', 'group' => 'branding', 'label_ar' => 'اللون الأساسي',  'is_public' => true,  'sort_order' => 5],

            // ── Contact ────────────────────────────────────────────────────
            ['key' => 'contact_email',    'value' => 'hello@sada.sa',      'type' => 'email', 'group' => 'contact', 'label_ar' => 'إيميل التواصل العام',  'is_public' => true,  'sort_order' => 1],
            ['key' => 'support_email',    'value' => 'support@sada.sa',    'type' => 'email', 'group' => 'contact', 'label_ar' => 'إيميل الدعم التقني',   'is_public' => true,  'sort_order' => 2],
            ['key' => 'legal_email',      'value' => 'legal@sada.sa',      'type' => 'email', 'group' => 'contact', 'label_ar' => 'إيميل الشؤون القانونية', 'is_public' => false, 'sort_order' => 3],
            ['key' => 'privacy_email',    'value' => 'privacy@sada.sa',    'type' => 'email', 'group' => 'contact', 'label_ar' => 'إيميل الخصوصية',       'is_public' => false, 'sort_order' => 4],
            ['key' => 'phone',            'value' => null,                  'type' => 'phone', 'group' => 'contact', 'label_ar' => 'رقم الهاتف',            'is_public' => true,  'sort_order' => 5],
            ['key' => 'whatsapp',         'value' => null,                  'type' => 'phone', 'group' => 'contact', 'label_ar' => 'رقم واتساب',            'is_public' => true,  'sort_order' => 6],
            ['key' => 'address',          'value' => null,                  'type' => 'text',  'group' => 'contact', 'label_ar' => 'العنوان',               'is_public' => true,  'sort_order' => 7],

            // ── SEO ────────────────────────────────────────────────────────
            ['key' => 'meta_title',       'value' => 'صدى — التسويق الخليجي بالذكاء الاصطناعي', 'type' => 'string', 'group' => 'seo', 'label_ar' => 'Meta Title',        'is_public' => true,  'sort_order' => 1],
            ['key' => 'meta_description', 'value' => 'منصة صدى لتوليد محتوى تسويقي بلهجات خليجية وإدارة الحملات الإعلانية على منصات التواصل الاجتماعي.', 'type' => 'text', 'group' => 'seo', 'label_ar' => 'Meta Description', 'is_public' => true, 'sort_order' => 2],
            ['key' => 'meta_keywords',    'value' => 'تسويق رقمي, ذكاء اصطناعي, محتوى خليجي, سوشيال ميديا, صدى', 'type' => 'text', 'group' => 'seo', 'label_ar' => 'Meta Keywords', 'is_public' => true, 'sort_order' => 3],
            ['key' => 'google_analytics', 'value' => null, 'type' => 'string', 'group' => 'seo', 'label_ar' => 'Google Analytics ID',     'is_public' => false, 'sort_order' => 4],
            ['key' => 'gtm_id',           'value' => null, 'type' => 'string', 'group' => 'seo', 'label_ar' => 'Google Tag Manager ID',  'is_public' => false, 'sort_order' => 5],

            // ── Social ─────────────────────────────────────────────────────
            ['key' => 'social_facebook',  'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط فيسبوك',     'is_public' => true, 'sort_order' => 1],
            ['key' => 'social_instagram', 'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط انستجرام',   'is_public' => true, 'sort_order' => 2],
            ['key' => 'social_twitter',   'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط X (تويتر)',  'is_public' => true, 'sort_order' => 3],
            ['key' => 'social_tiktok',    'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط تيك توك',    'is_public' => true, 'sort_order' => 4],
            ['key' => 'social_snapchat',  'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط سناب شات',   'is_public' => true, 'sort_order' => 5],
            ['key' => 'social_linkedin',  'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط لينكدإن',    'is_public' => true, 'sort_order' => 6],
            ['key' => 'social_youtube',   'value' => null, 'type' => 'url', 'group' => 'social', 'label_ar' => 'رابط يوتيوب',     'is_public' => true, 'sort_order' => 7],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
