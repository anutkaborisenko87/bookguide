<?php

namespace App\Listener;

use App\Model\ErrorResponse;
use App\Service\ExceptionHandler\ExceptionMapping;
use App\Service\ExceptionHandler\ExceptionMappingResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ApiExceptionListener
{
    private ExceptionMappingResolver $resolver;
    private LoggerInterface $logger;
    private SerializerInterface $serializer;
    private bool $isDebug;
    public function __construct(ExceptionMappingResolver $resolver,
                                LoggerInterface $logger,
                                SerializerInterface $serializer,
                                bool $isDebug
    )
    {
        $this->resolver = $resolver;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->isDebug = $isDebug;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $mapping = $this->resolver->resolve(get_class($throwable));
        if ($mapping === null) {
            $mapping = ExceptionMapping::fromCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($mapping->getCode() >= Response::HTTP_INTERNAL_SERVER_ERROR || $mapping->isLoggable()) {
            $this->logger->error(
                $throwable->getMessage(),
                [
                    'trace' => $throwable->getTraceAsString(),
                    'previous' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getMessage() : ''
                ]);
        }

        $message = $mapping->isHidden() ? Response::$statusTexts[$mapping->getCode()] : $throwable->getMessage();
        $details = $this->isDebug ? ['trace' => $throwable->getTraceAsString()] : null;
        $data = $this->serializer->serialize(new ErrorResponse($message, $details), JsonEncoder::FORMAT);
        $event->setResponse(new JsonResponse($data, $mapping->getCode(), [], true));
    }

}
