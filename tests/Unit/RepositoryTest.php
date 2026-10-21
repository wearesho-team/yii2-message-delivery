<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Tests\DatabaseTrait;

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
}
