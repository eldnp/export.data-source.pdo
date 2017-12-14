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
use Eldnp\Export\DataSource\Pdo\StatementDataSource;
use PHPUnit\Framework\TestCase;

/**
 * Class PdoStatementDataSourceTest
 *
 * @package EldnpTest\Export\DataSource\Pdo
 */
class StatementDataSourceTest extends TestCase
{
    public function currentDataProvider()
    {
        $query = 'select * from awesome_table';
        return array(
            array(FixturePdoFactory::factory(0)->query($query), 0),
            array(FixturePdoFactory::factory(10)->query($query), 10),
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
        $dataSource = new StatementDataSource($statement);
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
        $dataSource = new StatementDataSource(FixturePdoFactory::factory(0)->query('select 1 where 1 != 1'));
        $dataSource->rewind();
        $dataSource->rewind();
    }
}
