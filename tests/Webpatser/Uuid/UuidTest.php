<?php

use Webpatser\Uuid\Uuid;

class UuidTest extends PHPUnit_Framework_TestCase {

    public function testStaticGeneration()
    {
        // Arrange
        $uuid = Uuid::generate(1);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);
    }

    public function testGenerationOfVersionOne()
    {
        // Arrange
        $uuid = Uuid::generate(1);
        $this->assertRegExp('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', (string) $uuid);
    }

    public function testCorrectVersionUuid()
    {
        $uuidOne = Uuid::generate(1);
        $this->assertEquals(1, $uuidOne->version);

        $uuidThree = Uuid::generate(3,'test', Uuid::nsDNS);;
        $this->assertEquals(3, $uuidThree->version);

        $uuidFour = Uuid::generate(4);
        $this->assertEquals(4, $uuidFour->version);

        $uuidFive = Uuid::generate(5,'test', Uuid::nsDNS);;
        $this->assertEquals(5, $uuidFive->version);
    }

    public function testCorrectVersionOfImportedUuid()
    {
        $uuidOne = Uuid::generate(1);
        $importedOne = Uuid::import((string) $uuidOne);
        $this->assertEquals($uuidOne->version, $importedOne->version);

        $uuidThree = Uuid::generate(3,'test', Uuid::nsDNS);;
        $importedThree = Uuid::import((string) $uuidThree);
        $this->assertEquals($uuidThree->version, $importedThree->version);

        $uuidFour = Uuid::generate(4);
        $importeFour = Uuid::import((string) $uuidFour);
        $this->assertEquals($uuidFour->version, $importeFour->version);

        $uuidFive = Uuid::generate(5,'test', Uuid::nsDNS);;
        $importedFive = Uuid::import((string) $uuidFive);
        $this->assertEquals($uuidFive->version, $importedFive->version);
    }

    public function testCorrectTimeOfImportedUuid()
    {
        $uuidOne = Uuid::generate(1);
        $importedOne = Uuid::import((string) $uuidOne);
        $this->assertEquals($uuidOne->time, $importedOne->time);

        $uuidThree = Uuid::generate(3,'test', Uuid::nsDNS);;
        $importedThree = Uuid::import((string) $uuidThree);
        $this->assertEmpty($importedThree->time);

        $uuidFour = Uuid::generate(4);
        $importeFour = Uuid::import((string) $uuidFour);
        $this->assertEmpty($importeFour->time);

        $uuidFive = Uuid::generate(5,'test', Uuid::nsDNS);;
        $importedFive = Uuid::import((string) $uuidFive);
        $this->assertEmpty($importedFive->time);

    }
}
