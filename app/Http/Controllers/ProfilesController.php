<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfilesController extends Controller
{
    
    public function store(Request $request){
        $input = $request->all();

        $validationRules =[
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'summary' => 'required|min:5',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $profile = Profile::where('user_id', Auth::user()->id)->first();
        
        if (!$profile) {
            $profile = new Profile;
            $profile->user_id = Auth::user()->id;
        }

        $profile->first_name = $request->input('first_name');
        $profile->last_name = $request->input('last_name');
        $profile->summary = $request->input('summary');

        if ($request->hasFile('image')) {
            $firstName = str_replace(' ','_',$request->input('first_name'));
            $lastName = str_replace(' ','_', $request->input('last_name'));

            $imageName = Auth::user()->id . '_' . $firstName . '_' . $lastName;
            $request->file('image')->move(storage_path('uploads/image_profile'), $imageName);

            $current_image_path = storage_path('avatar') . '/' . $profile->image;
            if (file_exists($current_image_path)) {
                unlink($current_image_path);
            }

            $profile->image = $imageName;

        }

        $profile->save();

        return response()->json($profile, 200);

    }

    public function show($userId)
    {
        $profile = Profile::where('user_id', $userId)->first();

        if (!$profile) {
            abort(404);
        }

        return response()->json($profile, 200);

    }

    public function image($imageName)
    {
        $imagePath = storage_path('uploads/image_profile') . '/' . $imageName;
        if (file_exists($imagePath)) {
            $file = file_get_contents($imagePath);
            return response($file, 200)->header('Content-Type', 'image/jpeg');
        }

        return response()->json(array(
            'message' => "Image not found"
        ), 401);

    }

}
