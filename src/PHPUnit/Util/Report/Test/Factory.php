<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */









PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Factory for a test information tree.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
abstract class PHPUnit_Util_Report_Test_Factory
{
    /**
     * Creates a new test information tree.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @return PHPUnit_Util_Report_Test_Node_TestSuite
     * @access public
     * @static
     */
    public static function create(PHPUnit_Framework_TestResult $result)
    {
        $tests = self::getTests($result);
        $keys  = array_keys($tests);
        $root  = new PHPUnit_Util_Report_Test_Node_TestSuite($keys[0]);

        self::addTests($tests[$keys[0]], $root);

        return $root;
    }

    /**
     * @param  array                                    $tests
     * @param  PHPUnit_Util_Report_Test_Node_TestSuite $root
     * @access protected
     * @static
     */
    protected static function addTests(array $tests, PHPUnit_Util_Report_Test_Node_TestSuite $root)
    {
        foreach ($tests as $key => $value) {
            if (is_int($key)) {
                $root->addTest($value['name'], $value['object'], $value['result']);
            } else {
                $child = $root->addTestSuite($key);
                self::addTests($value, $child);
            }
        }

        return $root;
    }

    /**
     * @param  PHPUnit_Framework_TestResult $result
     * @param  PHPUnit_Framework_TestSuite  $testSuite
     * @return array
     * @access protected
     * @since  Method available since Release 3.0.0
     */
    protected static function getTests(PHPUnit_Framework_TestResult $result, PHPUnit_Framework_TestSuite $testSuite = NULL)
    {
        if ($testSuite === NULL) {
            $testSuite = $result->topTestSuite();
        }

        $tests = array();

        foreach ($testSuite->tests() as $test) {
            if ($test instanceof PHPUnit_Framework_TestSuite) {
                $tests = array_merge(
                  $tests,
                  self::getTests($result, $test)
                );
            } else {
                $testName = PHPUnit_Util_Test::describe($test, FALSE);

                $tests[] = array(
                  'name'   => $testName[1],
                  'object' => $test,
                  'result' => PHPUnit_Util_Test::lookupResult($test, $result)
                );
            }
        }

        return array($testSuite->getName() => $tests);
    }
}
?>
