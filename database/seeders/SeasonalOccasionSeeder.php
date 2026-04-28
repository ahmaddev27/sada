<?php

namespace Database\Seeders;

use App\Models\SeasonalOccasion;
use Illuminate\Database\Seeder;

class SeasonalOccasionSeeder extends Seeder
{
    public function run(): void
    {
        $occasions = [
            // ── Islamic ──────────────────────────────────────────────────────
            [
                'key' => 'ramadan', 'type' => 'islamic', 'sort_order' => 1,
                'name' => 'رمضان المبارك', 'subtitle' => 'شهر القرآن والعطاء',
                'date' => '2026-03-07', 'end_date' => '2026-04-05',
                'icon' => 'moon', 'color' => '#0F6F5C',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => true, 'is_recurring' => true,
                'hashtags' => ['#رمضان', '#رمضان_كريم', '#رمضان_مبارك', '#شهر_رمضان'],
            ],
            [
                'key' => 'eid_fitr', 'type' => 'islamic', 'sort_order' => 2,
                'name' => 'عيد الفطر المبارك', 'subtitle' => 'عيد الفرحة والتسامح',
                'date' => '2026-04-06', 'end_date' => '2026-04-08',
                'icon' => 'star', 'color' => '#C8965F',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => true, 'is_recurring' => true,
                'hashtags' => ['#عيد_الفطر', '#عيد_مبارك', '#كل_عام_وانتم_بخير'],
            ],
            [
                'key' => 'eid_adha', 'type' => 'islamic', 'sort_order' => 3,
                'name' => 'عيد الأضحى المبارك', 'subtitle' => 'عيد التضحية والوفاء',
                'date' => '2026-06-17', 'end_date' => '2026-06-20',
                'icon' => 'moon', 'color' => '#C8965F',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#عيد_الأضحى', '#عيد_مبارك', '#أضحى_مبارك'],
            ],
            [
                'key' => 'arafat', 'type' => 'islamic', 'sort_order' => 4,
                'name' => 'يوم عرفة', 'subtitle' => 'يوم الدعاء والمغفرة',
                'date' => '2026-06-16', 'end_date' => null,
                'icon' => 'moon', 'color' => '#0A5A4B',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#يوم_عرفة', '#الحج'],
            ],
            [
                'key' => 'laylat_qadr', 'type' => 'islamic', 'sort_order' => 5,
                'name' => 'ليلة القدر', 'subtitle' => 'خير من ألف شهر',
                'date' => '2026-04-01', 'end_date' => null,
                'icon' => 'star', 'color' => '#7C3AED',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#ليلة_القدر', '#رمضان'],
            ],
            [
                'key' => 'mawlid', 'type' => 'islamic', 'sort_order' => 6,
                'name' => 'المولد النبوي الشريف', 'subtitle' => 'ذكرى المولد الكريم',
                'date' => '2026-09-04', 'end_date' => null,
                'icon' => 'star', 'color' => '#0F6F5C',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#المولد_النبوي', '#مولد_النبي'],
            ],
            [
                'key' => 'hijri_new_year', 'type' => 'islamic', 'sort_order' => 7,
                'name' => 'رأس السنة الهجرية', 'subtitle' => 'بداية عام هجري جديد',
                'date' => '2026-07-17', 'end_date' => null,
                'icon' => 'moon', 'color' => '#0F6F5C',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#رأس_السنة_الهجرية', '#عام_هجري_جديد'],
            ],

            // ── National Days ─────────────────────────────────────────────────
            [
                'key' => 'saudi_national_day', 'type' => 'national', 'sort_order' => 10,
                'name' => 'اليوم الوطني السعودي', 'subtitle' => 'ذكرى توحيد المملكة العربية السعودية · ٢٣ سبتمبر',
                'date' => '2026-09-23', 'end_date' => null,
                'icon' => 'crown', 'color' => '#15803D',
                'countries' => ['sa'], 'featured' => true, 'is_recurring' => true,
                'hashtags' => ['#اليوم_الوطني', '#اليوم_الوطني_السعودي', '#٢٣_سبتمبر', '#نحن_هويتنا'],
            ],
            [
                'key' => 'saudi_founding_day', 'type' => 'national', 'sort_order' => 11,
                'name' => 'يوم التأسيس السعودي', 'subtitle' => 'يوم تأسيس الدولة السعودية الأولى · ٢٢ فبراير',
                'date' => '2026-02-22', 'end_date' => null,
                'icon' => 'crown', 'color' => '#0A5A4B',
                'countries' => ['sa'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#يوم_التأسيس', '#يوم_التأسيس_السعودي'],
            ],
            [
                'key' => 'uae_national_day', 'type' => 'national', 'sort_order' => 12,
                'name' => 'اليوم الوطني الإماراتي', 'subtitle' => 'الذكرى ٥٥ لتأسيس الاتحاد · ٢ ديسمبر',
                'date' => '2026-12-02', 'end_date' => null,
                'icon' => 'crown', 'color' => '#EF4444',
                'countries' => ['ae'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#اليوم_الوطني_الإماراتي', '#يوم_الاتحاد', '#عيد_الاتحاد'],
            ],
            [
                'key' => 'kuwait_national_day', 'type' => 'national', 'sort_order' => 13,
                'name' => 'اليوم الوطني الكويتي', 'subtitle' => 'ذكرى الاستقلال الكويتي · ٢٥ فبراير',
                'date' => '2026-02-25', 'end_date' => null,
                'icon' => 'crown', 'color' => '#15803D',
                'countries' => ['kw'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#اليوم_الوطني_الكويتي', '#عيد_الاستقلال_الكويتي'],
            ],
            [
                'key' => 'qatar_national_day', 'type' => 'national', 'sort_order' => 14,
                'name' => 'اليوم الوطني القطري', 'subtitle' => 'يوم الوحدة الوطنية · ١٨ ديسمبر',
                'date' => '2026-12-18', 'end_date' => null,
                'icon' => 'crown', 'color' => '#9B1C1C',
                'countries' => ['qa'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#اليوم_الوطني_القطري', '#قطر'],
            ],
            [
                'key' => 'bahrain_national_day', 'type' => 'national', 'sort_order' => 15,
                'name' => 'اليوم الوطني البحريني', 'subtitle' => 'عيد الاستقلال البحريني · ١٦ ديسمبر',
                'date' => '2026-12-16', 'end_date' => null,
                'icon' => 'crown', 'color' => '#EF4444',
                'countries' => ['bh'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#اليوم_الوطني_البحريني', '#البحرين'],
            ],
            [
                'key' => 'oman_national_day', 'type' => 'national', 'sort_order' => 16,
                'name' => 'اليوم الوطني العُماني', 'subtitle' => 'عيد النهضة العُمانية · ١٨ نوفمبر',
                'date' => '2026-11-18', 'end_date' => null,
                'icon' => 'crown', 'color' => '#15803D',
                'countries' => ['om'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#اليوم_الوطني_العُماني', '#عُمان'],
            ],

            // ── Commercial / Social ──────────────────────────────────────────
            [
                'key' => 'new_year', 'type' => 'commercial', 'sort_order' => 20,
                'name' => 'رأس السنة الميلادية', 'subtitle' => 'بداية عام ٢٠٢٧',
                'date' => '2027-01-01', 'end_date' => null,
                'icon' => 'star', 'color' => '#7C3AED',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#رأس_السنة', '#عام_جديد', '#٢٠٢٧'],
            ],
            [
                'key' => 'valentines', 'type' => 'commercial', 'sort_order' => 21,
                'name' => 'عيد الحب', 'subtitle' => 'يوم الوردة والمشاعر · ١٤ فبراير',
                'date' => '2026-02-14', 'end_date' => null,
                'icon' => 'heart', 'color' => '#EF4444',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#عيد_الحب', '#فالنتاين'],
            ],
            [
                'key' => 'mothers_day', 'type' => 'commercial', 'sort_order' => 22,
                'name' => 'عيد الأم', 'subtitle' => 'يوم تكريم الأمهات · ٢١ مارس',
                'date' => '2026-03-21', 'end_date' => null,
                'icon' => 'heart', 'color' => '#EC4899',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#عيد_الأم', '#أمي_روحي', '#يوم_الأم'],
            ],
            [
                'key' => 'womens_day', 'type' => 'commercial', 'sort_order' => 23,
                'name' => 'يوم المرأة العالمي', 'subtitle' => 'تمكين وإلهام · ٨ مارس',
                'date' => '2026-03-08', 'end_date' => null,
                'icon' => 'star', 'color' => '#7C3AED',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#يوم_المرأة_العالمي', '#تمكين_المرأة'],
            ],
            [
                'key' => 'black_friday', 'type' => 'commercial', 'sort_order' => 24,
                'name' => 'الجمعة البيضاء', 'subtitle' => 'موسم التخفيضات الكبرى',
                'date' => '2026-11-27', 'end_date' => '2026-11-30',
                'icon' => 'flash', 'color' => '#1E293B',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#الجمعة_البيضاء', '#تخفيضات', '#Black_Friday'],
            ],
            [
                'key' => 'back_to_school', 'type' => 'commercial', 'sort_order' => 25,
                'name' => 'العودة إلى المدارس', 'subtitle' => 'موسم بداية العام الدراسي',
                'date' => '2026-08-25', 'end_date' => '2026-09-10',
                'icon' => 'sparkle', 'color' => '#0284C7',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#العودة_للمدارس', '#بداية_الدراسة'],
            ],
            [
                'key' => 'summer_season', 'type' => 'commercial', 'sort_order' => 26,
                'name' => 'موسم الصيف', 'subtitle' => 'حملات الصيف والسياحة',
                'date' => '2026-06-21', 'end_date' => '2026-09-22',
                'icon' => 'flash', 'color' => '#F59E0B',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#موسم_الصيف', '#إجازة_الصيف', '#صيف'],
            ],
            [
                'key' => 'winter_season', 'type' => 'commercial', 'sort_order' => 27,
                'name' => 'موسم الشتاء', 'subtitle' => 'فعاليات وعروض الشتاء الخليجي',
                'date' => '2026-12-21', 'end_date' => '2027-03-20',
                'icon' => 'moon', 'color' => '#0284C7',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#موسم_الشتاء', '#شتاء'],
            ],
            [
                'key' => 'arab_world_day', 'type' => 'commercial', 'sort_order' => 28,
                'name' => 'يوم الوطن العربي', 'subtitle' => 'يوم التضامن العربي · ٢٢ مارس',
                'date' => '2026-03-22', 'end_date' => null,
                'icon' => 'crown', 'color' => '#15803D',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#يوم_الوطن_العربي', '#الوطن_العربي'],
            ],
            [
                'key' => 'volunteer_day', 'type' => 'commercial', 'sort_order' => 29,
                'name' => 'يوم التطوع الدولي', 'subtitle' => 'يوم العطاء والمساهمة · ٥ ديسمبر',
                'date' => '2026-12-05', 'end_date' => null,
                'icon' => 'heart', 'color' => '#0F6F5C',
                'countries' => ['sa', 'ae', 'kw', 'qa', 'bh', 'om'],
                'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#يوم_التطوع', '#تطوع'],
            ],
            [
                'key' => 'bookfair_riyadh', 'type' => 'commercial', 'sort_order' => 30,
                'name' => 'معرض الرياض الدولي للكتاب', 'subtitle' => 'موسم الثقافة والمعرفة',
                'date' => '2026-10-01', 'end_date' => '2026-10-11',
                'icon' => 'sparkle', 'color' => '#7C3AED',
                'countries' => ['sa'], 'featured' => false, 'is_recurring' => true,
                'hashtags' => ['#معرض_الكتاب', '#معرض_الرياض_للكتاب'],
            ],
        ];

        foreach ($occasions as $data) {
            SeasonalOccasion::updateOrCreate(
                ['key' => $data['key']],
                array_merge($data, ['active' => true])
            );
        }
    }
}
