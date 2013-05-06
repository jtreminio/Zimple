<?php

namespace jtreminio\Zimple\Tests;

use jtreminio\Zimple\Zimple;

class ZimpleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Zimple */
    public $zimple;

    public function setUp()
    {
        $this->zimple = new zimple;
    }

    public function tearDown()
    {
        $this->zimple->clear();
    }

    public function testGetInstantiatesNewObjectWithoutParameters()
    {
        $stdClass = $this->zimple->get('\stdClass');

        $this->assertInstanceOf(
            '\stdClass',
            $stdClass,
            'Expected result to be instance of stdClass'
        );
    }

    public function testGetInstantiatesNewObjectWithParameters()
    {
        $date = '2000-01-01';

        /** @var $date \DateTime */
        $date = $this->zimple->get('\DateTime', array($date));

        $expectedResult = '946706400';

        $this->assertEquals(
            $expectedResult,
            $date->getTimestamp(),
            'Returned timestamp did not match expected'
        );
    }

    public function testGetReturnsNewObjectsOnSubsequentCalls()
    {
        /** @var $date1 \DateTime */
        $date1 = $this->zimple->get('\DateTime', array('2000-01-01'));

        /** @var $date2 \DateTime */
        $date2 = $this->zimple->get('\DateTime', array('2005-01-01'));

        /** @var $date3 \DateTime */
        $date3 = $this->zimple->get('\DateTime', array('2013-01-01'));

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

    public function testTakesObjectAndStringAsValue()
    {
        $this->zimple->set('\DateTime', new \DateTime);
        $this->zimple->set('\DateTime2', '\DateTime');

        $this->assertInstanceOf(
            '\DateTime',
            $this->zimple->get('\DateTime'),
            'Expected \DateTime definition to return a DateTime object'
        );

        $this->assertInstanceOf(
            '\DateTime',
            $this->zimple->get('\DateTime2'),
            'Expected \DateTime2 definition to return a DateTime object'
        );
    }

    public function testGetReturnsPreviouslySetParameteredObjectWithNoGetParameters()
    {
        $this->zimple->set('\DateTime', '\DateTime', array('2000-01-01'));
        $this->zimple->set('foobar', '\DateTime', array('2020-01-01'));

        $date1 = $this->zimple->get('\DateTime');
        $date2 = $this->zimple->get('foobar');

        $this->assertEquals(
            '946706400',
            $date1->getTimestamp(),
            'Expected \DateTime to match previously defined'
        );

        $this->assertEquals(
            '1577858400',
            $date2->getTimestamp(),
            'Expected foobar to match previously defined'
        );
    }

    public function testGetWithParametersAllowsOverrideOfPreviouslySetDefinition()
    {
        $this->zimple->set('\DateTime', '\DateTime', array('2000-01-01'));

        $date = $this->zimple->get('\DateTime');
        $dateOverride = $this->zimple->get('\DateTime', array('2020-01-01'));

        $this->assertEquals(
            '946706400',
            $date->getTimestamp(),
            'Expected \DateTime to match previously defined'
        );

        $this->assertEquals(
            '1577858400',
            $dateOverride->getTimestamp(),
            'Expected get overrides existing definition with new parameters'
        );
    }

    public function testGetIgnoresParametersWhenDefinitionLocked()
    {
        $this->zimple->lock('date1', new \DateTime('2000-01-01'));
        $this->zimple->lock('date2', new \DateTime('2001-01-01'));
        $this->zimple->lock('date3', '\DateTime', array('2002-01-01'));

        $this->zimple->set('date1', new \DateTime('2000-01-01'));
        $this->zimple->set('date3', new \DateTime('2000-01-01'));

        $date1 = $this->zimple->get('date1');
        $date2 = $this->zimple->get('date2', array('2005-01-01'));
        $date3 = $this->zimple->get('date3');

        $this->assertEquals(
            '946706400',
            $date1->getTimestamp()
        );

        $this->assertEquals(
            '978328800',
            $date2->getTimestamp()
        );

        $this->assertEquals(
            '1009864800',
            $date3->getTimestamp()
        );
    }

    public function testSetReturnsDefinedValueAfterPreviousUninstantiatedCall()
    {
        /** @var $date1 \DateTime */
        $date1 = $this->zimple->get('\DateTime', array('2000-01-01'));

        $dateOverride = new \DateTime();

        /** @var $date2 \DateTime */
        $this->zimple->set('\DateTime', $dateOverride);

        $date2 = $this->zimple->get('\DateTime');

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
    public function testGetReturnsSameObjectWhenSetPassedLiteral()
    {
        $this->zimple->set('unique', uniqid());
        $uniqid1 = $this->zimple->get('unique');
        $uniqid2 = $this->zimple->get('unique');

        $this->assertSame(
            $uniqid1,
            $uniqid2,
            'Expected ::get() to return same value on multiple calls'
        );
    }

    public function testSetAcceptsObjectsAndClosures()
    {
        $this->zimple->set('DateTime1', new \DateTime('2013-05-06'));

        $this->zimple->set('DateTime2', function () {
            return new \DateTime('2005-01-01');
        });

        /** @var \DateTime $date1 */
        $date1 = $this->zimple->get('DateTime1');
        /** @var \DateTime $date2 */
        $date2 = $this->zimple->get('DateTime2');

        $expectedResult1 = '1367816400';
        $expectedResult2 = '1104559200';

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
    }
}
