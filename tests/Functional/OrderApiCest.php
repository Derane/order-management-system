<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

final class OrderApiCest
{
    public function _before(FunctionalTester $tester): void
    {
        $this->clearDatabase($tester);
    }

    public function testCreateOrderSuccess(FunctionalTester $tester): void
    {
        $tester->wantTo('create a new order successfully');

        $orderData = [
            'customerName' => 'John Doe',
            'customerEmail' => 'john@example.com',
            'items' => [
                [
                    'productName' => 'Laptop',
                    'quantity' => 1,
                    'price' => 999.99,
                ],
                [
                    'productName' => 'Mouse',
                    'quantity' => 2,
                    'price' => 25.50,
                ],
            ],
        ];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPOST('/api/orders', $orderData);

        $tester->seeResponseCodeIs(201);
        $tester->seeResponseIsJson();
        $this->seeResponseMatchesOrderStructure($tester);

        $tester->seeResponseContainsJson([
            'customerName' => 'John Doe',
            'customer_email' => 'john@example.com',
            'totalAmount' => '1050.99',
            'status' => 'pending',
        ]);

        $tester->seeResponseJsonMatchesJsonPath('$.items[0].productName');
        $tester->seeResponseJsonMatchesJsonPath('$.items[1].productName');

        $response = json_decode($tester->grabResponse(), true);
        $tester->assertCount(2, $response['items']);
    }

    public function testCreateOrderValidationErrors(FunctionalTester $tester): void
    {
        $tester->wantTo('test validation errors when creating order');

        $invalidOrderData = [
            'customerName' => '',
            'customerEmail' => 'invalid-email',
            'items' => [],
        ];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPOST('/api/orders', $invalidOrderData);

        $tester->seeResponseCodeIs(422);
        $tester->seeResponseIsJson();
        $tester->seeResponseJsonMatchesJsonPath('$.detail');
        $tester->seeResponseContainsJson(['status' => 422, 'title' => 'Unprocessable Entity']);
    }

    public function testCreateOrderWithInvalidItemData(FunctionalTester $tester): void
    {
        $tester->wantTo('test validation for invalid item data');

        $invalidOrderData = [
            'customerName' => 'John Doe',
            'customerEmail' => 'john@example.com',
            'items' => [
                [
                    'productName' => '',
                    'quantity' => 0,
                    'price' => -5.99,
                ],
            ],
        ];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPOST('/api/orders', $invalidOrderData);

        $tester->seeResponseCodeIs(422);
        $tester->seeResponseIsJson();
        $tester->seeResponseJsonMatchesJsonPath('$.detail');
        $tester->seeResponseContainsJson(['status' => 422, 'title' => 'Unprocessable Entity']);
    }

    public function testGetOrdersList(FunctionalTester $tester): void
    {
        $tester->wantTo('get list of orders');

        $this->createTestOrder($tester, 'Customer 1', 'customer1@example.com');
        $this->createTestOrder($tester, 'Customer 2', 'customer2@example.com');

        $tester->sendGET('/api/orders');

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $this->seeResponseMatchesPaginationStructure($tester);

        $response = json_decode($tester->grabResponse(), true);
        $tester->assertCount(2, $response['data']);
        $tester->assertEquals(2, $response['meta']['total']);
    }

    public function testGetSingleOrder(FunctionalTester $tester): void
    {
        $tester->wantTo('get a single order by ID');

        $order = $this->createTestOrder($tester);
        $orderId = $order['id'];

        $tester->sendGET("/api/orders/{$orderId}");

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $this->seeResponseMatchesOrderStructure($tester);

        $tester->seeResponseContainsJson([
            'id' => $orderId,
            'customerName' => 'Test Customer',
            'customer_email' => 'test@example.com',
        ]);
    }

    public function testGetNonExistentOrder(FunctionalTester $tester): void
    {
        $tester->wantTo('test getting non-existent order returns 404');

        $tester->sendGET('/api/orders/99999');

        $tester->seeResponseCodeIs(404);
        $tester->seeResponseIsJson();
        $tester->seeResponseContainsJson(['status' => 404, 'title' => 'Not Found']);
    }

    public function testOrdersPagination(FunctionalTester $tester): void
    {
        $tester->wantTo('test pagination functionality');

        for ($i = 1; $i <= 5; $i++) {
            $this->createTestOrder($tester, "Customer {$i}", "customer{$i}@example.com");
        }

        $tester->sendGET('/api/orders?page=1&limit=2');

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $this->seeResponseMatchesPaginationStructure($tester);

        $tester->seeResponseContainsJson([
            'meta' => [
                'page' => 1,
                'limit' => 2,
                'total' => 5,
                'pages' => 3,
            ],
        ]);

        $response = json_decode($tester->grabResponse(), true);
        $tester->assertCount(2, $response['data']);
    }

    public function testOrdersFilterByEmail(FunctionalTester $tester): void
    {
        $tester->wantTo('test filtering orders by email');

        $this->createTestOrder($tester, 'John Doe', 'john@example.com');
        $this->createTestOrder($tester, 'Jane Smith', 'jane@example.com');
        $this->createTestOrder($tester, 'Bob Wilson', 'bob@different.com');

        $tester->sendGET('/api/orders?email=john');

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();

        $response = json_decode($tester->grabResponse(), true);
        $tester->assertCount(1, $response['data']);
        $tester->assertEquals('john@example.com', $response['data'][0]['customer_email']);
    }

    public function testUpdateOrder(FunctionalTester $tester): void
    {
        $tester->wantTo('update an existing order');

        $order = $this->createTestOrder($tester, 'Original Customer', 'original@example.com');
        $orderId = $order['id'];

        $updateData = [
            'customerName' => 'Updated Customer',
            'customerEmail' => 'updated@example.com',
            'items' => [
                [
                    'productName' => 'Updated Product',
                    'quantity' => 3,
                    'price' => 33.33,
                ],
            ],
        ];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPUT("/api/orders/{$orderId}", $updateData);

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $this->seeResponseMatchesOrderStructure($tester);

        $tester->seeResponseContainsJson([
            'id' => $orderId,
            'customerName' => 'Updated Customer',
            'customer_email' => 'updated@example.com',
            'totalAmount' => '99.99',
        ]);
    }

    public function testUpdateOrderStatus(FunctionalTester $tester): void
    {
        $tester->wantTo('update order status');

        $order = $this->createTestOrder($tester);
        $orderId = $order['id'];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPATCH("/api/orders/{$orderId}/status", json_encode(['status' => 'shipped']));

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $tester->seeResponseContainsJson([
            'id' => $orderId,
            'status' => 'shipped',
        ]);
    }

    public function testUpdateOrderStatusMissing(FunctionalTester $tester): void
    {
        $tester->wantTo('test updating order with missing status');

        $order = $this->createTestOrder($tester);
        $orderId = $order['id'];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPATCH("/api/orders/{$orderId}/status", json_encode([]));
        $tester->seeResponseCodeIs(422);
        $tester->seeResponseIsJson();
        $tester->seeResponseContainsJson(['status' => 422, 'title' => 'Unprocessable Entity']);
    }

    public function testUpdateOrderStatusInvalid(FunctionalTester $tester): void
    {
        $tester->wantTo('test updating order with invalid status');

        $order = $this->createTestOrder($tester);
        $orderId = $order['id'];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPATCH("/api/orders/{$orderId}/status", json_encode(['status' => 'invalid_status']));
        $tester->seeResponseCodeIs(422);
        $tester->seeResponseIsJson();
        $tester->seeResponseContainsJson(['status' => 422, 'title' => 'Unprocessable Entity']);
    }

    public function testDeleteOrder(FunctionalTester $tester): void
    {
        $tester->wantTo('delete an order');

        $order = $this->createTestOrder($tester);
        $orderId = $order['id'];

        $tester->sendDELETE("/api/orders/{$orderId}");

        $tester->seeResponseCodeIs(204);

        $tester->sendGET("/api/orders/{$orderId}");
        $tester->seeResponseCodeIs(404);
    }

    public function testDeleteNonExistentOrder(FunctionalTester $tester): void
    {
        $tester->wantTo('test deleting non-existent order returns 404');

        $tester->sendDELETE('/api/orders/99999');

        $tester->seeResponseCodeIs(404);
        $tester->seeResponseContainsJson(['status' => 404, 'title' => 'Not Found']);
    }

    private function clearDatabase(FunctionalTester $tester): void
    {
        $doctrine = $tester->grabService('doctrine');
        $em = $doctrine->getManager();
        $connection = $em->getConnection();
        $connection->executeStatement('DELETE FROM order_items');
        $connection->executeStatement('DELETE FROM orders');

        try {
            $connection->executeStatement('ALTER SEQUENCE orders_id_seq RESTART WITH 1');
            $connection->executeStatement('ALTER SEQUENCE order_items_id_seq RESTART WITH 1');
        } catch (\Exception $e) {
        }
    }

    private function createTestOrder(
        FunctionalTester $tester,
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

        $orderData = [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'items' => $items,
        ];

        $tester->haveHttpHeader('Content-Type', 'application/json');
        $tester->sendPOST('/api/orders', $orderData);
        $tester->seeResponseCodeIs(201);

        return json_decode($tester->grabResponse(), true);
    }

    private function seeResponseMatchesOrderStructure(FunctionalTester $tester): void
    {
        $tester->seeResponseJsonMatchesJsonPath('$.id');
        $tester->seeResponseJsonMatchesJsonPath('$.customerName');
        $tester->seeResponseJsonMatchesJsonPath('$.customer_email');
        $tester->seeResponseJsonMatchesJsonPath('$.totalAmount');
        $tester->seeResponseJsonMatchesJsonPath('$.status');
        $tester->seeResponseJsonMatchesJsonPath('$.createdAt');
        $tester->seeResponseJsonMatchesJsonPath('$.updatedAt');
        $tester->seeResponseJsonMatchesJsonPath('$.items');
    }

    private function seeResponseMatchesPaginationStructure(FunctionalTester $tester): void
    {
        $tester->seeResponseJsonMatchesJsonPath('$.data');
        $tester->seeResponseJsonMatchesJsonPath('$.meta.page');
        $tester->seeResponseJsonMatchesJsonPath('$.meta.limit');
        $tester->seeResponseJsonMatchesJsonPath('$.meta.total');
        $tester->seeResponseJsonMatchesJsonPath('$.meta.pages');
    }
}
