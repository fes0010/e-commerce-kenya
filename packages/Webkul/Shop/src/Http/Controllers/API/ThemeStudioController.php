<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ThemeStudioController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CoreConfigRepository $coreConfigRepository,
        protected ThemeCustomizationRepository $themeCustomizationRepository
    ) {
    }

    /**
     * Fetch all layout customizations for the current theme.
     */
    public function layout(): JsonResponse
    {
        $customizations = $this->themeCustomizationRepository
            ->orderBy('sort_order')
            ->findWhere([
                'channel_id' => core()->getCurrentChannel()->id,
                'theme_code' => core()->getCurrentChannel()->theme,
            ]);

        return response()->json([
            'data' => $customizations->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'name'       => $item->name,
                    'type'       => $item->type,
                    'status'     => (int) $item->status,
                    'sort_order' => (int) $item->sort_order,
                ];
            })
        ]);
    }

    /**
     * Save theme colors, fonts, and layout arrangements.
     */
    public function save(): JsonResponse
    {
        // Secure endpoint: only admins logged into the same domain can save.
        if (! auth()->guard('admin')->check()) {
            return response()->json(['message' => 'Unauthorized. Please login to the Admin Dashboard first in another tab.'], 401);
        }

        $requestData = request()->all();

        // 1. Save Theme Colors / Fonts (Core Config)
        if (isset($requestData['colors']) && is_array($requestData['colors'])) {
            $data = [
                'general' => [
                    'design' => [
                        'theme_colors' => $requestData['colors']
                    ]
                ],
                'channel' => core()->getCurrentChannelCode(),
                'locale'  => app()->getLocale(),
            ];
            $this->coreConfigRepository->create($data);
        }

        // 2. Save Layout Settings (Theme Customizations Table)
        if (isset($requestData['layout']) && is_array($requestData['layout'])) {
            foreach ($requestData['layout'] as $layoutItem) {
                if (isset($layoutItem['id'])) {
                    // Temporarily disable events if we don't need heavy processing
                    // Update only status and sort order without touching image/HTML fields
                    $this->themeCustomizationRepository->update([
                        'status'     => $layoutItem['status'],
                        'sort_order' => $layoutItem['sort_order'],
                    ], $layoutItem['id']);
                }
            }
        }

        return response()->json([
            'message' => 'Storefront theme and layout saved successfully!'
        ]);
    }
}
