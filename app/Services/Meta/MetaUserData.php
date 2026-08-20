<?php

namespace App\Services\Meta;

use Illuminate\Http\Request;

/**
 * Matching signals for a server-side event. PII is normalized then SHA-256
 * hashed — raw email never leaves the app.
 */
class MetaUserData
{
    public function __construct(
        private readonly ?string $email,
        private readonly ?int $userId,
        private readonly ?string $ip,
        private readonly ?string $userAgent,
        private readonly ?string $fbp,
        private readonly ?string $fbc,
    ) {}

    /**
     * The Pixel sets _fbp/_fbc in the browser; the server reads them off the
     * request, so a queue worker never needs request context.
     */
    public static function fromRequest(Request $request): self
    {
        $user = $request->user();

        return new self(
            email: $user?->email,
            userId: $user?->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            fbp: $request->cookie('_fbp'),
            fbc: $request->cookie('_fbc'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'em' => $this->email ? [self::hash($this->email)] : null,
            'external_id' => $this->userId ? [self::hash((string) $this->userId)] : null,
            'client_ip_address' => $this->ip,
            'client_user_agent' => $this->userAgent,
            'fbp' => $this->fbp,
            'fbc' => $this->fbc,
        ]);
    }

    public static function hash(string $value): string
    {
        return hash('sha256', mb_strtolower(trim($value)));
    }
}
