<?php

declare(strict_types=1);

namespace App\Service\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Exception\LogicException;

final class MessageBusWrapper
{

    /**
     * @param MessageBusInterface $bus
     */
    public function __construct(private readonly MessageBusInterface $bus) {}

    /**
     * @param object $message
     * @return void
     */
    public function dispatch(object $message): void
    {
        $this->bus->dispatch($message);
    }


    /**
     * @param object $message
     * @return mixed
     */
    public function dispatchWithResult(object $message): mixed
    {
        $envelope = $this->bus->dispatch($message);
        $handled = $envelope->last(HandledStamp::class);

        if (!$handled) {
            throw new LogicException(sprintf(
                'No HandledStamp found for %s. Are you running sync and do you have a handler?',
                $message::class
            ));
        }

        return $handled->getResult();
    }
}
