<script setup lang="ts">
import { Form, type FormSchema } from '@erag/inertia-forms-vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import CodeInput from '@/components/form-fields/CodeInput.vue';
import QuantityStepper from '@/components/form-fields/QuantityStepper.vue';
import Rating from '@/components/form-fields/Rating.vue';
import FormsDemoPanel from '@/components/FormsDemoPanel.vue';
import FormsDemoPicker, { demoAccent } from '@/components/FormsDemoPicker.vue';
import { dashboard } from '@/routes';
import { index, show } from '@/routes/forms-demo';
import type { FormEntrySummary, FormsDemoPageProps } from '@/types';

const props = defineProps<
    FormsDemoPageProps & {
        form: FormSchema;
        entry: (FormEntrySummary & { data: Record<string, unknown> }) | null;
    }
>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Inertia Forms demo', href: index() },
        ],
    },
});

/**
 * Components for the custom fields in app/Forms/Fields, keyed by `component()`.
 */
const customFields = { Rating, CodeInput, QuantityStepper };

const title = computed(() =>
    props.entry
        ? `Edit ${props.label.toLowerCase()} entry`
        : `New ${props.label.toLowerCase()} entry`,
);
</script>

<template>
    <Head :title="title" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <FormsDemoPicker :demo="demo" :demos="demos" />

        <FormsDemoPanel
            :title="title"
            :class-name="className"
            :back-href="entry ? show([demo, entry.id]) : index(demo)"
            :data="entry?.data ?? null"
        >
            <Form
                :key="`${demo}-${entry?.id ?? 'new'}`"
                :form="form"
                :accent="demoAccent"
                :components="customFields"
            />
        </FormsDemoPanel>
    </div>
</template>
