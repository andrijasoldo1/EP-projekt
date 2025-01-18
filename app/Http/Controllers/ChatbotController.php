<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function showChatbot()
    {
        return view('chatbot'); // This points to a Blade view we'll create next
    }

    public function ask(Request $request)
    {
        // Validate the query input
        $request->validate([
            'query' => 'required|string',
        ]);

        // For now, return a mock response
        return response()->json([
            'answer' => 'This is a test response for your query: ' . $request->input('query'),
        ]);
    }
}
