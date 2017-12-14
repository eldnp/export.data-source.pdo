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
use Eldnp\Export\DataSource\Pdo\Exception\RuntimeException;
use Eldnp\Export\DataSource\Pdo\PaginatedDataSource;
use PHPUnit\Framework\TestCase;

/**
 * Class PaginatedDataSourceTest
 *
 * @package EldnpTest\Export\DataSource\Pdo
 */
class PaginatedDataSourceTest extends TestCase
{
    const TEST_ROWS_COUNT = 100;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @param string $query
     *
     * @throws \RuntimeException
     */
    private function executeQuery($query)
    {
        if (false === $this->pdo->exec($query)) {
            throw new \RuntimeException(sprintf(
                'An error occurred while running the request. Error: %s. Query: %s',
                implode(', ', $this->pdo->errorInfo()),
                $query
            ));
        }
    }

    protected function setUp()
    {
        parent::setUp();
        $this->pdo = new \PDO("sqlite::memory:");
        $this->executeQuery(<<< SQL
create table awesome_table
(
  id INT not null primary key,
  field_one INT not null,
  field_two INT
)
SQL
        );
        $maxRows = self::TEST_ROWS_COUNT;
        for ($i = 1; $i <= $maxRows; $i++) {
            $this->executeQuery(
                'insert into awesome_table (id, field_one, field_two) values ' .
                sprintf('(%d, %d, %s)', $i, rand(0, $maxRows), rand(0, 10) > 3 ? rand(0, $maxRows) : 'null')
            );
        }
    }

    private function buildDataSource(
        $table = 'awesome_table',
        $leftPlaceholder = 'leftBorder',
        $rightPlacehodler = 'rightBorder',
        $batchSize = 10
    ) {
        return new PaginatedDataSource(
            $this->pdo,
            "select * from {$table} where id > :leftBorder and id <= :rightBorder order by id asc",
            $leftPlaceholder,
            $rightPlacehodler,
            $batchSize
        );
    }

    public function testPaginatedDataSource()
    {
        $dataSource = $this->buildDataSource();
        $counter = 0;
        foreach ($dataSource as $key => $item) {
            $counter++;
        }
        $this->assertEquals(self::TEST_ROWS_COUNT, $counter);
    }

    /**
     * @expectedException LogicException
     */
    public function testNegativeBatchSizeException()
    {
        $this->buildDataSource('awesome_table', 'leftBorder', 'rightBorder', 0);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testPrepareQueryException()
    {
        $dataSource = $this->buildDataSource('undefined_table');
        $dataSource->rewind();
    }


    /**
     * @expectedException RuntimeException
     */
    public function testExecuteQueryException()
    {
        $dataSource = $this->buildDataSource('awesome_table', 'undefinedLeftPlaceholder');
        $dataSource->rewind();
    }

    /**
     * @expectedException LogicException
     */
    public function testRewindNotAllowedException()
    {
        $dataSource = $this->buildDataSource();
        $dataSource->rewind();
        $dataSource->rewind();
    }
}
