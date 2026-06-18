<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import { kanban } from '@/routes';
import { VueDraggable } from 'vue-draggable-plus';
import { useSidebar } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';
import {
    Card,
    CardContent,
    CardHeader,
    CardFooter,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Search,
    Plus,
    Mail,
    Building,
    DollarSign,
    Calendar,
    Trash2,
    Briefcase,
    Globe,
    Check,
    X,
    GripVertical,
    ExternalLink,
} from '@lucide/vue';

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
    company: string;
    status: 'New' | 'Contacted' | 'Qualified' | 'Proposal Sent' | 'Won' | 'Lost';
    value: number;
    source: string;
    date: string;
    rating?: 'cold' | 'warm';
}

interface Column {
    id: 'New' | 'Contacted' | 'Qualified' | 'Proposal Sent' | 'Won' | 'Lost';
    name: string;
    leads: Lead[];
    indicatorClass: string;
    borderClass: string;
}

const LOCAL_STORAGE_KEY = 'crm_leads';

const defaultLeads: Lead[] = [
    { id: 1, name: 'Alice Smith', email: 'alice@example.com', company: 'Acme Corp', status: 'New', value: 12500, source: 'Website', date: (() => { const d = new Date(); d.setDate(d.getDate() - 15); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 2, name: 'Bob Johnson', email: 'bob@example.com', company: 'Infinite Loop', status: 'Contacted', value: 45000, source: 'Referral', date: (() => { const d = new Date(); d.setDate(d.getDate() - 2); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 3, name: 'Charlie Brown', email: 'charlie@example.com', company: 'Peanuts Inc', status: 'Qualified', value: 8000, source: 'LinkedIn', date: (() => { const d = new Date(); d.setDate(d.getDate() - 1); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 4, name: 'Diana Prince', email: 'diana@example.com', company: 'Wayne Ent.', status: 'Proposal Sent', value: 95000, source: 'Direct', date: new Date().toISOString().split('T')[0], rating: 'warm' },
    { id: 5, name: 'Ethan Hunt', email: 'ethan@example.com', company: 'IMF Agency', status: 'Won', value: 150000, source: 'Referral', date: (() => { const d = new Date(); d.setMonth(d.getMonth() - 2); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 6, name: 'Fiona Gallagher', email: 'fiona@example.com', company: 'Patsy\'s Pies', status: 'Lost', value: 3200, source: 'Cold Call', date: (() => { const d = new Date(); d.setFullYear(d.getFullYear() - 1); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 7, name: 'George Clark', email: 'george@example.com', company: 'Nexus Ltd', status: 'New', value: 18000, source: 'Website', date: (() => { const d = new Date(); d.setDate(d.getDate() - 4); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 8, name: 'Hannah Abbott', email: 'hannah@example.com', company: 'Apothecary Co', status: 'Contacted', value: 5200, source: 'Inbound', date: (() => { const d = new Date(); d.setDate(d.getDate() - 3); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 9, name: 'Ian Malcolm', email: 'ian@example.com', company: 'InGen Bios', status: 'Qualified', value: 65000, source: 'Conference', date: (() => { const d = new Date(); d.setDate(d.getDate() - 5); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 10, name: 'Julia Roberts', email: 'julia@example.com', company: 'Pretty Pics', status: 'Proposal Sent', value: 120000, source: 'Referral', date: (() => { const d = new Date(); d.setDate(d.getDate() - 6); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 11, name: 'Kevin Bacon', email: 'kevin@example.com', company: 'Six Degrees', status: 'Won', value: 30000, source: 'Direct', date: (() => { const d = new Date(); d.setDate(d.getDate() - 7); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 12, name: 'Laura Croft', email: 'laura@example.com', company: 'Tomb Explorer', status: 'Lost', value: 45000, source: 'Cold Call', date: (() => { const d = new Date(); d.setDate(d.getDate() - 8); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 13, name: 'Bruce Wayne', email: 'bruce@example.com', company: 'Wayne Ent.', status: 'New', value: 500000, source: 'Direct', date: new Date().toISOString().split('T')[0], rating: 'warm' },
    { id: 14, name: 'Clark Kent', email: 'clark@example.com', company: 'Daily Planet', status: 'Contacted', value: 15000, source: 'Website', date: (() => { const d = new Date(); d.setDate(d.getDate() - 1); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 15, name: 'Peter Parker', email: 'peter@example.com', company: 'Daily Bugle', status: 'Qualified', value: 9000, source: 'LinkedIn', date: (() => { const d = new Date(); d.setDate(d.getDate() - 2); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 16, name: 'Tony Stark', email: 'tony@example.com', company: 'Stark Ind.', status: 'Proposal Sent', value: 750000, source: 'Referral', date: (() => { const d = new Date(); d.setDate(d.getDate() - 3); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 17, name: 'Natasha Romanoff', email: 'natasha@example.com', company: 'S.H.I.E.L.D.', status: 'Won', value: 110000, source: 'Inbound', date: (() => { const d = new Date(); d.setDate(d.getDate() - 4); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 18, name: 'Steve Rogers', email: 'steve@example.com', company: 'Brooklyn Sp.', status: 'New', value: 25000, source: 'Conference', date: (() => { const d = new Date(); d.setDate(d.getDate() - 10); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
    { id: 19, name: 'Wanda Maximoff', email: 'wanda@example.com', company: 'Westview Co', status: 'Qualified', value: 85000, source: 'Website', date: (() => { const d = new Date(); d.setDate(d.getDate() - 12); return d.toISOString().split('T')[0]; })(), rating: 'warm' },
    { id: 20, name: 'Barry Allen', email: 'barry@example.com', company: 'Star Labs', status: 'Won', value: 60000, source: 'Referral', date: (() => { const d = new Date(); d.setDate(d.getDate() - 14); return d.toISOString().split('T')[0]; })(), rating: 'cold' },
];

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

const loadLeads = (): Lead[] => {
    if (typeof window === 'undefined') return defaultLeads;
    const data = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (data) {
        try {
            return JSON.parse(data);
        } catch (e) {
            console.error('Failed to parse leads from localstorage:', e);
        }
    }
    return defaultLeads;
};

const persistBoard = () => {
    const flatLeads: Lead[] = [];
    columns.value.forEach(col => {
        col.leads.forEach(lead => {
            lead.status = col.id;
            flatLeads.push(lead);
        });
    });
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(flatLeads));
};

const initColumns = () => {
    let leads = loadLeads();
    // Reset if it's the old 6-lead list or has static dates to seed the dynamic dates list
    if (leads.length <= 6 || leads[0]?.date === '2026-06-05') {
        leads = defaultLeads;
        localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(leads));
    }
    columns.value.forEach(col => {
        col.leads = leads.filter(l => l.status === col.id);
    });
};

const { setOpen } = useSidebar();

onMounted(() => {
    initColumns();
    setOpen(false);
});

onUnmounted(() => {
    setOpen(true);
});

const onDragChange = (evt: any, targetColumnId: Column['id']) => {
    if (evt.added) {
        const lead = evt.added.element as Lead;
        lead.status = targetColumnId;
        persistBoard();
        toast.success(`Moved ${lead.name} to ${targetColumnId}`);
    } else if (evt.removed || evt.moved) {
        persistBoard();
    }
};

const dateFilter = ref('all');
const statusFilter = ref('all');

const isSameDay = (d1: Date, d2: Date) => {
    return d1.getFullYear() === d2.getFullYear() &&
           d1.getMonth() === d2.getMonth() &&
           d1.getDate() === d2.getDate();
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
    return d.getFullYear() === refDate.getFullYear() &&
           d.getMonth() === refDate.getMonth();
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
        if (!matchesSearch) return false;
    }

    // 2. Status Filter
    if (statusFilter.value !== 'all') {
        if (lead.status !== statusFilter.value) return false;
    }

    // 3. Date Filter
    if (dateFilter.value !== 'all') {
        const leadDate = new Date(lead.date);
        const today = new Date();
        
        if (dateFilter.value === 'today') {
            if (!isSameDay(leadDate, today)) return false;
        } else if (dateFilter.value === 'week') {
            if (!isThisWeek(leadDate, today)) return false;
        } else if (dateFilter.value === 'month') {
            if (!isThisMonth(leadDate, today)) return false;
        } else if (dateFilter.value === 'year') {
            if (!isThisYear(leadDate, today)) return false;
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
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(val);
};
const getStatusClass = (status: string) => {
    switch (status) {
        case 'New':
            return 'bg-muted text-muted-foreground border-transparent';
        case 'Contacted':
            return 'bg-accent text-accent-foreground border-transparent';
        case 'Qualified':
            return 'bg-secondary text-secondary-foreground border-transparent';
        case 'Proposal Sent':
            return 'bg-primary/10 text-primary border-transparent';
        case 'Won':
            return 'bg-primary text-primary-foreground border-transparent';
        case 'Lost':
            return 'bg-destructive/15 text-destructive dark:bg-destructive/25 border-transparent';
        default:
            return '';
    }
};

const openAddDialog = (status: Column['id'] = 'New') => {
    newLead.value = {
        name: '',
        company: '',
        email: '',
        value: 0,
        status: status,
        source: 'Website',
        rating: undefined,
    };
    isAddOpen.value = true;
};

const createLead = () => {
    if (!newLead.value.name) {
        toast.error('Lead Name is required');
        return;
    }

    const status = newLead.value.status || 'New';
    const targetCol = columns.value.find(c => c.id === status);
    if (!targetCol) return;

    const lead: Lead = {
        id: Date.now(),
        name: newLead.value.name,
        company: newLead.value.company || 'Unknown Inc.',
        email: newLead.value.email || '',
        status: status,
        value: Number(newLead.value.value) || 0,
        source: newLead.value.source || 'Website',
        date: new Date().toISOString().split('T')[0],
        rating: newLead.value.rating,
    };

    targetCol.leads.push(lead);
    persistBoard();
    toast.success(`Created Lead: ${lead.name}`);
    isAddOpen.value = false;
};
const openDetailsDialog = (lead: Lead) => {
    activeLead.value = { ...lead };
    isDetailsOpen.value = true;
};

const saveLeadDetails = () => {
    if (!activeLead.value.name) {
        toast.error('Lead Name is required');
        return;
    }

    const updated = { ...activeLead.value };
    updated.value = Number(updated.value) || 0;

    let found = false;
    for (const col of columns.value) {
        const idx = col.leads.findIndex(l => l.id === updated.id);
        if (idx !== -1) {
            if (col.id !== updated.status) {
                // Remove from old column, push to target column
                col.leads.splice(idx, 1);
                const targetCol = columns.value.find(c => c.id === updated.status);
                if (targetCol) {
                    targetCol.leads.push(updated);
                }
            } else {
                col.leads[idx] = updated;
            }
            found = true;
            break;
        }
    }

    if (found) {
        persistBoard();
        toast.success(`Updated Lead: ${updated.name}`);
    }
    isDetailsOpen.value = false;
};

const deleteLead = (id: number) => {
    let deleted = false;
    for (const col of columns.value) {
        const idx = col.leads.findIndex(l => l.id === id);
        if (idx !== -1) {
            col.leads.splice(idx, 1);
            deleted = true;
            break;
        }
    }

    if (deleted) {
        persistBoard();
        toast.success('Lead deleted');
    }
    isDetailsOpen.value = false;
};
</script>

<template>
    <Head title="Kanban" />

    <div class="flex flex-col gap-6 p-6 overflow-hidden kanban-container">
        <!-- Header Section -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between shrink-0">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-foreground">Kanban Board</h1>
                <p class="text-sm text-muted-foreground">Drag and Manage leads and track pipeline stages interactively.</p>
            </div>
            <div class="flex items-center gap-2 relative">
                <!-- Collapsible Search input -->
                <div 
                    class="relative transition-all duration-300 ease-in-out overflow-hidden flex items-center"
                    :class="[isSearchExpanded ? 'w-52 opacity-100' : 'w-0 opacity-0 pointer-events-none']"
                >
                    <Search class="absolute left-2.5 w-4 h-4 text-muted-foreground pointer-events-none" />
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
                    class="w-9 h-9 shrink-0 text-muted-foreground hover:text-foreground hover:bg-muted" 
                    @click="toggleSearch"
                    :class="{'text-foreground bg-muted': isSearchExpanded}"
                >
                    <X class="w-4 h-4" v-if="isSearchExpanded" />
                    <Search class="w-4 h-4" v-else />
                </Button>

                <!-- Date Filter Dropdown -->
                <Select v-model="dateFilter">
                    <SelectTrigger class="w-32 h-9 text-xs">
                        <SelectValue placeholder="Date Created" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" class="text-xs">All Time</SelectItem>
                        <SelectItem value="today" class="text-xs">Today</SelectItem>
                        <SelectItem value="week" class="text-xs">This Week</SelectItem>
                        <SelectItem value="month" class="text-xs">This Month</SelectItem>
                        <SelectItem value="year" class="text-xs">This Year</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Status Filter Dropdown -->
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-32 h-9 text-xs">
                        <SelectValue placeholder="Stage Filter" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" class="text-xs">All Stages</SelectItem>
                        <SelectItem v-for="col in columns" :key="col.id" :value="col.id" class="text-xs">
                            {{ col.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- New Lead Button -->
                <Button class="shrink-0 h-9 px-4 text-sm" @click="openAddDialog()">
                    <Plus class="mr-2 w-4 h-4" />
                    New Lead
                </Button>
            </div>
        </div>

        <!-- Board Wrapper -->
        <div class="flex flex-1 gap-3 overflow-x-auto pb-3 select-none items-stretch max-h-full custom-scrollbar h-full">
            <div
                v-for="col in columns"
                :key="col.id"
                class="w-56 min-w-56 max-h-full flex flex-col rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-sidebar/30 dark:bg-sidebar/10 shadow-sm border-t-2"
                :class="col.borderClass"
            >
                <!-- Column Header -->
                <div class="flex items-center justify-between p-2.5 border-b border-sidebar-border/50 shrink-0 bg-sidebar/50 dark:bg-sidebar/30 rounded-t-xl">
                    <div class="flex items-center gap-1.5">
                        <span :class="['w-2 h-2 rounded-full', col.indicatorClass]"></span>
                        <span class="font-semibold text-xs text-foreground leading-none">{{ col.name }}</span>
                        <Badge variant="secondary" class="h-4 px-1 text-2xs font-bold rounded-sm">
                            {{ getColumnLeadsCount(col) }}
                        </Badge>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-2xs font-bold text-muted-foreground">
                            {{ getColumnTotalValue(col) }}
                        </span>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="w-5 h-5 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground"
                            @click="openAddDialog(col.id)"
                        >
                            <Plus class="w-3 h-3" />
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
                    class="flex-1 flex flex-col gap-2 p-2 overflow-y-auto custom-scrollbar"
                    @change="(evt) => onDragChange(evt, col.id)"
                >
                    <div
                        v-for="lead in col.leads"
                        :key="lead.id"
                        v-show="matchesFilters(lead)"
                        class="group/card flex items-center justify-between p-2 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border bg-card hover:border-foreground/20 dark:hover:border-foreground/35 hover:shadow-2xs transition-all duration-200 cursor-grab active:cursor-grabbing"
                    >
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <!-- Drag Indicator (6 dots) -->
                            <div class="text-muted-foreground/45 hover:text-foreground shrink-0 p-0.5 rounded hover:bg-muted transition-colors">
                                <GripVertical class="w-3.5 h-3.5" />
                            </div>

                            <!-- Card Content (Compact details) -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 justify-between pr-1">
                                    <span class="font-medium text-xs text-foreground truncate max-w-28">
                                        {{ lead.name }}
                                    </span>
                                    <span class="text-xs font-bold text-success shrink-0">
                                        {{ formatCurrency(lead.value) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5 text-2xs text-muted-foreground">
                                    <span class="truncate max-w-24">{{ lead.company }}</span>
                                    <span v-if="lead.rating" :class="[
                                        'rating-badge-custom rounded-sm font-bold uppercase border shrink-0',
                                        lead.rating === 'warm' 
                                            ? 'bg-destructive/10 text-destructive border-destructive/20' 
                                            : 'bg-muted text-muted-foreground border-muted-foreground/15'
                                    ]">
                                        {{ lead.rating }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Open Option Button -->
                        <Button
                            variant="ghost"
                            size="icon"
                            class="no-drag w-6 h-6 rounded-md text-muted-foreground hover:text-foreground shrink-0 opacity-0 group-hover/card:opacity-100 focus:opacity-100 transition-opacity"
                            @click="openDetailsDialog(lead)"
                        >
                            <ExternalLink class="w-3 h-3" />
                        </Button>
                    </div>

                    <!-- Empty placeholder inside empty columns -->
                    <div
                        v-if="getColumnLeadsCount(col) === 0"
                        class="flex-1 flex flex-col items-center justify-center border border-dashed border-sidebar-border/50 rounded-lg p-4 text-center text-xs text-muted-foreground min-h-28"
                    >
                        <Briefcase class="w-4 h-4 mb-1 opacity-40" />
                        <span>No leads matching.</span>
                        <span class="text-2xs opacity-70">Drag cards here.</span>
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
                        Create a new lead. It will be added to the stage you selected.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="name" class="text-right">Name</Label>
                        <Input id="name" v-model="newLead.name" placeholder="John Doe" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="company" class="text-right">Company</Label>
                        <Input id="company" v-model="newLead.company" placeholder="Acme Inc." class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="email" class="text-right">Email</Label>
                        <Input id="email" v-model="newLead.email" type="email" placeholder="john@example.com" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="value" class="text-right">Value ($)</Label>
                        <Input id="value" v-model="newLead.value" type="number" placeholder="5000" class="col-span-3" />
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
                                        <SelectItem v-for="col in columns" :key="col.id" :value="col.id">
                                            {{ col.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="source" class="text-right">Source</Label>
                        <Input id="source" v-model="newLead.source" placeholder="Website" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="rating" class="text-right">Rating</Label>
                        <div class="col-span-3">
                            <Select v-model="newLead.rating">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select rating (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="cold">Cold</SelectItem>
                                        <SelectItem value="warm">Warm</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isAddOpen = false">Cancel</Button>
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
                        <Input id="edit-name" v-model="activeLead.name" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-company" class="text-right">Company</Label>
                        <Input id="edit-company" v-model="activeLead.company" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-email" class="text-right">Email</Label>
                        <Input id="edit-email" v-model="activeLead.email" type="email" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-value" class="text-right">Value ($)</Label>
                        <Input id="edit-value" v-model="activeLead.value" type="number" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-status" class="text-right">Stage</Label>
                        <div class="col-span-3">
                            <Select v-model="activeLead.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select a stage" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="col in columns" :key="col.id" :value="col.id">
                                            {{ col.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-source" class="text-right">Source</Label>
                        <Input id="edit-source" v-model="activeLead.source" class="col-span-3" />
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="edit-rating" class="text-right">Rating</Label>
                        <div class="col-span-3">
                            <Select v-model="activeLead.rating">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select rating (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="cold">Cold</SelectItem>
                                        <SelectItem value="warm">Warm</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4 text-xs text-muted-foreground">
                        <span class="text-right font-medium">Created</span>
                        <span class="col-span-3 pl-1">{{ activeLead.date }}</span>
                    </div>
                </div>

                <DialogFooter class="flex sm:justify-between items-center w-full gap-2">
                    <Button variant="destructive" class="gap-1.5 shrink-0" @click="deleteLead(activeLead.id)">
                        <Trash2 class="size-4" />
                        Delete
                    </Button>
                    <div class="flex items-center gap-2 w-full justify-end">
                        <Button variant="outline" @click="isDetailsOpen = false">Cancel</Button>
                        <Button @click="saveLeadDetails">Save Changes</Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
