<?php

namespace App\Command;

use App\Repository\ProductRepository;
use App\Service\ProductImageMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-product-images',
    description: 'Update each product image URL to match its name and category.',
)]
final class SyncProductImagesCommand extends Command
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductImageMatcher $imageMatcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $products = $this->productRepository->findAll();
        $updated = 0;

        foreach ($products as $product) {
            $newUrl = $this->imageMatcher->resolveForProduct($product);
            if ($product->getImage() !== $newUrl) {
                $product->setImage($newUrl);
                ++$updated;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Updated images for %d of %d products.', $updated, \count($products)));
        $io->note('Refresh the shop page to see new photos.');

        return Command::SUCCESS;
    }
}
