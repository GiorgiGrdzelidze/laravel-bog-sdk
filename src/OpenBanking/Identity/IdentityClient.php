<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\OpenBanking\Identity;

use GiorgiGrdzelidze\BogSdk\Exceptions\BogOpenBankingException;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto\IdentityAssuranceDto;
use GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto\IdentityRequestDto;

/**
 * Open Banking identity verification client.
 */
final class IdentityClient
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Perform an identity assurance check against the Open Banking API.
     *
     * @throws BogOpenBankingException
     */
    public function assure(IdentityRequestDto $request): IdentityAssuranceDto
    {
        try {
            $data = $this->http->post(
                'open_banking',
                $this->baseUrl.'/identity/v1/assurance',
                $request->toArray(),
            );
        } catch (\Throwable $e) {
            throw new BogOpenBankingException('Identity assurance request failed: '.$e->getMessage(), 0, $e);
        }

        return IdentityAssuranceDto::fromArray($data);
    }
}
