<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Console;

use Illuminate\Console\Command;

/**
 * Artisan command to publish the Apple Pay merchant domain association file.
 */
final class PublishAppleDomainAssociationCommand extends Command
{
    protected $signature = 'bog-sdk:publish-apple-domain-association';

    protected $description = 'Publish the Apple Pay merchant domain association file to public/.well-known/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDir = public_path('.well-known');

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $sourcePath = config(
            'bog-sdk.open_banking.apple_pay_domain_association_source',
            __DIR__.'/../../resources/apple-pay/apple-developer-merchantid-domain-association',
        );

        $targetPath = $targetDir.'/apple-developer-merchantid-domain-association';

        if (! file_exists($sourcePath)) {
            $this->error("Source file not found at: {$sourcePath}");
            $this->info('Create the file or update the config key: bog-sdk.open_banking.apple_pay_domain_association_source');

            return self::FAILURE;
        }

        copy($sourcePath, $targetPath);
        $this->info("Apple Pay domain association file published to: {$targetPath}");

        return self::SUCCESS;
    }
}
