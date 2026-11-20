<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.ollama.url' => 'http://ollama.test',
            'services.ollama.model' => 'test-model',
        ]);
    }

    public function test_chatbot_page_can_be_opened(): void
    {
        $this->get(route('chatbot.index'))
            ->assertOk()
            ->assertSee('المساعد الذكي');
    }

    public function test_homepage_has_a_floating_link_to_the_chatbot(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('chatbot.index'), false)
            ->assertSee('aria-label="Open MoveIt chatbot"', false);
    }

    public function test_guest_message_is_saved_to_a_session_owned_conversation(): void
    {
        Http::fake([
            'ollama.test/api/chat' => Http::sequence()
                ->push(['message' => ['content' => 'A guest reply.']]),
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'Remember this detail.',
        ])
            ->assertOk()
            ->assertJsonPath('reply', 'A guest reply.');

        $conversation = ChatConversation::findOrFail($response->json('conversation_id'));

        $this->assertNull($conversation->user_id);
        $this->assertNotNull($conversation->session_key);
        $this->assertDatabaseCount('chat_messages', 2);
    }

    public function test_chatbot_context_uses_only_active_services_from_the_database(): void
    {
        Service::create([
            'name' => 'Furniture moving',
            'description' => 'Transport household furniture between locations.',
            'type' => 'moving',
            'unit' => 'per move',
            'price' => '45.000',
            'is_active' => true,
        ]);
        Service::create([
            'name' => 'Archived service',
            'description' => 'This service is inactive.',
            'type' => 'other',
            'unit' => 'fixed',
            'price' => '1.000',
            'is_active' => false,
        ]);

        Http::fake([
            'ollama.test/api/chat' => Http::response([
                'message' => ['content' => 'The website offers furniture moving.'],
            ]),
        ]);

        $this->postJson(route('chatbot.message'), [
            'message' => 'What services does this website offer?',
        ])->assertOk();

        Http::assertSent(fn (ClientRequest $request) =>
            str_contains($request['messages'][0]['content'], 'Answer only questions that relate to services')
            && str_contains($request['messages'][0]['content'], 'Never infer a currency for a numeric price')
            && str_contains($request['messages'][0]['content'], 'Choose the response language from the latest user message only')
            && str_contains($request['messages'][0]['content'], 'If the latest user message is in English, write the entire response in English only')
            && str_contains($request['messages'][0]['content'], 'When a catalog service matches the request, answer directly')
            && str_contains($request['messages'][0]['content'], 'Furniture moving')
            && str_contains($request['messages'][0]['content'], '45.000')
            && ! str_contains($request['messages'][0]['content'], 'Archived service')
        );
    }

    public function test_authenticated_conversation_persists_history_and_is_private(): void
    {
        Http::fake([
            'ollama.test/api/chat' => Http::sequence()
                ->push(['message' => ['content' => 'First answer.']])
                ->push(['message' => ['content' => 'Second answer.']]),
        ]);

        $owner = User::create([
            'name' => 'Chat Owner',
            'email' => 'chat-owner@example.com',
            'password' => 'password',
        ]);
        $this->actingAs($owner);

        $firstResponse = $this->postJson(route('chatbot.message'), [
            'message' => 'Remember this detail.',
        ])
            ->assertOk()
            ->assertJsonPath('reply', 'First answer.');
        $conversationId = $firstResponse->json('conversation_id');

        $this->postJson(route('chatbot.message'), [
            'conversation_id' => $conversationId,
            'message' => 'What was the detail?',
        ])
            ->assertOk()
            ->assertJsonPath('reply', 'Second answer.');

        Http::assertSent(fn (ClientRequest $request) =>
            count($request['messages']) === 4
            && $request['messages'][1]['content'] === 'Remember this detail.'
            && $request['messages'][2]['content'] === 'First answer.'
            && $request['messages'][3]['content'] === 'What was the detail?'
        );

        $conversation = ChatConversation::findOrFail($conversationId);
        $this->assertSame($owner->id, $conversation->user_id);
        $this->assertNull($conversation->session_key);

        $this->get(route('chatbot.index', ['conversation_id' => $conversationId]))
            ->assertOk()
            ->assertSee('Remember this detail.')
            ->assertSee('First answer.')
            ->assertSee('What was the detail?')
            ->assertSee('Second answer.');

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other-user@example.com',
            'password' => 'password',
        ]);
        $this->actingAs($otherUser)
            ->get(route('chatbot.index', ['conversation_id' => $conversationId]))
            ->assertNotFound();
    }

    public function test_message_is_required_before_calling_ollama(): void
    {
        Http::fake();

        $this->postJson(route('chatbot.message'), ['message' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');

        Http::assertNothingSent();
        $this->assertDatabaseCount('chat_conversations', 0);
    }

    public function test_ollama_failure_returns_a_gateway_error(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Unavailable'], 503),
        ]);

        $this->postJson(route('chatbot.message'), ['message' => 'Hello'])
            ->assertStatus(502)
            ->assertExactJson([
                'success' => false,
                'message' => 'Sorry, the AI assistant is currently unavailable.',
            ]);

        $this->assertDatabaseCount('chat_conversations', 0);
    }
}
