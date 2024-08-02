<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatHistory;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Sends a message to the AI model and saves the conversation history.
     *
     * @param Request $request The incoming request containing the user's message and optional model parameter.
     * @return \Illuminate\Http\JsonResponse The response containing the AI's reply or an error message.
     */
    public function sendMessage(Request $request)
    {
        // Get the user's message from the request
        $userMessage = $request->input('message');
        // Get the user's ID from the request
        $userId = $request->user()->id;
        // Get the model from the request, default to 'gpt-3.5-turbo'
        $model = $request->input('model', 'gpt-3.5-turbo');

        // Get the OpenAI API key from the environment variables
        $openAiApiKey = env('OPENAI_API_KEY');

        // Check if the OpenAI API key is set
        if (!$openAiApiKey) {
            // Log an error if the OpenAI API key is not set
            Log::error('OpenAI API key not found');
            // Return a JSON response with an error message
            return response()->json(['error' => 'OpenAI API key not found'], 500);
        }

        // Make a POST request to the OpenAI API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $openAiApiKey,
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        // Check if the request failed
        if ($response->failed()) {
            // Log an error if the request failed
            Log::error('Failed to communicate with OpenAI API', ['response' => $response->json()]);
            // Return a JSON response with an error message
            return response()->json(['error' => 'Failed to communicate with OpenAI API', 'details' => $response->json()], 500);
        }

        // Get the response data
        $responseData = $response->json();
        // Check if the response data contains the expected structure
        // isset($var, ...$vars): Determine if a variable is considered set, this means if a variable is declared and is different than null .
        if (!isset($responseData['choices']) || empty($responseData['choices'])) {
            // Log an error if the response data does not contain the expected structure
            Log::error('Unexpected response structure from OpenAI API', ['response' => $responseData]);
            // Return a JSON response with an error message
            return response()->json(['error' => 'Unexpected response structure from BE OpenAI API'], 500);
        }

        // Get the AI's reply from the response data
        $aiReply = $responseData['choices'][0]['message']['content'];

        // Save the chat history
        ChatHistory::create(['message' => $userMessage, 'is_user' => true, 'user_id' => $userId]);
        ChatHistory::create(['message' => $aiReply, 'is_user' => false, 'user_id' => $userId]);

        // Return a JSON response with the AI's reply
        return response()->json(['choices' => [['message' => ['content' => $aiReply]]]]);    }

    /**
     * Retrieves the chat history for the authenticated user.
     *
     * @param Request $request The incoming request containing the authenticated user.
     * @return \Illuminate\Http\JsonResponse The response containing the chat history or a welcome message.
     */
    public function getChatHistory(Request $request)
    {
        $userId = $request->user()->id;
        $userName = $request->user()->name;
        $history = ChatHistory::where('user_id', $userId)->get();

        // Check if the user has no chat history
        if ($history->isEmpty()) {
            return response()->json([
                'history' => [],
                'welcomeMessage' => "Hi, $userName. How may I assist you?"
            ]);
        }

        return response()->json([
            'history' => $history,
            'welcomeMessage' => null
        ]);
    }
}
