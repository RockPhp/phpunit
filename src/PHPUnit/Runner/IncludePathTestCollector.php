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
 * @since      File available since Release 2.1.0
 */





PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * An implementation of a TestCollector that consults the
 * include path set in the php.ini.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */

class PHPUnit_Runner_IncludePathTestCollector implements PHPUnit_Runner_TestCollector
{
    /**
     * @var    string
     * @access private
     */
    private $filterIterator = NULL;

    /**
     * @return array
     * @access public
     */
    public function collectTests()
    {
        $includePathsIterator = new AppendIterator;
        $result = array();

        $includePaths = PHPUnit_Util_Fileloader::getIncludePaths();

        foreach ($includePaths as $includePath) {
            $includePathsIterator->append(
              new RecursiveIteratorIterator(
                  new RecursiveDirectoryIterator($includePath)
              )
            );
        }

        $filterIterator = new PHPUnit_Util_FilterIterator(
          $includePathsIterator
        );

        if ($this->filterIterator !== NULL) {
            $class          = new ReflectionClass($this->filterIterator);
            $filterIterator = $class->newInstance($filterIterator);
        }

        foreach ($filterIterator as $file) {
            $result[] = $file;
        }

        return $result;
    }

    /**
     * Adds a FilterIterator to filter the source files to be collected.
     *
     * @param  string $filterIterator
     * @throws InvalidArgumentException
     * @access public
     */
    public function setFilterIterator($filterIterator)
    {
        if (is_string($filterIterator) && class_exists($filterIterator, FALSE)) {
            try {
                $class = new ReflectionClass($filterIterator);

                if ($class->isSubclassOf('FilterIterator')) {
                    $this->filterIterator = $filterIterator;
                }
            }

            catch (ReflectionException $e) {
                throw new InvalidArgumentException;
            }
        } else {
            throw new InvalidArgumentException;
        }
    }
}
?>
