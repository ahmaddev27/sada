<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Icon from '@/Components/Base/Icon.vue';
import AvatarCropper from '@/Components/Base/AvatarCropper.vue';

const props = defineProps<{
    user: {
        name: string;
        email: string;
        token_balance: number;
        created_at: string;
        avatar_url: string | null;
    };
}>();

type Tab = 'profile' | 'password' | 'account';
const activeTab = ref<Tab>('profile');

const tabs: { key: Tab; label: string; icon: string }[] = [
    { key: 'profile',  label: 'الملف الشخصي', icon: 'user'     },
    { key: 'password', label: 'كلمة المرور',   icon: 'eye'      },
    { key: 'account',  label: 'الحساب',         icon: 'settings' },
];

/* ── Avatar upload ── */
const avatarInput     = ref<HTMLInputElement | null>(null);
const avatarPreview   = ref<string | null>(props.user.avatar_url);
const uploadingAvatar = ref(false);
const cropperFile     = ref<File | null>(null);
const cropperShow     = ref(false);

function pickAvatar() { avatarInput.value?.click(); }

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    // Reset input so same file can be re-selected
    (e.target as HTMLInputElement).value = '';
    cropperFile.value = file;
    cropperShow.value = true;
}

function onCropDone(blob: Blob) {
    cropperShow.value = false;
    // Preview immediately
    avatarPreview.value = URL.createObjectURL(blob);
    // Upload
    uploadingAvatar.value = true;
    const form = new FormData();
    form.append('avatar', blob, 'avatar.jpg');
    router.post('/settings/avatar', form, {
        forceFormData: true,
        onFinish: () => { uploadingAvatar.value = false; },
    });
}

/* ── Profile form ── */
const profileForm = useForm({ name: props.user.name });
const submitProfile = () => profileForm.post('/settings/profile');

/* ── Password form ── */
const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});
const showCurrent = ref(false);
const showNew     = ref(false);
const showConfirm = ref(false);

const submitPassword = () => {
    passwordForm.post('/settings/password', {
        onFinish: () => passwordForm.reset(),
    });
};

/* ── Account tab ── */
const memberSince = computed(() => {
    const d = new Date(props.user.created_at);
    return d.toLocaleDateString('ar-SA', { year: 'numeric', month: 'long', day: 'numeric' });
});

const userInitials = computed(() => {
    return props.user.name.split(' ').map(w => w[0]).join('').slice(0, 2);
});
</script>

<template>
    <AvatarCropper
        :show="cropperShow"
        :file="cropperFile"
        @close="cropperShow = false"
        @crop="onCropDone"
    />

    <AppLayout title="الإعدادات" :crumbs="['الإعدادات']">
        <div dir="rtl" class="settings-page">

            <!-- ── Sidebar ── -->
            <aside class="settings-sidebar">

                <!-- User info -->
                <div class="sidebar-user">
                    <div class="sidebar-avatar">
                        <img v-if="avatarPreview" :src="avatarPreview" class="sidebar-avatar-img" alt="صورة" />
                        <div v-else class="sidebar-avatar-initials">{{ userInitials }}</div>
                    </div>
                    <div class="sidebar-user-name">{{ user.name }}</div>
                    <div class="sidebar-user-email">{{ user.email }}</div>
                </div>

                <!-- Nav tabs (vertical) -->
                <nav class="sidebar-nav">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        class="sidebar-tab"
                        :class="{ 'sidebar-tab--active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        <Icon :name="tab.icon" :size="16" />
                        {{ tab.label }}
                    </button>
                </nav>

                <!-- Token balance pill -->
                <div class="sidebar-tokens">
                    <Icon name="sparkle" :size="14" style="color:var(--sada-500)" />
                    <span>{{ user.token_balance.toLocaleString('ar-SA') }} توكن</span>
                    <a href="/billing" class="sidebar-refill">شحن</a>
                </div>
            </aside>

            <!-- ── Main content ── -->
            <main class="settings-main">

            <!-- ── Tab 1: Profile ── -->
            <div v-if="activeTab === 'profile'" class="settings-card">

                <!-- Avatar section -->
                <div class="avatar-section">
                    <div class="avatar-wrap">
                        <div v-if="avatarPreview" class="avatar-img-wrap">
                            <img :src="avatarPreview" class="avatar-img" alt="صورة الملف الشخصي" />
                        </div>
                        <div v-else class="avatar-initials">{{ userInitials }}</div>
                        <button
                            class="avatar-edit-btn"
                            :class="{ 'avatar-edit-btn--loading': uploadingAvatar }"
                            @click="pickAvatar"
                            :disabled="uploadingAvatar"
                            title="تغيير الصورة"
                        >
                            <Icon v-if="!uploadingAvatar" name="edit" :size="12" />
                            <span v-else class="spinner" />
                        </button>
                    </div>
                    <input
                        ref="avatarInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        style="display:none"
                        @change="onAvatarChange"
                    />
                    <div class="avatar-meta">
                        <div class="avatar-name">{{ user.name }}</div>
                        <div class="avatar-email">{{ user.email }}</div>
                        <button class="avatar-change-link" @click="pickAvatar" :disabled="uploadingAvatar">
                            {{ uploadingAvatar ? 'جارٍ الرفع…' : 'تغيير الصورة الشخصية' }}
                        </button>
                        <div class="avatar-hint">JPG أو PNG أو WebP · حجم أقصى 2MB</div>
                    </div>
                </div>

                <div class="divider-line" />

                <!-- Name field -->
                <form @submit.prevent="submitProfile" class="field-group">
                    <div class="field">
                        <label class="field-label">الاسم الكامل</label>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            class="field-input"
                            :class="{ 'field-input--error': profileForm.errors.name }"
                            placeholder="أحمد العتيبي"
                        />
                        <p v-if="profileForm.errors.name" class="field-error">{{ profileForm.errors.name }}</p>
                    </div>

                    <div class="field">
                        <label class="field-label">البريد الإلكتروني</label>
                        <div class="field-input field-input--readonly" dir="ltr" style="text-align:right">
                            {{ user.email }}
                        </div>
                        <p class="field-hint">لا يمكن تغيير البريد الإلكتروني حالياً.</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save" :disabled="profileForm.processing">
                            {{ profileForm.processing ? 'جارٍ الحفظ…' : 'حفظ التغييرات' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Tab 2: Password ── -->
            <div v-if="activeTab === 'password'" class="settings-card">
                <div class="card-section-title">تغيير كلمة المرور</div>
                <p class="card-section-desc">اختر كلمة مرور قوية لم تستخدمها من قبل.</p>

                <form @submit.prevent="submitPassword" class="field-group">

                    <div class="field">
                        <label class="field-label">كلمة المرور الحالية</label>
                        <div class="input-wrap">
                            <input
                                v-model="passwordForm.current_password"
                                :type="showCurrent ? 'text' : 'password'"
                                class="field-input"
                                :class="{ 'field-input--error': passwordForm.errors.current_password }"
                                dir="ltr"
                                style="text-align:right;padding-left:44px"
                            />
                            <button type="button" class="eye-btn" @click="showCurrent = !showCurrent">
                                <svg v-if="!showCurrent" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="field-error">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <div class="field">
                        <label class="field-label">كلمة المرور الجديدة</label>
                        <div class="input-wrap">
                            <input
                                v-model="passwordForm.password"
                                :type="showNew ? 'text' : 'password'"
                                class="field-input"
                                :class="{ 'field-input--error': passwordForm.errors.password }"
                                dir="ltr"
                                style="text-align:right;padding-left:44px"
                            />
                            <button type="button" class="eye-btn" @click="showNew = !showNew">
                                <svg v-if="!showNew" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.password" class="field-error">{{ passwordForm.errors.password }}</p>
                        <p class="field-hint">8 أحرف على الأقل · حرف كبير وصغير ورقم</p>
                    </div>

                    <div class="field">
                        <label class="field-label">تأكيد كلمة المرور الجديدة</label>
                        <div class="input-wrap">
                            <input
                                v-model="passwordForm.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                class="field-input"
                                dir="ltr"
                                style="text-align:right;padding-left:44px"
                            />
                            <button type="button" class="eye-btn" @click="showConfirm = !showConfirm">
                                <svg v-if="!showConfirm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save" :disabled="passwordForm.processing">
                            {{ passwordForm.processing ? 'جارٍ الحفظ…' : 'تغيير كلمة المرور' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ── Tab 3: Account ── -->
            <div v-if="activeTab === 'account'" class="settings-card">

                <!-- Token balance -->
                <div class="info-card info-card--green">
                    <div class="info-card-icon">
                        <Icon name="sparkle" :size="18" />
                    </div>
                    <div class="info-card-body">
                        <div class="info-card-label">رصيد التوكنز</div>
                        <div class="info-card-value">{{ user.token_balance.toLocaleString('ar-SA') }} <span>توكن</span></div>
                    </div>
                    <a href="/billing" class="btn-save" style="margin:0;white-space:nowrap">شحن المزيد</a>
                </div>

                <!-- Member since -->
                <div class="info-card">
                    <div class="info-card-icon" style="background:var(--info-bg);color:var(--info)">
                        <Icon name="calendar" :size="18" />
                    </div>
                    <div class="info-card-body">
                        <div class="info-card-label">عضو منذ</div>
                        <div class="info-card-value">{{ memberSince }}</div>
                    </div>
                </div>

                <div class="divider-line" style="margin:8px 0" />

                <!-- Danger zone -->
                <div class="danger-zone">
                    <div class="danger-zone-title">منطقة الخطر</div>
                    <div class="danger-zone-desc">هذه الإجراءات لا يمكن التراجع عنها. تأكد تماماً قبل المتابعة.</div>
                    <button class="btn-danger" disabled title="قريباً">
                        <Icon name="trash" :size="14" />
                        حذف الحساب نهائياً
                        <span class="coming-soon-badge">قريباً</span>
                    </button>
                </div>
            </div>

            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Page layout ── */
.settings-page {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

/* ── Sidebar ── */
.settings-sidebar {
    width: 230px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: sticky;
    top: 20px;
}

.sidebar-user {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 20px 16px;
    text-align: center;
    margin-bottom: 8px;
}
.sidebar-avatar {
    width: 64px; height: 64px; border-radius: 50%;
    overflow: hidden; margin: 0 auto 10px;
}
.sidebar-avatar-img { width: 100%; height: 100%; object-fit: cover; }
.sidebar-avatar-initials {
    width: 64px; height: 64px; border-radius: 50%;
    background: var(--sada-500); color: #fff;
    font-size: 22px; font-weight: 700;
    display: grid; place-items: center;
}
.sidebar-user-name { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
.sidebar-user-email { font-size: 12px; color: var(--text-muted); word-break: break-all; }

.sidebar-nav {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex; flex-direction: column;
}
.sidebar-tab {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 16px;
    border: none; background: transparent;
    font-family: var(--font-arabic); font-size: 13px; font-weight: 600;
    color: var(--text-secondary); cursor: pointer;
    text-align: right; transition: background .12s, color .12s;
    border-bottom: 1px solid var(--border-subtle);
}
.sidebar-tab:last-child { border-bottom: none; }
.sidebar-tab:hover { background: var(--bg-muted); color: var(--text-primary); }
.sidebar-tab--active {
    background: color-mix(in oklab, var(--sada-500) 8%, transparent);
    color: var(--sada-500);
    border-right: 3px solid var(--sada-500);
}

.sidebar-tokens {
    display: flex; align-items: center; gap: 6px;
    margin-top: 8px; padding: 10px 14px;
    background: color-mix(in oklab, var(--sada-500) 8%, transparent);
    border: 1px solid color-mix(in oklab, var(--sada-500) 20%, transparent);
    border-radius: var(--radius-md);
    font-size: 12px; font-weight: 600; color: var(--text-secondary);
}
.sidebar-tokens span { flex: 1; }
.sidebar-refill {
    font-size: 11px; font-weight: 700;
    color: var(--sada-500); text-decoration: none;
    padding: 2px 8px; border-radius: 6px;
    background: var(--bg-surface);
    border: 1px solid color-mix(in oklab, var(--sada-500) 30%, transparent);
    transition: background .12s;
}
.sidebar-refill:hover { background: var(--sada-500); color: #fff; }

/* ── Main content ── */
.settings-main { flex: 1; min-width: 0; }

/* Card */
.settings-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 28px;
}

.card-section-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.card-section-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0 0 20px;
}

/* Avatar */
.avatar-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.avatar-wrap {
    position: relative;
    flex-shrink: 0;
}

.avatar-img-wrap {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    overflow: hidden;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: var(--sada-500);
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    display: grid;
    place-items: center;
}

.avatar-edit-btn {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--bg-surface);
    border: 2px solid var(--border-subtle);
    display: grid;
    place-items: center;
    cursor: pointer;
    color: var(--text-primary);
    transition: background 0.15s, border-color 0.15s;
}
.avatar-edit-btn:hover { background: var(--bg-muted); border-color: var(--sada-500); }
.avatar-edit-btn--loading { cursor: wait; }

.spinner {
    width: 10px;
    height: 10px;
    border: 2px solid var(--border-default);
    border-top-color: var(--sada-500);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: block;
}
@keyframes spin { to { transform: rotate(360deg); } }

.avatar-meta { flex: 1; }
.avatar-name  { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
.avatar-email { font-size: 13px; color: var(--text-muted); margin-bottom: 8px; }

.avatar-change-link {
    font-size: 13px;
    font-weight: 600;
    color: var(--sada-600);
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-family: var(--font-arabic);
    display: block;
    margin-bottom: 3px;
    text-align: right;
}
.avatar-change-link:hover { text-decoration: underline; }
.avatar-change-link:disabled { opacity: 0.6; cursor: wait; }

.avatar-hint { font-size: 11px; color: var(--text-muted); }

/* Divider */
.divider-line {
    height: 1px;
    background: var(--border-subtle);
    margin: 20px 0;
}

/* Fields */
.field-group { display: flex; flex-direction: column; gap: 18px; }

.field { display: flex; flex-direction: column; gap: 6px; }

.field-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink-700);
    line-height: 1.4;
}

.field-input {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid var(--ink-200);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-family: var(--font-arabic);
    background: var(--bg-surface);
    color: var(--ink-900);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    box-sizing: border-box;
}
.field-input:focus {
    border-color: var(--sada-500);
    box-shadow: var(--shadow-focus);
}
.field-input--error { border-color: var(--error); }
.field-input--readonly {
    background: var(--bg-muted);
    color: var(--text-muted);
    cursor: not-allowed;
    padding: 11px 14px;
    border-radius: var(--radius-md);
    font-size: 14px;
    border: 1px solid var(--border-subtle);
}

.field-error { font-size: 12px; color: var(--error); margin: 0; }
.field-hint  { font-size: 12px; color: var(--text-muted); margin: 0; }

.input-wrap { position: relative; }
.input-wrap .field-input { padding-left: 44px; }

.eye-btn {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
    display: grid;
    place-items: center;
    border-radius: 4px;
    transition: color 0.15s;
}
.eye-btn:hover { color: var(--text-primary); }

/* Form actions */
.form-actions { display: flex; justify-content: flex-end; margin-top: 4px; }

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: var(--sada-500);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 700;
    font-family: var(--font-arabic);
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
}
.btn-save:hover:not(:disabled) { background: var(--sada-600); }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

/* Info cards (account tab) */
.info-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    background: var(--bg-muted);
    border-radius: var(--radius-md);
    margin-bottom: 12px;
}

.info-card--green .info-card-icon {
    background: var(--sada-50);
    color: var(--sada-500);
}

.info-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.info-card-body { flex: 1; }
.info-card-label { font-size: 12px; color: var(--text-muted); margin-bottom: 2px; }
.info-card-value { font-size: 18px; font-weight: 700; color: var(--text-primary); line-height: 1.2; }
.info-card-value span { font-size: 13px; font-weight: 500; color: var(--text-muted); margin-right: 4px; }

/* Danger zone */
.danger-zone {
    background: color-mix(in oklab, var(--error) 6%, transparent);
    border: 1px solid color-mix(in oklab, var(--error) 20%, transparent);
    border-radius: var(--radius-md);
    padding: 18px 20px;
}
.danger-zone-title { font-size: 14px; font-weight: 700; color: var(--error); margin-bottom: 4px; }
.danger-zone-desc  { font-size: 13px; color: var(--text-muted); margin-bottom: 14px; line-height: 1.5; }

.btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    background: var(--error-bg);
    color: var(--error);
    border: 1px solid color-mix(in oklab, var(--error) 30%, transparent);
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 600;
    font-family: var(--font-arabic);
    cursor: pointer;
    transition: background 0.15s;
}
.btn-danger:disabled { opacity: 0.7; cursor: not-allowed; }

.coming-soon-badge {
    font-size: 10px;
    padding: 2px 6px;
    background: var(--bg-muted);
    border-radius: 4px;
    color: var(--text-muted);
    margin-right: 4px;
}

/* ── Mobile ── */
@media (max-width: 768px) {
    .settings-page {
        flex-direction: column;
    }
    .settings-sidebar {
        width: 100%;
        position: static;
    }
    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 14px;
        text-align: right;
    }
    .sidebar-avatar { margin: 0; }
    .sidebar-user-name { font-size: 13px; }
    .sidebar-user-email { font-size: 11px; }
    .sidebar-nav {
        flex-direction: row;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .sidebar-nav::-webkit-scrollbar { display: none; }
    .sidebar-tab {
        flex-direction: column;
        gap: 4px;
        padding: 10px 14px;
        font-size: 11px;
        white-space: nowrap;
        border-bottom: none;
        border-left: 1px solid var(--border-subtle);
        flex-shrink: 0;
    }
    .sidebar-tab:last-child { border-left: none; }
    .sidebar-tab--active {
        border-right: none;
        border-bottom: 3px solid var(--sada-500);
    }
    .sidebar-tokens { display: none; }
}
</style>
