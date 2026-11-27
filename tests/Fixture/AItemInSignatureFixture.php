<?php

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

use TiMMiT\PHPStan\Rules\Tests\Stub\aItem;
use TiMMiT\PHPStan\Rules\Tests\Stub\Client;
use TiMMiT\PHPStan\Rules\Tests\Stub\Declaratie;

class AItemInSignatureFixture
{
    private ?Declaratie $Declaratie = null;

    private Client $Client;

    public function __construct(
        Client $Client,
        private Declaratie $promoted
    ) {
        $this->Client = $Client;
    }

    public function write(Declaratie $Declaratie, string $werknemer_declaratie_id): void
    {
    }

    public function nullable(?Declaratie $Declaratie): void
    {
    }

    public function union(Declaratie|string $value): void
    {
    }

    public function base(aItem $Item): void
    {
    }

    public function byId(string $werknemer_declaratie_id): Declaratie
    {
        $Declaratie = new Declaratie();
        $Declaratie->getSingle($werknemer_declaratie_id);

        return $Declaratie;
    }

    public function facade(Client $Client): void
    {
        $callback = static function (Declaratie $Declaratie): void {
        };
    }
}
