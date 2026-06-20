<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Users,
    Target,
    CheckCircle2,
    DollarSign,
    TrendingUp,
    TrendingDown,
    Award,
    Zap,
    Activity,
    ArrowRight,
    Calendar,
    Filter,
    Layers,
    UserCheck,
    LineChart as LineIcon,
    BarChart3 as BarIcon,
    AlertCircle,
    ChevronRight,
    TrendingUp as TrendUpIcon
} from '@lucide/vue';
import { dashboard, kanban } from '@/routes';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';

// Define breadcrumbs for the main AppLayout
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Receive leads collection and sales team from the controller
const props = defineProps<{
    leads: any[];
    salesTeam: any[];
}>();

// Filter states
const dateFilter = ref<'all' | '30d' | '90d' | 'this-month'>('all');
const sourceFilter = ref<string>('all');
const userFilter = ref<string>('all');

// Extract unique sources for filter dropdown
const availableSources = computed(() => {
    const sources = props.leads.map(l => l.source).filter(Boolean);
    return ['all', ...new Set(sources)];
});

// Format currency in INR to match Kanban Board
const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(val);
};

// Format standard numbers
const formatNumber = (val: number) => {
    return new Intl.NumberFormat('en-IN').format(val);
};

// Filtered leads based on interactive selections
const filteredLeads = computed(() => {
    let list = props.leads || [];

    // Filter by Source
    if (sourceFilter.value !== 'all') {
        list = list.filter(l => l.source === sourceFilter.value);
    }

    // Filter by Date
    if (dateFilter.value !== 'all') {
        const now = new Date();
        const cutoff = new Date();
        if (dateFilter.value === '30d') {
            cutoff.setDate(now.getDate() - 30);
            list = list.filter(l => new Date(l.date) >= cutoff);
        } else if (dateFilter.value === '90d') {
            cutoff.setDate(now.getDate() - 90);
            list = list.filter(l => new Date(l.date) >= cutoff);
        } else if (dateFilter.value === 'this-month') {
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
            list = list.filter(l => new Date(l.date) >= startOfMonth);
        }
    }

    return list;
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const displayLeads = computed(() => {
    const list = filteredLeads.value;
    const user = page.props.auth?.user;
    if (user && user.role !== 'admin') {
        return list.filter(l => l.assigned_to === user.id);
    }
    if (userFilter.value !== 'all') {
        return list.filter(l => l.assigned_to === Number(userFilter.value));
    }
    return list;
});

// 4 top KPI cards calculations
const kpis = computed(() => {
    const list = displayLeads.value;

    const totalLeads = list.length;
    const qualifiedLeads = list.filter(l => l.status === 'Qualified').length;
    const wonDeals = list.filter(l => l.status === 'Won').length;
    const pipelineValue = list.filter(l => l.status !== 'Lost').reduce((sum, l) => sum + (l.value || 0), 0);

    return {
        totalLeads,
        qualifiedLeads,
        wonDeals,
        pipelineValue
    };
});

// Calculate percentages relative to prior month window (30d windows)
const trends = computed(() => {
    const now = new Date();
    let daysWindow = 30;

    if (dateFilter.value === '90d') daysWindow = 90;
    else if (dateFilter.value === 'this-month') {
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        daysWindow = Math.ceil((now.getTime() - startOfMonth.getTime()) / (1000 * 60 * 60 * 24));
        if (daysWindow <= 0) daysWindow = 30;
    }

    const currentPeriodStart = new Date();
    currentPeriodStart.setDate(now.getDate() - daysWindow);

    const previousPeriodStart = new Date();
    previousPeriodStart.setDate(now.getDate() - (daysWindow * 2));

    // Only slice by source/user
    let baseData = props.leads || [];
    const user = page.props.auth?.user;
    if (user && user.role !== 'admin') {
        baseData = baseData.filter(l => l.assigned_to === user.id);
    } else if (userFilter.value !== 'all') {
        baseData = baseData.filter(l => l.assigned_to === Number(userFilter.value));
    }
    if (sourceFilter.value !== 'all') {
        baseData = baseData.filter(l => l.source === sourceFilter.value);
    }

    const currentLeads: any[] = [];
    const previousLeads: any[] = [];

    baseData.forEach(l => {
        const d = new Date(l.date);
        if (d >= currentPeriodStart && d <= now) {
            currentLeads.push(l);
        } else if (d >= previousPeriodStart && d < currentPeriodStart) {
            previousLeads.push(l);
        }
    });

    const calculateTrend = (metric: 'total' | 'qualified' | 'won' | 'value') => {
        const getMetricVal = (leadsList: any[]) => {
            if (metric === 'total') return leadsList.length;
            if (metric === 'qualified') return leadsList.filter(l => l.status === 'Qualified').length;
            if (metric === 'won') return leadsList.filter(l => l.status === 'Won').length;
            if (metric === 'value') return leadsList.filter(l => l.status !== 'Lost').reduce((sum, l) => sum + (l.value || 0), 0);
            return 0;
        };

        const currVal = getMetricVal(currentLeads);
        const prevVal = getMetricVal(previousLeads);

        if (prevVal === 0) {
            return {
                percent: currVal > 0 ? 100 : 0,
                isPositive: currVal > 0,
                formatted: currVal > 0 ? '+100%' : '0%'
            };
        }

        const diff = currVal - prevVal;
        const percent = Math.round((diff / prevVal) * 100);

        return {
            percent: Math.abs(percent),
            isPositive: percent >= 0,
            formatted: percent >= 0 ? `+${percent}%` : `${percent}%`
        };
    };

    return {
        total: calculateTrend('total'),
        qualified: calculateTrend('qualified'),
        won: calculateTrend('won'),
        value: calculateTrend('value')
    };
});

// Funnel Calculations: Cumulative drop-off
const funnelStages = computed(() => {
    const list = displayLeads.value;

    const stages = [
        { id: 'New', label: 'New', statusKey: 'New', color: 'bg-muted-foreground/30' },
        { id: 'Contacted', label: 'Contacted', statusKey: 'Contacted', color: 'bg-accent/80' },
        { id: 'Qualified', label: 'Qualified', statusKey: 'Qualified', color: 'bg-secondary-foreground/40' },
        { id: 'Proposal', label: 'Proposal', statusKey: 'Proposal Sent', color: 'bg-primary/40' },
        { id: 'Won', label: 'Won', statusKey: 'Won', color: 'bg-primary' }
    ];

    const getStageIndex = (status: string) => {
        if (status === 'New') return 0;
        if (status === 'Contacted') return 1;
        if (status === 'Qualified') return 2;
        if (status === 'Proposal Sent') return 3;
        if (status === 'Won') return 4;
        return -1; // Lost
    };

    const stageCounts = stages.map((stage, idx) => {
        // A lead is counted in this stage if it reached this stage index or beyond (won/proposal sent have passed contacted/qualified)
        const cumulativeLeads = list.filter(l => {
            const lIdx = getStageIndex(l.status);
            return lIdx >= idx;
        });

        const count = cumulativeLeads.length;
        const value = cumulativeLeads.reduce((sum, l) => sum + (l.value || 0), 0);

        return {
            ...stage,
            count,
            value
        };
    });

    const firstStageCount = stageCounts[0]?.count || 1;

    return stageCounts.map((s, idx) => {
        const rate = firstStageCount > 0 ? Math.round((s.count / firstStageCount) * 100) : 0;
        return {
            ...s,
            conversionRate: rate
        };
    });
});

// Helper to get initials for avatar
const getInitials = (name: string) => {
    return name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'US';
};

// Cycle of background colors for avatars
const colors = [
    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    'bg-blue-500/10 text-blue-600 dark:text-blue-400',
    'bg-violet-500/10 text-violet-600 dark:text-violet-400',
    'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    'bg-rose-500/10 text-rose-600 dark:text-rose-400',
    'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400'
];

const getUserBg = (userId: number) => {
    return colors[userId % colors.length];
};

// Map of name -> title
const roleTitles: Record<string, string> = {
    'Sarah Jenkins': 'Enterprise Account Executive',
    'Alex Rivera': 'Senior Mid-Market AE',
    'Elena Rostova': 'Inbound Specialist',
    'Marcus Chen': 'Outbound Specialist',
};

const getRoleTitle = (name: string, role: string) => {
    if (roleTitles[name]) {
        return roleTitles[name];
    }
    return role === 'admin' ? 'Administrator' : 'Sales Representative';
};

// Process salesTeam dynamically from database
const salesTeam = computed(() => {
    return (props.salesTeam || []).map(member => ({
        id: member.id,
        name: member.name,
        email: member.email,
        role: getRoleTitle(member.name, member.role),
        avatar: getInitials(member.name),
        bg: getUserBg(member.id)
    }));
});

// Leaderboard calculations using database associations
const teamPerformance = computed(() => {
    const list = filteredLeads.value;

    const performance = salesTeam.value.map((member) => {
        // Filter leads assigned to this specific user ID
        const memberLeads = list.filter(l => l.assigned_to === member.id);

        // Active Leads (New, Contacted, Qualified, Proposal Sent)
        const activeLeads = memberLeads.filter(l => l.status !== 'Won' && l.status !== 'Lost').length;

        // Won Leads & Revenue
        const wonLeads = memberLeads.filter(l => l.status === 'Won');
        const revenue = wonLeads.reduce((sum, l) => sum + (l.value || 0), 0);

        // Conversion Rate (Won Deals / Total leads allocated)
        const totalLeads = memberLeads.length;
        const conversionRate = totalLeads > 0 ? Math.round((wonLeads.length / totalLeads) * 100) : 0;

        return {
            ...member,
            activeLeads,
            conversionRate,
            revenue,
            totalLeads
        };
    });

    // Sort by revenue descending
    return [...performance].sort((a, b) => b.revenue - a.revenue);
});

// Top salesperson highlight
const topPerformer = computed(() => {
    return teamPerformance.value[0];
});

// Smart Assignment Insights
const assignmentInsights = computed(() => {
    const list = teamPerformance.value;
    if (list.length === 0) return null;

    // Lowest active workload
    const lowestWorkload = [...list].sort((a, b) => a.activeLeads - b.activeLeads)[0];

    // Highest historical win rate
    const highestConversion = [...list].sort((a, b) => b.conversionRate - a.conversionRate)[0];

    // Recommendation logic: lowest workload first, tie-breaker: highest conversion rate
    const recommended = [...list].sort((a, b) => {
        if (a.activeLeads !== b.activeLeads) {
            return a.activeLeads - b.activeLeads;
        }
        return b.conversionRate - a.conversionRate;
    })[0];

    return {
        lowestWorkload,
        highestConversion,
        recommended
    };
});

// Generate dynamic activity timeline from lead creation dates and statuses
const recentActivities = computed(() => {
    const list = [...displayLeads.value];
    
    // Sort leads by date descending
    list.sort((a, b) => {
        const dateA = new Date(a.date).getTime();
        const dateB = new Date(b.date).getTime();
        if (dateB !== dateA) return dateB - dateA;
        return b.id - a.id;
    });

    return list.slice(0, 5).map(lead => {
        const salesperson = lead.assignee || { name: 'Unassigned' };
        
        let type: 'create' | 'assign' | 'status' | 'won' = 'create';
        let title = '';
        let desc = '';
        let color = '';

        if (lead.status === 'Won') {
            type = 'won';
            title = 'Deal Won';
            desc = `${salesperson.name} closed a ${formatCurrency(lead.value)} deal with ${lead.company}.`;
            color = 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
        } else if (lead.status === 'Proposal Sent') {
            type = 'status';
            title = 'Proposal Sent';
            desc = `${salesperson.name} sent a proposal of ${formatCurrency(lead.value)} to ${lead.company}.`;
            color = 'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
        } else if (lead.status === 'Qualified') {
            type = 'status';
            title = 'Lead Qualified';
            desc = `${lead.name} from ${lead.company} was qualified by ${salesperson.name}.`;
            color = 'bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400';
        } else if (lead.status === 'Contacted') {
            type = 'assign';
            title = 'Contact Established';
            desc = `${salesperson.name} contacted ${lead.name} at ${lead.company}.`;
            color = 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
        } else {
            type = 'create';
            title = 'Lead Created';
            desc = `New lead ${lead.name} from ${lead.source} was assigned to ${salesperson.name}.`;
            color = 'bg-gray-500/10 text-gray-600 dark:bg-gray-500/20 dark:text-gray-400';
        }

        const dateObj = new Date(lead.date);
        const diffTime = Math.abs(new Date().getTime() - dateObj.getTime());
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let relativeTime = '';
        if (diffDays <= 1) relativeTime = 'Today';
        else if (diffDays === 2) relativeTime = 'Yesterday';
        else relativeTime = `${diffDays} days ago`;

        return {
            id: lead.id,
            type,
            title,
            desc,
            relativeTime,
            color
        };
    });
});

// Group Won and Total leads into the last 6 calendar months for visual charting
const last6Months = computed(() => {
    const list = [];
    const now = new Date();
    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        list.push({
            name: d.toLocaleString('default', { month: 'short' }),
            year: d.getFullYear(),
            monthIndex: d.getMonth()
        });
    }
    return list;
});

const monthlyChartData = computed(() => {
    const baseLeads = displayLeads.value;
    const months = last6Months.value;

    return months.map(m => {
        const monthLeads = baseLeads.filter(l => {
            const d = new Date(l.date);
            return d.getFullYear() === m.year && d.getMonth() === m.monthIndex;
        });

        const wonLeads = monthLeads.filter(l => l.status === 'Won');
        const revenue = wonLeads.reduce((sum, l) => sum + (l.value || 0), 0);
        
        const totalCount = monthLeads.length;
        const wonCount = wonLeads.length;
        const conversionRate = totalCount > 0 ? Math.round((wonCount / totalCount) * 100) : 0;

        return {
            label: m.name,
            revenue,
            conversionRate,
            totalCount,
            wonCount
        };
    });
});

// Custom responsive SVG line chart coordinate generator for Revenue Trend
const hoveredRevenueIndex = ref<number | null>(null);

const revenueChartSvg = computed(() => {
    const data = monthlyChartData.value;
    const maxVal = Math.max(...data.map(d => d.revenue), 10000);

    const width = 500;
    const height = 180;
    const paddingLeft = 60;
    const paddingRight = 15;
    const paddingTop = 15;
    const paddingBottom = 25;

    const chartWidth = width - paddingLeft - paddingRight;
    const chartHeight = height - paddingTop - paddingBottom;

    const points = data.map((d, idx) => {
        const x = paddingLeft + (idx * (chartWidth / (data.length - 1 || 1)));
        const y = height - paddingBottom - ((d.revenue / maxVal) * chartHeight);
        return { x, y, value: d.revenue, label: d.label };
    });

    let linePath = '';
    let areaPath = '';

    if (points.length > 0) {
        linePath = `M ${points[0].x} ${points[0].y}`;
        for (let i = 1; i < points.length; i++) {
            const prev = points[i - 1];
            const curr = points[i];
            const cp1x = prev.x + (curr.x - prev.x) / 3;
            const cp1y = prev.y;
            const cp2x = prev.x + 2 * (curr.x - prev.x) / 3;
            const cp2y = curr.y;
            linePath += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${curr.x} ${curr.y}`;
        }
        areaPath = linePath + ` L ${points[points.length - 1].x} ${height - paddingBottom} L ${points[0].x} ${height - paddingBottom} Z`;
    }

    return {
        points,
        linePath,
        areaPath,
        width,
        height,
        paddingLeft,
        paddingRight,
        paddingTop,
        paddingBottom,
        chartWidth,
        chartHeight,
        maxVal
    };
});

const onRevenueMouseMove = (event: MouseEvent) => {
    const svg = event.currentTarget as SVGSVGElement;
    const rect = svg.getBoundingClientRect();
    const clientX = event.clientX - rect.left;
    const svgX = (clientX / rect.width) * 500;

    const pts = revenueChartSvg.value.points;
    if (pts.length === 0) return;

    let closestIdx = 0;
    let minDist = Math.abs(pts[0].x - svgX);

    for (let i = 1; i < pts.length; i++) {
        const dist = Math.abs(pts[i].x - svgX);
        if (dist < minDist) {
            minDist = dist;
            closestIdx = i;
        }
    }

    hoveredRevenueIndex.value = closestIdx;
};

// Custom rounded SVG columns coordinate generator for Conversion Rate
const hoveredConversionIndex = ref<number | null>(null);

const conversionChartSvg = computed(() => {
    const data = monthlyChartData.value;
    const maxVal = 100; // Conversion percentage cap

    const width = 500;
    const height = 180;
    const paddingLeft = 40;
    const paddingRight = 15;
    const paddingTop = 15;
    const paddingBottom = 25;

    const chartWidth = width - paddingLeft - paddingRight;
    const chartHeight = height - paddingTop - paddingBottom;

    const bars = data.map((d, idx) => {
        const barWidth = 24;
        const xCenter = paddingLeft + (idx * (chartWidth / (data.length - 1 || 1)));
        const x = xCenter - barWidth / 2;
        const barHeight = Math.max((d.conversionRate / maxVal) * chartHeight, 3); // min height 3px for visibility
        const y = height - paddingBottom - barHeight;

        return {
            x,
            y,
            width: barWidth,
            height: barHeight,
            value: d.conversionRate,
            label: d.label,
            total: d.totalCount,
            won: d.wonCount
        };
    });

    return {
        bars,
        width,
        height,
        paddingLeft,
        paddingRight,
        paddingTop,
        paddingBottom,
        chartWidth,
        chartHeight
    };
});

const onConversionMouseMove = (event: MouseEvent) => {
    const svg = event.currentTarget as SVGSVGElement;
    const rect = svg.getBoundingClientRect();
    const clientX = event.clientX - rect.left;
    const svgX = (clientX / rect.width) * 500;

    const bars = conversionChartSvg.value.bars;
    if (bars.length === 0) return;

    let closestIdx = 0;
    let minDist = Math.abs((bars[0].x + bars[0].width / 2) - svgX);

    for (let i = 1; i < bars.length; i++) {
        const center = bars[i].x + bars[i].width / 2;
        const dist = Math.abs(center - svgX);
        if (dist < minDist) {
            minDist = dist;
            closestIdx = i;
        }
    }

    hoveredConversionIndex.value = closestIdx;
};
</script>

<template>
    <Head title="CRM Analytics Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
        
        <!-- Header Section -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between shrink-0">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-foreground font-sans">
                    Sales Dashboard
                </h1>
                <p class="text-sm text-muted-foreground mt-1 max-w-xl">
                    Real-time performance, conversions, and intelligent assignment recommendations.
                </p>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- User Filter (Admin Only) -->
                <Select v-model="userFilter" v-if="isAdmin">
                    <SelectTrigger class="h-9 border border-sidebar-border bg-card/60 text-xs w-fit min-w-[140px] px-3 rounded-md shadow-2xs hover:bg-card/90 transition-colors flex items-center justify-between gap-2 focus:ring-0 focus:ring-offset-0">
                        <div class="flex items-center gap-2">
                            <Users class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <SelectValue placeholder="All Executives" />
                        </div>
                    </SelectTrigger>
                    <SelectContent class="text-xs">
                        <SelectItem value="all">All Executives</SelectItem>
                        <SelectItem
                            v-for="user in salesTeam"
                            :key="user.id"
                            :value="String(user.id)"
                        >
                            {{ user.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Date Filter -->
                <Select v-model="dateFilter">
                    <SelectTrigger class="h-9 border border-sidebar-border bg-card/60 text-xs w-fit min-w-[140px] px-3 rounded-md shadow-2xs hover:bg-card/90 transition-colors flex items-center justify-between gap-2 focus:ring-0 focus:ring-offset-0">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <SelectValue placeholder="All Time" />
                        </div>
                    </SelectTrigger>
                    <SelectContent class="text-xs">
                        <SelectItem value="all">All Time</SelectItem>
                        <SelectItem value="this-month">This Month</SelectItem>
                        <SelectItem value="30d">Last 30 Days</SelectItem>
                        <SelectItem value="90d">Last 90 Days</SelectItem>
                    </SelectContent>
                </Select>

                <!-- Source Filter -->
                <Select v-model="sourceFilter" v-if="availableSources.length > 1">
                    <SelectTrigger class="h-9 border border-sidebar-border bg-card/60 text-xs w-fit min-w-[140px] px-3 rounded-md shadow-2xs hover:bg-card/90 transition-colors flex items-center justify-between gap-2 focus:ring-0 focus:ring-offset-0">
                        <div class="flex items-center gap-2">
                            <Filter class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                            <SelectValue placeholder="All Sources" />
                        </div>
                    </SelectTrigger>
                    <SelectContent class="text-xs">
                        <SelectItem value="all">All Sources</SelectItem>
                        <SelectItem 
                            v-for="source in availableSources.filter(s => s !== 'all')" 
                            :key="source" 
                            :value="source"
                        >
                            {{ source }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Button size="sm" as-child class="text-xs font-medium shadow-2xs h-9 transition-transform active:scale-98">
                    <Link :href="kanban()">
                        Manage Pipeline
                        <ArrowRight class="ml-1.5 h-3.5 w-3.5" />
                    </Link>
                </Button>
            </div>
        </div>

        <!-- 4 Top KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- KPI 1: Total Leads -->
            <Card class="border border-sidebar-border/70 shadow-sm relative overflow-hidden bg-card/40 backdrop-blur-xs transition-all duration-300 hover:shadow-md dark:border-sidebar-border/80">
                <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Leads</span>
                    <div class="rounded-md bg-muted/60 p-2 text-muted-foreground">
                        <Users class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent class="pt-1">
                    <div class="text-2xl font-bold tracking-tight text-foreground">{{ formatNumber(kpis.totalLeads) }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-2xs">
                        <span 
                            class="inline-flex items-center gap-0.5 rounded-sm px-1.5 py-0.5 font-bold"
                            :class="[trends.total.isPositive ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive']"
                        >
                            <TrendingUp class="h-3 w-3" v-if="trends.total.isPositive" />
                            <TrendingDown class="h-3 w-3" v-else />
                            {{ trends.total.formatted }}
                        </span>
                        <span class="text-muted-foreground font-medium">vs prior window</span>
                    </div>
                </CardContent>
            </Card>

            <!-- KPI 2: Qualified Leads -->
            <Card class="border border-sidebar-border/70 shadow-sm relative overflow-hidden bg-card/40 backdrop-blur-xs transition-all duration-300 hover:shadow-md dark:border-sidebar-border/80">
                <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Qualified Leads</span>
                    <div class="rounded-md bg-muted/60 p-2 text-muted-foreground">
                        <Target class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent class="pt-1">
                    <div class="text-2xl font-bold tracking-tight text-foreground">{{ formatNumber(kpis.qualifiedLeads) }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-2xs">
                        <span 
                            class="inline-flex items-center gap-0.5 rounded-sm px-1.5 py-0.5 font-bold"
                            :class="[trends.qualified.isPositive ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive']"
                        >
                            <TrendingUp class="h-3 w-3" v-if="trends.qualified.isPositive" />
                            <TrendingDown class="h-3 w-3" v-else />
                            {{ trends.qualified.formatted }}
                        </span>
                        <span class="text-muted-foreground font-medium">vs prior window</span>
                    </div>
                </CardContent>
            </Card>

            <!-- KPI 3: Won Deals -->
            <Card class="border border-sidebar-border/70 shadow-sm relative overflow-hidden bg-card/40 backdrop-blur-xs transition-all duration-300 hover:shadow-md dark:border-sidebar-border/80">
                <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Won Deals</span>
                    <div class="rounded-md bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent class="pt-1">
                    <div class="text-2xl font-bold tracking-tight text-foreground">{{ formatNumber(kpis.wonDeals) }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-2xs">
                        <span 
                            class="inline-flex items-center gap-0.5 rounded-sm px-1.5 py-0.5 font-bold"
                            :class="[trends.won.isPositive ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive']"
                        >
                            <TrendingUp class="h-3 w-3" v-if="trends.won.isPositive" />
                            <TrendingDown class="h-3 w-3" v-else />
                            {{ trends.won.formatted }}
                        </span>
                        <span class="text-muted-foreground font-medium">vs prior window</span>
                    </div>
                </CardContent>
            </Card>

            <!-- KPI 4: Pipeline Value -->
            <Card class="border border-sidebar-border/70 shadow-sm relative overflow-hidden bg-card/40 backdrop-blur-xs transition-all duration-300 hover:shadow-md dark:border-sidebar-border/80">
                <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Pipeline Value</span>
                    <div class="rounded-md bg-muted/60 p-2 text-muted-foreground">
                        <DollarSign class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent class="pt-1">
                    <div class="text-2xl font-bold tracking-tight text-foreground truncate">{{ formatCurrency(kpis.pipelineValue) }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-2xs">
                        <span 
                            class="inline-flex items-center gap-0.5 rounded-sm px-1.5 py-0.5 font-bold"
                            :class="[trends.value.isPositive ? 'bg-success/10 text-success' : 'bg-destructive/10 text-destructive']"
                        >
                            <TrendingUp class="h-3 w-3" v-if="trends.value.isPositive" />
                            <TrendingDown class="h-3 w-3" v-else />
                            {{ trends.value.formatted }}
                        </span>
                        <span class="text-muted-foreground font-medium">vs prior window</span>
                    </div>
                </CardContent>
            </Card>

        </div>

        <!-- Sales Pipeline Funnel (Centerpiece) -->
        <Card class="border border-sidebar-border/70 bg-card/25 shadow-sm dark:border-sidebar-border/80">
            <CardHeader class="pb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="text-lg font-semibold flex items-center gap-2">
                            <Layers class="h-4 w-4 text-muted-foreground" />
                            Sales Pipeline Funnel
                        </CardTitle>
                        <CardDescription class="text-xs mt-1">
                            Cumulative pipeline velocity and conversions calculated from New to Won.
                        </CardDescription>
                    </div>
                    <Badge variant="outline" class="text-2xs font-semibold py-0.5 px-2 bg-muted/30">
                        {{ formatNumber(filteredLeads.length) }} Leads Analyzed
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col md:flex-row items-stretch gap-3 md:gap-2">
                    
                    <template v-for="(stage, idx) in funnelStages" :key="stage.id">
                        <!-- Funnel Stage Card -->
                        <div class="flex-1 flex flex-col rounded-xl border border-sidebar-border/60 bg-card/65 p-4 shadow-2xs relative overflow-hidden group hover:border-foreground/15 dark:border-sidebar-border/80 dark:hover:border-foreground/30 transition-all duration-200">
                            <!-- Background color overlay that visualizes conversion rate density -->
                            <div class="absolute inset-x-0 bottom-0 h-1 bg-muted/30">
                                <div 
                                    class="h-full bg-primary/75 dark:bg-primary transition-all duration-500" 
                                    :style="{ width: `${stage.conversionRate}%` }"
                                ></div>
                            </div>

                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold text-foreground/80">{{ stage.label }}</span>
                                <Badge 
                                    variant="secondary" 
                                    class="text-2xs font-bold px-1.5 h-5 rounded-sm"
                                    :class="[idx === 0 ? 'bg-primary/10 text-primary dark:bg-primary/20' : '']"
                                >
                                    {{ stage.conversionRate }}%
                                </Badge>
                            </div>

                            <div class="space-y-1">
                                <div class="text-lg font-bold tracking-tight text-foreground">
                                    {{ stage.count }} <span class="text-2xs font-normal text-muted-foreground">leads</span>
                                </div>
                                <div class="text-xs font-semibold text-success">
                                    {{ formatCurrency(stage.value) }}
                                </div>
                            </div>
                        </div>

                        <!-- Funnel connector (chevron/rate) between columns (not after last) -->
                        <div 
                            v-if="idx < funnelStages.length - 1" 
                            class="flex md:flex-col items-center justify-center py-1 md:py-0 px-2 shrink-0 text-muted-foreground/50 md:h-auto"
                        >
                            <!-- Arrow desktop vs mobile -->
                            <ChevronRight class="h-4 w-4 hidden md:block" />
                            <span class="text-2xs font-bold bg-muted/65 rounded-sm px-1.5 py-0.5 text-muted-foreground/75 md:-mt-1 md:-mb-1 z-10 block text-center">
                                {{ funnelStages[idx].count > 0 ? Math.round((funnelStages[idx+1].count / funnelStages[idx].count) * 100) : 0 }}%
                            </span>
                            <ChevronRight class="h-4 w-4 rotate-90 md:rotate-0 md:hidden mt-1" />
                        </div>
                    </template>

                </div>
            </CardContent>
        </Card>

        <!-- Revenue & Conversion Section Charts -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            
            <!-- Revenue Trend Chart -->
            <Card class="border border-sidebar-border/70 bg-card/25 shadow-sm dark:border-sidebar-border/80 relative">
                <CardHeader class="pb-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-sm font-semibold flex items-center gap-1.5">
                                <LineIcon class="h-4 w-4 text-muted-foreground" />
                                Revenue Growth
                            </CardTitle>
                            <CardDescription class="text-2xs">
                                Monthly value of closed deals (Won) over time.
                            </CardDescription>
                        </div>
                        <span class="text-xs font-bold text-success" v-if="hoveredRevenueIndex !== null">
                            {{ formatCurrency(revenueChartSvg.points[hoveredRevenueIndex].value) }} ({{ revenueChartSvg.points[hoveredRevenueIndex].label }})
                        </span>
                        <span class="text-xs font-semibold text-muted-foreground" v-else>
                            Overall Growth Trend
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="pt-2">
                    <div class="relative w-full h-[180px]">
                        <!-- Custom SVG Chart -->
                        <svg 
                            :viewBox="`0 0 ${revenueChartSvg.width} ${revenueChartSvg.height}`" 
                            class="w-full h-full overflow-visible"
                            @mousemove="onRevenueMouseMove"
                            @mouseleave="hoveredRevenueIndex = null"
                        >
                            <!-- Gradients -->
                            <defs>
                                <linearGradient id="revenueAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="var(--color-primary)" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="var(--color-primary)" stop-opacity="0.00" />
                                </linearGradient>
                            </defs>

                            <!-- Horizontal Grid Lines -->
                            <g stroke="var(--color-border)" stroke-width="1" stroke-dasharray="3,3" opacity="0.45">
                                <line 
                                    v-for="i in 3" 
                                    :key="i"
                                    :x1="revenueChartSvg.paddingLeft" 
                                    :y1="revenueChartSvg.paddingTop + (i - 1) * (revenueChartSvg.chartHeight / 2)" 
                                    :x2="revenueChartSvg.width - revenueChartSvg.paddingRight" 
                                    :y2="revenueChartSvg.paddingTop + (i - 1) * (revenueChartSvg.chartHeight / 2)" 
                                />
                            </g>

                            <!-- Left Axis Labels -->
                            <g fill="var(--color-muted-foreground)" font-size="8" font-family="var(--font-sans)" font-weight="600" opacity="0.85">
                                <text 
                                    :x="revenueChartSvg.paddingLeft - 8" 
                                    :y="revenueChartSvg.paddingTop + 3" 
                                    text-anchor="end"
                                >
                                    {{ formatCurrency(revenueChartSvg.maxVal) }}
                                </text>
                                <text 
                                    :x="revenueChartSvg.paddingLeft - 8" 
                                    :y="revenueChartSvg.paddingTop + (revenueChartSvg.chartHeight / 2) + 3" 
                                    text-anchor="end"
                                >
                                    {{ formatCurrency(revenueChartSvg.maxVal / 2) }}
                                </text>
                                <text 
                                    :x="revenueChartSvg.paddingLeft - 8" 
                                    :y="revenueChartSvg.height - revenueChartSvg.paddingBottom + 3" 
                                    text-anchor="end"
                                >
                                    ₹0
                                </text>
                            </g>

                            <!-- Area Path -->
                            <path 
                                :d="revenueChartSvg.areaPath" 
                                fill="url(#revenueAreaGrad)" 
                                v-if="revenueChartSvg.areaPath" 
                            />

                            <!-- Line Path -->
                            <path 
                                :d="revenueChartSvg.linePath" 
                                fill="none" 
                                stroke="var(--color-primary)" 
                                stroke-width="2.5" 
                                stroke-linecap="round"
                                v-if="revenueChartSvg.linePath" 
                            />

                            <!-- Points & Tooltip Line on Hover -->
                            <g v-if="revenueChartSvg.points.length > 0">
                                <!-- Vertical interactive hover tracker line -->
                                <line 
                                    v-if="hoveredRevenueIndex !== null"
                                    :x1="revenueChartSvg.points[hoveredRevenueIndex].x"
                                    :y1="revenueChartSvg.paddingTop"
                                    :x2="revenueChartSvg.points[hoveredRevenueIndex].x"
                                    :y2="revenueChartSvg.height - revenueChartSvg.paddingBottom"
                                    stroke="var(--color-primary)"
                                    stroke-width="1.5"
                                    stroke-dasharray="2,2"
                                />

                                <!-- Hover circle highlight -->
                                <circle 
                                    v-if="hoveredRevenueIndex !== null"
                                    :cx="revenueChartSvg.points[hoveredRevenueIndex].x"
                                    :cy="revenueChartSvg.points[hoveredRevenueIndex].y"
                                    r="5.5"
                                    fill="var(--color-background)"
                                    stroke="var(--color-primary)"
                                    stroke-width="2.5"
                                />
                                
                                <!-- Static smaller points -->
                                <circle 
                                    v-for="(pt, idx) in revenueChartSvg.points"
                                    :key="idx"
                                    :cx="pt.x"
                                    :cy="pt.y"
                                    r="3"
                                    fill="var(--color-primary)"
                                    class="transition-all duration-200"
                                    :opacity="hoveredRevenueIndex === idx ? 0 : 0.8"
                                />
                            </g>

                            <!-- Bottom Labels (Months) -->
                            <g fill="var(--color-muted-foreground)" font-size="9" font-family="var(--font-sans)" font-weight="600" text-anchor="middle" opacity="0.85">
                                <text 
                                    v-for="(pt, idx) in revenueChartSvg.points" 
                                    :key="idx"
                                    :x="pt.x" 
                                    :y="revenueChartSvg.height - 8"
                                >
                                    {{ pt.label }}
                                </text>
                            </g>
                        </svg>

                        <!-- Glass Tooltip Box -->
                        <div 
                            v-if="hoveredRevenueIndex !== null"
                            class="absolute z-10 bg-card/90 dark:bg-card/95 border border-sidebar-border shadow-md rounded-md p-2.5 text-2xs space-y-0.5 pointer-events-none transition-all duration-100"
                            :style="{
                                left: `${(revenueChartSvg.points[hoveredRevenueIndex].x / revenueChartSvg.width) * 100}%`,
                                top: `${Math.max((revenueChartSvg.points[hoveredRevenueIndex].y / revenueChartSvg.height) * 100 - 45, 5)}%`,
                                transform: 'translateX(-50%)'
                            }"
                        >
                            <div class="font-bold text-foreground">{{ revenueChartSvg.points[hoveredRevenueIndex].label }} {{ last6Months[hoveredRevenueIndex].year }}</div>
                            <div class="text-success font-semibold flex items-center gap-1">
                                <TrendingUp class="h-3 w-3" />
                                {{ formatCurrency(revenueChartSvg.points[hoveredRevenueIndex].value) }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Conversion Trend Chart -->
            <Card class="border border-sidebar-border/70 bg-card/25 shadow-sm dark:border-sidebar-border/80 relative">
                <CardHeader class="pb-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-sm font-semibold flex items-center gap-1.5">
                                <BarIcon class="h-4 w-4 text-muted-foreground" />
                                Win Rate Trend
                            </CardTitle>
                            <CardDescription class="text-2xs">
                                Lead-to-Won conversion percentage per month.
                            </CardDescription>
                        </div>
                        <span class="text-xs font-bold text-primary" v-if="hoveredConversionIndex !== null">
                            {{ conversionChartSvg.bars[hoveredConversionIndex].value }}% Win Rate
                        </span>
                        <span class="text-xs font-semibold text-muted-foreground" v-else>
                            Overall Win Rate
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="pt-2">
                    <div class="relative w-full h-[180px]">
                        <!-- Custom SVG Bar Chart -->
                        <svg 
                            :viewBox="`0 0 ${conversionChartSvg.width} ${conversionChartSvg.height}`" 
                            class="w-full h-full overflow-visible"
                            @mousemove="onConversionMouseMove"
                            @mouseleave="hoveredConversionIndex = null"
                        >
                            <!-- Horizontal Grid Lines -->
                            <g stroke="var(--color-border)" stroke-width="1" stroke-dasharray="3,3" opacity="0.45">
                                <line 
                                    v-for="i in 3" 
                                    :key="i"
                                    :x1="conversionChartSvg.paddingLeft" 
                                    :y1="conversionChartSvg.paddingTop + (i - 1) * (conversionChartSvg.chartHeight / 2)" 
                                    :x2="conversionChartSvg.width - conversionChartSvg.paddingRight" 
                                    :y2="conversionChartSvg.paddingTop + (i - 1) * (conversionChartSvg.chartHeight / 2)" 
                                />
                            </g>

                            <!-- Left Axis Labels -->
                            <g fill="var(--color-muted-foreground)" font-size="8" font-family="var(--font-sans)" font-weight="600" opacity="0.85">
                                <text :x="conversionChartSvg.paddingLeft - 8" :y="conversionChartSvg.paddingTop + 3" text-anchor="end">100%</text>
                                <text :x="conversionChartSvg.paddingLeft - 8" :y="conversionChartSvg.paddingTop + (conversionChartSvg.chartHeight / 2) + 3" text-anchor="end">50%</text>
                                <text :x="conversionChartSvg.paddingLeft - 8" :y="conversionChartSvg.height - conversionChartSvg.paddingBottom + 3" text-anchor="end">0%</text>
                            </g>

                            <!-- Column Bars -->
                            <g>
                                <rect 
                                    v-for="(bar, idx) in conversionChartSvg.bars"
                                    :key="idx"
                                    :x="bar.x"
                                    :y="bar.y"
                                    :width="bar.width"
                                    :height="bar.height"
                                    rx="4"
                                    class="transition-all duration-200 cursor-pointer"
                                    :fill="hoveredConversionIndex === idx ? 'var(--color-primary)' : 'var(--color-primary-foreground)'"
                                    :stroke="hoveredConversionIndex === idx ? 'none' : 'var(--color-primary)'"
                                    stroke-width="1.5"
                                    :opacity="hoveredConversionIndex === idx ? 0.95 : 0.45"
                                />
                            </g>

                            <!-- Bottom Labels (Months) -->
                            <g fill="var(--color-muted-foreground)" font-size="9" font-family="var(--font-sans)" font-weight="600" text-anchor="middle" opacity="0.85">
                                <text 
                                    v-for="(bar, idx) in conversionChartSvg.bars" 
                                    :key="idx"
                                    :x="bar.x + bar.width / 2" 
                                    :y="conversionChartSvg.height - 8"
                                >
                                    {{ bar.label }}
                                </text>
                            </g>
                        </svg>

                        <!-- Glass Tooltip Box -->
                        <div 
                            v-if="hoveredConversionIndex !== null"
                            class="absolute z-10 bg-card/90 dark:bg-card/95 border border-sidebar-border shadow-md rounded-md p-2.5 text-2xs space-y-0.5 pointer-events-none transition-all duration-100"
                            :style="{
                                left: `${((conversionChartSvg.bars[hoveredConversionIndex].x + conversionChartSvg.bars[hoveredConversionIndex].width / 2) / conversionChartSvg.width) * 100}%`,
                                top: `${Math.max((conversionChartSvg.bars[hoveredConversionIndex].y / conversionChartSvg.height) * 100 - 65, 5)}%`,
                                transform: 'translateX(-50%)'
                            }"
                        >
                            <div class="font-bold text-foreground">{{ conversionChartSvg.bars[hoveredConversionIndex].label }} {{ last6Months[hoveredConversionIndex].year }}</div>
                            <div class="font-semibold text-primary">Win Rate: {{ conversionChartSvg.bars[hoveredConversionIndex].value }}%</div>
                            <div class="text-muted-foreground font-medium">Won: {{ conversionChartSvg.bars[hoveredConversionIndex].won }} / Total: {{ conversionChartSvg.bars[hoveredConversionIndex].total }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>

        <!-- Team & Insights Section -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            <!-- Team Leaderboard (Col-span-2) -->
            <Card class="border border-sidebar-border/70 bg-card/25 shadow-sm lg:col-span-2 dark:border-sidebar-border/80">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="text-sm font-semibold flex items-center gap-1.5">
                                <Award class="h-4 w-4 text-muted-foreground" />
                                Sales Leaderboard
                            </CardTitle>
                            <CardDescription class="text-2xs">
                                Individual conversion rates and revenue generated.
                            </CardDescription>
                        </div>
                        <Badge variant="outline" class="text-2xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20" v-if="topPerformer">
                            👑 Best AE: {{ topPerformer.name }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="px-0 md:px-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-sidebar-border/60 text-2xs uppercase tracking-wider text-muted-foreground">
                                    <th class="py-2.5 px-4 font-semibold">Rep Name</th>
                                    <th class="py-2.5 px-4 font-semibold text-center">Active Leads</th>
                                    <th class="py-2.5 px-4 font-semibold text-center">Win Rate</th>
                                    <th class="py-2.5 px-4 font-semibold text-right">Revenue Closed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/40 text-xs">
                                <tr 
                                    v-for="(member, idx) in teamPerformance" 
                                    :key="member.name"
                                    class="group hover:bg-card/50 transition-colors"
                                    :class="[idx === 0 ? 'bg-primary/2 dark:bg-primary/1' : '']"
                                >
                                    <td class="py-3 px-4 flex items-center gap-3">
                                        <div 
                                            class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-2xs shadow-2xs border border-sidebar-border/50 shrink-0"
                                            :class="member.bg"
                                        >
                                            {{ member.avatar }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-foreground flex items-center gap-1">
                                                {{ member.name }}
                                                <Badge 
                                                    v-if="idx === 0" 
                                                    variant="secondary" 
                                                    class="text-3xs font-bold leading-none py-0 px-1 bg-amber-500/15 text-amber-600 dark:text-amber-500 hover:bg-amber-500/15 scale-90"
                                                >
                                                    TOP AE
                                                </Badge>
                                            </div>
                                            <div class="text-3xs text-muted-foreground font-medium">{{ member.role }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-medium text-foreground">
                                        {{ member.activeLeads }} <span class="text-3xs text-muted-foreground">leads</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="font-bold text-foreground">{{ member.conversionRate }}%</span>
                                            <div class="w-12 h-1.5 bg-muted/65 rounded-full overflow-hidden hidden sm:block">
                                                <div 
                                                    class="h-full bg-primary" 
                                                    :style="{ width: `${member.conversionRate}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-foreground">
                                        {{ formatCurrency(member.revenue) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Right Column Sidebar: Assignment Insights & Activities -->
            <div class="flex flex-col gap-6 lg:col-span-1">
                
                <!-- Smart Assignment Insights -->
                <Card class="border border-sidebar-border/70 bg-card/25 shadow-sm dark:border-sidebar-border/80">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-semibold flex items-center gap-1.5">
                            <Zap class="h-4 w-4 text-primary" />
                            Smart Assignment
                        </CardTitle>
                        <CardDescription class="text-2xs">
                            Data-driven workload balancing and recommendations.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-1" v-if="assignmentInsights">
                        <!-- Recommendation Alert -->
                        <div class="rounded-lg border border-primary/20 bg-primary/3 p-3 flex gap-2.5 items-start">
                            <UserCheck class="h-4 w-4 text-primary shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                <div class="text-2xs font-bold uppercase tracking-wider text-primary">Recommended Assignee</div>
                                <div class="text-xs font-semibold text-foreground">Assign next lead to {{ assignmentInsights.recommended.name }}</div>
                                <p class="text-3xs text-muted-foreground leading-relaxed">
                                    Selected due to lowest current workload ({{ assignmentInsights.recommended.activeLeads }} active leads) and strong closing metrics ({{ assignmentInsights.recommended.conversionRate }}% win rate).
                                </p>
                            </div>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 gap-2 text-2xs border-t border-sidebar-border/60 pt-3">
                            <div class="space-y-0.5">
                                <span class="text-muted-foreground font-semibold">Lowest Workload</span>
                                <div class="font-bold text-foreground truncate">{{ assignmentInsights.lowestWorkload.name }}</div>
                                <div class="text-3xs text-muted-foreground font-medium">Workload: {{ assignmentInsights.lowestWorkload.activeLeads }} active</div>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-muted-foreground font-semibold">Highest Closer</span>
                                <div class="font-bold text-foreground truncate">{{ assignmentInsights.highestConversion.name }}</div>
                                <div class="text-3xs text-muted-foreground font-medium">Win Rate: {{ assignmentInsights.highestConversion.conversionRate }}%</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity Timeline -->
                <Card class="border border-sidebar-border/70 bg-card/25 shadow-sm dark:border-sidebar-border/80 flex-1 flex flex-col">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-semibold flex items-center gap-1.5">
                            <Activity class="h-4 w-4 text-muted-foreground" />
                            Recent Activities
                        </CardTitle>
                        <CardDescription class="text-2xs">
                            Pipeline updates and won deal notifications.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-2 flex-1 relative">
                        <div class="relative pl-4 space-y-4 border-l border-sidebar-border/70 dark:border-sidebar-border ml-1.5 my-2">
                            <div 
                                v-for="act in recentActivities" 
                                :key="act.id" 
                                class="relative text-2xs space-y-0.5"
                            >
                                <!-- Circle timeline node -->
                                <span 
                                    class="absolute -left-6.5 top-0.5 w-3 h-3 rounded-full border-2 border-background flex items-center justify-center scale-90"
                                    :class="act.color.split(' ')[0]"
                                ></span>
                                
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-foreground">{{ act.title }}</span>
                                    <span class="text-3xs text-muted-foreground font-medium shrink-0">{{ act.relativeTime }}</span>
                                </div>
                                <p class="text-3xs text-muted-foreground leading-normal font-medium">
                                    {{ act.desc }}
                                </p>
                            </div>

                            <div v-if="recentActivities.length === 0" class="text-center py-6 text-2xs text-muted-foreground font-medium">
                                No recent activities in this filter.
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>

        </div>

    </div>
</template>
