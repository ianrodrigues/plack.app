<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final readonly class CreateMessage
{
    public function __construct(
        private CreateAttachment $createAttachment,
    ) {}

    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    public function handle(Channel $channel, User $user, ?string $body = null, array $attachments = []): Message
    {
        return DB::transaction(function () use ($channel, $user, $body, $attachments): Message {
            $message = $channel->messages()->create([
                'user_id' => $user->id,
                'body' => $body,
            ]);

            foreach ($attachments as $file) {
                $this->createAttachment->handle($channel, $message, $user, $file);
            }

            return $message;
        });
    }
}
