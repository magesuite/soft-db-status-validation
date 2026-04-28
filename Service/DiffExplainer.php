<?php

declare(strict_types=1);

namespace MageSuite\SoftDbStatusValidation\Service;

class DiffExplainer
{
    public function getReasons(\Magento\Framework\Setup\Declaration\Schema\Diff\Diff $diff): array
    {
        $reasons = [];

        /** @var array{operation_name: string, element: \Magento\Framework\Setup\Declaration\Schema\ElementHistory}[] $changes */
        $changes = $this->getDifferencesList($diff);
        foreach ($changes as $change) {
            $reasons[] = $this->explainElement($change['element'], $change['operation_name']);
        }

        return $reasons;
    }

    public function explainElement(
        \Magento\Framework\Setup\Declaration\Schema\ElementHistory $element,
        string $operationName
    ): string {
        $isDrop = !str_starts_with($operationName, 'drop_');
        $diff = $isDrop ? ($element->getNew() ?? $element->getOld()) : ($element->getOld() ?? $element->getNew());

        if (!$diff) {
            return sprintf('Unable to explain operation %s because diff element is empty', $operationName);
        }

        return match (true) {
            $diff instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Column => sprintf(
                "Column '%s' %s in %s table or it has wrong definition. It should be %s.",
                $diff->getName(),
                $isDrop ? "doesn't exist" : "is not declared",
                $diff->getTable()?->getName() ?? 'UNKNOWN',
                $diff->getType()
            ),

            $diff instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference => sprintf(
                "%s '%s' %s for column %s in %s table",
                $isDrop ? "Missing" : "Undeclared",
                $diff->getName(),
                $diff->getElementType(),
                $diff->getColumn()?->getName() ?? 'UNKNOWN',
                $diff->getTable()?->getName() ?? 'UNKNOWN',
            ),

            $diff instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal => sprintf(
                "%s '%s' %s for columns %s in %s table",
                $isDrop ? "Missing" : "Undeclared",
                $diff->getName(),
                $diff->getElementType(),
                \implode(', ', $diff->getColumnNames()),
                $diff->getTable()?->getName() ?? 'UNKNOWN',
            ),

            $diff instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Constraint => sprintf(
                "%s '%s' %s in %s table",
                $isDrop ? "Missing" : "Undeclared",
                $diff->getName(),
                $diff->getElementType(),
                $diff->getTable()?->getName() ?? 'UNKNOWN',
            ),

            $diff instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Index,
            $diff instanceof \Magento\Framework\Setup\Declaration\Schema\Dto\Table => sprintf(
                "%s '%s' %s",
                $isDrop ? "Missing" : "Undeclared",
                $diff->getName(),
                $diff->getType(),
            ),

            default => "Something is wrong with {$diff->getType()} with name '{$diff->getName()}'",
        };

    }

    protected function getDifferencesList(\Magento\Framework\Setup\Declaration\Schema\Diff\Diff $diff): array
    {
        $changes = [];
        $differences = $this->flattenDifferences($diff->getAll());

        foreach ($differences as $operationName => $difference) {
            foreach ($difference as $change) {
                $changes[] = [
                    'operation_name' => $operationName,
                    'element' => array_first($change),
                ];
            }
        }

        return $changes;
    }

    protected function flattenDifferences(array $differences): array
    {
        $result = [];

        foreach ($differences as $items) {
            foreach ($items as $key => $value) {
                if (isset($result[$key])) {
                    $result[$key][] = $value;
                    continue;
                }

                $result[$key] = [$value];
            }
        }

        return $result;
    }
}
