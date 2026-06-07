<div class="1180:hidden flex flex-col gap-4">
    <!-- Main Image/Video with Shimmer -->
    <div
        class="w-full"
        v-show="isMediaLoading"
    >
        <div class="shimmer aspect-square w-full rounded-xl bg-zinc-200"></div>
    </div>

    <div
        class="w-full"
        v-show="! isMediaLoading"
    >
        <img
            class="w-full cursor-pointer rounded-xl aspect-square object-cover"
            :src="baseFile.path"
            v-if="baseFile.type == 'image'"
            alt="{{ $product->name }}"
            tabindex="0"
            @click="isImageZooming = !isImageZooming"
            @load="onMediaLoad()"
            fetchpriority="high"
        />

        <div
            class="w-full cursor-pointer rounded-xl aspect-square"
            tabindex="0"
            v-if="baseFile.type == 'video'"
        >
            <video
                controls
                class="w-full h-full object-cover rounded-xl"
                alt="{{ $product->name }}"
                @click="isImageZooming = !isImageZooming"
                @loadeddata="onMediaLoad()"
                :key="baseFile.path"
            >
                <source
                    :src="baseFile.path"
                    type="video/mp4"
                />
            </video>
        </div>
    </div>

    <!-- Thumbnails below -->
    <div class="flex gap-2.5 overflow-x-auto scrollbar-hide w-full pb-2" v-if="[...media.images, ...media.videos].length > 1">
        <template v-for="(mediaItem, index) in [...media.images, ...media.videos]">
            <video
                v-if="mediaItem.type == 'videos'"
                :class="`h-[80px] min-w-[80px] object-cover cursor-pointer rounded-xl border ${isActiveMedia(index) ? 'pointer-events-none border-navyBlue' : 'border-zinc-200'}`"
                @click="change(mediaItem, index)"
                alt="{{ $product->name }}"
                tabindex="0"
            >
                <source
                    :src="mediaItem.video_url"
                    type="video/mp4"
                />
            </video>

            <img
                v-else
                :class="`h-[80px] min-w-[80px] object-cover cursor-pointer rounded-xl border ${isActiveMedia(index) ? 'pointer-events-none border-navyBlue' : 'border-zinc-200'}`"
                :src="mediaItem.small_image_url"
                alt="{{ $product->name }}"
                width="80"
                height="80"
                tabindex="0"
                @click="change(mediaItem, index)"
            />
        </template>
    </div>
</div>
