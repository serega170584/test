<?php

namespace App\DataFixtures;

use App\Entity\ConsumerEntity;
use App\Entity\ShopGroupEntity;
use App\Enum\PrefixEnum;
use App\ValueObject\ConsumerCode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Test\PhpServicesBundle\Faker\FactoryProcessor;
use Test\PhpServicesBundle\Faker\FakerGeneratorFactory;
use Symfony\Component\Uid\UuidV7;

class AppFixtures extends Fixture
{
    public function __construct(private FakerGeneratorFactory $factory)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $processor = $this->factory->init();
        $this->createConsumers($manager, $processor);
    }

    private function createConsumers(ObjectManager $manager, FactoryProcessor $processor): void
    {
        $entity = $processor->create(
            ConsumerEntity::class,
            [
                'code' => new ConsumerCode('pharma'),
                'name' => 'Аптеки',
            ]
        );
        $this->createPharmaShopGroups($entity, $processor);

        $manager->persist($entity);
        $manager->flush();
    }

    private function createPharmaShopGroups(ConsumerEntity $consumer, FactoryProcessor $processor): void
    {
        $shopGroups = [
            'region_1' => [
                'title' => 'Москва',
                'fiasId' => UuidV7::generate(),
                'shopsCodes' => [
                    '901185',
                    '901514',
                    '901887',
                    '901495',
                    '902077',
                    '903766',
                    '901466',
                    '902917',
                    '903714',
                    '902355',
                    '901842',
                    '904540',
                    '901184',
                    '901671',
                    '903916',
                    '901727',
                    '902919',
                    '902363',
                    '903913',
                    '901847',
                    '901399',
                    '901651',
                    '901560',
                    '901877',
                    '901496',
                    '904294',
                    '905355',
                    '902024',
                    '902376',
                    '903779',
                    '902341',
                    '901762',
                    '904035',
                    '905344',
                    '902342',
                ],
            ],
            'region_74014' => [
                'title' => 'Краснодар',
                'fiasId' => UuidV7::generate(),
                'shopsCodes' => [
                    '900023',
                    '900001',
                    '900002',
                    '900060',
                    '903871',
                    '582076',
                    '900022',
                    '900003',
                    '905249',
                    '900057',
                    '901177',
                    '901176',
                    '904059',
                    '900026',
                    '901175',
                    '901178',
                    '904020',
                    '900004',
                    '905240',
                ],
            ],
            'region_74265' => [
                'title' => 'Санкт-Петербург',
                'fiasId' => UuidV7::generate(),
                'shopsCodes' => [
                    '901899',
                    '902855',
                    '632546',
                    '902550',
                    '901471',
                    '903713',
                    '905312',
                    '904456',
                    '637887',
                    '632544',
                    '901908',
                    '902560',
                    '904438',
                    '611052',
                    '905182',
                    '632462',
                    '904466',
                    '632509',
                    '670823',
                    '074835',
                    '901893',
                    '632469',
                    '903712',
                    '904404',
                    '902459',
                    '905183',
                    '902294',
                    '905166',
                    '611065',
                ],
            ],
        ];

        foreach ($shopGroups as $code => $value) {
            $entity = $processor->create(
                ShopGroupEntity::class,
                [
                    'code' => PrefixEnum::SHOP_GROUP_PREFIX->appendPrefixIfNotExist($code),
                    'title' => $value['title'],
                    'fiasId' => $value['fiasId'],
                ]
            );
            $entity->addConsumer($consumer);
            array_map([$entity, 'addShop'], $value['shopsCodes']);
        }
    }
}
