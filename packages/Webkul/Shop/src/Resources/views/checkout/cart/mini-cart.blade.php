<!-- Mini Cart Vue Component -->
<v-mini-cart>
    <span
        class="icon-cart cursor-pointer text-2xl"
        role="button"
        aria-label="@lang('shop::app.checkout.cart.mini-cart.shopping-cart')"
    ></span>
</v-mini-cart>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-mini-cart-template"
    >
        {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.before') !!}

        @if (core()->getConfigData('sales.checkout.mini_cart.display_mini_cart'))
            <x-shop::drawer>
                <!-- Drawer Toggler -->
                <x-slot:toggle>
                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.toggle.before') !!}

                    <span class="relative">
                        <span
                            class="icon-cart cursor-pointer text-2xl"
                            role="button"
                            aria-label="@lang('shop::app.checkout.cart.mini-cart.shopping-cart')"
                            tabindex="0"
                            @click="getCart"
                        ></span>

                        @if (core()->getConfigData('sales.checkout.my_cart.summary') == 'display_item_quantity')
                            <span
                                class="absolute -top-4 rounded-[44px] bg-navyBlue px-2 py-1.5 text-xs font-semibold leading-[9px] text-white ltr:left-5 rtl:right-5 max-md:ltr:left-4 max-md:rtl:right-4"
                                v-if="cart?.items_qty"
                            >
                                @{{ cart.items_qty }}
                            </span>
                        @else
                            <span
                                class="absolute -top-4 rounded-[44px] bg-navyBlue px-2 py-1.5 text-xs font-semibold leading-[9px] text-white ltr:left-5 rtl:right-5 max-md:px-2 max-md:py-1.5 max-md:ltr:left-4 max-md:rtl:right-4"
                                v-if="cart?.items_count"
                            >
                                @{{ cart.items_count }}
                            </span>
                        @endif
                    </span>

                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.toggle.after') !!}
                </x-slot>

                <!-- Drawer Header -->
                <x-slot:header>
                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.header.before') !!}

                    <div class="flex items-center justify-between">
                        <p class="text-2xl font-medium max-md:text-xl max-sm:text-xl">
                            @lang('shop::app.checkout.cart.mini-cart.shopping-cart')
                        </p>
                    </div>

                    <p class="text-base max-md:text-zinc-500 max-sm:text-xs">
                        {{ core()->getConfigData('sales.checkout.mini_cart.offer_info')}}
                    </p>

                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.header.after') !!}
                </x-slot>

                <!-- Drawer Content -->
                <x-slot:content>
                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.before') !!}

                    <!-- Cart Item Listing -->
                    <div
                        class="mt-9 grid gap-8 max-md:mt-3 max-md:gap-4"
                        v-if="cart?.items?.length"
                    >
                        <div
                            class="flex gap-x-4 max-md:gap-x-3"
                            v-for="item in cart?.items"
                        >
                            <!-- Cart Item Image -->
                            {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.image.before') !!}

                            <div class="flex-shrink-0">
                                <a :href="'{{ route('shop.product_or_category.index', ':slug') }}'.replace(':slug', item.product_url_key)">
                                    <img
                                        :src="item.base_image.small_image_url"
                                        class="w-24 h-24 rounded-xl object-cover max-md:w-[72px] max-md:h-[72px] max-sm:w-16 max-sm:h-16"
                                    />
                                </a>
                            </div>

                            {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.image.after') !!}

                            <!-- Cart Item Information -->
                            <div class="flex flex-1 flex-col gap-y-1.5 min-w-0">
                                <!-- Name + Price row -->
                                <div class="flex items-start justify-between gap-2">

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.name.before') !!}

                                    <a
                                        class="min-w-0 flex-1"
                                        :href="'{{ route('shop.product_or_category.index', ':slug') }}'.replace(':slug', item.product_url_key)"
                                    >
                                        <p class="text-sm font-medium leading-snug line-clamp-2 max-sm:text-xs">
                                            @{{ item.name }}
                                        </p>
                                    </a>

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.name.after') !!}

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.price.before') !!}

                                    <template v-if="displayTax.prices == 'including_tax'">
                                        <p class="flex-shrink-0 text-sm font-semibold max-sm:text-xs">
                                            @{{ item.formatted_price_incl_tax }}
                                        </p>
                                    </template>

                                    <template v-else-if="displayTax.prices == 'both'">
                                        <p class="flex-shrink-0 flex flex-col text-sm font-semibold max-sm:text-xs">
                                            @{{ item.formatted_price_incl_tax }}
                                            <span class="text-xs font-normal text-zinc-400 max-sm:text-[10px]">
                                                @lang('shop::app.checkout.cart.mini-cart.excl-tax')
                                                <span class="font-medium text-black">@{{ item.formatted_price }}</span>
                                            </span>
                                        </p>
                                    </template>

                                    <template v-else>
                                        <p class="flex-shrink-0 text-sm font-semibold max-sm:text-xs">
                                            @{{ item.formatted_price }}
                                        </p>
                                    </template>

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.price.after') !!}
                                </div>

                                <!-- Cart Item Options Container -->
                                <div
                                    class="select-none max-sm:gap-y-0.5"
                                    v-if="item.options.length"
                                >

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.product_details.before') !!}

                                    <!-- Details Toggler -->
                                    <p
                                        class="flex cursor-pointer items-center gap-x-2 text-xs text-zinc-500"
                                        @click="item.option_show = ! item.option_show"
                                    >
                                        @lang('shop::app.checkout.cart.mini-cart.see-details')
                                        <span
                                            class="text-base"
                                            :class="{'icon-arrow-up': item.option_show, 'icon-arrow-down': ! item.option_show}"
                                        ></span>
                                    </p>

                                    <!-- Option Details -->
                                    <div
                                        class="grid gap-1 mt-1"
                                        v-show="item.option_show"
                                    >
                                        <template v-for="attribute in item.options">
                                            <div class="flex gap-1">
                                                <p class="text-xs font-medium text-zinc-500">@{{ attribute.attribute_name + ':' }}</p>

                                                <p class="text-xs">
                                                    <template v-if="attribute?.attribute_type === 'file'">
                                                        <a
                                                            :href="attribute.file_url"
                                                            class="text-blue-700"
                                                            target="_blank"
                                                            :download="attribute.file_name"
                                                        >
                                                            @{{ attribute.file_name }}
                                                        </a>
                                                    </template>

                                                    <template v-else>
                                                        @{{ attribute.option_label }}
                                                    </template>
                                                </p>
                                            </div>
                                        </template>
                                    </div>

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.product_details.after') !!}
                                </div>

                                <!-- Qty changer + Remove -->
                                <div class="flex items-center gap-3 mt-auto">
                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.quantity_changer.before') !!}

                                    <!-- Cart Item Quantity Changer -->
                                    <x-shop::quantity-changer
                                        v-if="item.can_change_qty"
                                        ::key="'qty-' + item.id + '-' + refreshKey"
                                        class="h-8 gap-x-2 rounded-full px-2.5 py-1 text-sm max-sm:h-7 max-sm:px-2 max-sm:text-xs"
                                        name="quantity"
                                        ::value="item?.quantity"
                                        @change="updateItem($event, item)"
                                    />

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.quantity_changer.after') !!}

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.remove_button.before') !!}

                                    <!-- Cart Item Remove Button -->
                                    <button
                                        type="button"
                                        class="ml-auto flex items-center gap-1 text-xs text-zinc-400 hover:text-red-500 transition-colors"
                                        @click="removeItem(item.id)"
                                        :aria-label="'@lang('shop::app.checkout.cart.mini-cart.remove')'"
                                    >
                                        <span class="icon-bin text-base"></span>
                                        <span class="max-sm:hidden">@lang('shop::app.checkout.cart.mini-cart.remove')</span>
                                    </button>

                                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.remove_button.after') !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Cart Section -->
                    <div
                        class="flex flex-col items-center justify-center py-16 gap-4 max-md:py-12"
                        v-else
                    >
                        <img
                            class="w-24 h-24 max-md:w-20 max-md:h-20 opacity-60"
                            src="{{ bagisto_asset('images/thank-you.png') }}"
                            loading="lazy"
                            decoding="async"
                        >

                        <p
                            class="text-base font-medium text-zinc-500 max-md:text-sm"
                            role="heading"
                        >
                            @lang('shop::app.checkout.cart.mini-cart.empty-cart')
                        </p>
                    </div>

                    {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.content.after') !!}
                </x-slot>

            <!-- Drawer Footer -->
            <x-slot:footer>
                <div
                    v-if="cart?.items?.length"
                    class="grid gap-3 max-md:gap-2"
                >
                    <!-- Subtotal bar -->
                    <div
                        class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 max-md:px-4 max-md:py-3"
                        :class="{'!justify-end': isLoading}"
                    >
                        {!! view_render_event('bagisto.shop.checkout.mini-cart.subtotal.before') !!}

                        <template v-if="! isLoading">
                            <p class="text-sm font-medium text-zinc-500">
                                @lang('shop::app.checkout.cart.mini-cart.subtotal')
                            </p>

                        <template v-if="displayTax.subtotal == 'including_tax'">
                            <p class="text-xl font-bold max-md:text-lg">
                                @{{ cart.formatted_sub_total_incl_tax }}
                            </p>
                        </template>

                        <template v-else-if="displayTax.subtotal == 'both'">
                            <p class="flex flex-col text-xl font-bold max-md:text-lg max-sm:text-right">
                                @{{ cart.formatted_sub_total_incl_tax }}

                                <span class="text-xs font-normal text-zinc-500">
                                    @lang('shop::app.checkout.cart.mini-cart.excl-tax')

                                    <span class="font-medium text-black">@{{ cart.formatted_sub_total }}</span>
                                </span>
                            </p>
                        </template>

                        <template v-else>
                            <p class="text-xl font-bold max-md:text-lg">
                                @{{ cart.formatted_sub_total }}
                            </p>
                        </template>
                    </template>

                        <template v-else>
                            <!-- Spinner -->
                            <svg
                                class="h-6 w-6 animate-spin text-navyBlue"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                aria-hidden="true"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>

                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </template>

                        {!! view_render_event('bagisto.shop.checkout.mini-cart.subtotal.after') !!}
                    </div>

                    {!! view_render_event('bagisto.shop.checkout.mini-cart.action.before') !!}

                    <!-- Cart Action Buttons -->
                    <div class="grid gap-2 px-6 pb-4 max-md:px-4 max-md:pb-3">
                        {!! view_render_event('bagisto.shop.checkout.mini-cart.continue_to_checkout.before') !!}

                        <a
                            href="{{ route('shop.checkout.onepage.index') }}"
                            class="block w-full cursor-pointer rounded-xl bg-navyBlue px-6 py-3.5 text-center text-sm font-semibold text-white transition-opacity hover:opacity-90 max-md:py-3 max-sm:py-2.5"
                        >
                            @lang('shop::app.checkout.cart.mini-cart.continue-to-checkout')
                        </a>

                        {!! view_render_event('bagisto.shop.checkout.mini-cart.continue_to_checkout.after') !!}

                        <a
                            href="{{ route('shop.checkout.cart.index') }}"
                            class="block w-full cursor-pointer rounded-xl border border-zinc-200 px-6 py-3 text-center text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 max-md:py-2.5 max-sm:py-2"
                        >
                            @lang('shop::app.checkout.cart.mini-cart.view-cart')
                        </a>
                    </div>

                    {!! view_render_event('bagisto.shop.checkout.mini-cart.action.after') !!}
                </div>
            </x-slot>
            </x-shop::drawer>

        @else
            <a href="{{ route('shop.checkout.onepage.index') }}">
                {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.toggle.before') !!}

                    <span class="relative">
                        <span
                            class="icon-cart cursor-pointer text-2xl"
                            role="button"
                            aria-label="@lang('shop::app.checkout.cart.mini-cart.shopping-cart')"
                            tabindex="0"
                        ></span>

                        <span
                            class="absolute -top-4 rounded-[44px] bg-navyBlue px-2 py-1.5 text-xs font-semibold leading-[9px] text-white ltr:left-5 rtl:right-5 max-md:px-2 max-md:py-1.5 max-md:ltr:left-4 max-md:rtl:right-4"
                            v-if="cart?.items_qty"
                        >
                            @{{ cart.items_qty }}
                        </span>
                    </span>

                {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.toggle.after') !!}
            </a>
        @endif

        {!! view_render_event('bagisto.shop.checkout.mini-cart.drawer.after') !!}
    </script>

    @php
        /**
         * When the cart is empty there is nothing to fetch, so the mini-cart is
         * seeded with an empty cart server-side. This avoids an `/api/checkout/cart`
         * request (and a `Cart::collectTotals()` recalculation) on every page view
         * for the common case of a guest with no cart.
         */
        $hasCartItems = (bool) \Webkul\Checkout\Facades\Cart::getCart()?->items->isNotEmpty();
    @endphp

    <script type="module">
        app.component("v-mini-cart", {
            template: '#v-mini-cart-template',

            data() {
                return  {
                    refreshKey: 0,

                    cart: {!! $hasCartItems ? 'null' : json_encode(['items_qty' => 0, 'items' => []]) !!},

                    isLoading:false,

                    displayTax: {
                        prices: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_prices') }}",
                        subtotal: "{{ core()->getConfigData('sales.taxes.shopping_cart.display_subtotal') }}",
                    },
                };
            },

            mounted() {
                if (!this.cart) {
                    this.getCart();
                }

                /**
                 * Action.
                 */
                this.$emitter.on('update-mini-cart', (cart) => {
                    this.cart = cart;
                });
            },

            methods: {
                getCart() {
                    this.$axios.get('{{ route('shop.api.checkout.cart.index') }}')
                        .then(response => {
                            this.cart = response.data.data;
                        })
                        .catch(error => {});
                },

                updateItem(quantity, item) {
                    this.isLoading = true;

                    let qty = {};

                    qty[item.id] = quantity;

                    this.$axios.put('{{ route('shop.api.checkout.cart.update') }}', { qty })
                        .then(response => {
                            this.isLoading = false;

                            /**
                             * The update endpoint returns `{ data: CartResource, message }`
                             * on success and only `{ message }` on failure (e.g.
                             * inventory-warning). Only treat the payload as a cart when
                             * it has an `items` field — otherwise surface the server
                             * message as a warning flash.
                             */
                            const payload = response.data.data;

                            if (payload && payload.items !== undefined) {
                                this.cart = payload;
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'warning',
                                    message: payload?.message || response.data.message,
                                });
                            }

                            /**
                             * Bump the key so the quantity-changer remounts from the
                             * current server value even when the update was rejected
                             * (in which case `value` didn't change and the component's
                             * `value` watcher wouldn't fire).
                             */
                            this.refreshKey++;
                        })
                        .catch(error => {
                            this.isLoading = false;

                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || error.message,
                            });

                            this.refreshKey++;
                        });
                },

                removeItem(itemId) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.isLoading = true;

                            this.$axios.post('{{ route('shop.api.checkout.cart.destroy') }}', {
                                '_method': 'DELETE',
                                'cart_item_id': itemId,
                            })
                            .then(response => {
                                this.cart = response.data.data;

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                this.isLoading = false;
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', { type: 'error', message: response.data.message });

                                this.isLoading = false;
                            });
                        }
                    });
                },
            },
        });
    </script>
@endpushOnce
