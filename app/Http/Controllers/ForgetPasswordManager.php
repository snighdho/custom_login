<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash; // Import Hash facade for password hashing
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User; // Import User model

// class ForgetPasswordManager extends Controller
// {
//     public function forgetPassword()
//     {
//         return view('forget-password');
//     }

//     public function forgetPasswordPost(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|email|exists:users,email',
//         ]);

//         $token = Str::random(64);

//         // Insert the token into the password_resets table
//         DB::table('password_resets')->updateOrInsert(
//             ['email' => $request->email], // Key to identify existing record
//             ['token' => $token, 'created_at' => Carbon::now()], // Data to update or insert
//         );

//         // Send email with password reset token
//         Mail::send('emails.forget-password', ['token' => $token], function ($message) use ($request) {
//             $message->to($request->email);
//             $message->subject('Reset Password');
//         });

//         return redirect()->route('forget.password')->with('success', 'We have sent an email to reset your password.');
//     }

//     public function resetPassword($token)
//     {
//         return view('new-password', compact('token'));
//     }

//     public function resetPasswordPost(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|email|exists:users,email',
//             'password' => 'required|string|min:6|confirmed', // 'confirmed' rule checks if passwords match
//         ]);

//         // Retrieve the record for the given email and token
//         $updatePassword = DB::table('password_resets')
//             ->where([
//                 'email' => $request->email,
//                 'token' => $request->token,
//             ])
//             ->first();

//         // If no record found, redirect back with error
//         if (!$updatePassword) {
//             return redirect()
//                 ->route('reset.password', ['token' => $request->token])
//                 ->with('error', 'Invalid token or email.');
//         }

//         // Update the user's password
//         User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

//         // Delete the used token from the password_resets table
//         DB::table('password_resets')
//             ->where(['email' => $request->email])
//             ->delete();

//         return redirect()->route('login')->with('success', 'Password reset successful.');
//     }
// }

class ForgetPasswordManager extends Controller
{
    // Show the forget password form
    public function forgetPassword()
    {
        return view('forget-password');
    }

    // Handle forget password form submission
    public function forgetPasswordPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        // Delete old tokens for this email before inserting a new one
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        // Insert the new token
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        // Send email with reset token
        Mail::send('emails.forget-password', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return redirect()->route('forget.password')->with('success', 'We have sent an email to reset your password.');
    }

    // Show the password reset form with the token
    public function resetPassword($token)
    {
        // Return the reset password view with the token
        return view('new-password', compact('token'));
    }

    // Handle the password reset form submission
    public function resetPasswordPost(Request $request)
    {
        // Validate the form input
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Look for the reset token and email in the password_resets table
        $updatePassword = DB::table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token,
            ])
            ->first();

        // If token or email is invalid, return an error
        if (!$updatePassword) {
            return redirect()
                ->route('reset.password', ['token' => $request->token])
                ->with('error', 'Invalid token or email.');
        }

        // Update the user's password in the users table
        User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

        // Delete the password reset token from the password_resets table
        DB::table('password_resets')
            ->where(['email' => $request->email])
            ->delete();

        // Redirect to login with a success message
        return redirect()->route('login')->with('success', 'Password reset successful.');
    }
}
