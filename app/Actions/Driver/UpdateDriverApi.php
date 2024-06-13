<?php

namespace App\Actions\Driver;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDriverApi
{
    use AsAction;

    public function handle(Request $request)
    {

        // dd($request);
        $user = User::find($request->id);
        $name = $request->name;
        $password = $request->password;

        if (! empty($name)) {
            $user->name = $name;
        }

        if (! empty($password)) {
            $user->forceFill([
                'password' => Hash::make($password),
            ]);
        }

        if ($request->hasFile('profile_picture')) {
            $folderPath = public_path('uploads');
            // Generate a unique file name
            $filename = time().'_'.$user->id.'.'.$request->profile_picture->getClientOriginalExtension();
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $request->profile_picture->move($folderPath, $filename);

            if (isset($user->profile_photo_path)) {
                $exitsfilepath = public_path('uploads/'.$user->profile_photo_path);

                if (file_exists($exitsfilepath)) { // delete exist image
                    unlink($exitsfilepath);
                }
            }
            $user->profile_photo_path = $filename; //update file path
        }

        $user->save();

    }
}
