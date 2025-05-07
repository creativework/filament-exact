<?php

namespace CreativeWork\FilamentExact\Controllers;

use CreativeWork\FilamentExact\Resources\ExactQueueResource;
use CreativeWork\FilamentExact\Services\ExactService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ExactController extends Controller
{
    public function callback(Request $request)
    {
        $code = $request->get('code');
        if (! $code) {
            return response()->json(['error' => 'code is required'], 400);
        }

        try {
            $service = new ExactService;
            $service->authorize($code);

            return redirect()->away(ExactQueueResource::getUrl())->with('success', __('Connected to Exact successfully'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
