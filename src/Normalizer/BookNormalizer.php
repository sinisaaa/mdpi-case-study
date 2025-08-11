<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Book;
use App\Helpers\BookHelper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BookNormalizer implements NormalizerInterface
{

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @param Book $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $normalizedData['isbn'] = $object->getIsbn() ? BookHelper::formatISBN($object->getIsbn(), true) : null;

        return $normalizedData;
    }

    /**
     * @param $data
     * @param string|null $format
     * @param array $context
     * @return bool
     */
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Book;
    }

    /**
     * @param string|null $format
     * @return true[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Book::class => true,
        ];
    }

}
