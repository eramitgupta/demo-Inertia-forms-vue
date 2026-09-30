<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { destroy } from '@/routes/forms-demo';
import type { FormEntrySummary } from '@/types';

defineProps<{
    demo: string;
    entry: FormEntrySummary;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete this entry?</DialogTitle>
                <DialogDescription>
                    “{{ entry.title }}” and its uploaded files will be deleted.
                    This cannot be undone.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="destroy.form([demo, entry.id])"
                v-slot="{ processing }"
            >
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        Delete entry
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
