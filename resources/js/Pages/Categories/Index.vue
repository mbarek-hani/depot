<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '../../Components/AppLayout.vue';
import PageHeader from '../../Components/PageHeader.vue';
import UiButton from '../../Components/UiButton.vue';
import Icon from '../../Components/Icon.vue';
import Field from '../../Components/Field.vue';
import Modal from '../../Components/Modal.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    colors: { type: Array, default: () => [] },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin === true);

const inputClass = 'h-11 w-full rounded-xl border border-stone-300 bg-white px-3.5 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';
const textareaClass = 'min-h-20 w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-sm text-stone-900 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-400/20 dark:border-night-800 dark:bg-night-950 dark:text-white';

const form = useForm({
    name: '',
    color: 'brand',
    description: '',
});

const editForm = useForm({
    name: '',
    color: 'brand',
    description: '',
});

const editing = ref(null);
const deleting = ref(null);
const deletingNow = ref(false);

function add() {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function openEdit(category) {
    editing.value = category;
    editForm.name = category.name;
    editForm.color = category.color;
    editForm.description = category.description ?? '';
    editForm.clearErrors();
}

function update() {
    if (!editing.value) {
        return;
    }

    editForm.patch(route('categories.update', editing.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
        },
    });
}

function destroy() {
    if (!deleting.value) {
        return;
    }

    deletingNow.value = true;
    router.delete(route('categories.destroy', deleting.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deletingNow.value = false;
            deleting.value = null;
        },
    });
}
</script>

<template>
    <AppLayout title="الأصناف">
        <PageHeader title="الأصناف" description="نظّم المنتجات ضمن أصناف واضحة لتسهيل البحث والتقارير." eyebrow="الإدارة" />

        <div class="mt-6 grid gap-4 xl:grid-cols-3">
            <div v-if="isAdmin" class="rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card xl:col-span-1 dark:border-night-800 dark:bg-night-900">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-100 text-brand-800 dark:bg-brand-400/15 dark:text-brand-300"><Icon name="tag" :size="20" /></span>
                    <div>
                        <h2 class="font-display text-base font-extrabold text-stone-900 dark:text-white">إضافة صنف</h2>
                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">أنشئ صنفًا جديدًا للمنتجات.</p>
                    </div>
                </div>

                <form class="space-y-4" @submit.prevent="add">
                    <Field label="اسم الصنف" :error="form.errors.name" required>
                        <input v-model="form.name" :class="inputClass" placeholder="مثال: معلبات">
                    </Field>

                    <Field label="لون الصنف" :error="form.errors.color" required>
                        <select v-model="form.color" :class="inputClass">
                            <option v-for="color in colors" :key="color.value" :value="color.value">{{ color.label }}</option>
                        </select>
                    </Field>

                    <Field label="الوصف" :error="form.errors.description">
                        <textarea v-model="form.description" :class="textareaClass" rows="3" placeholder="وصف مختصر للصنف..."></textarea>
                    </Field>

                    <UiButton type="submit" icon="plus" class="w-full" :disabled="form.processing">إضافة الصنف</UiButton>
                </form>
            </div>

            <div class="space-y-3" :class="isAdmin ? 'xl:col-span-2' : 'xl:col-span-3'">
                <p class="text-xs font-semibold text-stone-500 dark:text-stone-400">{{ categories.length }} صنف</p>

                <EmptyState v-if="!categories.length" icon="tag" title="لا توجد أصناف" description="أضف أول صنف من النموذج المجاور." class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900" />

                <div v-else class="space-y-3">
                    <article v-for="category in categories" :key="category.id" class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-1 size-3 shrink-0 rounded-full" :class="category.color_class"></span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-stone-900 dark:text-white">{{ category.name }}</h3>
                                    <p class="mt-0.5 line-clamp-2 text-[11px] leading-5 text-stone-500 dark:text-stone-400">{{ category.description || 'بدون وصف' }}</p>
                                </div>
                            </div>
                            <div v-if="isAdmin" class="flex shrink-0 items-center gap-1">
                                <UiButton variant="ghost" size="icon" icon="pencil" aria-label="تعديل الصنف" @click="openEdit(category)" />
                                <button v-if="category.all_products_count === 0" type="button" class="inline-flex size-10 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف الصنف" @click="deleting = category">
                                    <Icon name="trash" :size="18" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-2 rounded-xl bg-stone-50 p-3 dark:bg-night-950">
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">المنتجات</p>
                                <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ category.products_count }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">الكميات</p>
                                <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ category.quantity_total_formatted }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-stone-400">الكل</p>
                                <p class="mt-0.5 text-xs font-extrabold text-stone-900 tabular dark:text-white">{{ category.all_products_count }}</p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <Modal :open="Boolean(editing)" title="تعديل الصنف" size="sm" @close="editing = null">
            <form class="space-y-4" @submit.prevent="update">
                <Field label="اسم الصنف" :error="editForm.errors.name" required>
                    <input v-model="editForm.name" :class="inputClass">
                </Field>
                <Field label="لون الصنف" :error="editForm.errors.color" required>
                    <select v-model="editForm.color" :class="inputClass">
                        <option v-for="color in colors" :key="color.value" :value="color.value">{{ color.label }}</option>
                    </select>
                </Field>
                <Field label="الوصف" :error="editForm.errors.description">
                    <textarea v-model="editForm.description" :class="textareaClass" rows="3"></textarea>
                </Field>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <UiButton variant="outline" @click="editing = null">إلغاء</UiButton>
                    <UiButton type="submit" icon="check" :disabled="editForm.processing">حفظ التعديلات</UiButton>
                </div>
            </form>
        </Modal>

        <Modal :open="Boolean(deleting)" title="تأكيد حذف الصنف" size="sm" @close="deleting = null">
            <div v-if="deleting" class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                <Icon name="warning" :size="20" class="mt-0.5 shrink-0" />
                <p>هل أنت متأكد من حذف صنف «{{ deleting.name }}»؟ لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <template #footer>
                <UiButton variant="outline" @click="deleting = null">إلغاء</UiButton>
                <UiButton variant="danger" icon="trash" :disabled="deletingNow" @click="destroy">حذف الصنف</UiButton>
            </template>
        </Modal>
    </AppLayout>
</template>
