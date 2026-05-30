@php
    $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $channel->home_seo['meta_title'] ?? '' }}"
    />

    <meta
        name="description"
        content="{{ $channel->home_seo['meta_description'] ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $channel->home_seo['meta_keywords'] ?? '' }}"
    />
@endPush

@push('scripts')
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? '' }}
    </x-slot>

    <!-- Loop over the theme customization -->
    @foreach ($customizations as $customization)
        @php ($data = $customization->options) @endphp

        <!-- Static content -->
        @switch ($customization->type)
            @case ($customization::IMAGE_CAROUSEL)
                <!-- Image Carousel -->
                <x-shop::carousel
                    :options="$data"
                    aria-label="{{ trans('shop::app.home.index.image-carousel') }}"
                />

                @break
            @case ($customization::STATIC_CONTENT)
                <!-- push style -->
                @if (! empty($data['css']))
                    @push ('styles')
                        <style>
                            {{ $data['css'] }}
                        </style>
                    @endpush
                @endif

                <!-- Structured images mode -->
                @if (! empty($data['images']) && is_array($data['images']))
                    @php
                        $layout = $data['layout'] ?? 'grid';
                        $text = $data['text'] ?? '';
                    @endphp

                    <section class="static-content-section container mx-auto px-4 py-6">
                        @if ($layout === 'grid')
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                                @foreach ($data['images'] as $image)
                                    @if (! empty($image['url']))
                                        <div class="text-center">
                                            @if (! empty($image['link']))
                                                <a href="{{ $image['link'] }}">
                                            @endif
                                            <img
                                                src="{{ $image['url'] }}"
                                                alt="{{ $image['alt'] ?? '' }}"
                                                class="w-full rounded-lg object-cover"
                                                loading="lazy"
                                            />
                                            @if (! empty($image['caption']))
                                                <p class="mt-1 text-xs text-gray-500">{{ $image['caption'] }}</p>
                                            @endif
                                            @if (! empty($image['link']))
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                        @elseif ($layout === 'slider')
                            <div class="relative overflow-hidden rounded-lg">
                                @php $first = $data['images'][0] ?? null; @endphp
                                @if ($first && ! empty($first['url']))
                                    @if (! empty($first['link']))
                                        <a href="{{ $first['link'] }}">
                                    @endif
                                    <img
                                        src="{{ $first['url'] }}"
                                        alt="{{ $first['alt'] ?? '' }}"
                                        class="h-64 w-full object-cover"
                                    />
                                    @if (! empty($first['caption']))
                                        <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-2 text-center text-sm text-white">
                                            {{ $first['caption'] }}
                                        </div>
                                    @endif
                                    @if (! empty($first['link']))
                                        </a>
                                    @endif
                                @endif
                            </div>

                        @elseif ($layout === 'masonry')
                            <div class="columns-2 gap-4 md:columns-3 lg:columns-4">
                                @foreach ($data['images'] as $image)
                                    @if (! empty($image['url']))
                                        <div class="mb-4 break-inside-avoid">
                                            @if (! empty($image['link']))
                                                <a href="{{ $image['link'] }}">
                                            @endif
                                            <img
                                                src="{{ $image['url'] }}"
                                                alt="{{ $image['alt'] ?? '' }}"
                                                class="w-full rounded-lg"
                                                loading="lazy"
                                            />
                                            @if (! empty($image['caption']))
                                                <p class="mt-1 text-center text-xs text-gray-500">{{ $image['caption'] }}</p>
                                            @endif
                                            @if (! empty($image['link']))
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                        @elseif ($layout === 'banner')
                            @php $first = $data['images'][0] ?? null; @endphp
                            @if ($first && ! empty($first['url']))
                                <div class="relative">
                                    @if (! empty($first['link']))
                                        <a href="{{ $first['link'] }}">
                                    @endif
                                    <img
                                        src="{{ $first['url'] }}"
                                        alt="{{ $first['alt'] ?? '' }}"
                                        class="h-64 w-full rounded-lg object-cover"
                                    />
                                    @if (! empty($first['caption']) || $text)
                                        <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/40">
                                            <div class="p-4 text-center text-white">
                                                @if (! empty($first['caption']))
                                                    <h3 class="mb-2 text-lg font-bold">{{ $first['caption'] }}</h3>
                                                @endif
                                                @if ($text)
                                                    <p>{!! nl2br(e($text)) !!}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if (! empty($first['link']))
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @endif

                        <!-- Text content below images -->
                        @if ($text && $layout !== 'banner')
                            <div class="mt-4 whitespace-pre-line text-gray-700">{!! nl2br(e($text)) !!}</div>
                        @endif
                    </section>
                @endif

                <!-- Legacy HTML mode (backward compatible) -->
                @if (! empty($data['html']))
                    {!! $data['html'] !!}
                @endif

                @break
            @case ($customization::CATEGORY_CAROUSEL)
                <!-- Categories carousel -->
                <x-shop::categories.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.categories.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.home.index')"
                    aria-label="{{ trans('shop::app.home.index.categories-carousel') }}"
                />

                @break
            @case ($customization::PRODUCT_CAROUSEL)
                <!-- Product Carousel -->
                <x-shop::products.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.products.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.search.index', $data['filters'] ?? [])"
                    aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
                />

                @break
        @endswitch
    @endforeach
</x-shop::layouts>
