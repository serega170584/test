<?php

declare(strict_types=1);

namespace App\Tests\Unit\Endpoints\Shops\v1;

use App\Bus\BusManager;
use App\Endpoints\Shops\v1\ShopGroupImportFileEndpoint;
use App\UseCase\Command\Import\ImportCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ShopGroups\Shops\v1\ImportFileRequest;
use Symfony\Component\Uid\UuidV7;

class ShopGroupImportFileTransportTest extends TestCase
{
    private ShopGroupImportFileEndpoint $transport;

    private MockObject|BusManager $busManager;

    protected function setUp(): void
    {
        $this->busManager = $this->createMock(BusManager::class);
        $this->transport = new ShopGroupImportFileEndpoint($this->busManager);
    }

    /**
     * @dataProvider dataProviderImportFileRequest
     */
    public function testInvoke(array $consumers, array $shopGroups, array $shopsToShopGroups): void
    {
        $commandImport = null;
        $this->busManager
            ->method('executeImportCommand')
            ->willReturnCallback(
                static function (ImportCommand $command) use (&$commandImport) {
                    $commandImport = $command;
                }
            );

        $request = new ImportFileRequest();
        $request->setConsumers($this->convertToCSV($consumers));
        $request->setShopGroups($this->convertToCSV($shopGroups));
        $request->setShopsToShopGroups($this->convertToCSV($shopsToShopGroups));

        ($this->transport)($request);

        static::assertCount(count($consumers), $commandImport->consumers);

        foreach ($commandImport->consumers as $index => $item) {
            static::assertEquals($consumers[$index]['code'], $item->code);
            static::assertEquals($consumers[$index]['title'], $item->title);
            static::assertEquals($consumers[$index]['description'], $item->description);
        }

        static::assertCount(count($shopGroups), $commandImport->shopGroups);

        foreach ($commandImport->shopGroups as $index => $item) {
            static::assertEquals($shopGroups[$index]['code'], $item->code);
            static::assertEquals($shopGroups[$index]['title'], $item->title);
            static::assertEquals($shopGroups[$index]['description'], $item->description);
            static::assertEquals($shopGroups[$index]['parent_code'], $item->parentCode);
            static::assertEquals($shopGroups[$index]['active'] === '1', $item->active);
            static::assertEquals($shopGroups[$index]['consumer_code'], implode(',', $item->consumers));
            static::assertEquals($shopGroups[$index]['fias_id'], $item->fiasId);
        }

        static::assertCount(count($shopsToShopGroups), $commandImport->relationshipOfStoreGroupsToShopItems);

        foreach ($commandImport->relationshipOfStoreGroupsToShopItems as $index => $item) {
            static::assertEquals($shopsToShopGroups[$index]['uf_xml_id'], $item->ufXmlId);
            static::assertEquals($shopsToShopGroups[$index]['shop_group_code'], $item->shopGroupCode);
        }
    }

    public function dataProviderImportFileRequest(): iterable
    {
        yield [
            'consumers' => [
                [
                    'code' => 'mobile',
                    'title' => 'ћобильное приложение',
                    'description' => 'ќписание',
                ],
                [
                    'code' => 'front',
                    'title' => '‘ронт сайт',
                    'description' => 'ќписание\'',
                ],
            ],
            'shopGroups' => [
                [
                    'code' => 'sg_moscow',
                    'title' => 'ћосква (вс€)',
                    'description' => 'ѕримечание',
                    'parent_code' => '',
                    'active' => '1',
                    'consumer_code' => 'front',
                    'fias_id' => UuidV7::generate(),
                ],
                [
                    'code' => 'sg_moscow_north',
                    'title' => 'ћосква (север)',
                    'description' => 'ѕримечание',
                    'parent_code' => 'sg_moscow',
                    'active' => '0',
                    'consumer_code' => 'mobile',
                    'fias_id' => UuidV7::generate(),
                ],
            ],
            'shopsToShopGroups' => [
                [
                    'uf_xml_id' => '108537',
                    'shop_group_code' => 'sg_moscow_north',
                ],
                [
                    'uf_xml_id' => '108559',
                    'shop_group_code' => 'sg_moscow_north',
                ],
            ],
        ];
    }

    private function convertToCSV(array $data): string
    {
        $csvRows = [];

        array_unshift($data, array_keys(current($data)));

        foreach ($data as $row) {
            $csvRows[] = implode(';', $row);
        }

        return implode(PHP_EOL, $csvRows);
    }
}
