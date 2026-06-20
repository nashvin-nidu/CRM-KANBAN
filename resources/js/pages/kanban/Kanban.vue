<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    Search,
    Plus,
    Trash2,
    Briefcase,
    X,
    GripVertical,
    ExternalLink,
} from '@lucide/vue';
import axios from 'axios';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

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
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useSidebar } from '@/components/ui/sidebar';
import { kanban } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kanban',
                href: kanban(),
            },
        ],
    },
});

interface Lead {
    id: number;
    name: string;
    email: string;
    phone?: string;
    company: string;
    status:
        | 'New'
        | 'Contacted'
        | 'Qualified'
        | 'Proposal Sent'
        | 'Won'
        | 'Lost';
    value: number;
    source: string;
    date: string;
    rating?: 'cold' | 'warm';
    assigned_to?: number | null;
    assignee?: {
        id: number;
        name: string;
        email: string;
        role: string;
    } | null;
}

interface Column {
    id: 'New' | 'Contacted' | 'Qualified' | 'Proposal Sent' | 'Won' | 'Lost';
    name: string;
    leads: Lead[];
    indicatorClass: string;
    borderClass: string;
}

const searchQuery = ref('');
const isSearchExpanded = ref(false);
const searchInputRef = ref<any>(null);

const toggleSearch = async () => {
    isSearchExpanded.value = !isSearchExpanded.value;

    if (isSearchExpanded.value) {
        await nextTick();
        const el = searchInputRef.value?.$el || searchInputRef.value;

        if (el && typeof el.focus === 'function') {
            el.focus();
        }
    } else {
        searchQuery.value = '';
    }
};
const isAddOpen = ref(false);
const isDetailsOpen = ref(false);

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

const activeLead = ref<Lead>({
    id: 0,
    name: '',
    company: '',
    email: '',
    phone: '',
    value: 0,
    status: 'New',
    source: 'Website',
    date: '',
    rating: undefined,
});

const columns = ref<Column[]>([
    {
        id: 'New',
        name: 'New',
        leads: [],
        indicatorClass: 'bg-muted-foreground/50',
        borderClass: 'border-t-muted',
    },
    {
        id: 'Contacted',
        name: 'Contacted',
        leads: [],
        indicatorClass: 'bg-accent-foreground/50',
        borderClass: 'border-t-accent/60',
    },
    {
        id: 'Qualified',
        name: 'Qualified',
        leads: [],
        indicatorClass: 'bg-secondary-foreground/60',
        borderClass: 'border-t-secondary',
    },
    {
        id: 'Proposal Sent',
        name: 'Proposal Sent',
        leads: [],
        indicatorClass: 'bg-primary/60',
        borderClass: 'border-t-primary/50',
    },
    {
        id: 'Won',
        name: 'Won',
        leads: [],
        indicatorClass: 'bg-primary',
        borderClass: 'border-t-primary',
    },
    {
        id: 'Lost',
        name: 'Lost',
        leads: [],
        indicatorClass: 'bg-destructive',
        borderClass: 'border-t-destructive',
    },
]);

const initColumns = async () => {
    try {
        const response = await axios.get('/kanban/data');
        const leads = response.data;
        columns.value.forEach((col) => {
            col.leads = leads.filter((l: Lead) => l.status === col.id);
        });
    } catch (error) {
        console.error('Failed to load leads:', error);
        toast.error('Failed to load leads from the server.');
    }
};

const { setOpen } = useSidebar();

onMounted(() => {
    initColumns();
    setOpen(false);
});

onUnmounted(() => {
    setOpen(true);
});

const onAdd = async (evt: any, targetColumnId: Column['id']) => {
    const lead = evt.data as Lead;

    if (!lead) {
return;
}

    lead.status = targetColumnId;

    try {
        await axios.post('/kanban/update', lead);
        toast.success(`Moved ${lead.name} to ${targetColumnId}`);
    } catch {
        toast.error('Failed to update lead position.');
        initColumns();
    }
};

const dateFilter = ref('all');
const statusFilter = ref('all');
const userFilter = ref('all');

const page = usePage();
const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

const availableAssignees = computed(() => {
    const assigneesMap = new Map();
    columns.value.forEach((col) => {
        col.leads.forEach((lead) => {
            if (lead.assignee) {
                assigneesMap.set(lead.assignee.id, lead.assignee);
            }
        });
    });

    return Array.from(assigneesMap.values());
});

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

    // 4. User Filter (Admin Only)
    if (isAdmin.value && userFilter.value !== 'all') {
        if (String(lead.assigned_to) !== userFilter.value) {
            return false;
        }
    }

    return true;
};

const getColumnLeadsCount = (col: Column) => {
    return col.leads.filter(matchesFilters).length;
};

const getColumnTotalValue = (col: Column) => {
    const sum = col.leads
        .filter(matchesFilters)
        .reduce((acc, lead) => acc + lead.value, 0);

    return formatCurrency(sum);
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(val);
};

const openAddDialog = (status: Column['id'] = 'New') => {
    newLead.value = {
        name: '',
        company: '',
        email: '',
        phone: '',
        value: 0,
        status: status,
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

    const status = newLead.value.status || 'New';
    const leadPayload = {
        name: newLead.value.name,
        company: newLead.value.company || 'Unknown Inc.',
        email: newLead.value.email || '',
        phone: newLead.value.phone || '',
        status: status,
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
            initColumns();
        }
    } catch (error) {
        console.error('Failed to create lead:', error);
        toast.error('Failed to create lead on the server.');
    }
};
const openDetailsDialog = (lead: Lead) => {
    activeLead.value = { ...lead };
    isDetailsOpen.value = true;
};

const saveLeadDetails = async () => {
    if (!activeLead.value.name) {
        toast.error('Lead Name is required');

        return;
    }

    const updated = { ...activeLead.value };
    updated.value = Number(updated.value) || 0;

    try {
        const response = await axios.post('/kanban/update', updated);

        if (response.data.success) {
            toast.success(`Updated Lead: ${updated.name}`);
            isDetailsOpen.value = false;
            initColumns();
        }
    } catch (error) {
        console.error('Failed to update lead details:', error);
        toast.error('Failed to save lead details on the server.');
    }
};

const deleteLead = async (id: number) => {
    try {
        const response = await axios.delete(`/kanban/delete/${id}`);

        if (response.data.success) {
            toast.success('Lead deleted');
            isDetailsOpen.value = false;
            initColumns();
        }
    } catch (error) {
        console.error('Failed to delete lead:', error);
        toast.error('Failed to delete lead from the server.');
    }
};
</script>

<template>
    <Head title="Kanban" />

    <div class="kanban-container flex flex-col gap-6 overflow-hidden p-6">
        <!-- Header Section -->
        <div
            class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-3xl font-semibold tracking-tight text-foreground"
                >
                    Kanban Board
                </h1>
                <p class="text-sm text-muted-foreground">
                    Drag and Manage leads and track pipeline stages
                    interactively.
                </p>
            </div>
            <div class="relative flex items-center gap-2">
                <!-- Collapsible Search input -->
                <div
                    class="relative flex items-center overflow-hidden transition-all duration-300 ease-in-out"
                    :class="[
                        isSearchExpanded
                            ? 'w-52 opacity-100'
                            : 'pointer-events-none w-0 opacity-0',
                    ]"
                >
                    <Search
                        class="pointer-events-none absolute left-2.5 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        placeholder="Search leads..."
                        class="h-9 w-full pl-8 text-sm"
                    />
                </div>

                <!-- Search toggle icon button -->
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 shrink-0 text-muted-foreground hover:bg-muted hover:text-foreground"
                    @click="toggleSearch"
                    :class="{ 'bg-muted text-foreground': isSearchExpanded }"
                >
                    <X class="h-4 w-4" v-if="isSearchExpanded" />
                    <Search class="h-4 w-4" v-else />
                </Button>

                <!-- User Filter Dropdown (Admin Only) -->
                <Select v-model="userFilter" v-if="isAdmin">
                    <SelectTrigger class="h-9 w-36 text-xs">
                        <SelectValue placeholder="All Executives" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" class="text-xs"
                            >All Executives</SelectItem
                        >
                        <SelectItem
                            v-for="user in availableAssignees"
                            :key="user.id"
                            :value="String(user.id)"
                            class="text-xs"
                        >
                            {{ user.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Date Filter Dropdown -->
                <Select v-model="dateFilter">
                    <SelectTrigger class="h-9 w-32 text-xs">
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

                <!-- Status Filter Dropdown -->
                <Select v-model="statusFilter">
                    <SelectTrigger class="h-9 w-32 text-xs">
                        <SelectValue placeholder="Stage Filter" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" class="text-xs"
                            >All Stages</SelectItem
                        >
                        <SelectItem
                            v-for="col in columns"
                            :key="col.id"
                            :value="col.id"
                            class="text-xs"
                        >
                            {{ col.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- New Lead Button -->
                <Button
                    class="h-9 shrink-0 px-4 text-sm"
                    @click="openAddDialog()"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    New Lead
                </Button>
            </div>
        </div>

        <!-- Board Wrapper -->
        <div
            class="custom-scrollbar flex h-full max-h-full flex-1 items-stretch gap-3 overflow-x-auto pb-3 select-none"
        >
            <div
                v-for="col in columns"
                :key="col.id"
                class="flex max-h-full w-56 min-w-56 flex-col rounded-xl border border-t-2 border-sidebar-border/70 bg-sidebar/30 shadow-sm dark:border-sidebar-border dark:bg-sidebar/10"
                :class="col.borderClass"
            >
                <!-- Column Header -->
                <div
                    class="flex shrink-0 items-center justify-between rounded-t-xl border-b border-sidebar-border/50 bg-sidebar/50 p-2.5 dark:bg-sidebar/30"
                >
                    <div class="flex items-center gap-1.5">
                        <span
                            :class="[
                                'h-2 w-2 rounded-full',
                                col.indicatorClass,
                            ]"
                        ></span>
                        <span
                            class="text-xs leading-none font-semibold text-foreground"
                            >{{ col.name }}</span
                        >
                        <Badge
                            variant="secondary"
                            class="text-2xs h-4 rounded-sm px-1 font-bold"
                        >
                            {{ getColumnLeadsCount(col) }}
                        </Badge>
                    </div>
                    <div class="flex items-center gap-1">
                        <span
                            v-if="
                                col.id === 'Proposal Sent' || col.id === 'Won'
                            "
                            class="text-2xs font-bold text-muted-foreground"
                        >
                            {{ getColumnTotalValue(col) }}
                        </span>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-5 w-5 rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
                            @click="openAddDialog(col.id)"
                        >
                            <Plus class="h-3 w-3" />
                        </Button>
                    </div>
                </div>

                <!-- Draggable List -->
                <VueDraggable
                    v-model="col.leads"
                    group="leads"
                    :animation="150"
                    filter=".no-drag"
                    :prevent-on-filter="false"
                    ghost-class="opacity-40"
                    drag-class="cursor-grabbing"
                    class="custom-scrollbar flex flex-1 flex-col gap-2 overflow-y-auto p-2"
                    @add="(evt) => onAdd(evt, col.id)"
                >
                    <div
                        v-for="lead in col.leads"
                        :key="lead.id"
                        v-show="matchesFilters(lead)"
                        class="group/card flex cursor-grab items-center justify-between rounded-lg border border-sidebar-border/70 bg-card p-2 transition-all duration-200 hover:border-foreground/20 hover:shadow-2xs active:cursor-grabbing dark:border-sidebar-border dark:hover:border-foreground/35"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            <!-- Drag Indicator (6 dots) -->
                            <div
                                class="shrink-0 rounded p-0.5 text-muted-foreground/45 transition-colors hover:bg-muted hover:text-foreground"
                            >
                                <GripVertical class="h-3.5 w-3.5" />
                            </div>

                            <!-- Card Content (Compact details) -->
                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-1.5 pr-1"
                                >
                                    <span
                                        class="max-w-28 truncate text-xs font-medium text-foreground"
                                    >
                                        {{ lead.name }}
                                    </span>
                                    <span
                                        v-if="
                                            lead.status === 'Proposal Sent' ||
                                            lead.status === 'Won'
                                        "
                                        class="shrink-0 text-xs font-bold text-success"
                                    >
                                        {{ formatCurrency(lead.value) }}
                                    </span>
                                </div>
                                <div
                                    class="text-2xs mt-0.5 flex items-center gap-1.5 text-muted-foreground"
                                >
                                    <span class="max-w-24 truncate">{{
                                        lead.company
                                    }}</span>
                                    <span
                                        v-if="lead.rating"
                                        :class="[
                                            'rating-badge-custom shrink-0 rounded-sm border font-bold uppercase',
                                            lead.rating === 'warm'
                                                ? 'border-destructive/20 bg-destructive/10 text-destructive'
                                                : 'border-muted-foreground/15 bg-muted text-muted-foreground',
                                        ]"
                                    >
                                        {{ lead.rating }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Open Option Button -->
                        <Button
                            variant="ghost"
                            size="icon"
                            class="no-drag h-6 w-6 shrink-0 rounded-md text-muted-foreground opacity-0 transition-opacity group-hover/card:opacity-100 hover:text-foreground focus:opacity-100"
                            @click="openDetailsDialog(lead)"
                        >
                            <ExternalLink class="h-3 w-3" />
                        </Button>
                    </div>

                    <!-- Empty placeholder inside empty columns -->
                    <div
                        v-if="getColumnLeadsCount(col) === 0"
                        class="flex min-h-28 flex-1 flex-col items-center justify-center rounded-lg border border-dashed border-sidebar-border/50 p-4 text-center text-xs text-muted-foreground"
                    >
                        <Briefcase class="mb-1 h-4 w-4 opacity-40" />
                        <span>No leads matching.</span>
                        <span class="text-2xs opacity-70"
                            >Drag cards here.</span
                        >
                    </div>
                </VueDraggable>
            </div>
        </div>

        <!-- Add Lead Dialog -->
        <Dialog v-model:open="isAddOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Add New Lead</DialogTitle>
                    <DialogDescription>
                        Create a new lead. It will be added to the stage you
                        selected.
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
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="col in columns"
                                            :key="col.id"
                                            :value="col.id"
                                        >
                                            {{ col.name }}
                                        </SelectItem>
                                    </SelectGroup>
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
                                    <SelectValue
                                        placeholder="Select rating (optional)"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="cold"
                                            >Cold</SelectItem
                                        >
                                        <SelectItem value="warm"
                                            >Warm</SelectItem
                                        >
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isAddOpen = false"
                        >Cancel</Button
                    >
                    <Button @click="createLead">Create Lead</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Lead Details / Edit Dialog -->
        <Dialog v-model:open="isDetailsOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Lead Details</DialogTitle>
                    <DialogDescription>
                        View or update information for this lead opportunity.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-name" class="text-right">Name</Label>
                        <Input
                            id="edit-name"
                            v-model="activeLead.name"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-company" class="text-right"
                            >Company</Label
                        >
                        <Input
                            id="edit-company"
                            v-model="activeLead.company"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-email" class="text-right">Email</Label>
                        <Input
                            id="edit-email"
                            v-model="activeLead.email"
                            type="email"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-phone" class="text-right">Phone</Label>
                        <Input
                            id="edit-phone"
                            v-model="activeLead.phone"
                            type="tel"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-value" class="text-right"
                            >Value (₹)</Label
                        >
                        <Input
                            id="edit-value"
                            v-model="activeLead.value"
                            type="number"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-status" class="text-right"
                            >Stage</Label
                        >
                        <div class="col-span-3">
                            <Select v-model="activeLead.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a stage" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="col in columns"
                                            :key="col.id"
                                            :value="col.id"
                                        >
                                            {{ col.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-source" class="text-right"
                            >Source</Label
                        >
                        <Input
                            id="edit-source"
                            v-model="activeLead.source"
                            class="col-span-3"
                        />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-rating" class="text-right"
                            >Rating</Label
                        >
                        <div class="col-span-3">
                            <Select v-model="activeLead.rating">
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="Select rating (optional)"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="cold"
                                            >Cold</SelectItem
                                        >
                                        <SelectItem value="warm"
                                            >Warm</SelectItem
                                        >
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-4 items-center gap-4 text-xs text-muted-foreground"
                    >
                        <span class="text-right font-medium">Created</span>
                        <span class="col-span-3 pl-1">{{
                            activeLead.date
                        }}</span>
                    </div>
                </div>

                <DialogFooter
                    class="flex w-full items-center gap-2 sm:justify-between"
                >
                    <Button
                        variant="destructive"
                        class="shrink-0 gap-1.5"
                        @click="deleteLead(activeLead.id)"
                    >
                        <Trash2 class="size-4" />
                        Delete
                    </Button>
                    <div class="flex w-full items-center justify-end gap-2">
                        <Button variant="outline" @click="isDetailsOpen = false"
                            >Cancel</Button
                        >
                        <Button @click="saveLeadDetails">Save Changes</Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
