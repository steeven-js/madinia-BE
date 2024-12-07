<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTestController extends Controller
{
    public function showForm()
    {
        return view('emails.form');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255'
        ]);

        try {
            Mail::to($request->email)
                ->send(new TestEmail($request->name));

            return response()->json([
                'success' => true,
                'message' => "Email envoyé avec succès à {$request->email}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de l'envoi : " . $e->getMessage()
            ], 500);
        }
    }
}
