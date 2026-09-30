<script lang="ts">
import { ref } from 'vue';

/**
 * Accent colors offered by the demo picker.
 */
export const accents = [
    { name: 'Indigo', value: '#4f46e5' },
    { name: 'Sky', value: '#0284c7' },
    { name: 'Emerald', value: '#059669' },
    { name: 'Amber', value: '#d97706' },
    { name: 'Rose', value: '#e11d48' },
    { name: 'Violet', value: '#7c3aed' },
];

/**
 * The accent picked in the demo, kept while moving between the demo pages.
 */
export const demoAccent = ref(accents[0].value);
</script>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    CalendarDays,
    CreditCard,
    House,
    LayoutList,
    LayoutTemplate,
    LifeBuoy,
    Megaphone,
    MessagesSquare,
    Newspaper,
    Palette,
    Puzzle,
    Rocket,
    Shapes,
    Stethoscope,
    Target,
    Users,
} from '@lucide/vue';
import type { Component } from 'vue';
import { index } from '@/routes/forms-demo';

/**
 * Icon shown next to each demo in the picker.
 */
const demoIcons: Record<string, Component> = {
    'all-fields': LayoutList,
    'custom-fields': Puzzle,
    'onboarding-wizard': Rocket,
    'landing-page': LayoutTemplate,
    'support-chat': MessagesSquare,
    'product-launch': Megaphone,
    'project-kickoff': Briefcase,
    'support-triage': LifeBuoy,
    'event-session': CalendarDays,
    'campaign-plan': Target,
    'hiring-pipeline': Users,
    'subscription-billing': CreditCard,
    'clinic-intake': Stethoscope,
    'property-booking': House,
    'editorial-calendar': Newspaper,
};

defineProps<{
    demo: string;
    demos: Record<string, string>;
}>();
</script>

<template>
    <section
        class="flex flex-col gap-4 rounded-xl border bg-card p-4"
        :style="{ '--demo-accent': demoAccent }"
    >
        <div class="flex flex-col gap-3">
            <h2
                id="form-class-heading"
                class="flex items-center gap-1.5 text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
            >
                <Shapes class="size-3.5" />
                Form class
            </h2>
            <nav
                class="flex flex-wrap gap-2"
                aria-labelledby="form-class-heading"
            >
                <Link
                    v-for="(label, slug) in demos"
                    :key="slug"
                    :href="index(slug)"
                    preserve-scroll
                    :aria-current="slug === demo ? 'page' : undefined"
                    :class="[
                        'inline-flex items-center gap-2 rounded-xl border bg-background px-4 py-2 text-sm font-medium text-muted-foreground shadow-xs transition hover:border-foreground/20 hover:text-foreground',
                        slug === demo &&
                            'border-[color-mix(in_oklab,var(--demo-accent)_45%,transparent)] bg-[color-mix(in_oklab,var(--demo-accent)_8%,transparent)] text-[var(--demo-accent)] hover:border-[color-mix(in_oklab,var(--demo-accent)_45%,transparent)] hover:text-[var(--demo-accent)]',
                    ]"
                >
                    <component :is="demoIcons[slug] ?? Shapes" class="size-4" />
                    {{ label }}
                </Link>
            </nav>
        </div>
        <div
            class="flex flex-wrap items-center gap-2"
            role="group"
            aria-label="Accent color"
        >
            <span
                class="flex items-center gap-1.5 text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
            >
                <Palette class="size-3.5" />
                Accent
            </span>
            <button
                v-for="color in accents"
                :key="color.value"
                type="button"
                :aria-label="color.name"
                :aria-pressed="demoAccent === color.value"
                class="size-6 rounded-full ring-offset-2 ring-offset-background transition hover:scale-110 aria-pressed:ring-2"
                :style="{
                    backgroundColor: color.value,
                    '--tw-ring-color': color.value,
                }"
                @click="demoAccent = color.value"
            />
            <label
                class="relative size-6 cursor-pointer overflow-hidden rounded-full border border-dashed border-muted-foreground"
                title="Custom color"
            >
                <span class="sr-only">Custom accent color</span>
                <input
                    v-model="demoAccent"
                    type="color"
                    class="absolute inset-0 size-full cursor-pointer opacity-0"
                />
            </label>
        </div>
    </section>
</template>
