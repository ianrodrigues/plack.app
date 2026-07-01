<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateMessage;
use App\Http\Requests\CreateMessageRequest;
use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final readonly class MessageController
{
    public function store(
        CreateMessageRequest $request,
        #[CurrentUser] User $user,
        Workspace $workspace,
        Channel $channel,
        CreateMessage $createMessage,
    ): RedirectResponse {
        $body = $request->string('body')->value();

        $createMessage->handle($channel, $user, $body);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Message sent.'),
        ]);

        return back();
    }
}
