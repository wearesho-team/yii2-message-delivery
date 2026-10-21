<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Tests\DatabaseTrait;
use yii\db\IntegrityException;

class RepositoryTest extends Delivery\Test\History\RepositoryTest
{
    use DatabaseTrait;

    protected function setUp(): void
    {
        $this->setUpDatabase();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // rows with shared external ID do not allow to restore previous unique index while reverting migrations
        Delivery\Yii2\HistoryItem::deleteAll();
        parent::tearDown();
        $this->tearDownDatabase();
    }

    protected function createRepository(): Delivery\History\RepositoryInterface
    {
        return new Delivery\Yii2\Repository();
    }

    public function testBatch(): void
    {
        $this->markTestIncomplete(
            'Repository::batch() returns items without database IDs: batchInsert() does not return inserted IDs.'
        );
    }

    /**
     * Result is searched by service name, so the new one is used after update.
     */
    public function testUpdate(): void
    {
        $item = $this->repository->add('serviceNameTest', $this->createResult('msg_789'));
        $updatedResult = $this->createResult('msg_789', Delivery\Result\Status::Delivered);

        $updatedItem = $this->repository->update($item, $updatedResult, 'serviceNameTestTwo');

        $this->assertEquals($item->id(), $updatedItem->id());
        $this->validateItem($updatedItem, $updatedResult, 'serviceNameTestTwo');
        $this->assertNull($this->repository->getByResultId('serviceNameTest', 'msg_789'));
        $retrievedByResultId = $this->repository->getByResultId('serviceNameTestTwo', 'msg_789');
        $this->assertNotNull($retrievedByResultId);
        $this->assertEquals($updatedItem->id(), $retrievedByResultId->id());
        $this->assertEquals(Delivery\Result\Status::Delivered, $retrievedByResultId->result()->status());
    }

    public function testBatchWithSharedResultId(): void
    {
        // messages sent within single bulk request share result ID
        $results = [
            $this->createRecipientResult('sig_bulk', '2340000000001'),
            $this->createRecipientResult('sig_bulk', '2340000000002'),
            $this->createRecipientResult('sig_single', '2340000000001'),
        ];

        $items = $this->repository->batch('termii', $results);

        $this->assertCount(3, $items);
        $this->assertEquals(3, Delivery\Yii2\HistoryItem::find()->count());
        $this->assertEquals(2, Delivery\Yii2\HistoryItem::find()->andWhere(['external_id' => 'sig_bulk'])->count());

        // the first stored message is returned for shared result ID
        $item = $this->repository->getByResultId('termii', 'sig_bulk');
        $this->assertNotNull($item);
        $this->assertEquals('2340000000001', $item->result()->message()->getRecipient());
    }

    public function testBatchWithSharedResultIdAndRecipient(): void
    {
        $this->expectException(IntegrityException::class);
        $this->repository->batch('termii', [
            $this->createRecipientResult('sig_bulk', '2340000000001'),
            $this->createRecipientResult('sig_bulk', '2340000000001'),
        ]);
    }

    private function createRecipientResult(string $messageId, string $recipient): Delivery\ResultInterface
    {
        return new Delivery\Result(
            messageId: $messageId,
            message: new Delivery\Message('Promo', $recipient),
            status: Delivery\Result\Status::Accepted,
        );
    }
}
