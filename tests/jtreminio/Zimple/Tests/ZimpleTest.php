<?php

namespace jtreminio\Zimple\Tests;

use jtreminio\Zimple\Zimple as Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Override service container
     *
     * @param string $name    Name of service to replace: "\Namespace\Component\UserIdentity"
     * @param mixed  $service Value to replace service with - usually a PHPUnit mock object
     * @param bool   $final   Whether to mark service as final, prevent overrides
     * @return self
     */
    protected final function setService($name, $service, $final = true)
    {
        Container::set($name, $service, $final);

        return $this;
    }

    public function tearDown()
    {
        Container::clear();
    }

    public function testGetReturnsService()
    {
        $uniqId = uniqid();
        $string = 'it works!';

        $this->setService($uniqId, $string);

        $this->assertEquals(
            $string,
            Container::get($uniqId),
            "Expected \$container::get() to return {$string}"
        );
    }

    public function testGetReturnsServiceWithConstructorInjection()
    {
        $date = '2000-01-01';

        /** @var $date \DateTime */
        $date = Container::get('\DateTime', array($date));

        $expectedResult = '946706400';

        $this->assertEquals(
            $expectedResult,
            $date->getTimestamp(),
            'Returned timestamp did not match expected'
        );
    }

    public function testGetReturnsInstantiatedObjectWhenNotADefinedService()
    {
        $stdClass = Container::get('\stdClass');

        $this->assertInstanceOf(
            '\stdClass',
            $stdClass,
            'Expected result to be instance of stdClass'
        );
    }

    public function testGetReturnsInstantiatedObjectWithConstructorParametersWhenNotADefinedService()
    {
        /** @var $date \DateTime */
        $date = Container::get('\DateTime', array('2000-01-01'));

        $expectedResult = '946706400';

        $this->assertEquals(
            $expectedResult,
            $date->getTimestamp(),
            'Returned timestamp did not match expected'
        );
    }

    public function testGetReturnsNewObjectsWhenParametersAreDifferent()
    {
        /** @var $date1 \DateTime */
        $date1 = Container::get('\DateTime', array('2000-01-01'));

        /** @var $date2 \DateTime */
        $date2 = Container::get('\DateTime', array('2005-01-01'));

        /** @var $date3 \DateTime */
        $date3 = Container::get('\DateTime', array('2013-01-01'));

        $expectedResult1 = '946706400';
        $expectedResult2 = '1104559200';
        $expectedResult3 = '1357020000';

        $this->assertEquals(
            $expectedResult1,
            $date1->getTimestamp(),
            'Returned timestamp did not match expected for date1'
        );

        $this->assertEquals(
            $expectedResult2,
            $date2->getTimestamp(),
            'Returned timestamp did not match expected for date2'
        );

        $this->assertEquals(
            $expectedResult3,
            $date3->getTimestamp(),
            'Returned timestamp did not match expected for date3'
        );
    }

    public function testSetOverridesExistingServiceWithNew()
    {
        /** @var $date1 \DateTime */
        $date1 = Container::get('\DateTime', array('2000-01-01'));

        $dateOverride = new \DateTime();

        /** @var $date2 \DateTime */
        $this->setService('\DateTime', $dateOverride);

        $date2 = Container::get('\DateTime');

        $expectedResult1 = '946706400';

        $this->assertEquals(
            $expectedResult1,
            $date1->getTimestamp(),
            'Returned timestamp did not match expected for date1'
        );

        $this->assertEquals(
            $dateOverride->getTimestamp(),
            $date2->getTimestamp(),
            'Returned timestamp did not match expected for date2'
        );
    }

    public function testSetPreventsOverrideOfExistingServiceWhenFlagged()
    {
        /** @var $date1 \DateTime */
        $date1 = Container::get('\DateTime', array('2000-01-01'));

        $dateOverride = new \DateTime('2005-01-01');

        /** @var $date2 \DateTime */
        Container::set('\DateTime', $dateOverride, true);

        $date2 = Container::get('\DateTime');

        $date3 = Container::get('\DateTime', array('2000-01-01'));

        $this->assertSame(
            $date2,
            $date3,
            'Expecting ::set() to prevent overriding existing service when flagged to prevent overrides'
        );
    }

    public function testGetReturnsPredefinedServiceFromPimple()
    {
        Container::clear();

        $pimple = new \Pimple;

        $pimple['FooBarDateTime'] = function () {
            return new \DateTime();
        };

        Container::setPimple($pimple);

        $this->assertInstanceOf(
            '\DateTime',
            Container::get('FooBarDateTime'),
            'Expecting ::get() to return a pre-defined Pimple service'
        );
    }

    public function testGetReturnsSameInstanceWhenDefinedAsFinal()
    {
        /** @var $date1 \DateTime */
        $date1 = Container::get('\DateTime', array('2000-01-01'), true);

        $date2 = Container::get('\DateTime', array('2011-11-11'));

        $this->assertSame(
            $date1,
            $date2,
            'Expecting ::get() to return the same instance of \DateTime when final flag is set to true'
        );
    }
}
