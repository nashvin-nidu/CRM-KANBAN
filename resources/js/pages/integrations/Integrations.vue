<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    MessageSquare,
    FileSpreadsheet,
    FileText,
    Mail,
    Copy,
    Check,
    PlugZap,
    Info,
} from '@lucide/vue';
import { ref } from 'vue';
import { defineComponent, h } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { integrations } from '@/routes';

const FacebookIcon = defineComponent({
    render() {
        return h(
            'svg',
            {
                xmlns: 'http://www.w3.org/2000/svg',
                viewBox: '0 0 24 24',
                fill: 'none',
                stroke: 'currentColor',
                'stroke-width': '2',
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                class: 'w-[24px] h-[24px]',
            },
            [
                h('path', {
                    d: 'M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z',
                }),
            ],
        );
    },
});

const InstagramIcon = defineComponent({
    render() {
        return h(
            'svg',
            {
                xmlns: 'http://www.w3.org/2000/svg',
                viewBox: '0 0 24 24',
                fill: 'none',
                stroke: 'currentColor',
                'stroke-width': '2',
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                class: 'w-[24px] h-[24px]',
            },
            [
                h('rect', {
                    width: '20',
                    height: '20',
                    x: '2',
                    y: '2',
                    rx: '5',
                    ry: '5',
                }),
                h('path', {
                    d: 'M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z',
                }),
                h('line', { x1: '17.5', x2: '17.51', y1: '6.5', y2: '6.5' }),
            ],
        );
    },
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Integrations',
                href: integrations(),
            },
        ],
    },
});

interface Integration {
    id: string;
    title: string;
    description: string;
    icon: any;
    webhookUrl?: string;
    verifyToken?: string;
    emailAddress?: string;
    scriptCode?: string;
    steps: string[];
}

const integrationsList = ref<Integration[]>([
    {
        id: 'whatsapp_twilio',
        title: 'WhatsApp / Twilio',
        description:
            'Sync customer chats and incoming messaging leads into your pipeline stages.',
        icon: MessageSquare,
        webhookUrl: 'https://api.crm-kanban.test/v1/webhooks/twilio',
        steps: [
            'Log into your Twilio Sandbox Console or Meta Developer Dashboard.',
            'Navigate to the WhatsApp Sandbox settings or WhatsApp configuration settings.',
            'Locate the "When a message comes in" Webhook URL input field.',
            'Copy the webhook URL below and paste it into the Twilio/WhatsApp configuration.',
        ],
    },
    {
        id: 'google_forms',
        title: 'Google Forms',
        description:
            'Sync new Google Form survey or enquiry submissions directly to Kanban.',
        icon: FileText,
        scriptCode: `function onSubmit(e) {
  var form = FormApp.getActiveForm();
  var responses = form.getResponses();
  var lastResponse = responses[responses.length - 1];
  var itemResponses = lastResponse.getItemResponses();
  var leadData = {
    name: itemResponses[0].getResponse(),
    email: itemResponses[1].getResponse(),
    company: itemResponses[2] ? itemResponses[2].getResponse() : 'Inbound Google Form',
    value: itemResponses[3] ? Number(itemResponses[3].getResponse()) : 5000
  };
  UrlFetchApp.fetch('https://api.crm-kanban.test/v1/webhooks/google-form', {
    method: 'post',
    contentType: 'application/json',
    payload: JSON.stringify(leadData)
  });
}`,
        steps: [
            'Open your Google Form editor.',
            'Click the three dots in the top-right corner and select "Script Editor" to open Google Apps Script.',
            'Clear any existing script template and paste the script code displayed below.',
            'Replace parameters if necessary, save, and add an "On Form Submit" trigger in the dashboard.',
        ],
    },
    {
        id: 'facebook_ads',
        title: 'Facebook Lead Ads',
        description:
            'Retrieve and create leads instantly from active Meta Lead Generation campaigns.',
        icon: FacebookIcon,
        webhookUrl: 'https://api.crm-kanban.test/v1/webhooks/facebook',
        verifyToken: 'fb_crm_verify_token_2026',
        steps: [
            'Navigate to your Meta Apps Developer Console.',
            'Add the "Webhooks" product to your Meta application.',
            'Subscribe to "Page Lead Generation" fields.',
            'Copy the Webhook URL and the Verify Token below, then paste them in the verification popup.',
        ],
    },
    {
        id: 'instagram_ads',
        title: 'Instagram Lead Ads',
        description:
            'Capture leads directly from Instagram Stories, Feed Ads, and DM automation.',
        icon: InstagramIcon,
        webhookUrl: 'https://api.crm-kanban.test/v1/webhooks/instagram',
        verifyToken: 'ig_crm_verify_token_2026',
        steps: [
            'Ensure your Instagram account is switched to a Professional account and linked to a Facebook page.',
            'Open your Meta Developer Portal and select your integration app.',
            'Configure webhook subscription settings for LeadGen events.',
            'Provide the Webhook URL and Verification Token below to enable automated Instagram lead capture.',
        ],
    },
    {
        id: 'google_sheets',
        title: 'Google Sheets Sync',
        description:
            'Import or export pipelines automatically between rows and Kanban boards.',
        icon: FileSpreadsheet,
        steps: [
            'Enable the Google Sheets API in your Google Cloud Developer Console.',
            'Generate a Service Account JSON credential key from the IAM panel.',
            'Share your target Google Spreadsheet with editing permission to: sheet-sync@crm-kanban.iam.gserviceaccount.com',
            'Paste your Spreadsheet ID in the settings form inside the details modal to begin synchronization.',
        ],
    },
    {
        id: 'email_capture',
        title: 'Email Capture',
        description:
            'Auto-create opportunities by forwarding lead alerts to a secure inbox.',
        icon: Mail,
        emailAddress: 'inbox-leads-capture@crm-kanban.test',
        steps: [
            'Copy the custom inbound routing email address shown below.',
            'Go to your external mail client or CRM mail rules panel.',
            'Set up a mail forwarding rule to forward lead notifications to this unique email address.',
            'The inbound parser will extract the body details to automatically create Lead records.',
        ],
    },
]);

const activeIntegration = ref<Integration | null>(null);
const isDialogOpen = ref(false);
const copiedField = ref<string | null>(null);

const openConfig = (integration: Integration) => {
    activeIntegration.value = { ...integration };
    isDialogOpen.value = true;
    copiedField.value = null;
};

const copyText = (text: string, fieldKey: string) => {
    if (!navigator.clipboard) {
        toast.error('Clipboard API not supported in this browser.');

        return;
    }

    navigator.clipboard.writeText(text);
    copiedField.value = fieldKey;
    toast.success('Copied to clipboard!');
    setTimeout(() => {
        if (copiedField.value === fieldKey) {
            copiedField.value = null;
        }
    }, 2000);
};
</script>

<template>
    <Head title="Integrations" />

    <div
        class="custom-scrollbar flex h-full flex-1 flex-col gap-6 overflow-y-auto p-6"
    >
        <!-- Header -->
        <div
            class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="flex items-center gap-2 text-3xl font-semibold tracking-tight text-foreground"
                >
                    <PlugZap class="h-7 w-7 text-primary" />
                    Integrations
                </h1>
                <p class="text-sm text-muted-foreground">
                    Access setup guides and configurations to connect your
                    workflow channels.
                </p>
            </div>
        </div>

        <!-- Flex Wrap Row for Compact Cards -->
        <div class="flex flex-wrap gap-6">
            <Card
                v-for="item in integrationsList"
                :key="item.id"
                class="group/card flex min-h-56 w-56 flex-col justify-between rounded-xl border border-sidebar-border/70 bg-sidebar/20 transition-all duration-300 hover:border-foreground/20 hover:shadow-2xs dark:border-sidebar-border dark:bg-sidebar/5 dark:hover:border-foreground/35"
            >
                <CardHeader class="p-4 pb-3">
                    <div class="flex items-start justify-between">
                        <div
                            class="shrink-0 rounded-lg border border-sidebar-border/50 bg-card p-2.5 text-primary shadow-sm transition-transform duration-300 group-hover/card:scale-105"
                        >
                            <component
                                :is="item.icon"
                                class="h-6 w-6 text-foreground"
                            />
                        </div>
                    </div>
                    <CardTitle
                        class="mt-4 text-base font-semibold text-foreground"
                        >{{ item.title }}</CardTitle
                    >
                    <CardDescription
                        class="mt-1 line-clamp-3 min-h-12 text-xs leading-relaxed text-muted-foreground"
                    >
                        {{ item.description }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-1 items-end px-4 pt-0 pb-4">
                    <div
                        class="flex w-full items-center justify-between border-t border-sidebar-border/40 pt-3"
                    >
                        <span
                            class="text-2xs font-semibold tracking-wider text-muted-foreground/75 uppercase"
                        >
                            {{
                                item.webhookUrl
                                    ? 'Webhook-Based'
                                    : item.emailAddress
                                      ? 'Email-Based'
                                      : 'API-Based'
                            }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 px-3 text-xs font-medium"
                            @click="openConfig(item)"
                        >
                            Documentation
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Configuration Dialog -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent
                class="dialog-content-scroll custom-scrollbar overflow-y-auto rounded-xl p-6 sm:max-w-lg"
            >
                <DialogHeader v-if="activeIntegration">
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-lg border border-sidebar-border/50 bg-muted p-2"
                        >
                            <component
                                :is="activeIntegration.icon"
                                class="h-5 w-5 text-foreground"
                            />
                        </div>
                        <div>
                            <DialogTitle
                                class="flex items-center gap-2 text-lg font-semibold text-foreground"
                            >
                                {{ activeIntegration.title }} Setup
                            </DialogTitle>
                            <DialogDescription
                                class="text-xs text-muted-foreground"
                            >
                                Follow the steps below to integrate this
                                service.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div v-if="activeIntegration" class="min-w-0 space-y-6 py-4">
                    <!-- Setup Steps -->
                    <div class="space-y-3">
                        <h3
                            class="flex items-center gap-1.5 text-sm font-semibold text-foreground"
                        >
                            <Info class="h-4 w-4 shrink-0 text-primary" />
                            Setup Instructions
                        </h3>
                        <ol
                            class="list-inside list-decimal space-y-2 rounded-lg border border-sidebar-border/40 bg-muted/30 p-3.5 text-xs text-muted-foreground"
                        >
                            <li
                                v-for="(step, idx) in activeIntegration.steps"
                                :key="idx"
                                class="leading-relaxed"
                            >
                                {{ step }}
                            </li>
                        </ol>
                    </div>

                    <!-- Webhook / Config Inputs -->
                    <div
                        class="min-w-0 space-y-4 border-t border-sidebar-border/50 pt-2"
                    >
                        <h3
                            class="flex items-center gap-1.5 text-sm font-semibold text-foreground"
                        >
                            <PlugZap class="h-4 w-4 shrink-0 text-primary" />
                            Connection Parameters
                        </h3>

                        <!-- Webhook URL -->
                        <div
                            v-if="activeIntegration.webhookUrl"
                            class="min-w-0 space-y-1.5"
                        >
                            <label
                                class="text-2xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Webhook URL</label
                            >
                            <div class="flex min-w-0 items-center gap-2">
                                <div
                                    class="flex-1 truncate rounded-lg border border-sidebar-border/60 bg-muted p-2 font-mono text-xs select-all"
                                >
                                    {{ activeIntegration.webhookUrl }}
                                </div>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8 shrink-0 hover:bg-muted"
                                    @click="
                                        copyText(
                                            activeIntegration.webhookUrl!,
                                            'webhook',
                                        )
                                    "
                                >
                                    <Check
                                        class="h-4 w-4 text-success"
                                        v-if="copiedField === 'webhook'"
                                    />
                                    <Copy class="h-4 w-4" v-else />
                                </Button>
                            </div>
                        </div>

                        <!-- Verification Token -->
                        <div
                            v-if="activeIntegration.verifyToken"
                            class="min-w-0 space-y-1.5"
                        >
                            <label
                                class="text-2xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Verify Token</label
                            >
                            <div class="flex min-w-0 items-center gap-2">
                                <div
                                    class="flex-1 truncate rounded-lg border border-sidebar-border/60 bg-muted p-2 font-mono text-xs select-all"
                                >
                                    {{ activeIntegration.verifyToken }}
                                </div>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8 shrink-0 hover:bg-muted"
                                    @click="
                                        copyText(
                                            activeIntegration.verifyToken!,
                                            'token',
                                        )
                                    "
                                >
                                    <Check
                                        class="h-4 w-4 text-success"
                                        v-if="copiedField === 'token'"
                                    />
                                    <Copy class="h-4 w-4" v-else />
                                </Button>
                            </div>
                        </div>

                        <!-- Routing Email Address -->
                        <div
                            v-if="activeIntegration.emailAddress"
                            class="min-w-0 space-y-1.5"
                        >
                            <label
                                class="text-2xs font-bold tracking-wider text-muted-foreground uppercase"
                                >Inbound Email Address</label
                            >
                            <div class="flex min-w-0 items-center gap-2">
                                <div
                                    class="flex-1 truncate rounded-lg border border-sidebar-border/60 bg-muted p-2 font-mono text-xs select-all"
                                >
                                    {{ activeIntegration.emailAddress }}
                                </div>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8 shrink-0 hover:bg-muted"
                                    @click="
                                        copyText(
                                            activeIntegration.emailAddress!,
                                            'email',
                                        )
                                    "
                                >
                                    <Check
                                        class="h-4 w-4 text-success"
                                        v-if="copiedField === 'email'"
                                    />
                                    <Copy class="h-4 w-4" v-else />
                                </Button>
                            </div>
                        </div>

                        <!-- Apps Script Code block -->
                        <div
                            v-if="activeIntegration.scriptCode"
                            class="min-w-0 space-y-1.5"
                        >
                            <div class="flex items-center justify-between">
                                <label
                                    class="text-2xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >Google Apps Script Code</label
                                >
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 px-2 text-xs text-primary"
                                    @click="
                                        copyText(
                                            activeIntegration.scriptCode!,
                                            'script',
                                        )
                                    "
                                >
                                    <Check
                                        class="mr-1 h-3 w-3 text-success"
                                        v-if="copiedField === 'script'"
                                    />
                                    <Copy class="mr-1 h-3 w-3" v-else />
                                    Copy Code
                                </Button>
                            </div>
                            <pre
                                class="code-pre-max custom-scrollbar overflow-x-auto rounded-lg border border-sidebar-border/60 bg-muted p-3.5 font-mono text-xs leading-relaxed select-all"
                            ><code>{{ activeIntegration.scriptCode }}</code></pre>
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="mt-2 flex w-full items-center gap-2 border-t border-sidebar-border/50 pt-4 sm:justify-end"
                >
                    <Button
                        class="h-9 px-4 text-sm"
                        @click="isDialogOpen = false"
                        >Close documentation</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
