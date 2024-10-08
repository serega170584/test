<?php

namespace App\Tests\Sample;


use App\Bus\BusManager;
use App\DataFixtures\AppFixtures;
use App\Endpoints\Shops\v1\ShopGroupExportEndpoint;
use App\Endpoints\Shops\v1\ShopGroupImportFileEndpoint;
use Test\PhpServicesBundle\Bus\MessengerBus;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use Test\PhpServicesBundle\TestHelpers\RefreshDatabase;
use ShopGroups\Shops\v1\ExportRequest;
use ShopGroups\Shops\v1\ImportFileRequest;
use ShopGroups\Shops\v1\ShopGroupHttpClient;
use Spiral\RoadRunner\GRPC\Context;

class ShopGroupTest extends BaseTestClass
{

    use RefreshDatabase;

    /**
     * @group ignore
     */
    public function testExportToImport() {
        $exportEndpoint = new ShopGroupExportEndpoint(new BusManager(self::getContainer()->get(MessengerBus::class)));
        $importEndpoint = new ShopGroupImportFileEndpoint(new BusManager(self::getContainer()->get(MessengerBus::class)));
        $fixtures = new AppFixtures(self::getContainer()->get(FakerGeneratorFactory::class));

        $fixtures->load(self::getContainer()->get('doctrine.orm.entity_manager'));

        $resultExport1 = $exportEndpoint(new ExportRequest());

        $importRequest = new ImportFileRequest();
        $importRequest->setShopGroups($resultExport1->getShopGroups());
        $importRequest->setShopsToShopGroups($resultExport1->getShopsToShopGroups());
        $importRequest->setConsumers($resultExport1->getConsumers());
        $resultImport = $importEndpoint($importRequest);

        $resultExport2 = $exportEndpoint(new ExportRequest());

        self::assertEquals($resultExport1->getConsumers(), $resultExport2->getConsumers());
        self::assertEquals($resultExport1->getShopGroups(), $resultExport2->getShopGroups());
        self::assertEquals($resultExport1->getShopsToShopGroups(), $resultExport2->getShopsToShopGroups());

    }
}
