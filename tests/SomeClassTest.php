<?php
/**
 * SomeClass test case.
 */
class SomeClassTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var SomeClass
     */
    private $someClass;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated SomeClassTest::setUp()

        $this->someClass = new SomeClass(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated SomeClassTest::tearDown()
        $this->someClass = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests SomeClass->soma()
     */
    public function testSoma()
    {
        $resultado = $this->someClass->soma(2, 2);
        $this->assertEquals(4, $resultado);
    }

    /**
     * Tests SomeClass->subtrai()
     */
    public function testSubtrai()
    {
        $resultado = $this->someClass->subtrai(10, 5);
        $this->assertEquals(5, $resultado);
    }
}

