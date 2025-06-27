<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
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

        $services = Service::where('company_event_id', $validatedData['company_event_id'])->get();

        return response()->json($services, 200);
    }



    public function store(StoreServiceRequest $request)
    {
        $data = $request->validated();


        $exists = Service::where('company_event_id', $data['company_event_id'])
            ->where('service_name', $data['service_name'])
            ->where('service_description', $data['service_description'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This service already exists.'
            ], 409);
        }

        $service = Service::create($data);

        return response()->json([
            'message' => 'Service created successfully.',
            'service' => $service,
        ], 201);
    }

    public function update(UpdateServiceRequest $request, $id)
    {
        $user = Auth::user();
        $service = Service::find($id);

        if(!$service){
            return response()->json(['message' => 'this service doesn\'t exist.']);
        }


        if ($service->companyEvent->company->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $duplicate = Service::where('company_event_id', $service->company_event_id)
            ->where('service_name', $request->service_name)
            ->where('service_description', $request->service_description)
            ->where('id', '!=', $service->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['message' => 'this service already exists.'], 409);
        }

        if (!$service->isDirty()) {
            return response()->json(['message' => 'No changes detected.'], 200);
        }

        $service->update($request->validated());

        return response()->json([
            'message' => 'updated successfully.',
            'service' => $service
        ], 200);
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

    public function destroy($id)
    {
        $user = Auth::user();

        $service = Service::findOrFail($id);

        $companyEvent = $service->companyEvent;

        if (!$companyEvent || !$companyEvent->company) {
            return response()->json([
                'message' => 'Invalid service or data not linked correctly.'
            ], 400);
        }

        if ($companyEvent->company->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully.'
        ], 200);
    }

}
