<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto\IdentityAssuranceDto;
use GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto\IdentityRequestDto;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class OpenBankingTest extends TestCase
{
    public function test_identity_assurance(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'ob-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/identity/v1/assurance' => Http::response([
                'verified' => true,
                'first_name' => 'Giorgi',
                'last_name' => 'Grdzelidze',
                'confidence' => 'HIGH',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $request = new IdentityRequestDto(
            personalNumber: '01001012345',
            documentNumber: 'DOC123456',
            birthDate: '1990-01-01',
        );

        $result = $client->openBanking()->identity()->assure($request);

        $this->assertInstanceOf(IdentityAssuranceDto::class, $result);
        $this->assertTrue($result->verified);
        $this->assertSame('Giorgi', $result->firstName);
        $this->assertSame('HIGH', $result->confidence);
    }

    public function test_identity_request_dto_to_array(): void
    {
        $dto = new IdentityRequestDto('01001012345', 'DOC123', '1990-01-01');
        $array = $dto->toArray();

        $this->assertSame('01001012345', $array['personal_number']);
        $this->assertSame('DOC123', $array['document_number']);
        $this->assertSame('1990-01-01', $array['birth_date']);
    }

    public function test_identity_request_dto_excludes_nulls(): void
    {
        $dto = new IdentityRequestDto('01001012345');
        $array = $dto->toArray();

        $this->assertArrayHasKey('personal_number', $array);
        $this->assertArrayNotHasKey('document_number', $array);
        $this->assertArrayNotHasKey('birth_date', $array);
    }
}
