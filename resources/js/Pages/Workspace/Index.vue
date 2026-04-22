<script setup lang="ts">
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/Base/Card.vue';
import Button from '@/Components/Base/Button.vue';
import Badge from '@/Components/Base/Badge.vue';
import Modal from '@/Components/Base/Modal.vue';
import Input from '@/Components/Base/Input.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    workspaces: Array<{
        id: number;
        name: string;
        business_type: string | null;
        countries: string[] | null;
        default_dialect: string;
        logo_path: string | null;
    }>;
}>();

const showCreate = ref(false);

const form = useForm({
    name:             '',
    business_type:    '',
    countries:        ['sa'] as string[],
    default_dialect:  'sa',
});

const dialects: Record<string, string> = {
    fos: 'الفصحى',
    sa:  '🇸🇦 السعودية',
    ae:  '🇦🇪 الإمارات',
    kw:  '🇰🇼 الكويت',
    qa:  '🇶🇦 قطر',
    bh:  '🇧🇭 البحرين',
    om:  '🇴🇲 عُمان',
};

const countries = [
    { code: 'sa', label: '🇸🇦 السعودية' },
    { code: 'ae', label: '🇦🇪 الإمارات' },
    { code: 'kw', label: '🇰🇼 الكويت' },
    { code: 'qa', label: '🇶🇦 قطر' },
    { code: 'bh', label: '🇧🇭 البحرين' },
    { code: 'om', label: '🇴🇲 عُمان' },
];

function toggleCountry(code: string) {
    const idx = form.countries.indexOf(code);
    if (idx === -1) {
        form.countries.push(code);
    } else if (form.countries.length > 1) {
        form.countries.splice(idx, 1);
    }
}

function submit() {
    form.post('/workspaces', {
        onSuccess: () => { showCreate.value = false; form.reset(); },
    });
}

function switchWorkspace(id: number) {
    router.post(`/workspaces/${id}/switch`);
}

function archiveWorkspace(id: number, name: string) {
    if (confirm(`هل تريد أرشفة "${name}"؟ يمكن استرداده خلال ٣٠ يوماً.`)) {
        router.post(`/workspaces/${id}/archive`);
    }
}
</script>

<template>
    <AppLayout title="مساحات العمل">
        <div style="max-width: 900px; margin: 0 auto;">

            <!-- Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                <div>
                    <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: var(--color-ink-base);">مساحات العمل</h1>
                    <p style="margin: 4px 0 0; font-size: 13px; color: var(--color-ink-muted);">أدر مساحات عملك ومشاريعك التسويقية</p>
                </div>
                <Button @click="showCreate = true">+ مساحة جديدة</Button>
            </div>

            <!-- Empty state -->
            <div
                v-if="workspaces.length === 0"
                style="text-align: center; padding: 60px 20px; color: var(--color-ink-muted);"
            >
                <div style="font-size: 40px; margin-bottom: 12px;">🏢</div>
                <p style="font-size: 15px; font-weight: 600; color: var(--color-ink-base);">لا توجد مساحات عمل بعد</p>
                <p style="font-size: 13px; margin: 8px 0 20px;">أنشئ مساحتك الأولى للبدء بإدارة تسويقك</p>
                <Button @click="showCreate = true">إنشاء مساحة عمل</Button>
            </div>

            <!-- Workspace cards -->
            <div v-else style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                <Card
                    v-for="ws in workspaces"
                    :key="ws.id"
                    hoverable
                    padding="20px"
                >
                    <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;">
                        <!-- Logo / initial -->
                        <div style="
                            width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
                            background: linear-gradient(135deg, var(--color-sada-500), var(--color-sada-600));
                            color: #fff; font-weight: 700; font-size: 18px;
                            display: grid; place-items: center;
                            overflow: hidden;
                        ">
                            <img v-if="ws.logo_path" :src="`/storage/${ws.logo_path}`" style="width: 100%; height: 100%; object-fit: cover;" />
                            <span v-else>{{ ws.name.charAt(0) }}</span>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--color-ink-base); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ ws.name }}
                            </h3>
                            <p style="margin: 2px 0 0; font-size: 12px; color: var(--color-ink-muted);">{{ ws.business_type ?? 'غير محدد' }}</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <Badge variant="primary">{{ dialects[ws.default_dialect] ?? ws.default_dialect }}</Badge>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <Button
                            variant="primary"
                            size="sm"
                            style="flex: 1;"
                            @click="switchWorkspace(ws.id)"
                        >تفعيل</Button>
                        <a
                            :href="`/workspaces/${ws.id}/settings`"
                            style="
                                padding: 6px 12px; font-size: 12px; font-weight: 600;
                                border: 1px solid var(--color-border-default);
                                border-radius: 6px; color: var(--color-ink-muted);
                                text-decoration: none; display: inline-flex; align-items: center;
                            "
                        >إعدادات</a>
                        <button
                            style="
                                padding: 6px; border: none; background: transparent;
                                color: var(--color-ink-muted); cursor: pointer; border-radius: 6px;
                            "
                            title="أرشفة"
                            @click="archiveWorkspace(ws.id, ws.name)"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                        </button>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Create modal -->
        <Modal :show="showCreate" title="إنشاء مساحة عمل جديدة" size="md" @close="showCreate = false">
            <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 16px;">
                <Input
                    v-model="form.name"
                    label="اسم المساحة"
                    placeholder="مثلاً: متجر أنيق"
                    :error="form.errors.name"
                />
                <Input
                    v-model="form.business_type"
                    label="نوع النشاط (اختياري)"
                    placeholder="مثلاً: تجارة إلكترونية"
                />

                <!-- Countries -->
                <div>
                    <p style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); margin: 0 0 8px;">الدول المستهدفة</p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button
                            v-for="c in countries"
                            :key="c.code"
                            type="button"
                            :style="`
                                padding: 5px 12px; border-radius: 99px; font-size: 12px; font-weight: 600;
                                cursor: pointer; border: 1px solid;
                                border-color: ${form.countries.includes(c.code) ? 'var(--color-sada-500)' : 'var(--color-border-default)'};
                                background: ${form.countries.includes(c.code) ? 'color-mix(in oklab, var(--color-sada-500) 12%, transparent)' : 'transparent'};
                                color: ${form.countries.includes(c.code) ? 'var(--color-sada-600)' : 'var(--color-ink-muted)'};
                            `"
                            @click="toggleCountry(c.code)"
                        >{{ c.label }}</button>
                    </div>
                </div>

                <!-- Dialect -->
                <div>
                    <label style="font-size: 13px; font-weight: 500; color: var(--color-ink-base); display: block; margin-bottom: 4px;">اللهجة الافتراضية</label>
                    <select
                        v-model="form.default_dialect"
                        style="
                            width: 100%; padding: 9px 12px; font-size: 14px;
                            border-radius: 8px; border: 1px solid var(--color-border-default);
                            background: var(--color-bg-input); color: var(--color-ink-base);
                            font-family: var(--font-arabic); outline: none;
                        "
                    >
                        <option v-for="(label, code) in dialects" :key="code" :value="code">{{ label }}</option>
                    </select>
                </div>
            </form>

            <template #footer>
                <Button :loading="form.processing" @click="submit">إنشاء المساحة</Button>
                <Button variant="ghost" @click="showCreate = false">إلغاء</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
