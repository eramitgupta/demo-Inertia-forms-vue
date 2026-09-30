<script setup lang="ts">
import { computed } from 'vue';

/**
 * Minus / plus counter for the custom `QuantityStepper` field (app/Forms/Fields/QuantityStepper.php).
 */
const props = defineProps<{
    field: Record<string, any>;
    id: string;
    modelValue: unknown;
    error?: string;
    disabled: boolean;
    describedBy?: string;
}>();
const emit = defineEmits<{ 'update:modelValue': [value: unknown] }>();

const min = computed(() => Number(props.field.min ?? 0));
const max = computed(() => Number(props.field.max ?? 99));
const step = computed(() => Number(props.field.step ?? 1));
const unit = computed(() =>
    typeof props.field.unit === 'string' ? props.field.unit : null,
);
const locked = computed(() => props.disabled || Boolean(props.field.readonly));
const number = computed(() =>
    props.modelValue === '' ||
    props.modelValue === null ||
    props.modelValue === undefined
        ? null
        : Number(props.modelValue),
);
const buttonClass =
    'flex w-10 items-center justify-center text-lg font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 focus-visible:bg-zinc-100 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40 dark:text-zinc-300 dark:hover:bg-zinc-800';

function clamp(next: number): number {
    return Math.min(max.value, Math.max(min.value, next));
}

function input(event: Event): void {
    const value = (event.target as HTMLInputElement).value;
    emit('update:modelValue', value === '' ? null : Number(value));
}
</script>

<template>
    <div class="flex items-center gap-3">
        <div
            :aria-invalid="error ? true : undefined"
            class="inline-flex h-10 items-stretch overflow-hidden rounded-lg border border-zinc-300 bg-white shadow-xs focus-within:border-[var(--erag-form-accent,#4f46e5)] focus-within:ring-2 focus-within:ring-[color-mix(in_oklab,var(--erag-form-accent,#4f46e5)_22%,transparent)] aria-invalid:border-red-500 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <button
                type="button"
                :aria-label="`Decrease ${field.label}`"
                :disabled="locked || (number ?? min) <= min"
                :class="buttonClass"
                @click="
                    emit('update:modelValue', clamp((number ?? min) - step))
                "
            >
                −
            </button>
            <input
                :id="id"
                type="number"
                inputmode="numeric"
                :min="min"
                :max="max"
                :step="step"
                :value="number ?? ''"
                :disabled="locked"
                :aria-describedby="describedBy"
                :aria-invalid="error ? true : undefined"
                class="w-14 [appearance:textfield] border-x border-zinc-200 bg-transparent text-center text-sm font-semibold text-zinc-900 tabular-nums focus:outline-none dark:border-zinc-700 dark:text-zinc-100 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                @input="input"
                @blur="
                    number !== null && emit('update:modelValue', clamp(number))
                "
            />
            <button
                type="button"
                :aria-label="`Increase ${field.label}`"
                :disabled="locked || (number ?? min) >= max"
                :class="buttonClass"
                @click="
                    emit('update:modelValue', clamp((number ?? min) + step))
                "
            >
                +
            </button>
        </div>
        <span v-if="unit" class="text-sm text-zinc-500 dark:text-zinc-400">{{
            unit
        }}</span>
    </div>
</template>
