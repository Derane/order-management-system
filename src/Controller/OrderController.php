<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateOrderRequest;
use App\Entity\OrderStatus;
use App\Repository\OrderRepository;
use App\Service\OrderServiceInterface;
use App\Transformer\OrderToViewTransformerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderServiceInterface $orderService,
        private readonly OrderToViewTransformerInterface $orderTransformer,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $status = $this->parseStatus($request->query->get('status'));
        $dateFrom = $this->parseDate($request->query->get('date_from'));
        $dateTo = $this->parseDate($request->query->get('date_to'));
        $email = $request->query->get('email');

        $paginator = $this->orderRepository->findWithFilters(
            page: $page,
            limit: $limit,
            status: $status,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            email: $email,
        );

        $orders = [];
        foreach ($paginator as $order) {
            $orders[] = $this->orderTransformer->transform($order);
        }

        return $this->json([
            'data' => $orders,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => count($paginator),
                'pages' => (int) ceil(count($paginator) / $limit),
            ],
        ], context: ['groups' => ['order:read']]);
    }

    #[Route('/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->json(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }

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
            context: ['groups' => ['order:read']]
        );
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(
        int $id,
        #[MapRequestPayload] CreateOrderRequest $request
    ): JsonResponse {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->json(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $updatedOrder = $this->orderService->updateOrder($order, $request);
        $viewOrder = $this->orderTransformer->transform($updatedOrder);

        return $this->json(
            $viewOrder,
            context: ['groups' => ['order:read']]
        );
    }

    #[Route('/{id}', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->json(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->orderService->deleteOrder($order);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(
        '/{id}/status',
        methods: ['PATCH'],
        requirements: ['id' => '\d+']
    )]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $order = $this->orderRepository->find($id);

        if (!$order) {
            return $this->json(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);
        $statusValue = $data['status'] ?? null;

        if (!$statusValue) {
            return $this->json(
                ['error' => 'Status is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $status = $this->parseStatus($statusValue);
        if (!$status) {
            return $this->json(
                ['error' => 'Invalid status'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $updatedOrder = $this->orderService->updateOrderStatus(
                $order,
                $status
            );
            $viewOrder = $this->orderTransformer->transform($updatedOrder);

            return $this->json(
                $viewOrder,
                context: ['groups' => ['order:read']]
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    private function parseStatus(?string $status): ?OrderStatus
    {
        if ($status === null) {
            return null;
        }

        return OrderStatus::tryFrom($status);
    }

    private function parseDate(?string $date): ?\DateTimeInterface
    {
        if ($date === null) {
            return null;
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Exception) {
            return null;
        }
    }
}
