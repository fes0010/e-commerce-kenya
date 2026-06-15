<!-- Visitors Section Vue Component -->
<v-dashboard-visitors>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.over-all-details />
</v-dashboard-visitors>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-visitors-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.over-all-details />
        </template>

        <template v-else>
            <!-- Stats Row -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex flex-wrap gap-4">

                    <!-- Unique Visitors -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/customers.svg') }}"
                                title="Unique Visitors"
                            >
                        </div>

                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_visitors.current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Unique Visitors
                            </p>

                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base"
                                    :class="[report.statistics.total_visitors.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold"
                                    :class="[report.statistics.total_visitors.progress < 0 ? 'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_visitors.progress).toFixed(2) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Page Views -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="h-[60px] max-h-[60px] w-full max-w-[60px] dark:mix-blend-exclusion dark:invert">
                            <img
                                src="{{ bagisto_asset('images/total-orders.svg') }}"
                                title="Page Views"
                            >
                        </div>

                        <div class="grid place-content-start gap-1">
                            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_page_views.current }}
                            </p>

                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Page Views
                            </p>

                            <div class="flex items-center gap-0.5">
                                <span
                                    class="text-base"
                                    :class="[report.statistics.total_page_views.progress < 0 ? 'icon-down-stat text-red-500 dark:!text-red-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-xs font-semibold"
                                    :class="[report.statistics.total_page_views.progress < 0 ? 'text-red-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_page_views.progress).toFixed(2) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Device Breakdown -->
                    <div class="flex min-w-[200px] flex-1 flex-col gap-1.5">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                            Device Breakdown
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <template v-for="(count, device) in report.statistics.device_breakdown" :key="device">
                                <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-2.5 py-1 text-xs capitalize text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                    @{{ device }}: <strong class="text-gray-800 dark:text-white">@{{ count }}</strong>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="my-4 border-t border-gray-100 dark:border-gray-800"></div>

                <!-- Recent Visitors -->
                <p class="mb-3 text-sm font-semibold text-gray-600 dark:text-gray-300">
                    Recent Visitors
                </p>

                <!-- Empty state -->
                <p
                    v-if="! report.statistics.recent_visitors || ! report.statistics.recent_visitors.length"
                    class="py-4 text-center text-xs text-gray-400"
                >
                    No visitors recorded yet.
                </p>

                <!-- Visitor rows -->
                <template v-else>
                    <div class="overflow-hidden rounded border border-gray-100 dark:border-gray-800">
                        <!-- Header row -->
                        <div class="grid grid-cols-4 gap-2 border-b border-gray-100 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-500 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                            <span>IP Address</span>
                            <span class="col-span-2">Page</span>
                            <span>Time</span>
                        </div>

                        <!-- Data rows -->
                        <div
                            v-for="(visitor, index) in report.statistics.recent_visitors"
                            :key="index"
                            class="grid grid-cols-4 gap-2 border-b border-gray-100 px-3 py-2 text-xs last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                        >
                            <span class="font-mono text-gray-700 dark:text-gray-300">
                                @{{ visitor.ip_address }}
                            </span>

                            <span
                                class="col-span-2 truncate text-gray-500 dark:text-gray-400"
                                :title="visitor.url"
                            >
                                @{{ visitor.url }}
                            </span>

                            <span class="text-gray-400">
                                @{{ visitor.visited_at }}
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-visitors', {
            template: '#v-dashboard-visitors-template',

            data() {
                return {
                    report: [],

                    isLoading: true,
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);

                    filters.type = 'visitors';

                    this.$axios.get("{{ route('admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {});
                }
            }
        });
    </script>
@endPushOnce
