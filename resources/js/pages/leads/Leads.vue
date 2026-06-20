<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Search, Plus, Mail, Upload, Trash2 } from '@lucide/vue';
import axios from 'axios';
import { ref, computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import * as XLSX from 'xlsx';

import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { leads as leadsRoute } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Leads',
                href: leadsRoute(),
            },
        ],
    },
});

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Lead {
    id: number;
    name: string;
    email: string;
    phone?: string;
    company: string;
    status: string;
    value: number;
    source: string;
    date: string;
    rating?: 'cold' | 'warm';
    assigned_to?: number | null;
    assignee?: User | null;
}

interface ValidationError {
    row: number;
    error: string;
}

const props = defineProps<{
    leads: Lead[];
}>();

// State refs
const allLeads = ref(props.leads || []);
const searchQuery = ref('');
const statusFilter = ref('all');
const dateFilter = ref('all');

// Watch props.leads to keep in sync with Inertia reloads
watch(
    () => props.leads,
    (newLeads) => {
        allLeads.value = newLeads || [];
    },
    { deep: true },
);

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const getInitials = (name: string) => {
    if (!name) {
return '';
}

    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const deleteLead = async (id: number) => {
    if (!confirm('Are you sure you want to delete this lead?')) {
        return;
    }

    try {
        const response = await axios.delete(`/kanban/delete/${id}`);

        if (response.data.success) {
            toast.success('Lead deleted successfully');
            allLeads.value = allLeads.value.filter((l) => l.id !== id);
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to delete lead:', error);
        toast.error('Failed to delete lead.');
    }
};

// Modals state
const isAddOpen = ref(false);
const isImportModalOpen = ref(false);

// Add Lead form model
const newLead = ref<Partial<Lead>>({
    name: '',
    company: '',
    email: '',
    phone: '',
    value: 0,
    status: 'New',
    source: 'Website',
    rating: undefined,
});

// Import file state
const validLeads = ref<any[] | undefined>(undefined);
const invalidLeads = ref<ValidationError[]>([]);

// Filter helpers
const isSameDay = (d1: Date, d2: Date) => {
    return (
        d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate()
    );
};

const isThisWeek = (d: Date, refDate: Date) => {
    const startOfWeek = new Date(refDate);
    const day = refDate.getDay();
    startOfWeek.setDate(refDate.getDate() - day);
    startOfWeek.setHours(0, 0, 0, 0);

    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    endOfWeek.setHours(23, 59, 59, 999);

    return d >= startOfWeek && d <= endOfWeek;
};

const isThisMonth = (d: Date, refDate: Date) => {
    return (
        d.getFullYear() === refDate.getFullYear() &&
        d.getMonth() === refDate.getMonth()
    );
};

const isThisYear = (d: Date, refDate: Date) => {
    return d.getFullYear() === refDate.getFullYear();
};

const matchesFilters = (lead: Lead) => {
    // 1. Search Query
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        const matchesSearch =
            lead.name.toLowerCase().includes(q) ||
            lead.company.toLowerCase().includes(q) ||
            lead.email.toLowerCase().includes(q) ||
            lead.source.toLowerCase().includes(q);

        if (!matchesSearch) {
            return false;
        }
    }

    // 2. Status Filter
    if (statusFilter.value !== 'all') {
        if (lead.status !== statusFilter.value) {
            return false;
        }
    }

    // 3. Date Filter
    if (dateFilter.value !== 'all') {
        const leadDate = new Date(lead.date);
        const today = new Date();

        if (dateFilter.value === 'today') {
            if (!isSameDay(leadDate, today)) {
                return false;
            }
        } else if (dateFilter.value === 'week') {
            if (!isThisWeek(leadDate, today)) {
                return false;
            }
        } else if (dateFilter.value === 'month') {
            if (!isThisMonth(leadDate, today)) {
                return false;
            }
        } else if (dateFilter.value === 'year') {
            if (!isThisYear(leadDate, today)) {
                return false;
            }
        }
    }

    return true;
};

const filteredLeads = computed(() => {
    return allLeads.value.filter(matchesFilters);
});

// Single Lead Creation
const openAddDialog = () => {
    newLead.value = {
        name: '',
        company: '',
        email: '',
        phone: '',
        value: 0,
        status: 'New',
        source: 'Website',
        rating: undefined,
    };
    isAddOpen.value = true;
};

const createLead = async () => {
    if (!newLead.value.name) {
        toast.error('Lead Name is required');

        return;
    }

    const leadPayload = {
        name: newLead.value.name,
        company: newLead.value.company || 'Unknown Inc.',
        email: newLead.value.email || '',
        phone: newLead.value.phone || '',
        status: newLead.value.status || 'New',
        value: Number(newLead.value.value) || 0,
        source: newLead.value.source || 'Website',
        date: new Date().toISOString().split('T')[0],
        rating: newLead.value.rating,
    };

    try {
        const response = await axios.post('/kanban/update', leadPayload);

        if (response.data.success) {
            toast.success(`Created Lead: ${response.data.lead.name}`);
            isAddOpen.value = false;
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to create lead:', error);
        toast.error('Failed to create lead on the server.');
    }
};

// Import Excel / CSV
const openImportDialog = () => {
    validLeads.value = undefined;
    invalidLeads.value = [];
    isImportModalOpen.value = true;
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
return;
}

    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const data = new Uint8Array(e.target?.result as ArrayBuffer);
            const workbook = XLSX.read(data, { type: 'array' });
            const sheetName = workbook.SheetNames[0];
            const sheet = workbook.Sheets[sheetName];
            const json = XLSX.utils.sheet_to_json(sheet) as any[];

            validateImportData(json);
        } catch (err) {
            console.error('Error reading file:', err);
            toast.error(
                'Failed to read file. Please verify CSV or Excel format.',
            );
        }
    };
    reader.readAsArrayBuffer(file);
};

const validateImportData = (rawRows: any[]) => {
    const valid: any[] = [];
    const invalid: ValidationError[] = [];

    rawRows.forEach((row, index) => {
        const rowNum = index + 2; // Rows start at 2 (headers are row 1)
        const name =
            row.Name || row.name || row['Lead Name'] || row['Contact Name'];
        const email = row.Email || row.email;
        const phone = row.Phone || row.phone || row['Phone Number'];
        const company = row.Company || row.company;
        const value = row.Value || row.value || 0;
        const status =
            row.Status || row.status || row.Stage || row.stage || 'New';
        const source =
            row.Source || row.source || row['Lead Source'] || 'Website';
        const date =
            row.Date ||
            row.date ||
            row['Created Date'] ||
            new Date().toISOString().split('T')[0];
        const rating = row.Rating || row.rating;

        // 1. Validate Name
        if (!name || String(name).trim() === '') {
            invalid.push({ row: rowNum, error: 'Name is required' });

            return;
        }

        // Validate Phone
        const phoneStr = phone ? String(phone).trim() : '';

        if (!phoneStr) {
            invalid.push({ row: rowNum, error: 'Phone number is required' });

            return;
        }

        // 2. Validate Email format
        const emailStr = email ? String(email).trim() : '';

        if (emailStr) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(emailStr)) {
                invalid.push({
                    row: rowNum,
                    error: `Invalid email format: "${emailStr}"`,
                });

                return;
            }
        }

        // 3. Validate Value
        const valNum = Number(value);

        if (isNaN(valNum)) {
            invalid.push({
                row: rowNum,
                error: `Value must be a number: "${value}"`,
            });

            return;
        }

        // 4. Validate Status/Stage
        const validStatuses = [
            'New',
            'Contacted',
            'Qualified',
            'Proposal Sent',
            'Won',
            'Lost',
        ];
        const statusStr = String(status).trim();
        const matchedStatus =
            validStatuses.find(
                (s) => s.toLowerCase() === statusStr.toLowerCase(),
            ) || 'New';

        // 5. Validate Date (YYYY-MM-DD)
        let dateStr = String(date).trim();

        if (dateStr.includes('T')) {
            dateStr = dateStr.split('T')[0];
        }

        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;

        if (!dateRegex.test(dateStr)) {
            const parsedDate = new Date(dateStr);

            if (!isNaN(parsedDate.getTime())) {
                dateStr = parsedDate.toISOString().split('T')[0];
            } else {
                invalid.push({
                    row: rowNum,
                    error: `Invalid date format: "${dateStr}". Use YYYY-MM-DD.`,
                });

                return;
            }
        }

        // 6. Validate Rating
        const ratingStr = rating
            ? String(rating).trim().toLowerCase()
            : undefined;

        if (ratingStr && ratingStr !== 'warm' && ratingStr !== 'cold') {
            invalid.push({
                row: rowNum,
                error: `Rating must be 'warm' or 'cold': "${rating}"`,
            });

            return;
        }

        valid.push({
            name: String(name).trim(),
            email: emailStr || null,
            phone: phone ? String(phone).trim() : null,
            company: company ? String(company).trim() : 'Unknown Inc.',
            status: matchedStatus,
            value: valNum,
            source: String(source).trim(),
            date: dateStr,
            rating: ratingStr || null,
        });
    });

    validLeads.value = valid;
    invalidLeads.value = invalid;
};

const uploadLeads = async () => {
    if (!validLeads.value || validLeads.value.length === 0) {
return;
}

    try {
        const response = await axios.post('/leads/batch', {
            leads: validLeads.value,
        });

        if (response.data.success) {
            toast.success(
                `Imported ${response.data.created} leads. Skipped ${response.data.skipped} duplicates.`,
            );
            isImportModalOpen.value = false;
            router.reload({ only: ['leads'] });
        }
    } catch (error) {
        console.error('Failed to import leads:', error);
        toast.error('Failed to upload leads to database.');
    }
};

// UI formatting
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
                <Button variant="outline" @click="openImportDialog">
                    <Upload class="mr-2 h-4 w-4" />
                    Import
                </Button>
                <Button @click="openAddDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    New Lead
                </Button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="relative flex w-full max-w-sm items-center">
                <Search class="absolute left-3 size-4 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    placeholder="Search leads..."
                    class="h-10 w-full pl-9"
                />
            </div>
            <div class="flex items-center gap-3">
                <!-- Status Filter -->
                <Select v-model="statusFilter">
                    <SelectTrigger class="h-10 w-36 bg-card text-xs">
                        <SelectValue placeholder="Stage" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" class="text-xs"
                            >All Stages</SelectItem
                        >
                        <SelectItem value="New" class="text-xs">New</SelectItem>
                        <SelectItem value="Contacted" class="text-xs"
                            >Contacted</SelectItem
                        >
                        <SelectItem value="Qualified" class="text-xs"
                            >Qualified</SelectItem
                        >
                        <SelectItem value="Proposal Sent" class="text-xs"
                            >Proposal Sent</SelectItem
                        >
                        <SelectItem value="Won" class="text-xs">Won</SelectItem>
                        <SelectItem value="Lost" class="text-xs"
                            >Lost</SelectItem
                        >
                    </SelectContent>
                </Select>

                <!-- Date Filter -->
                <Select v-model="dateFilter">
                    <SelectTrigger class="h-10 w-36 bg-card text-xs">
                        <SelectValue placeholder="Date Created" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" class="text-xs"
                            >All Time</SelectItem
                        >
                        <SelectItem value="today" class="text-xs"
                            >Today</SelectItem
                        >
                        <SelectItem value="week" class="text-xs"
                            >This Week</SelectItem
                        >
                        <SelectItem value="month" class="text-xs"
                            >This Month</SelectItem
                        >
                        <SelectItem value="year" class="text-xs"
                            >This Year</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Table Card -->
        <Card
            class="overflow-hidden border-sidebar-border/70 shadow-sm dark:border-sidebar-border"
        >
            <div class="px-6">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead class="font-semibold"
                                >Contact Name</TableHead
                            >
                            <TableHead class="font-semibold">Company</TableHead>
                            <TableHead class="font-semibold">Email</TableHead>
                            <TableHead class="font-semibold">Phone</TableHead>
                            <TableHead class="font-semibold"
                                >Lead Source</TableHead
                            >
                            <TableHead class="font-semibold">Value</TableHead>
                            <TableHead class="font-semibold">Status</TableHead>
                            <TableHead class="font-semibold"
                                >Created Date</TableHead
                            >
                            <TableHead v-if="isAdmin" class="font-semibold"
                                >Assignee</TableHead
                            >
                            <TableHead
                                v-if="isAdmin"
                                class="text-right font-semibold"
                                >Actions</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="lead in filteredLeads"
                            :key="lead.id"
                            class="border-b border-border/40 transition-colors hover:bg-muted/50"
                        >
                            <TableCell
                                class="py-3.5 font-medium text-foreground"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-semibold tracking-tight"
                                        >{{ lead.name }}</span
                                    >
                                    <Badge
                                        v-if="lead.rating"
                                        :class="
                                            lead.rating === 'warm'
                                                ? 'border-transparent bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'
                                                : 'border-transparent bg-sky-100 px-2 py-0.5 text-[10px] font-medium text-sky-800 dark:bg-sky-900/30 dark:text-sky-400'
                                        "
                                    >
                                        {{ lead.rating }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell
                                class="py-3.5 text-sm text-muted-foreground"
                                >{{ lead.company }}</TableCell
                            >
                            <TableCell class="py-3.5">
                                <span
                                    v-if="lead.email"
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Mail
                                        class="size-4 text-muted-foreground/60"
                                    />
                                    {{ lead.email }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground/40"
                                    >—</span
                                >
                            </TableCell>
                            <TableCell class="py-3.5">
                                <span
                                    v-if="lead.phone"
                                    class="font-mono text-sm text-muted-foreground"
                                    >{{ lead.phone }}</span
                                >
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground/40"
                                    >—</span
                                >
                            </TableCell>
                            <TableCell
                                class="py-3.5 text-sm text-muted-foreground"
                                >{{ lead.source }}</TableCell
                            >
                            <TableCell
                                class="py-3.5 text-sm font-semibold text-foreground"
                                >{{ formatCurrency(lead.value) }}</TableCell
                            >
                            <TableCell class="py-3.5">
                                <Badge
                                    :variant="getStatusVariant(lead.status)"
                                    :class="[
                                        getStatusClass(lead.status),
                                        'px-2.5 py-1 text-xs font-semibold',
                                    ]"
                                >
                                    {{ lead.status }}
                                </Badge>
                            </TableCell>
                            <TableCell
                                class="py-3.5 text-sm text-muted-foreground"
                                >{{ lead.date }}</TableCell
                            >
                            <TableCell v-if="isAdmin" class="py-3.5">
                                <div
                                    v-if="lead.assignee"
                                    class="flex items-center gap-2"
                                >
                                    <Avatar
                                        class="size-7 border border-border/80"
                                    >
                                        <AvatarFallback
                                            class="bg-primary/10 text-[10px] font-bold text-primary"
                                        >
                                            {{
                                                getInitials(lead.assignee.name)
                                            }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span
                                        class="text-sm font-semibold text-foreground/90"
                                        >{{ lead.assignee.name }}</span
                                    >
                                </div>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground/40"
                                    >—</span
                                >
                            </TableCell>
                            <TableCell v-if="isAdmin" class="py-3.5 text-right">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                    @click="deleteLead(lead.id)"
                                >
                                    <Trash2 class="size-4" />
                                    <span class="sr-only">Delete</span>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredLeads.length === 0">
                            <TableCell
                                :colspan="isAdmin ? 10 : 8"
                                class="h-24 text-center text-muted-foreground"
                            >
                                No leads found matching your search.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </Card>

        <!-- Single Creation Dialog -->
        <Dialog v-model:open="isAddOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Add New Lead</DialogTitle>
                    <DialogDescription>
                        Create a new opportunity for your pipeline.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="name" class="text-right">Name</Label>
                        <Input
                            id="name"
                            v-model="newLead.name"
                            placeholder="John Doe"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="company" class="text-right">Company</Label>
                        <Input
                            id="company"
                            v-model="newLead.company"
                            placeholder="Acme Inc."
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="email" class="text-right">Email</Label>
                        <Input
                            id="email"
                            v-model="newLead.email"
                            type="email"
                            placeholder="john@example.com"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="phone" class="text-right">Phone</Label>
                        <Input
                            id="phone"
                            v-model="newLead.phone"
                            type="tel"
                            placeholder="+91 99999 99999"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="value" class="text-right">Value (₹)</Label>
                        <Input
                            id="value"
                            v-model="newLead.value"
                            type="number"
                            placeholder="5000"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="status" class="text-right">Stage</Label>
                        <div class="col-span-3">
                            <Select v-model="newLead.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a stage" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="New">New</SelectItem>
                                    <SelectItem value="Contacted"
                                        >Contacted</SelectItem
                                    >
                                    <SelectItem value="Qualified"
                                        >Qualified</SelectItem
                                    >
                                    <SelectItem value="Proposal Sent"
                                        >Proposal Sent</SelectItem
                                    >
                                    <SelectItem value="Won">Won</SelectItem>
                                    <SelectItem value="Lost">Lost</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="source" class="text-right">Source</Label>
                        <Input
                            id="source"
                            v-model="newLead.source"
                            placeholder="Website"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="rating" class="text-right">Rating</Label>
                        <div class="col-span-3">
                            <Select v-model="newLead.rating">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select rating" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="warm">Warm</SelectItem>
                                    <SelectItem value="cold">Cold</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button type="submit" @click="createLead"
                        >Create Lead</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Import Modal -->
        <Dialog v-model:open="isImportModalOpen">
            <DialogContent class="flex max-h-[85vh] flex-col p-6 sm:max-w-xl">
                <DialogHeader class="shrink-0">
                    <DialogTitle>Import Leads</DialogTitle>
                    <DialogDescription>
                        Upload a `.csv` or `.xlsx` file. We will validate rows
                        automatically before saving.
                    </DialogDescription>
                </DialogHeader>

                <div class="flex-1 space-y-4 overflow-y-auto py-4 pr-1">
                    <!-- File Input -->
                    <div class="flex flex-col gap-2">
                        <Label for="file-upload"
                            >Choose CSV or Excel File</Label
                        >
                        <Input
                            id="file-upload"
                            type="file"
                            accept=".csv, .xlsx, .xls"
                            @change="handleFileSelect"
                            class="cursor-pointer"
                        />
                    </div>

                    <!-- Validation Summary -->
                    <div
                        v-if="
                            validLeads !== undefined || invalidLeads.length > 0
                        "
                        class="space-y-4"
                    >
                        <div class="flex items-center gap-4">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400"
                            >
                                {{ validLeads ? validLeads.length : 0 }} Valid
                                Rows
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-900/30 dark:text-rose-400"
                            >
                                {{ invalidLeads.length }} Invalid Rows
                            </span>
                        </div>

                        <!-- Invalid Row Errors -->
                        <div
                            v-if="invalidLeads.length > 0"
                            class="space-y-2 rounded-lg border border-destructive/20 bg-destructive/5 p-3"
                        >
                            <h4 class="text-xs font-bold text-destructive">
                                Errors Found:
                            </h4>
                            <div
                                class="custom-scrollbar max-h-36 space-y-1.5 overflow-y-auto text-xs text-muted-foreground"
                            >
                                <div v-for="err in invalidLeads" :key="err.row">
                                    <span class="font-semibold text-destructive"
                                        >Row {{ err.row }}:</span
                                    >
                                    {{ err.error }}
                                </div>
                            </div>
                        </div>

                        <!-- Preview Valid Rows -->
                        <div
                            v-if="validLeads && validLeads.length > 0"
                            class="space-y-2 rounded-lg border border-sidebar-border/70 p-3"
                        >
                            <h4 class="text-xs font-bold text-foreground">
                                Valid Leads Preview:
                            </h4>
                            <div
                                class="custom-scrollbar max-h-36 space-y-1.5 overflow-y-auto text-xs"
                            >
                                <div
                                    v-for="(lead, idx) in validLeads.slice(
                                        0,
                                        5,
                                    )"
                                    :key="idx"
                                    class="flex justify-between border-b border-border/40 pb-1"
                                >
                                    <span class="font-medium text-foreground">
                                        {{ lead.name }}
                                        <span
                                            class="text-[10px] text-muted-foreground"
                                            >({{ lead.company }})</span
                                        >
                                    </span>
                                    <span class="text-muted-foreground">{{
                                        lead.email || 'No email'
                                    }}</span>
                                </div>
                                <div
                                    v-if="validLeads.length > 5"
                                    class="pt-1 text-center text-[10px] font-semibold text-muted-foreground"
                                >
                                    + {{ validLeads.length - 5 }} more leads
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="shrink-0 border-t pt-4">
                    <Button variant="outline" @click="isImportModalOpen = false"
                        >Cancel</Button
                    >
                    <Button
                        :disabled="!validLeads || validLeads.length === 0"
                        @click="uploadLeads"
                    >
                        Upload to Database
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
