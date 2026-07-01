<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Models\ProductProxy;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'position' => $this->position,
            'display_mode' => $this->display_mode,
            'description' => $this->description,
            'logo' => $this->getLogoData(),
            'banner' => $this->when($this->banner_path, [
                'small_image_url' => url('cache/small/'.$this->banner_path),
                'medium_image_url' => url('cache/medium/'.$this->banner_path),
                'large_image_url' => url('cache/large/'.$this->banner_path),
                'original_image_url' => url('cache/original/'.$this->banner_path),
            ]),
            'meta' => [
                'title' => $this->meta_title,
                'keywords' => $this->meta_keywords,
                'description' => $this->meta_description,
            ],
            'translations' => $this->translations,
            'additional' => $this->additional,
        ];
    }

    /**
     * Get category logo or first product's image as fallback.
     *
     * @return array|null
     */
    private function getLogoData()
    {
        if ($this->logo_path) {
            return [
                'small_image_url' => url('cache/small/'.$this->logo_path),
                'medium_image_url' => url('cache/medium/'.$this->logo_path),
                'large_image_url' => url('cache/large/'.$this->logo_path),
                'original_image_url' => url('cache/original/'.$this->logo_path),
            ];
        }

        $categoryIds = $this->descendantsAndSelf($this->id)->pluck('id');

        $productWithImage = ProductProxy::modelClass()::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->whereHas('images')->first();

        if ($productWithImage) {
            $baseImgUrl = $productWithImage->base_image_url;

            if ($baseImgUrl) {
                return [
                    'small_image_url' => $baseImgUrl,
                    'medium_image_url' => $baseImgUrl,
                    'large_image_url' => $baseImgUrl,
                    'original_image_url' => $baseImgUrl,
                ];
            }
        }

        return null;
    }
}
