<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Paperclip, Pencil, Trash2 } from '@lucide/vue';
import FormsDemoDeleteDialog from '@/components/FormsDemoDeleteDialog.vue';
import FormsDemoPicker, { demoAccent } from '@/components/FormsDemoPicker.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDate, formatFileSize } from '@/lib/formsDemo';
import { dashboard } from '@/routes';
import { edit, index } from '@/routes/forms-demo';
import type {
    FormEntrySection,
    FormEntrySummary,
    FormsDemoPageProps,
} from '@/types';

defineProps<
    FormsDemoPageProps & {
        entry: FormEntrySummary;
        sections: FormEntrySection[];
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
</script>

<template>
    <Head :title="entry.title" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        :style="{ '--demo-accent': demoAccent }"
    >
        <FormsDemoPicker :demo="demo" :demos="demos" />

        <Card>
            <CardHeader
                class="flex flex-row flex-wrap items-start justify-between gap-3"
            >
                <div class="grid gap-1.5">
                    <CardTitle>{{ entry.title }}</CardTitle>
                    <CardDescription>
                        {{ label }} entry #{{ entry.id }} · created
                        {{ formatDate(entry.createdAt) }} · updated
                        {{ formatDate(entry.updatedAt) }}
                    </CardDescription>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="index(demo)">
                            <ArrowLeft />
                            All entries
                        </Link>
                    </Button>
                    <Button
                        size="sm"
                        as-child
                        class="bg-(--demo-accent) text-white hover:bg-(--demo-accent)/90"
                    >
                        <Link :href="edit([demo, entry.id])">
                            <Pencil />
                            Edit
                        </Link>
                    </Button>
                    <FormsDemoDeleteDialog :demo="demo" :entry="entry">
                        <Button variant="destructive" size="sm">
                            <Trash2 />
                            Delete
                        </Button>
                    </FormsDemoDeleteDialog>
                </div>
            </CardHeader>

            <CardContent class="grid gap-6">
                <section
                    v-for="(section, sectionIndex) in sections"
                    :key="sectionIndex"
                    class="grid gap-3"
                >
                    <h3
                        v-if="section.title"
                        class="border-b pb-2 text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                    >
                        {{ section.title }}
                    </h3>
                    <dl
                        class="grid gap-x-6 gap-y-3 sm:grid-cols-[12rem_minmax(0,1fr)]"
                    >
                        <div
                            v-for="(row, rowIndex) in section.rows"
                            :key="rowIndex"
                            class="contents"
                        >
                            <dt class="text-sm text-muted-foreground">
                                {{ row.label }}
                            </dt>
                            <dd class="text-sm">
                                <span
                                    v-if="row.type === 'empty'"
                                    class="text-muted-foreground"
                                    >—</span
                                >
                                <pre
                                    v-else-if="row.type === 'json'"
                                    class="max-h-80 overflow-auto rounded-md bg-muted p-3 text-xs"
                                    >{{ row.value }}</pre>
                                <ul
                                    v-else-if="row.type === 'files'"
                                    class="grid gap-1.5"
                                >
                                    <li
                                        v-for="file in row.files"
                                        :key="file.url"
                                    >
                                        <a
                                            :href="file.url"
                                            target="_blank"
                                            rel="noreferrer"
                                            class="inline-flex items-center gap-1.5 hover:underline"
                                        >
                                            <Paperclip
                                                class="size-3.5 text-muted-foreground"
                                            />
                                            {{ file.name }}
                                            <span
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ formatFileSize(file.size) }}
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                                <span
                                    v-else
                                    class="break-words whitespace-pre-wrap"
                                    >{{ row.value }}</span
                                >
                            </dd>
                        </div>
                    </dl>
                </section>
            </CardContent>
        </Card>
    </div>
</template>
