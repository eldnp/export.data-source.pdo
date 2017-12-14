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

namespace Eldnp\Export\DataSource\Pdo;

use Eldnp\Export\DataSource\Pdo\Exception\LogicException;
use Eldnp\Export\Map\AbstractMapDataSource;

/**
 * Class PdoStatementDataSource
 *
 * @package Eldnp\Export\DataSource\Pdo
 */
class StatementDataSource extends AbstractMapDataSource
{
    /**
     * @var \PDOStatement
     */
    private $statement;

    /**
     * @var mixed|false
     */
    private $rawData;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var bool
     */
    private $allowRewind = true;

    /**
     * PdoStatementDataSource constructor.
     *
     * @param \PDOStatement $statement
     */
    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @inheritdoc
     */
    public function currentMap()
    {
        return $this->rawData;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->rawData = $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->counter;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        if ($valid = false !== $this->rawData) {
            $this->counter++;
        }
        return  $valid;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        if (!$this->allowRewind) {
            throw new LogicException('rewind not supported');
        }

        $this->allowRewind = false;
        $this->next();
    }
}
