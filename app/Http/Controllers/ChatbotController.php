<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Service;
use App\Services\OllamaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ChatbotController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        if ($request->filled('conversation_id')) {
            $this->ownedConversations($request)->findOrFail($request->integer('conversation_id'));
        }

        $destination = $request->user() ? route('home') : url('/');
        $query = ['chat' => 'open'];
        if ($request->boolean('new')) {
            $query['new'] = '1';
        } elseif ($request->filled('conversation_id')) {
            $query['conversation_id'] = $request->integer('conversation_id');
        }

        return redirect()->to($destination.'?'.http_build_query($query));
    }

    public function history(Request $request, int $conversation): JsonResponse
    {
        $conversation = $this->ownedConversations($request)
            ->findOrFail($conversation);
        $messages = $conversation->messages()->latest('id')->limit(100)->get()->reverse();

        return response()->json([
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $messages->map(static fn (ChatMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])->values(),
        ]);
    }

    public function send(Request $request, OllamaService $ollama): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $conversation = isset($validated['conversation_id'])
            ? $this->ownedConversations($request)->findOrFail($validated['conversation_id'])
            : null;

        $history = $conversation
            ? $conversation->messages()
                ->orderByDesc('id')
                ->limit(20)
                ->get()
                ->reverse()
                ->map(static fn (ChatMessage $message): array => [
                    'role' => $message->role,
                    'content' => $message->content,
                ])
                ->values()
                ->all()
            : [];

        $serviceCatalog = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'description', 'type', 'unit', 'price'])
            ->map(static fn ($service): array => [
                'name' => $service->name,
                'description' => $service->description,
                'type' => $service->type,
                'unit' => $service->unit,
                'price' => $service->price,
            ])
            ->all();

        try {
            $reply = $ollama->getResponse(
                $validated['message'],
                $history,
                $serviceCatalog
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Sorry, the AI assistant is currently unavailable.'
            ], 502);
        }

        $conversation = DB::transaction(function () use ($conversation, $request, $validated, $reply) {
            if ($conversation === null) {
                $user = $request->user();

                $conversation = ChatConversation::create([
                    'user_id' => $user?->getAuthIdentifier(),
                    'session_key' => $user
                        ? null
                        : hash('sha256', $request->session()->getId()),
                    'title' => Str::limit($validated['message'], 120),
                ]);
            }

            $conversation->messages()->create([
                'role' => 'user',
                'content' => $validated['message'],
            ]);
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $reply,
            ]);
            $conversation->touch();

            return $conversation;
        });

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
        ]);
    }

    /** @return Builder<ChatConversation> */
    private function ownedConversations(Request $request): Builder
    {
        if ($request->user()) {
            return ChatConversation::query()
                ->where('user_id', $request->user()->getAuthIdentifier());
        }

        return ChatConversation::query()
            ->whereNull('user_id')
            ->where('session_key', hash('sha256', $request->session()->getId()));
    }
}
