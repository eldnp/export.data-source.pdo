<?php
/*
 * This file is part of Eldnp/export.data-source.pdo.
 *
 * Eldnp/export.data-source.pdo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Eldnp/export.data-source.pdo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Eldnp/export.data-source.pdo. If not, see <http://www.gnu.org/licenses/>.
 *
 * @see       https://github.com/eldnp/export.data-source.pdo for the canonical source repository
 * @copyright Copyright (c) 2017 Oleg Verevskoy <verevskoy@gmail.com>
 * @license   https://github.com/eldnp/export.data-source.pdo/blob/master/LICENSE GNU GENERAL PUBLIC LICENSE Version 3
 */

namespace EldnpTest\Export\DataSource\Pdo;

use Eldnp\Export\DataSource\Pdo\Exception\LogicException;
use Eldnp\Export\DataSource\Pdo\PdoStatementDataSource;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * Class PdoStatementDataSourceTest
 *
 * @package EldnpTest\Export\DataSource\Pdo
 */
class PdoStatementDataSourceTest extends TestCase
{
    /**
     * @param int $elementsCount
     * @return \PDOStatement
     */
    private function createStatement($elementsCount)
    {
        $prophecy = $this->prophesize('\PDOStatement');
        $prophecy
            ->__call('fetch', array(Argument::any()))
            ->will(new Generator($elementsCount))
            ->shouldBeCalled()
        ;
        /** @var \PDOStatement $statement */
        $statement = $prophecy->reveal();
        return $statement;
    }

    public function currentDataProvider()
    {
        return array(
            array($this->createStatement(0), 0),
            array($this->createStatement(1), 1),
            array($this->createStatement(2), 2),
            array($this->createStatement(3), 3),
        );
    }

    /**
     * @dataProvider currentDataProvider
     *
     * @param \PDOStatement $statement
     * @param $expectedElementsCount
     */
    public function testCurrent(\PDOStatement $statement, $expectedElementsCount)
    {
        $dataSource = new PdoStatementDataSource($statement);
        $objectsCounter = 0;
        foreach ($dataSource as $key => $value) {
            $objectsCounter++;
        }
        $this->assertEquals($expectedElementsCount, $objectsCounter);
    }

    /**
     * @expectedException LogicException
     */
    public function testCurrentExceptionIfRewind()
    {
        $dataSource = new PdoStatementDataSource($this->createStatement(0));
        $dataSource->rewind();
        while ($dataSource->valid()) {
            $dataSource->next();
        }
        $dataSource->rewind();
    }
}
