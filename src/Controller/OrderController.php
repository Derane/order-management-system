<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateOrderRequest;
use App\DTO\ListOrdersQuery;
use App\DTO\UpdateOrderStatusRequest;
use App\Entity\Order;
use App\Repository\OrderRepositoryInterface;
use App\Service\OrderServiceInterface;
use App\Transformer\OrderToViewTransformerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderServiceInterface $orderService,
        private readonly OrderToViewTransformerInterface $orderTransformer,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(
        #[MapQueryString(
            serializationContext: [
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
            ]
        )]
        ListOrdersQuery $query
    ): JsonResponse {
        $paginator = $this->orderRepository->findWithFilters(
            page: $query->page,
            limit: $query->limit,
            status: $query->status,
            dateFrom: $query->dateFrom,
            dateTo: $query->dateTo,
            email: $query->email,
        );

        $orders = [];
        foreach ($paginator as $order) {
            $orders[] = $this->orderTransformer->transform($order);
        }

        return $this->json([
            'data' => $orders,
            'meta' => [
                'page' => $query->page,
                'limit' => $query->limit,
                'total' => count($paginator),
                'pages' => (int) ceil(count($paginator) / $query->limit),
            ],
        ], context: ['groups' => ['order:read']]);
    }

    #[Route('/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        #[MapEntity(id: 'id')] Order $order
    ): JsonResponse {
        $viewOrder = $this->orderTransformer->transform($order);

        return $this->json(
            $viewOrder,
            context: ['groups' => ['order:read']]
        );
    }

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateOrderRequest $request
    ): JsonResponse {
        $order = $this->orderService->createOrder($request);
        $viewOrder = $this->orderTransformer->transform($order);

        return $this->json(
            $viewOrder,
            Response::HTTP_CREATED,
            [
                'Location' => sprintf('/api/orders/%d', $order->getId()),
            ],
            context: ['groups' => ['order:read']]
        );
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(
        #[MapEntity(id: 'id')] Order $order,
        #[MapRequestPayload] CreateOrderRequest $request
    ): JsonResponse {
        $updatedOrder = $this->orderService->updateOrder($order, $request);
        $viewOrder = $this->orderTransformer->transform($updatedOrder);

        return $this->json(
            $viewOrder,
            context: ['groups' => ['order:read']]
        );
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(
        #[MapEntity(id: 'id')] Order $order
    ): JsonResponse {
        $this->orderService->deleteOrder($order);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(
        '/{id}/status',
        requirements: ['id' => '\d+'],
        methods: ['PATCH']
    )]
    public function updateStatus(
        #[MapEntity(id: 'id')] Order $order,
        #[MapRequestPayload] UpdateOrderStatusRequest $request
    ): JsonResponse {
        try {
            $updatedOrder = $this->orderService->updateOrderStatus(
                $order,
                $request->status
            );
            $viewOrder = $this->orderTransformer->transform($updatedOrder);

            return $this->json(
                $viewOrder,
                context: ['groups' => ['order:read']]
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                [
                    'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                    'title' => 'Bad Request',
                    'status' => 400,
                    'detail' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/problem+json']
            );
        }
    }
}
