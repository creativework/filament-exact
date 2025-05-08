<?php

namespace CreativeWork\FilamentExact\Controllers;

use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use CreativeWork\FilamentExact\Services\ExactTokenService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ExactController extends Controller
{
    public function callback(Request $request, ExactTokenService $exact)
    {
        $code = $request->get('code');
        if (! $code) {
            return response()->json(['error' => 'code is required'], 400);
        }

        try {
            $tokenData = $exact->refreshAccessToken($code);
            if (! $tokenData) {
                return response()->json(['error' => 'Failed to refresh access token'], 500);
            }

            return redirect()->away(ExactQueueResource::getUrl())->with('success', __('Connected to Exact successfully'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
