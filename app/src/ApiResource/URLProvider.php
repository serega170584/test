<?php

declare(strict_types=1);

namespace App\ApiResource;

use App\DTO\ApiRequest\Address;
use App\DTO\ApiRequest\AddressLevel;
use App\DTO\ApiRequest\AddressShop;
use App\DTO\ApiRequest\AddressToAddress;
use App\DTO\ApiRequest\Shop;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class URLProvider
{
    public function __construct(
        private ClientInterface $client
    ) {
    }

    /**
     * @return AddressLevel[]
     */
    public function getAddressLevels(int $offset, int $limit): array
    {
        $response = $this->request('GET', 'addresses/all_address_levels', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        $responseAddressLevels = $response->getBody()->getContents();
        $responseAddressLevels = json_decode($responseAddressLevels, true);
        $responseAddressLevels = $responseAddressLevels['address_levels'] ?? null;
        if (null === $responseAddressLevels) {
            throw new \Exception("Field address levels doesn't exist");
        }

        $addressLevels = [];
        foreach ($responseAddressLevels as $responseAddressLevel) {
            $id = $responseAddressLevel['id'] ?? null;
            if (null === $id) {
                throw new \Exception('Address level id field is invalid');
            }

            $name = $responseAddressLevel['name'] ?? null;
            if (null === $name) {
                throw new \Exception('Address level name field is invalid');
            }

            $shortName = $responseAddressLevel['short_name'] ?? null;
            if (null === $shortName) {
                throw new \Exception('Address level short name field is invalid');
            }

            $level = $responseAddressLevel['level'] ?? null;
            if (null === $level) {
                throw new \Exception('Address level level field is invalid');
            }

            $addressLevel = new AddressLevel((int) $id, $name, $shortName, (int) $level);
            $addressLevels[] = $addressLevel;
        }

        return $addressLevels;
    }

    /**
     * @return Address[]
     */
    public function getAddresses(int $offset, int $limit): array
    {
        $response = $this->request('GET', 'addresses/all', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        $responseAddresses = $response->getBody()->getContents();
        $responseAddresses = json_decode($responseAddresses, true);
        $responseAddresses = $responseAddresses['addresses'] ?? null;
        if (null === $responseAddresses) {
            throw new \Exception("Field addresses doesn't exist");
        }

        $addresses = [];
        foreach ($responseAddresses as $responseAddress) {
            $id = $responseAddress['id'] ?? null;
            if (null === $id) {
                throw new \Exception('Address id field is invalid');
            }

            $name = $responseAddress['name'] ?? null;
            if (null === $name) {
                throw new \Exception('Address name field is invalid');
            }

            $fullName = $responseAddress['fullName'] ?? null;
            if (null === $fullName) {
                throw new \Exception('Address full name field is invalid');
            }

            $parentFullName = $responseAddress['parent_full_name'] ?? null;
            if (null === $parentFullName) {
                throw new \Exception('Address parent full name field is invalid');
            }

            $firstLevelParent = $responseAddress['first_level_parent'] ?? null;
            if (null === $firstLevelParent) {
                throw new \Exception('Address first level parent field is invalid');
            }

            $active = $responseAddress['active'] ?? null;
            if (null === $active) {
                throw new \Exception('Address active field is invalid');
            }

            $display = $responseAddress['display'] ?? null;
            if (null === $display) {
                throw new \Exception('Address display field is invalid');
            }

            $parentId = $responseAddress['parent_id'] ?? null;

            $levelId = $responseAddress['levelId'] ?? null;
            if (null === $levelId) {
                throw new \Exception('Address level id field is invalid');
            }

            $guid = $responseAddress['guid'] ?? null;
            if (null === $guid) {
                throw new \Exception('Address guid field is invalid');
            }

            $address = new Address((int) $id, $name, $fullName, $parentFullName, $firstLevelParent, (bool) $active, (bool) $display, $parentId, (int) $levelId, $guid);
            $addresses[] = $address;
        }

        return $addresses;
    }

    /**
     * @return Shop[]
     */
    public function getShops(int $offset, int $limit): array
    {
        $response = $this->request('GET', 'catalog/all_stores', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        $responseShops = $response->getBody()->getContents();
        $responseShops = json_decode($responseShops, true);
        $responseShops = $responseShops['stores'] ?? null;
        if (null === $responseShops) {
            throw new \Exception("Field stores doesn't exist");
        }

        $shops = [];
        foreach ($responseShops as $responseShop) {
            $id = $responseShop['id'] ?? null;
            if (null === $id) {
                throw new \Exception('Shop id field is invalid');
            }

            $xmlId = $responseShop['xml_id'] ?? null;
            if (null === $xmlId) {
                throw new \Exception('Shop xml id field is invalid');
            }

            $shop = new Shop((int) $id, $xmlId);
            $shops[] = $shop;
        }

        return $shops;
    }

    /**
     * @return AddressShop[]
     */
    public function getAddressShops(int $offset, int $limit): array
    {
        $response = $this->request('GET', 'catalog/all_stores_link', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        $responseAddressShops = $response->getBody()->getContents();
        $responseAddressShops = json_decode($responseAddressShops, true);
        $responseAddressShops = $responseAddressShops['stores'] ?? null;
        if (null === $responseAddressShops) {
            throw new \Exception("Field stores doesn't exist");
        }

        $addressShops = [];
        foreach ($responseAddressShops as $responseAddressShop) {
            $addressId = $responseAddressShop['address_id'] ?? null;
            if (null === $addressId) {
                throw new \Exception('Address id field is invalid');
            }

            $shopId = $responseAddressShop['store_id'] ?? null;
            if (null === $shopId) {
                throw new \Exception('shop id field is invalid');
            }

            if (0 === $shopId || 0 === $addressId) {
                continue;
            }

            $addressShop = new AddressShop((int) $addressId, (int) $shopId);
            $addressShops[] = $addressShop;
        }

        return $addressShops;
    }

    /**
     * @return AddressToAddress[]
     */
    public function getAddressToAddresses(int $offset, int $limit): array
    {
        $response = $this->request('GET', 'addresses/all_addresses_link', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        $responseAddressToAddresses = $response->getBody()->getContents();
        $responseAddressToAddresses = json_decode($responseAddressToAddresses, true);
        $responseAddressToAddresses = $responseAddressToAddresses['addresses_link'] ?? null;
        if (null === $responseAddressToAddresses) {
            throw new \Exception("Field stores doesn't exist");
        }

        $addressToAddresses = [];
        foreach ($responseAddressToAddresses as $responseAddressToAddress) {
            $addressId = $responseAddressToAddress['address_id'] ?? null;
            if (null === $addressId) {
                throw new \Exception('Address id field is invalid');
            }

            $parentId = $responseAddressToAddress['parent_id'] ?? null;
            if (null === $parentId) {
                throw new \Exception('Parent id field is invalid');
            }

            $depth = $responseAddressToAddress['depth'] ?? null;
            if (null === $depth) {
                throw new \Exception('Depth field is invalid');
            }

            $addressToAddress = new AddressToAddress((int) $addressId, (int) $parentId, (int) $depth);
            $addressToAddresses[] = $addressToAddress;
        }

        return $addressToAddresses;
    }

    public function getAddressLevelCount(): int
    {
        $response = $this->request('GET', 'addresses/levels_count');

        $count = $response->getBody()->getContents();
        $count = json_decode($count, true);
        $count = $count['count'] ?? null;

        if (null === $count) {
            throw new \Exception('Invalid field count');
        }

        return $count;
    }

    public function getAddressCount(): int
    {
        $response = $this->request('GET', 'addresses/count');

        $count = $response->getBody()->getContents();
        $count = json_decode($count, true);
        $count = $count['count'] ?? null;

        if (null === $count) {
            throw new \Exception('Invalid address field count');
        }

        return $count;
    }

    public function getShopCount(): int
    {
        $response = $this->request('GET', 'catalog/all_stores_count');

        $count = $response->getBody()->getContents();
        $count = json_decode($count, true);
        $count = $count['count'] ?? null;

        if (null === $count) {
            throw new \Exception('Invalid shop field count');
        }

        return $count;
    }

    public function getAddressToShopCount(): int
    {
        $response = $this->request('GET', 'catalog/all_stores_link_count');

        $count = $response->getBody()->getContents();
        $count = json_decode($count, true);
        $count = $count['count'] ?? null;

        if (null === $count) {
            throw new \Exception('Invalid stores link field count');
        }

        return $count;
    }

    public function getAddressToAddressCount(): int
    {
        $response = $this->request('GET', 'addresses/addresses_link_count');

        $count = $response->getBody()->getContents();
        $count = json_decode($count, true);
        $count = $count['count'] ?? null;

        if (null === $count) {
            throw new \Exception('Invalid addresses link field count');
        }

        return $count;
    }

    /**
     * @throws GuzzleException
     */
    private function request(string $method, string $path, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $path, $options);
    }
}
