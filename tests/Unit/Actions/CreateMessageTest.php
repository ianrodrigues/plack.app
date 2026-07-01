<?php

declare(strict_types=1);

use App\Actions\CreateMessage;
use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('may create messages', function (): void {
    $channel = Channel::factory()->create();
    $user = User::factory()->create();

    $message = resolve(CreateMessage::class)->handle(
        $channel,
        $user,
        'Hello, world!',
    );

    expect($message)
        ->toBeInstanceOf(Message::class)
        ->and($message->channel->id)->toBe($channel->id)
        ->and($message->user->id)->toBe($user->id)
        ->and($message->body)->toBe('Hello, world!');
});

it('may create messages with attachments', function (): void {
    Storage::fake('local');

    $channel = Channel::factory()->create();
    $user = User::factory()->create();

    $message = resolve(CreateMessage::class)->handle(
        $channel,
        $user,
        null,
        [UploadedFile::fake()->image('screenshot.png')],
    );

    $attachment = $message->attachments->sole();

    expect($message->body)->toBeNull()
        ->and($attachment->user_id)->toBe($user->id)
        ->and($attachment->workspace_id)->toBe($channel->workspace_id)
        ->and($attachment->original_filename)->toBe('screenshot.png')
        ->and($attachment->mime_type)->toBe('image/png')
        ->and($attachment->storage_key)->toStartWith(sprintf('workspaces/%s/attachments/', $channel->workspace_id))
        ->and($attachment->storage_key)->not->toContain('screenshot');

    Storage::disk('local')->assertExists($attachment->storage_key);
});
