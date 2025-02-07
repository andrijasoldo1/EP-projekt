<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function showChatbot($conversationId = null)
    {
        $conversations = Conversation::where('user_id', auth()->id())->get();

        $chatHistory = [];
        if ($conversationId) {
            $chatHistory = ChatMessage::where('conversation_id', $conversationId)
                ->orderBy('created_at')
                ->get();
        }

        return view('chatbot', [
            'conversations' => $conversations,
            'chatHistory' => $chatHistory,
            'conversationId' => $conversationId,
        ]);
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
        ]);

        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'topic' => $request->input('topic'),
        ]);

        return response()->json(['conversation_id' => $conversation->id]);
    }

    public function ask(Request $request, $conversationId)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $conversation = Conversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found.'], 404);
        }

        // Save user's message
        $userMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'role' => 'user',
            'message' => $request->input('query'),
        ]);

        try {
            $client = new \GuzzleHttp\Client();
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
                'user_id' => auth()->id(),
                'role' => 'assistant',
                'message' => $result['choices'][0]['message']['content'],
            ]);

            return response()->json(['answer' => $assistantMessage->message]);
        } catch (\Exception $e) {
            \Log::error('OpenAI API Error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to fetch response. Please try again later.'], 500);
        }
    }

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
}
