<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Eye,
    Inbox,
    Pencil,
    Plus,
    Search,
    Trash2,
} from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
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
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/formsDemo';
import { dashboard } from '@/routes';
import { create, edit, index, show } from '@/routes/forms-demo';
import type { FormEntrySummary, FormsDemoPageProps, Paginated } from '@/types';

const props = defineProps<
    FormsDemoPageProps & {
        search: string;
        entries: Paginated<FormEntrySummary>;
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

const query = ref(props.search);
let timer: ReturnType<typeof setTimeout> | undefined;

watch(query, (value) => {
    clearTimeout(timer);

    if (value.trim() === props.search) {
        return;
    }

    timer = setTimeout(() => {
        router.get(
            index(props.demo, {
                query: { search: value.trim() || undefined },
            }),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <Head :title="`${label} entries`" />

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
                    <CardTitle>{{ label }} entries</CardTitle>
                    <CardDescription>
                        Saved with
                        <code>app/Forms/{{ className }}.php</code> and
                        <code>FormEntryService</code>.
                    </CardDescription>
                </div>
                <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                    <div class="relative flex-1 sm:w-64 sm:flex-none">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="query"
                            type="search"
                            placeholder="Search entries…"
                            aria-label="Search entries"
                            class="pl-8"
                        />
                    </div>
                    <Button
                        as-child
                        class="bg-(--demo-accent) text-white hover:bg-(--demo-accent)/90"
                    >
                        <Link :href="create(demo)">
                            <Plus />
                            New entry
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    v-if="entries.data.length === 0"
                    class="flex flex-col items-center gap-3 rounded-lg border border-dashed px-4 py-12 text-center"
                >
                    <Inbox class="size-8 text-muted-foreground" />
                    <p class="text-sm text-muted-foreground">
                        {{
                            search
                                ? `No entries match “${search}”.`
                                : 'No entries yet. Fill in the form to save the first one.'
                        }}
                    </p>
                    <Button v-if="!search" variant="outline" size="sm" as-child>
                        <Link :href="create(demo)">
                            <Plus />
                            Create entry
                        </Link>
                    </Button>
                </div>

                <div v-else class="overflow-x-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead
                            class="bg-muted/50 text-left text-xs text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-4 py-2.5 font-medium">Title</th>
                                <th
                                    class="hidden px-4 py-2.5 font-medium md:table-cell"
                                >
                                    Created
                                </th>
                                <th
                                    class="hidden px-4 py-2.5 font-medium sm:table-cell"
                                >
                                    Updated
                                </th>
                                <th class="px-4 py-2.5 text-right font-medium">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="entry in entries.data"
                                :key="entry.id"
                                class="transition hover:bg-muted/40"
                            >
                                <td class="px-4 py-3">
                                    <Link
                                        :href="show([demo, entry.id])"
                                        class="font-medium hover:underline"
                                    >
                                        {{ entry.title }}
                                    </Link>
                                    <span
                                        class="ml-2 text-xs text-muted-foreground"
                                    >
                                        #{{ entry.id }}
                                    </span>
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-muted-foreground md:table-cell"
                                >
                                    {{ formatDate(entry.createdAt) }}
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-muted-foreground sm:table-cell"
                                >
                                    {{ formatDate(entry.updatedAt) }}
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex justify-end gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                        >
                                            <Link
                                                :href="show([demo, entry.id])"
                                                :aria-label="`View ${entry.title}`"
                                            >
                                                <Eye />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                        >
                                            <Link
                                                :href="edit([demo, entry.id])"
                                                :aria-label="`Edit ${entry.title}`"
                                            >
                                                <Pencil />
                                            </Link>
                                        </Button>
                                        <FormsDemoDeleteDialog
                                            :demo="demo"
                                            :entry="entry"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="text-destructive hover:text-destructive"
                                                :aria-label="`Delete ${entry.title}`"
                                            >
                                                <Trash2 />
                                            </Button>
                                        </FormsDemoDeleteDialog>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="entries.total > 0"
                    class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground"
                >
                    <p>
                        Showing {{ entries.from }}–{{ entries.to }} of
                        {{ entries.total }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="!entries.prev_page_url"
                            variant="outline"
                            size="icon"
                            disabled
                            aria-label="Previous"
                        >
                            <ChevronLeft />
                        </Button>
                        <Button v-else variant="outline" size="icon" as-child>
                            <Link
                                :href="entries.prev_page_url"
                                preserve-scroll
                                preserve-state
                                aria-label="Previous"
                            >
                                <ChevronLeft />
                            </Link>
                        </Button>
                        <span>
                            Page {{ entries.current_page }} of
                            {{ entries.last_page }}
                        </span>
                        <Button
                            v-if="!entries.next_page_url"
                            variant="outline"
                            size="icon"
                            disabled
                            aria-label="Next"
                        >
                            <ChevronRight />
                        </Button>
                        <Button v-else variant="outline" size="icon" as-child>
                            <Link
                                :href="entries.next_page_url"
                                preserve-scroll
                                preserve-state
                                aria-label="Next"
                            >
                                <ChevronRight />
                            </Link>
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
