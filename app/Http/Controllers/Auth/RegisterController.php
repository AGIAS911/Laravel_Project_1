<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */


    protected function validator(array $data)
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['required', 'in:customer,driver'],
        ];

        if (isset($data['account_type']) && $data['account_type'] === 'driver') {
            $rules['license_number'] = ['required', 'string', 'max:100'];
            $rules['license_image'] = ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'];
        }

        return Validator::make($data, $rules);
    }

    protected function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $isActive = ($data['account_type'] === 'customer') ? 1 : 0;

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => $data['account_type'],
                'is_active' => $isActive,
                'password' => Hash::make($data['password']),
            ]);

            if ($data['account_type'] === 'driver') {
                $imagePath = request()->file('license_image')->store('licenses', 'public');

                DriverProfile::create([
                    'user_id' => $user->id,
                    'license_number' => $data['license_number'],
                    'license_image' => $imagePath,
                    'status' => 'pending',
                ]);
            }

            return $user;
        });
    }
}
