<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\CompanyEvent;
use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function ShowServices(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'company_event_id' => ['required', 'integer', 'exists:company_events,id'],
        ]);

        $services = Service::where('company_events_id', $validatedData['company_event_id'])->get();

        return response()->json($services, 200);
    }



    public function store(StoreServiceRequest $request)
    {
        $user = Auth::user();

        $company = $user->company;

        if (!$company) {
            return response()->json(['message' =>__('service.You don\'t have a Company')], 400);
        }


        $companyEvent = $company->companyEvents()->find($request->company_events_id);

        if (!$companyEvent) {
            return response()->json(['message' =>__('service.Not your Company')], 403);
        }

        $service = Service::create($request->validated());

        return response()->json(['message' =>__('service.You Added the Service Successfully'), 'service' => $service], 201);
    }

    public function addImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('image')->store('service_images', 'public');

         ServiceImage::create([
            'service_id' => $validated['service_id'],
            'image_url' => $path,
        ]);

        return response()->json([
            'message' =>__('Image uploaded successfully'),
            'image' => $path,
        ]);
    }

    public function getImages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $images = ServiceImage::where('service_id', $validated['service_id'])->get();

        return response()->json([
            'images' => $images,
        ], 200);
    }


}
