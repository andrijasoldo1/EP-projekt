<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class ChatbotController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     */
    public function getConversations(Request $request)
    {
        $user = $this->authenticateUser($request);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $conversations = Conversation::where('user_id', $user->id)->get();

        return response()->json($conversations);
    }

    /**
     * Get chat history for a specific conversation.
     */
    public function getChatHistory(Request $request, $conversationId)
    {
        $user = $this->authenticateUser($request);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $chatHistory = ChatMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get();

        return response()->json($chatHistory);
    }

    /**
     * Create a new conversation.
     */
    public function createConversation(Request $request)
    {
        $user = $this->authenticateUser($request);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'topic' => 'required|string|max:255',
        ]);

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'topic' => $request->input('topic'),
        ]);

        return response()->json(['conversation_id' => $conversation->id]);
    }

    /**
     * Handle user query and get response from AI.
     */
    public function ask(Request $request, $conversationId)
    {
        $user = $this->authenticateUser($request);

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'query' => 'required|string',
        ]);

        $conversation = Conversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found.'], 404);
        }

        // Save user's message
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'user',
            'message' => $request->input('query'),
        ]);

        try {
            $client = new Client();
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'ft:gpt-4o-2024-08-06:toplaw:toplaw:AtYaitAD',
                    'messages' => $this->formatChatMessages($conversation->id),
                    'max_tokens' => 1000,
                    'temperature' => 0.7,
                ],
            ]);

            $result = json_decode($response->getBody(), true);

            // Save bot's response
            $assistantMessage = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => $result['choices'][0]['message']['content'],
            ]);

            return response()->json(['answer' => $assistantMessage->message]);
        } catch (\Exception $e) {
            Log::error('OpenAI API Error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to fetch response. Please try again later.'], 500);
        }
    }

    /**
     * Format chat messages for AI request.
     */
    private function formatChatMessages($conversationId)
    {
        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->take(15)
            ->get();

        $chatMessages = [
            [
                'role' => 'system',
                'content' => 'You are an AI lawyer specializing in Bosnian and Herzegovian law. Provide accurate legal advice and avoid answering unrelated questions.',
            ],
        ];

        foreach ($messages as $message) {
            $chatMessages[] = [
                'role' => $message->role,
                'content' => $message->message,
            ];
        }

        return $chatMessages;
    }

    /**
     * Authenticate user using the token from request.
     */
    private function authenticateUser(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        $personalAccessToken = PersonalAccessToken::findToken($token);
        if (!$personalAccessToken) {
            return null;
        }

        $user = $personalAccessToken->tokenable;
        Auth::setUser($user);

        return $user;
    }
}
