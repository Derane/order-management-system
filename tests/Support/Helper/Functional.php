<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;

class Functional extends Module
{
    public function clearDatabase(): void
    {
        $doctrine = $this->getModule('Doctrine2');
        $em = $doctrine->_getEntityManager();

        $connection = $em->getConnection();

        $connection->executeStatement('DELETE FROM order_items');
        $connection->executeStatement('DELETE FROM orders');

        try {
            $connection->executeStatement('ALTER SEQUENCE orders_id_seq RESTART WITH 1');
            $connection->executeStatement('ALTER SEQUENCE order_items_id_seq RESTART WITH 1');
        } catch (\Exception $e) {
        }
    }

    public function createTestOrder(
        string $customerName = 'Test Customer',
        string $customerEmail = 'test@example.com',
        array $items = []
    ): array {
        if (empty($items)) {
            $items = [
                [
                    'productName' => 'Test Product',
                    'quantity' => 2,
                    'price' => 15.99,
                ],
            ];
        }

        $rest = $this->getModule('REST');

        $orderData = [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'items' => $items,
        ];

        $rest->sendPOST('/api/orders', $orderData);
        $rest->seeResponseCodeIs(201);

        return json_decode($rest->grabResponse(), true);
    }

    public function seeResponseMatchesOrderStructure(): void
    {
        $rest = $this->getModule('REST');

        $rest->seeResponseJsonMatchesJsonPath('$.id');
        $rest->seeResponseJsonMatchesJsonPath('$.customerName');
        $rest->seeResponseJsonMatchesJsonPath('$.customer_email');
        $rest->seeResponseJsonMatchesJsonPath('$.totalAmount');
        $rest->seeResponseJsonMatchesJsonPath('$.status');
        $rest->seeResponseJsonMatchesJsonPath('$.createdAt');
        $rest->seeResponseJsonMatchesJsonPath('$.updatedAt');
        $rest->seeResponseJsonMatchesJsonPath('$.items');
    }

    public function seeResponseMatchesPaginationStructure(): void
    {
        $rest = $this->getModule('REST');

        $rest->seeResponseJsonMatchesJsonPath('$.data');
        $rest->seeResponseJsonMatchesJsonPath('$.meta.page');
        $rest->seeResponseJsonMatchesJsonPath('$.meta.limit');
        $rest->seeResponseJsonMatchesJsonPath('$.meta.total');
        $rest->seeResponseJsonMatchesJsonPath('$.meta.pages');
    }
}
