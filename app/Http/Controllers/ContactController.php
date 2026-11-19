<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:3000',
        ]);
        DB::table('contact_messages')->insert($data + ['created_at' => now()]);

        return back()->with('success', 'Your message was sent to the MoveIt team.');
    }
}
