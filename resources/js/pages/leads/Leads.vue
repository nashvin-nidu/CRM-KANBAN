<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Search, Plus, Mail, Upload } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { leads } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Leads',
                href: leads(),
            },
        ],
    },
});

const searchQuery = ref('');

const allLeads = ref([
    {
        id: 1,
        name: 'Alice Smith',
        email: 'alice@example.com',
        company: 'Acme Corp',
        status: 'New',
        value: 12500,
        source: 'Website',
        date: '2026-06-05',
    },
    {
        id: 2,
        name: 'Bob Johnson',
        email: 'bob@example.com',
        company: 'Infinite Loop',
        status: 'Contacted',
        value: 45000,
        source: 'Referral',
        date: '2026-06-03',
    },
    {
        id: 3,
        name: 'Charlie Brown',
        email: 'charlie@example.com',
        company: 'Peanuts Inc',
        status: 'Qualified',
        value: 8000,
        source: 'LinkedIn',
        date: '2026-06-02',
    },
    {
        id: 4,
        name: 'Diana Prince',
        email: 'diana@example.com',
        company: 'Wayne Ent.',
        status: 'Proposal Sent',
        value: 95000,
        source: 'Direct',
        date: '2026-05-30',
    },
    {
        id: 5,
        name: 'Ethan Hunt',
        email: 'ethan@example.com',
        company: 'IMF Agency',
        status: 'Won',
        value: 150000,
        source: 'Referral',
        date: '2026-05-28',
    },
    {
        id: 6,
        name: 'Fiona Gallagher',
        email: 'fiona@example.com',
        company: "Patsy's Pies",
        status: 'Lost',
        value: 3200,
        source: 'Cold Call',
        date: '2026-05-25',
    },
]);

const filteredLeads = computed(() => {
    if (!searchQuery.value) {
        return allLeads.value;
    }

    const q = searchQuery.value.toLowerCase();

    return allLeads.value.filter(
        (lead) =>
            lead.name.toLowerCase().includes(q) ||
            lead.company.toLowerCase().includes(q) ||
            lead.email.toLowerCase().includes(q) ||
            lead.status.toLowerCase().includes(q) ||
            lead.source.toLowerCase().includes(q),
    );
});

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(val);
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'New':
            return 'secondary';
        case 'Contacted':
            return 'outline';
        case 'Qualified':
            return 'default';
        case 'Proposal Sent':
            return 'default';
        case 'Won':
            return 'default';
        case 'Lost':
            return 'destructive';
        default:
            return 'outline';
    }
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'New':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border-transparent';
        case 'Contacted':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border-transparent';
        case 'Qualified':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 border-transparent';
        case 'Proposal Sent':
            return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 border-transparent';
        case 'Won':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border-transparent';
        case 'Lost':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400 border-transparent';
        default:
            return '';
    }
};
</script>

<template>
    <Head title="Leads" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
        <!-- Header Section -->
        <div
            class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Leads</h1>
                <p class="text-muted-foreground">
                    Manage and track your sales opportunities.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline">
                    <Upload class="mr-2 h-4 w-4" />
                    Import
                </Button>
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Lead
                </Button>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="flex items-center justify-between gap-4">
            <div class="relative flex w-full max-w-sm items-center">
                <Search class="absolute left-3 size-4 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    placeholder="Search leads..."
                    class="h-10 w-full pl-9"
                />
            </div>
        </div>

        <!-- Table Card -->
        <Card
            class="overflow-hidden border-sidebar-border/70 shadow-sm dark:border-sidebar-border"
        >
            <div class="px-6">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Contact Name</TableHead>
                            <TableHead>Company</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Lead Source</TableHead>
                            <TableHead>Value</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Created Date</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="lead in filteredLeads"
                            :key="lead.id"
                            class="transition-colors hover:bg-muted/50"
                        >
                            <TableCell class="font-medium">{{
                                lead.name
                            }}</TableCell>
                            <TableCell>{{ lead.company }}</TableCell>
                            <TableCell>
                                <span
                                    class="flex items-center gap-1.5 text-muted-foreground"
                                >
                                    <Mail class="size-3.5" />
                                    {{ lead.email }}
                                </span>
                            </TableCell>
                            <TableCell>{{ lead.source }}</TableCell>
                            <TableCell class="font-semibold">{{
                                formatCurrency(lead.value)
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="getStatusVariant(lead.status)"
                                    :class="getStatusClass(lead.status)"
                                >
                                    {{ lead.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{
                                lead.date
                            }}</TableCell>
                            <TableCell class="text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 w-8 p-0"
                                >
                                    <Mail class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredLeads.length === 0">
                            <TableCell
                                colspan="8"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No leads found matching your search.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </Card>
    </div>
</template>
