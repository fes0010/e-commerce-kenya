<!-- Visitors Section Vue Component -->
<v-dashboard-visitors>
    <!-- Shimmer -->
    <div class="shimmer h-[300px] w-full rounded-xl"></div>
</v-dashboard-visitors>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-visitors-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <div class="shimmer h-[300px] w-full rounded-xl"></div>
        </template>

        <template v-else>
            <!-- Visitor Stats Row -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex flex-wrap gap-4">

                    <!-- Unique Visitors -->
                    <div class="flex min-w-[200px] flex-1 gap-2.5">
                        <div class="flex h-[60px] max-h-[60px] w-full max-w-[60px] items-center justify-center rounded-lg bg-blue-50 text-blue-500 dark:bg-blue-900/30">
                            <span class="icon-customers text-3xl"></span>
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
                        <div class="flex h-[60px] max-h-[60px] w-full max-w-[60px] items-center justify-center rounded-lg bg-purple-50 text-purple-500 dark:bg-purple-900/30">
                            <span class="icon-eye text-3xl"></span>
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
                            <template v-for="(count, device) in report.statistics.device_breakdown">
                                <div class="flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs dark:border-gray-700">
                                    <span
                                        :class="{
                                            'icon-laptop': device === 'desktop',
                                            'icon-mobile': device === 'mobile',
                                            'icon-tablet': device === 'tablet',
                                            'icon-cancel': device === 'unknown',
                                        }"
                                        class="text-sm text-gray-500"
                                    ></span>
                                    <span class="capitalize text-gray-700 dark:text-gray-300">@{{ device }}</span>
                                    <span class="font-semibold text-gray-800 dark:text-white">@{{ count }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Recent Visitors Table -->
                <div class="mt-4">
                    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-300">
                        Recent Visitors
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b text-left text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    <th class="pb-2 pr-4 font-semibold">IP Address</th>
                                    <th class="pb-2 pr-4 font-semibold">Page</th>
                                    <th class="pb-2 pr-4 font-semibold">Device</th>
                                    <th class="pb-2 font-semibold">Time</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="visitor in report.statistics.recent_visitors"
                                    class="border-b transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                >
                                    <td class="py-2 pr-4 font-mono text-gray-700 dark:text-gray-300">
                                        @{{ visitor.ip_address }}
                                    </td>

                                    <td class="max-w-[260px] truncate py-2 pr-4 text-gray-600 dark:text-gray-400" :title="visitor.url">
                                        @{{ visitor.url }}
                                    </td>

                                    <td class="py-2 pr-4 capitalize text-gray-600 dark:text-gray-400">
                                        @{{ visitor.device_type }}
                                    </td>

                                    <td class="py-2 text-gray-500 dark:text-gray-500">
                                        @{{ visitor.visited_at }}
                                    </td>
                                </tr>

                                <tr v-if="! report.statistics.recent_visitors.length">
                                    <td colspan="4" class="py-4 text-center text-gray-400">
                                        No visitors recorded yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
