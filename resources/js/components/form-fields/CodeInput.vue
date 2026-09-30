<script setup lang="ts">
import { computed, ref, watch } from 'vue';

/**
 * One box per digit for the custom `CodeInput` field (app/Forms/Fields/CodeInput.php).
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

const length = computed(() => Number(props.field.length ?? 6));
const code = computed(() =>
    typeof props.modelValue === 'string'
        ? props.modelValue.replace(/\D/g, '')
        : '',
);
const boxes = ref<HTMLInputElement[]>([]);
// The code just typed, before the new value comes back as a prop.
let latest = code.value;
watch(code, (next) => {
    latest = next;
});

function emitCode(next: string): void {
    latest = next;
    emit('update:modelValue', next);
}
const locked = computed(() => props.disabled || Boolean(props.field.readonly));

function onFocus(index: number): void {
    if (index > latest.length) focusBox(latest.length);
}

function focusBox(index: number): void {
    const box = boxes.value[Math.min(Math.max(index, 0), length.value - 1)];
    box?.focus();
    box?.select();
}

function type(index: number, event: Event): void {
    const input = event.target as HTMLInputElement;
    const digit = input.value.replace(/\D/g, '').slice(-1);
    input.value = code.value[index] ?? '';

    if (!digit) {
        return;
    }

    const position = Math.min(index, code.value.length);
    emitCode(
        (
            code.value.slice(0, position) +
            digit +
            code.value.slice(position + 1)
        ).slice(0, length.value),
    );
    focusBox(position + 1);
}

function keydown(index: number, event: KeyboardEvent): void {
    if (event.key === 'Backspace') {
        event.preventDefault();
        const position = code.value[index] ? index : index - 1;

        if (position >= 0) {
            emitCode(
                code.value.slice(0, position) + code.value.slice(position + 1),
            );
            focusBox(position);
        }
    } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        focusBox(index - 1);
    } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        focusBox(Math.min(index + 1, code.value.length));
    }
}

function paste(event: ClipboardEvent): void {
    event.preventDefault();
    const pasted = (event.clipboardData?.getData('text') ?? '')
        .replace(/\D/g, '')
        .slice(0, length.value);

    if (pasted) {
        emitCode(pasted);
        focusBox(pasted.length);
    }
}
</script>

<template>
    <div
        role="group"
        :aria-describedby="describedBy"
        class="flex flex-wrap gap-2"
    >
        <input
            v-for="(_, index) in length"
            :id="index === 0 ? id : `${id}-${index}`"
            :key="index"
            ref="boxes"
            type="text"
            inputmode="numeric"
            :autocomplete="index === 0 ? 'one-time-code' : 'off'"
            :aria-label="`Digit ${index + 1} of ${length}`"
            :aria-invalid="error ? true : undefined"
            :value="code[index] ?? ''"
            :disabled="locked"
            class="size-11 rounded-lg border border-zinc-300 bg-white text-center text-lg font-semibold text-zinc-900 tabular-nums shadow-xs transition focus:border-[var(--erag-form-accent,#4f46e5)] focus:ring-2 focus:ring-[color-mix(in_oklab,var(--erag-form-accent,#4f46e5)_22%,transparent)] focus:outline-none disabled:cursor-not-allowed disabled:bg-zinc-50 aria-invalid:border-red-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
            @focus="onFocus(index)"
            @input="type(index, $event)"
            @keydown="keydown(index, $event)"
            @paste="paste"
        />
    </div>
</template>
