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

/**
 * Class FixturePdoFactory
 *
 * @package EldnpTest\Export\DataSource\Pdo
 */
class FixturePdoFactory
{
    /**
     * @param \PDO $pdo
     * @param string $query
     *
     * @throws \RuntimeException
     */
    private static function executeQuery(\PDO $pdo, $query)
    {
        if (false === $pdo->exec($query)) {
            throw new \RuntimeException(sprintf(
                'An error occurred while running the request. Error: %s. Query: %s',
                implode(', ', $pdo->errorInfo()),
                $query
            ));
        }
    }

    /**
     * @param \PDO $pdo
     */
    private static function createScheme(\PDO $pdo)
    {
        self::executeQuery($pdo, <<< SQL
create table awesome_table
(
  id INT not null primary key,
  field_one INT not null,
  field_two INT
)
SQL
        );
    }

    /**
     * @param \PDO $pdo
     * @param int $numRows
     */
    private static function populateAwesomeTable(\PDO $pdo, $numRows)
    {
        for ($i = 1; $i <= $numRows; $i++) {
            $query = 'insert into awesome_table (id, field_one, field_two) values '
                . sprintf('(%d, %d, %s)', $i, rand(0, $numRows), rand(0, 10) > 3 ? rand(0, $numRows) : 'null')
            ;
            self::executeQuery($pdo, $query);
        }
    }

    /**
     * @param int $numRows
     *
     * @return \PDO
     */
    public static function factory($numRows)
    {
        $pdo = new \PDO("sqlite::memory:");
        self::createScheme($pdo);
        self::populateAwesomeTable($pdo, $numRows);
        return $pdo;
    }
}