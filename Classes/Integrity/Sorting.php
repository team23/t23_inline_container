<?php

declare(strict_types=1);

namespace Team23\T23InlineContainer\Integrity;

class Sorting extends \B13\Container\Integrity\Sorting
{
    public function runForSingleContainer($containerRecord, $cType)
    {
        $columns = $this->tcaRegistry->getAvailableColumns($cType);
        $colPosByCType[$cType] = [];
        foreach ($columns as $column) {
            $colPosByCType[$cType][] = $column['colPos'];
        }
        $this->unsetContentDefenderConfiguration($cType);
        $this->fixChildrenSorting([$containerRecord], $colPosByCType, false);
    }
}