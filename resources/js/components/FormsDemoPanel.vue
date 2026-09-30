<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    title: string;
    className: string;
    backHref: NonNullable<InertiaLinkProps['href']>;
    data: Record<string, unknown> | null;
}>();
</script>

<template>
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <Card>
            <CardHeader
                class="flex flex-row flex-wrap items-start justify-between gap-3"
            >
                <div class="grid gap-1.5">
                    <CardTitle>{{ title }}</CardTitle>
                    <CardDescription>
                        Defined in
                        <code>app/Forms/{{ className }}.php</code> and rendered
                        with one <code>&lt;Form&gt;</code> component. Saving
                        validates it on the server and stores it with
                        <code>FormEntryService</code>.
                    </CardDescription>
                </div>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="backHref">
                        <ArrowLeft />
                        Back
                    </Link>
                </Button>
            </CardHeader>

            <CardContent>
                <slot />
            </CardContent>
        </Card>

        <Card class="h-fit">
            <CardHeader>
                <CardTitle>Stored data</CardTitle>
                <CardDescription>
                    What <code>FormEntryService</code> saved in the
                    <code>form_entries</code> table. Secrets are masked.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <pre
                    v-if="data"
                    class="max-h-128 overflow-auto rounded-md bg-muted p-3 text-xs"
                    >{{ JSON.stringify(data, null, 2) }}</pre>
                <p
                    v-else
                    class="rounded-md border border-dashed p-3 text-sm text-muted-foreground"
                >
                    Nothing saved yet. Submit the form to create an entry.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
