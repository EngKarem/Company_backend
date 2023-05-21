<?php

namespace App\Http\Controllers;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ServiceController extends Controller
{
    public function getServices(): JsonResponse
    {
        // Retrieve all users from the database
        $services = Service::all();
        if (!$services) {
            return response()->json(['message' => 'Services not found'], 404);
        }

        foreach ($services as $service) {
            $companyId = $service->pluck('user_id');
//            $companyId = $service->user_id;
            $company = User::where('id', $companyId)->get();
            $company_name = $company->pluck('company_name');
            $service['Company Name'] = $company_name[0];
        }

        // Return the users as a response
        return response()->json($services);
    }

    public function getCompanyServices($id): JsonResponse
    {
        // Retrieve the user data from the database based on the ID
        $services = Service::where('Company_id', $id)->get();

        // Check if the user exists
        if (!$services) {
            return response()->json(['message' => 'Services not found'], 404);
        }

        // Return the user data as a response
        return response()->json($services);
    }
    public function getServiceLocation($id): JsonResponse
    {
        // Retrieve the user data from the database based on the ID
        $service = Service::find($id);

        // Check if the user exists
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $company_id = $service->pluck('Company_id');

        $Company = User::whereIn('id', $company_id)->get();
        if ($Company -> isEmpty()) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $company_location = $Company->pluck('company_location');
        // Check if any services exist for the favourite entries


        // Return the services' data as a response
        return response()->json(["Location" => $company_location[0]]);
    }
}
