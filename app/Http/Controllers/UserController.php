<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {

        try {
            $validation=Validator::make($request->all(),[
                'userName' => 'required|string',
                'email'=> 'required|string|unique:users',
                'password' => 'required|string',
                'company_size'=>'string',
                'company_name' => 'required|string',
                'company_address' => 'required|string',
                'company_location' => 'required|string',
                'phone' =>'required|string',
                'industry' => 'string'
            ]);
            if($validation->fails()){
                return response()->json([
                    'msg'=>'error',
                    'error'=>$validation->errors()
                ]);
            }else{
                $user = new User([
                    'userName' => $request->input('userName'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'company_size' => $request->input('company_size'),
                    'company_name' => $request->input('company_name'),
                    'company_address' => $request->input('company_address'),
                    'company_location' => $request->input('company_location'),
                    'phone' => $request->input('phone'),
                    'company_industry' => $request->input('company_industry')
                ]);
                if ($user->save()){
                    return response()->json([
                        'message' => 'User has been registered',
                        'user'=>$user
                    ], 200);
                }else{
                    return response()->json([
                        'message' => 'User not registered'
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to register user',
                'e'=>$e->getMessage()
            ], 400);
        }
    }
    public function login(Request $request): JsonResponse
    {
        $loginData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($loginData)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $accessToken = Auth::user()->createToken('authToken')->accessToken;
        $user = Auth::user();
        return response()->json(['email' => $user->email, 'id' => $user->id ], 200);
    }
    public function getData($id): JsonResponse
    {
        // Retrieve the user data from the database based on the ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Return the user data as a response
        return response()->json($user);
    }

    public function updatePhoto(Request $request, $id): JsonResponse
    {
        // Validate the image upload
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Process the uploaded image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();

            // Upload the image to Cloudinary
            $cloudinaryResponse = Cloudinary::upload($image->getRealPath(), [
                'folder' => config('cloudinary.upload_folder'), // Use the default folder path from the configuration file
                'public_id' => $filename, // Use the filename as the public ID
            ]);

            // Check if the upload was successful
            if (isset($cloudinaryResponse['public_id'])) {
                // Save the Cloudinary URL in your database, associated with the user
                $user->image = $cloudinaryResponse['secure_url'];
                $user->save();

                return response()->json([
                    'message' => 'Image uploaded successfully',
                    'url' => $cloudinaryResponse['secure_url']
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Image upload failed'
                ], 400);
            }
        }

        return response()->json(['error' => 'Image upload failed'], 400);
    }

    /*public function updatePhoto(Request $request, $id): JsonResponse
    {
        // Validate the image upload
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Process the uploaded image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $filename);

            // Save the image details in your database, associated with the user
            $user->image = $filename;
            $user->save();
            if ($user->save()){
                return response()->json([
                    'message' => 'Image uploaded successfully', 'filename' => $filename
                ], 200);
            }else{
                return response()->json([
                    'message' => 'Image upload failed'
                ], 400);
            }

        }

        return response()->json(['error' => 'Image upload failed'], 400);
    }*/
    public function addFavourite(Request $request): JsonResponse
    {
        try {
            $validation=Validator::make($request->all(),[
                'user_id' => 'required|integer',
                'Service_id'=> 'required|integer'
            ]);
            if($validation->fails()){
                return response()->json([
                    'msg'=>'error',
                    'error'=>$validation->errors()
                ]);
            }else{
                $fav = new Favourite([
                    'user_id' => $request->input('user_id'),
                    'Service_id' => $request->input('Service_id')
                ]);
                if ($fav->save()){
                    return response()->json([
                        'message' => 'Added Successfully',
                    ], 200);
                }else{
                    return response()->json([
                        'message' => 'Failed'
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to Add ',
                'e'=>$e->getMessage()
            ], 400);
        }
    }
    public function getFavourites($id): JsonResponse
    {
        $favourites  = Favourite::where('user_id', $id)->get();

        // Check if the user exists
        if (!$favourites) {
            return response()->json(['message' => 'Favourites not found'], 404);
        }
        $serviceIds = $favourites->pluck('Service_id');

        $services = Service::whereIn('id', $serviceIds)->get();

        // Check if any services exist for the favourite entries
        if ($services->isEmpty()) {
            return response()->json(['message' => 'Services not found'], 404);
        }

        // Return the services' data as a response
        return response()->json($services);
    }

    public function search($name): JsonResponse
    {
        // Retrieve all services containing the specified name
        $services = Service::where('Service Name', 'like', '%' . $name . '%')->get();

        // Check if any services are found
        if ($services->isEmpty()) {
            return response()->json(['message' => 'No services found'], 404);
        }

        // Return the services as a response
        return response()->json($services);
    }

}
