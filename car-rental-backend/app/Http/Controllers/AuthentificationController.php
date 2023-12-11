<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Models\Authentification;
use App\Models\Permissions;
use App\Mail\PasswordResetEmail;
use App\Mail\PinResetEmail;
use PDO;
use Ramsey\Uuid\Uuid;

class AuthentificationController extends Controller
{
    //

    public function index()
    {
        return Authentification::where('deleted', false)->get();
    }

    public function register(Request $request)
    {
        $email = $request['email'];
        $username = $request['username'];
        $phone = $request['phone'];
        $createdBy = $request['createdBy'];
    
        // Check if the email, username, or phone already exist
        $getEmail = Authentification::where('email', $email)->first();
        if ($getEmail) {
            $response = [
                'message' => 'error',
                'description' => "Email $email is already taken",
            ];
            return response($response, 200);
        }
    
        $getUsername = Authentification::where('username', $username)->first();
        if ($getUsername) {
            $response = [
                'message' => 'error',
                'description' => "Username $username is already taken",
            ];
            return response($response, 200);
        }
    
        $getPhone = Authentification::where('phone', $phone)->first();
        if ($getPhone) {
            $response = [
                'message' => 'error',
                'description' => "Phone Number $phone is already taken",
            ];
            return response($response, 200);
        }
    
        // Generate PIN and create user
        $pin = $this->generateUniqueCode();
        $user = Authentification::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'username' => $request['username'],
            'phone' => $request['phone'],
            'role' => $request['role'],
            'photoURL' => $request['photoURL'],
            'createdBy' => $request['createdBy'],
            'password' => bcrypt($request['password']),
            'pin' => $pin,
        ]);
    
        // Send PIN reset email if the role is WAITER
        if ($request['role'] === 'WAITER') {
            Mail::to($email)->send(new \App\Mail\PinResetEmail($pin));
        }
    
        // Prepare report details
        $userWhoCreated = Authentification::where('email', $createdBy)->first();
        $createdName = $userWhoCreated['name'];
        $createdPhone = $userWhoCreated['phone'];
        $name = $request['name'];
    
        $report = [
            'type' => 'users',
            'title' => 'New User Added',
            'message' => "$createdName, $createdPhone, added a new user called $name. Phone number: $phone",
            'info' => 'milked',
        ];
    
        // Return success response
        $response = [
            'message' => 'success',
            'user' => $user,
        ];
    
        return response($response, 201);
    }
    


    public function login(Request $request)
    {


        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);



        // check email
        $user = Authentification::where('email', $fields['email'])->first();

        //password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Wrong email or password'], 401);
        } else {

            $token = $user->createToken('c-pos')->plainTextToken;
            $user['permissions'] = Permissions::where('userId', $user['id'])->get();

            $response = [
                'user' => $user,
                'token' => $token,
            ];

            return response($response, 200);
        }
    }


    public function pinAuthentication(Request $request)
    {


        // $fields = $request->validate([
        //     'pin' => 'required',
        // ]);

        // Encrypt the pin
        $pin = $request['pin'];

        // check pin
        $user = Authentification::where('pin', $pin)->first();

        if (!$user) {
            return response(['message' =>  $pin], 401);
        } else {

            $token = $user->createToken('c-pos')->plainTextToken;
            $user['permissions'] = Permissions::where('userId', $user['id'])->get();

            $response = [
                'user' => $user,
                'token' => $token,
            ];

            return response($response, 200);
        }
    }


    public static function generateUniqueCode(): string
    {
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $exists = Authentification::where('pin', $code)->exists();

        while ($exists) {
            $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $exists = Authentification::where('pin', $code)->exists();
        }
        return $code;
    }




    public function resetPassword(Request $request)
    {
        return  $this->resetUserPassword($request['email'], $request['organization']);
    }

    public function resetUserPassword($email, $organization)
    {
        if (!$email || !$organization) {
            return;
        }

        $passwordResetTable = 'password_reset_tokens';

        $key = env('ENCRYPTION_KEY');
        $orgCode = Crypt::decryptString($organization, $key);

        $orgFromDB =  get_object_vars(DB::connection('DEFAULT')->table('organizations')->where('orgCode', $orgCode)->first());
        $orgShortName = $orgFromDB['shortName'];
        $userFromDB = DB::table('users')->where('email', $email)->first();

        if (!$userFromDB) {
            return response()->json(['message' => "There is no user with email $email in $orgShortName"]);
        }


        $orgId = $orgFromDB['id'];
        // $user = User::find($request->user_id); // Fetch the user details from the database
        $token = Uuid::uuid4()->toString();

        $saveToDB = [
            'email' => $email,
            'token' => $token,
        ];
        DB::table($passwordResetTable)->where('email', $email)->delete();

        DB::table($passwordResetTable)->insert($saveToDB);

        Mail::to($email)->send(new PasswordResetEmail($email, $token, $orgId));




        return response()->json(['message' => 'Success']);
    }


    public function setNewPassword(Request $request)
    {
        // find organization
        $orgId = $request['org'];
        $orgFromDB =  get_object_vars(DB::connection('DEFAULT')->table('organizations')->where('id', $orgId)->first());
        $key = env('ENCRYPTION_KEY');

        $username = $orgFromDB['orgUsername'];
        $password = Crypt::decryptString($orgFromDB['password'], $key);
        $dbname = env($orgFromDB['orgCode']);
        $host =  env('DB_HOST', '127.0.0.1');

        //connect to the correct organization
        // try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

        // select user from organization
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $request['uid']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        //check whether token is valid (email, token)
        $email = $result['email'];
        $tokenQuery = $pdo->prepare('SELECT * FROM password_reset_tokens WHERE email = :email AND token = :token LIMIT 1');
        $tokenQuery->execute(['email' => $email, 'token' => $request['token']]);
        $tokenResult = $tokenQuery->fetch(PDO::FETCH_ASSOC);

        //reset the password
        if ($tokenResult) {
            $encryptedPassword = bcrypt($request['password']);
            $userUpdate = $pdo->prepare('UPDATE users SET password = :encryptedPassword WHERE email = :email');
            $userUpdate->execute(['encryptedPassword' => $encryptedPassword, 'email' => $email]);
            // $tokenResult = $tokenQuery->fetch(PDO::FETCH_ASSOC);
            // $userDelete = $pdo->prepare('DELETE FROM password_reset_tokens WHERE email = :email');
            // $userDelete->execute(['email' => $email]);
            $this->deletePasswordToken($email, $host, $dbname, $username, $password);

            if ($userUpdate) {
                // DB::table('password_access_tokens')->where('email', $email)->delete();
                $response = [
                    'message' => 'Success',
                    'email' => $email,
                ];
                return response($response, 200);
            } else {
                $response = [
                    'message' => 'Error',
                ];

                return response($response, 200);
            }
        } else {

            $response = [
                'message' => 'Error',
            ];

            return response($response, 200);
        }
    }

    public function deletePasswordToken($email, $host, $dbname, $username, $password)
    {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

        $userDelete = $pdo->prepare('DELETE FROM password_reset_tokens WHERE email = :email');
        $userDelete->execute(['email' => $email]);
    }
}
