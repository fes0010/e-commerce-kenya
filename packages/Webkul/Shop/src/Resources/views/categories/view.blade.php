<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="description"
        content="{{ trim($category->meta_description) != "" ? $category->meta_description : \Illuminate\Support\Str::limit(strip_tags($category->description), 120, '') }}"
    />

    <meta
        name="keywords"
        content="{{ $category->meta_keywords }}"
    />

    @if (core()->getConfigData('catalog.rich_snippets.categories.enable'))
        <script type="application/ld+json">
            {!! app('Webkul\Product\Helpers\SEO')->getCategoryJsonLd($category) !!}
        </script>
    @endif
@endPush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ trim($category->meta_title) != "" ? $category->meta_title : $category->name }}
    </x-slot>

    {!! view_render_event('bagisto.shop.categories.view.banner_path.before') !!}

    <!-- Hero Banner — uses category banner if set, otherwise auto-fetches first product image -->
    <v-category-banner
        category-name="{{ $category->name }}"
        @if($category->banner_path)
            banner-url="{{ $category->banner_url }}"
        @else
            banner-url=""
        @endif
        api-url="{{ route('shop.api.products.index', ['category_id' => $category->id, 'limit' => 1]) }}"
    ></v-category-banner>

    {!! view_render_event('bagisto.shop.categories.view.banner_path.after') !!}

    {!! view_render_event('bagisto.shop.categories.view.description.before') !!}

    @if (in_array($category->display_mode, [null, 'description_only', 'products_and_description']))
        @if ($category->description)
            <div class="container mt-[34px] px-[60px] max-lg:px-8 max-md:mt-4 max-md:px-4 max-md:text-sm max-sm:text-xs">
                {!! $category->description !!}
            </div>
        @endif
    @endif

    {!! view_render_event('bagisto.shop.categories.view.description.after') !!}

    @if (in_array($category->display_mode, [null, 'products_only', 'products_and_description']))
        <!-- Category Vue Component -->
        <v-category>
            <!-- Category Shimmer Effect -->
            <x-shop::shimmer.categories.view />
        </v-category>
    @endif

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-category-template"
        >
            <div class="container px-[60px] max-lg:px-8 max-md:px-4">
                <div class="flex items-start gap-10 max-lg:gap-5 md:mt-10">
                    <!-- Product Listing Filters -->
                    @include('shop::categories.filters')

                    <!-- Product Listing Container -->
                    <div class="flex-1">
                        <!-- Desktop Product Listing Toolbar -->
                        <div class="max-md:hidden">
                            @include('shop::categories.toolbar')
                        </div>

                        <!-- Product List Card Container -->
                        <div
                            class="mt-8 grid grid-cols-1 gap-6"
                            v-if="(filters.toolbar.applied.mode ?? filters.toolbar.default.mode) === 'list'"
                        >
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <x-shop::shimmer.products.cards.list count="12" />
                            </template>

                            <!-- Product Card Listing -->
                            {!! view_render_event('bagisto.shop.categories.view.list.product_card.before') !!}

                            <template v-else>
                                <template v-if="products.length">
                                    <x-shop::products.card
                                        ::mode="'list'"
                                        v-for="product in products"
                                    />
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                                        <img
                                            class="max-md:h-[100px] max-md:w-[100px]"
                                            src="{{ bagisto_asset('images/thank-you.png') }}"
                                            alt="@lang('shop::app.categories.view.empty')"
                                            loading="lazy"
                                            decoding="async"
                                        />

                                        <p
                                            class="text-xl max-md:text-sm"
                                            role="heading"
                                        >
                                            @lang('shop::app.categories.view.empty')
                                        </p>
                                    </div>
                                </template>
                            </template>

                            {!! view_render_event('bagisto.shop.categories.view.list.product_card.after') !!}
                        </div>

                        <!-- Product Grid Card Container -->
                        <div v-else class="mt-8 max-md:mt-5">
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <div class="grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-md:justify-items-center max-md:gap-x-4">
                                    <x-shop::shimmer.products.cards.grid count="12" />
                                </div>
                            </template>

                            {!! view_render_event('bagisto.shop.categories.view.grid.product_card.before') !!}

                            <!-- Product Card Listing -->
                            <template v-else>
                                <template v-if="products.length">
                                    <div class="grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-md:justify-items-center max-md:gap-x-4">
                                        <x-shop::products.card
                                            ::mode="'grid'"
                                            v-for="product in products"
                                        />
                                    </div>
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                                        <img
                                            class="max-md:h-[100px] max-md:w-[100px]"
                                            src="{{ bagisto_asset('images/thank-you.png') }}"
                                            alt="@lang('shop::app.categories.view.empty')"
                                            loading="lazy"
                                            decoding="async"
                                        />

                                        <p
                                            class="text-xl max-md:text-sm"
                                            role="heading"
                                        >
                                            @lang('shop::app.categories.view.empty')
                                        </p>
                                    </div>
                                </template>
                            </template>

                            {!! view_render_event('bagisto.shop.categories.view.grid.product_card.after') !!}
                        </div>

                        {!! view_render_event('bagisto.shop.categories.view.load_more_button.before') !!}

                        <!-- Load More Button -->
                        <button
                            class="secondary-button mx-auto mt-14 block w-max rounded-2xl px-11 py-3 text-center text-base max-md:rounded-lg max-sm:mt-6 max-sm:px-6 max-sm:py-1.5 max-sm:text-sm"
                            @click="loadMoreProducts"
                            v-if="links.next && ! loader"
                        >
                            @lang('shop::app.categories.view.load-more')
                        </button>

                        <button
                            v-else-if="links.next"
                            class="secondary-button mx-auto mt-14 block w-max rounded-2xl px-[74.5px] py-3.5 text-center text-base max-md:rounded-lg max-md:py-3 max-sm:mt-6 max-sm:px-[50.8px] max-sm:py-1.5"
                        >
                            <!-- Spinner -->
                            <img
                                class="h-5 w-5 animate-spin text-navyBlue"
                                src="{{ bagisto_asset('images/spinner.svg') }}"
                                alt="Loading"
                            />
                        </button>

                        {!! view_render_event('bagisto.shop.categories.view.grid.load_more_button.after') !!}
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-category', {
                template: '#v-category-template',

                data() {
                    return {
                        isMobile: window.innerWidth <= 767,

                        isLoading: true,

                        isDrawerActive: {
                            toolbar: false,

                            filter: false,
                        },

                        filters: {
                            toolbar: {
                                default: {},

                                applied: {},
                            },

                            filter: {},
                        },

                        products: [],

                        links: {},

                        loader: false,
                    }
                },

                computed: {
                    queryParams() {
                        let queryParams = Object.assign({}, this.filters.filter, this.filters.toolbar.applied);

                        return this.removeJsonEmptyValues(queryParams);
                    },

                    queryString() {
                        return this.jsonToQueryString(this.queryParams);
                    },
                },

                watch: {
                    queryParams() {
                        this.getProducts();
                    },

                    queryString() {
                        window.history.pushState({}, '', '?' + this.queryString);
                    },
                },

                methods: {
                    setFilters(type, filters) {
                        this.filters[type] = filters;
                    },

                    clearFilters(type, filters) {
                        this.filters[type] = {};
                    },

                    getProducts() {
                        this.isDrawerActive = {
                            toolbar: false,

                            filter: false,
                        };

                        document.body.style.overflow ='scroll';

                        this.isLoading = true;

                        this.$axios.get("{{ route('shop.api.products.index', ['category_id' => $category->id]) }}", {
                            params: this.queryParams
                        })
                            .then(response => {
                                this.isLoading = false;

                                this.products = response.data.data;

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                    },

                    loadMoreProducts() {
                        if (! this.links.next) {
                            return;
                        }

                        this.loader = true;

                        this.$axios.get(this.links.next)
                            .then(response => {
                                this.loader = false;

                                this.products = [...this.products, ...response.data.data];

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach(function (key) {
                            if ((! params[key] && params[key] !== undefined)) {
                                delete params[key];
                            }

                            if (Array.isArray(params[key])) {
                                params[key] = params[key].join(',');
                            }
                        });

                        return params;
                    },

                    jsonToQueryString(params) {
                        let parameters = new URLSearchParams();

                        for (const key in params) {
                            parameters.append(key, params[key]);
                        }

                        return parameters.toString();
                    }
                },
            });
        </script>

        <!-- Category Banner Vue Component -->
        <script type="text/x-template" id="v-category-banner-template">
            <div class="container mt-6 px-[60px] max-lg:px-8 max-md:mt-4 max-md:px-4">
                <div class="relative w-full overflow-hidden rounded-xl bg-gray-100"
                     style="aspect-ratio: 4/1;"
                     :style="'aspect-ratio: ' + aspectRatio"
                >
                    <!-- Loading shimmer -->
                    <div v-if="isLoading" class="absolute inset-0 animate-pulse bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200"></div>

                    <!-- Banner image -->
                    <img
                        v-if="!isLoading && resolvedImage"
                        :src="resolvedImage"
                        :alt="categoryName"
                        class="h-full w-full object-cover object-center transition-opacity duration-500"
                        :class="imageLoaded ? 'opacity-100' : 'opacity-0'"
                        @load="imageLoaded = true"
                        @error="onImageError"
                    />

                    <!-- Dark gradient overlay + category name -->
                    <div v-if="!isLoading && resolvedImage && imageLoaded"
                         class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 via-black/10 to-transparent p-6 max-md:p-4">
                        <h1 class="text-3xl font-bold text-white drop-shadow-lg max-md:text-xl max-sm:text-lg">
                            @{{ categoryName }}
                        </h1>
                    </div>

                    <!-- Fallback: no image at all -->
                    <div v-if="!isLoading && !resolvedImage"
                         class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 p-6">
                        <h1 class="text-3xl font-bold text-gray-700 max-md:text-xl">@{{ categoryName }}</h1>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-category-banner', {
                template: '#v-category-banner-template',

                props: {
                    categoryName: { type: String, default: '' },
                    bannerUrl:    { type: String, default: '' },
                    apiUrl:       { type: String, default: '' },
                },

                data() {
                    return {
                        isLoading: true,
                        resolvedImage: '',
                        imageLoaded: false,
                        aspectRatio: '4/1',
                    };
                },

                mounted() {
                    // Adjust aspect ratio per device
                    this.updateAspectRatio();
                    window.addEventListener('resize', this.updateAspectRatio);

                    if (this.bannerUrl) {
                        // Category already has a banner — use it directly
                        this.resolvedImage = this.bannerUrl;
                        this.isLoading = false;
                    } else if (this.apiUrl) {
                        // Fetch first product image as fallback
                        this.$axios.get(this.apiUrl)
                            .then(response => {
                                const products = response.data?.data || [];
                                if (products.length && products[0].base_image?.large_image_url) {
                                    this.resolvedImage = products[0].base_image.large_image_url;
                                } else {
                                    this.resolvedImage = '';
                                }
                            })
                            .catch(() => { this.resolvedImage = ''; })
                            .finally(() => { this.isLoading = false; });
                    } else {
                        this.resolvedImage = '';
                        this.isLoading = false;
                    }
                },

                beforeUnmount() {
                    window.removeEventListener('resize', this.updateAspectRatio);
                },

                methods: {
                    updateAspectRatio() {
                        const w = window.innerWidth;
                        if (w < 480) {
                            this.aspectRatio = '3/2'; // tallish on very small phones
                        } else if (w < 768) {
                            this.aspectRatio = '2/1'; // slightly taller on mobile
                        } else if (w < 1024) {
                            this.aspectRatio = '3/1'; // tablet
                        } else {
                            this.aspectRatio = '4/1'; // desktop wide banner
                        }
                    },
                    onImageError() {
                        this.resolvedImage = '';
                        this.imageLoaded = false;
                    },
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>
