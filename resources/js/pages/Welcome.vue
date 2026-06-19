<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
</script>

<template>
    <Head title="Welcome to Streamline CRM">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col overflow-hidden bg-[#FDFDFC] text-[#1b1b18] lg:flex-row dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <!-- Left Side: Welcome, Features, Actions, Footer -->
        <div
            class="relative z-10 flex min-h-screen flex-1 flex-col justify-between bg-white p-8 lg:p-16 xl:p-24 dark:bg-[#0f0f0f]"
        >
            <!-- Header -->
            <header
                class="mb-8 flex shrink-0 items-center justify-between lg:mb-0"
            >
                <div class="flex items-center gap-2.5">
                    <span
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-500 p-2 text-white shadow-lg shadow-indigo-500/30"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                    </span>
                    <span
                        class="text-lg font-bold tracking-tight text-foreground"
                        >Streamline CRM</span
                    >
                </div>
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-block rounded-lg border border-sidebar-border bg-sidebar px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-block rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700"
                        >
                            Log in
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Main Content Area -->
            <div
                class="flex max-w-lg flex-1 flex-col justify-center py-12 lg:py-0"
            >
                <h1
                    class="mb-4 text-4xl leading-tight font-black tracking-tight text-foreground lg:text-5xl lg:leading-[1.15]"
                >
                    Manage sales pipeline like a pro
                </h1>
                <p
                    class="mb-8 text-base leading-relaxed text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Streamline CRM helps high-performing teams close more deals
                    through visual pipeline tracking, real-time analytics, and
                    automated stages.
                </p>

                <!-- Features list -->
                <ul class="mb-10 flex flex-col gap-6">
                    <li class="flex items-start gap-4">
                        <span
                            class="shrink-0 rounded-xl bg-indigo-500/10 p-2.5 text-indigo-600 dark:text-indigo-400"
                        >
                            <!-- Columns icon -->
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"
                                />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold text-foreground">
                                Interactive Kanban Pipeline
                            </h3>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                Drag and drop leads between sales stages. Track
                                total values instantly.
                            </p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <span
                            class="shrink-0 rounded-xl bg-emerald-500/10 p-2.5 text-emerald-600 dark:text-emerald-400"
                        >
                            <!-- Users icon -->
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold text-foreground">
                                Prospect & Lead Insights
                            </h3>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                Maintain a clean log of companies, contact info,
                                ratings, and sources.
                            </p>
                        </div>
                    </li>
                </ul>

                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-colors hover:bg-indigo-700"
                    >
                        Go to Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="login()"
                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-colors hover:bg-indigo-700"
                    >
                        Get Started Now
                    </Link>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-8 shrink-0 text-xs text-muted-foreground lg:mt-0">
                &copy; {{ new Date().getFullYear() }} Streamline CRM. All rights
                reserved.
            </footer>
        </div>

        <!-- Right Side: Graphic Visualizer -->
        <div
            class="relative hidden min-h-screen flex-1 items-center justify-center overflow-hidden border-l border-white/5 bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 p-12 lg:flex"
        >
            <!-- Glow background effects -->
            <div
                class="absolute -top-20 -right-20 h-[500px] w-[500px] rounded-full bg-indigo-500/10 blur-[100px]"
            ></div>
            <div
                class="absolute -bottom-20 -left-20 h-[500px] w-[500px] rounded-full bg-purple-500/10 blur-[100px]"
            ></div>

            <div
                class="relative z-10 flex aspect-[4/3] w-full max-w-lg transform flex-col gap-4 overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-md transition-transform duration-300 hover:scale-[1.02]"
            >
                <div
                    class="flex items-center justify-between border-b border-white/10 pb-3"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-red-500/80"></span>
                        <span
                            class="h-3 w-3 rounded-full bg-yellow-500/80"
                        ></span>
                        <span
                            class="h-3 w-3 rounded-full bg-green-500/80"
                        ></span>
                        <span
                            class="ml-2 text-xs font-semibold tracking-wide text-white/50"
                            >Live Pipeline Summary</span
                        >
                    </div>
                    <span
                        class="rounded border border-indigo-500/20 bg-indigo-500/10 px-2 py-0.5 text-xs font-bold text-indigo-400"
                        >Active Stage</span
                    >
                </div>
                <div class="grid flex-1 grid-cols-3 gap-3">
                    <!-- Column 1 -->
                    <div
                        class="flex flex-col gap-3 rounded-xl border border-white/5 bg-white/5 p-3"
                    >
                        <span
                            class="text-[10px] font-bold tracking-widest text-white/40 uppercase"
                            >New Leads</span
                        >
                        <div
                            class="flex flex-col gap-2 rounded-lg border border-white/5 bg-white/5 p-2.5"
                        >
                            <div class="h-2.5 w-3/4 rounded bg-white/30"></div>
                            <div class="h-2 w-1/2 rounded bg-white/15"></div>
                        </div>
                        <div
                            class="flex flex-col gap-2 rounded-lg border border-white/5 bg-white/5 p-2.5 opacity-50"
                        >
                            <div class="h-2.5 w-5/6 rounded bg-white/30"></div>
                            <div class="h-2 w-2/3 rounded bg-white/15"></div>
                        </div>
                    </div>
                    <!-- Column 2 -->
                    <div
                        class="flex flex-col gap-3 rounded-xl border border-white/5 bg-white/5 p-3"
                    >
                        <span
                            class="text-[10px] font-bold tracking-widest text-white/40 uppercase"
                            >Proposal Sent</span
                        >
                        <div
                            class="group relative flex flex-col gap-2 overflow-hidden rounded-lg border border-indigo-500/30 bg-indigo-500/15 p-2.5 shadow-md"
                        >
                            <div
                                class="absolute -top-6 -right-6 h-12 w-12 rounded-full bg-indigo-500/20 blur-md"
                            ></div>
                            <div class="h-2.5 w-5/6 rounded bg-white/40"></div>
                            <div class="h-2 w-1/3 rounded bg-indigo-400"></div>
                        </div>
                    </div>
                    <!-- Column 3 -->
                    <div
                        class="flex flex-col gap-3 rounded-xl border border-white/5 bg-white/5 p-3"
                    >
                        <span
                            class="text-[10px] font-bold tracking-widest text-white/40 uppercase"
                            >Won Deals</span
                        >
                        <div
                            class="flex flex-col gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/15 p-2.5"
                        >
                            <div
                                class="h-2.5 w-2/3 rounded bg-emerald-300/80"
                            ></div>
                            <div
                                class="h-2 w-1/2 rounded bg-emerald-400/50"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
