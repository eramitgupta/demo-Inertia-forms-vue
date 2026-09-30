<script setup lang="ts">
import { computed, ref } from 'vue';

/**
 * Star rating for the custom `Rating` field (app/Forms/Fields/Rating.php).
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

const starPath =
    'M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8L12 2.5z';
const stars = computed(() => Number(props.field.stars ?? 5));
const current = computed(() => Number(props.modelValue) || 0);
const hover = ref(0);
const buttons = ref<HTMLButtonElement[]>([]);
const locked = computed(() => props.disabled || Boolean(props.field.readonly));
const shown = computed(() => hover.value || current.value);

function pick(star: number): void {
    emit('update:modelValue', star);
    buttons.value[star - 1]?.focus();
}

function keydown(event: KeyboardEvent): void {
    const moves: Record<string, number> = {
        ArrowRight: 1,
        ArrowUp: 1,
        ArrowLeft: -1,
        ArrowDown: -1,
    };

    if (event.key in moves) {
        event.preventDefault();
        pick(
            Math.min(
                stars.value,
                Math.max(1, current.value + moves[event.key]!),
            ),
        );
    } else if (event.key === 'Home' || event.key === 'End') {
        event.preventDefault();
        pick(event.key === 'Home' ? 1 : stars.value);
    }
}
</script>

<template>
    <div class="flex items-center gap-3">
        <div
            :id="id"
            role="radiogroup"
            :aria-describedby="describedBy"
            :aria-invalid="error ? true : undefined"
            :aria-required="field.required || undefined"
            class="flex gap-0.5 rounded-lg aria-invalid:ring-2 aria-invalid:ring-red-500/30"
            @pointerleave="hover = 0"
        >
            <button
                v-for="star in stars"
                :key="star"
                ref="buttons"
                type="button"
                role="radio"
                :aria-checked="current === star"
                :aria-label="`${star} of ${stars} stars`"
                :tabindex="star === (current || 1) ? 0 : -1"
                :disabled="locked"
                class="rounded-md p-0.5 transition hover:scale-110 focus-visible:ring-2 focus-visible:ring-[var(--erag-form-accent,#4f46e5)] focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                @pointerenter="!locked && (hover = star)"
                @click="pick(star)"
                @keydown="keydown"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                    :class="
                        shown >= star
                            ? 'size-7 fill-amber-400 stroke-amber-500'
                            : 'size-7 fill-transparent stroke-zinc-300 dark:stroke-zinc-600'
                    "
                >
                    <path :d="starPath" />
                </svg>
            </button>
        </div>
        <span class="text-sm text-zinc-500 tabular-nums dark:text-zinc-400">
            {{ shown ? `${shown} / ${stars}` : 'Not rated yet' }}
        </span>
    </div>
</template>
